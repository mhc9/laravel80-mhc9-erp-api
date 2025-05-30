<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Inspection;
use App\Models\InspectionDetail;
use App\Models\Order;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\Division;
use App\Models\Department;

class InspectionController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $deliver_no     = $req->get('deliver_no');
        $inspect_date   = $req->get('inspect_date');
        $supplier       = $req->get('supplier');
        $status         = $req->get('status');
        $year           = $req->get('year');

        $inspections = Inspection::with('details','details.item','details.unit')
                                ->with('supplier','supplier.tambon','supplier.amphur','supplier.changwat','supplier.bank')
                                ->with('order','order.requisition','order.requisition.category')
                                ->with('order.requisition.requester','order.requisition.requester.prefix')
                                ->with('order.requisition.requester.position','order.requisition.requester.level')
                                ->with('order.requisition.budgets.budget.type','order.requisition.budgets')
                                ->with('order.requisition.budgets.budget.activity','order.requisition.budgets.budget.activity.project')
                                ->with('order.requisition.budgets.budget.activity.project.plan')
                                // ->with('order.requisition.division','order.requisition.division.department')
                                // ->with('order.requisition.committees','order.requisition.committees.employee','order.requisition.committees.employee.prefix')
                                // ->with('order.requisition.committees.employee.position','order.requisition.committees.employee.level')
                                ->when(!empty($deliver_no), function($q) use ($deliver_no) {
                                    $q->where('deliver_no', 'like', '%'.$deliver_no.'%');
                                })
                                ->when(!empty($inspect_date), function($q) use ($inspect_date) {
                                    $q->where('inspect_date', $inspect_date);
                                })
                                ->when(!empty($supplier), function($q) use ($supplier) {
                                    $q->where('supplier_id', $supplier);
                                })
                                ->when(!empty($status), function($q) use ($status) {
                                    $q->where('status', $status);
                                })
                                ->when(!empty($year), function($q) use ($year) {
                                    $q->where('year', $year);
                                })
                                ->orderBy('inspect_date','DESC')
                                ->paginate(10);

        return $inspections;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $department = $req->get('department');
        $status     = $req->get('status');

        $inspections = Inspection::with('details','details.item','details.unit','supplier')
                        ->with('requisition','requisition.requester','requisition.requester.prefix')
                        ->with('requisition.requester.position','requisition.requester.level')
                        // ->when(!empty($department), function($q) use ($department) {
                        //     $q->where('department_id', $department);
                        // })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        ->get();

        return $inspections;
    }

    public function getById($id)
    {
        $inspection = Inspection::with('details','details.item','details.unit')
                                ->with('supplier','supplier.tambon','supplier.amphur','supplier.changwat','supplier.bank')
                                ->with('order','order.requisition','order.requisition.category')
                                ->with('order.requisition.requester','order.requisition.requester.prefix')
                                ->with('order.requisition.requester.position','order.requisition.requester.level')
                                ->with('order.requisition.budgets.budget.type','order.requisition.budgets')
                                ->with('order.requisition.budgets.budget.activity','order.requisition.budgets.budget.activity.project')
                                ->with('order.requisition.budgets.budget.activity.project.plan')
                                // ->with('order.requisition.division','order.requisition.division.department')
                                // ->with('order.requisition.committees','order.requisition.committees.employee','order.requisition.committees.employee.prefix')
                                // ->with('order.requisition.committees.employee.position','order.requisition.committees.employee.level')
                                ->find($id);

        return $inspection;
    }

    public function getInitialFormData()
    {
        return [
            'departments'   => Department::all(),
            'suppliers'     => Supplier::where('status', 1)->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $inspection = new Inspection();
            $inspection->inspect_date   = $req['inspect_date'];
            $inspection->deliver_no     = $req['deliver_no'];
            $inspection->deliver_date   = $req['deliver_date'];
            $inspection->report_no      = $req['report_no'];
            $inspection->report_date    = $req['report_date'];
            $inspection->order_id       = $req['order_id'];
            $inspection->supplier_id    = $req['supplier_id'];
            $inspection->year           = $req['year'];
            $inspection->item_count     = $req['item_count'];
            $inspection->item_received  = $req['item_received'];
            $inspection->total          = currencyToNumber($req['total']);
            $inspection->vat_rate       = currencyToNumber($req['vat_rate']);
            $inspection->vat            = currencyToNumber($req['vat']);
            $inspection->net_total      = currencyToNumber($req['net_total']);
            $inspection->status         = 1;

            if($inspection->save()) {
                foreach ($req['items'] as $item) {
                    $detail  = new InspectionDetail();
                    $detail->inspection_id      = $inspection->id;
                    $detail->order_detail_id    = $item['id'];
                    $detail->item_id            = $item['item_id'];
                    $detail->price              = $item['price'];
                    $detail->amount             = $item['amount'];
                    $detail->unit_id            = $item['unit_id'];
                    $detail->total              = $item['total'];
                    $detail->is_received        = array_key_exists('is_received', $item) ? $item['is_received'] : 0;
                    $detail->save();
                }

                /** อัพเดตสถานะของคำขอเป็น 2=ตรวจรับแล้ว */
                $order = Order::find($inspection->order_id);
                $order->status = 2;
                $order->save();

                /** อัพเดตสถานะของคำขอเป็น 5=ตรวจรับแล้ว */
                Requisition::where('id', $order->requisition_id)->update(['status' => 5]);

                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'inspection'    => $inspection
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function update(Request $req, $id)
    {
        try {
            $inspection = Inspection::find($id);
            $inspection->inspect_date   = $req['inspect_date'];
            $inspection->deliver_no     = $req['deliver_no'];
            $inspection->deliver_date   = $req['deliver_date'];
            $inspection->report_no      = $req['report_no'];
            $inspection->report_date    = $req['report_date'];
            // $inspection->order_id       = $req['order_id'];
            // $inspection->supplier_id    = $req['supplier_id'];
            $inspection->year           = $req['year'];
            $inspection->item_count     = $req['item_count'];
            $inspection->item_received  = $req['item_received'];
            $inspection->total          = currencyToNumber($req['total']);
            $inspection->vat_rate       = currencyToNumber($req['vat_rate']);
            $inspection->vat            = currencyToNumber($req['vat']);
            $inspection->net_total      = currencyToNumber($req['net_total']);
            $inspection->status         = 1;

            if($inspection->save()) {
                foreach ($req['items'] as $item) {
                    /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property updated หรือไม่ (รายการที่ต้องแก้ไข) */
                    if (array_key_exists('updated', $item) && $item['updated']) {
                        $detail  = InspectionDetail::find($item['id']);
                        // $detail->inspection_id      = $inspection->id;
                        // $detail->order_detail_id    = $item['id'];
                        // $detail->item_id            = $item['item_id'];
                        // $detail->price              = $item['price'];
                        // $detail->amount             = $item['amount'];
                        // $detail->unit_id            = $item['unit_id'];
                        // $detail->total              = $item['total'];
                        $detail->is_received        = $item['is_received'];
                        $detail->save();
                    }
                }

                return [
                    'status'        => 1,
                    'message'       => 'Updating successfully!!',
                    'inspection'    => $inspection
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function destroy(Request $req, $id)
    {
        try {
            $inspection = Inspection::find($id);

            if($inspection->delete()) {
                /** ลบรายการสินค้า/บริการของการตรวจรับ */
                InspectionDetail::where('inspection_id', $id)->delete();

                /** อัพเดตสถานะของคำขอเป็น 1=รอดำเนินการ */
                $order = Order::find($inspection->order_id);
                $order->status = 1;
                $order->save();

                /** อัพเดตสถานะของคำขอเป็น 4=จัดซื้อแล้ว */
                Requisition::where('id', $order->requisition_id)->update(['status' => 4]);

                return [
                    'status'     => 1,
                    'message'    => 'Deleting successfully!!',
                    'id'         => $id
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function getForm(Request $req, $id)
    {
        $inspection = Inspection::with('details','details.item','details.unit')
                                ->with('supplier','supplier.tambon','supplier.amphur','supplier.changwat','supplier.bank')
                                ->with('order','order.requisition','order.requisition.category')
                                ->with('order.requisition.requester','order.requisition.requester.prefix')
                                ->with('order.requisition.requester.position','order.requisition.requester.level')
                                ->with('order.requisition.budgets.budget.type','order.requisition.budgets')
                                ->with('order.requisition.budgets.budget.activity','order.requisition.budgets.budget.activity.project')
                                ->with('order.requisition.budgets.budget.activity.project.plan')
                                // ->with('order.requisition.division','order.requisition.division.department')
                                ->with('order.requisition.committees','order.requisition.committees.employee','order.requisition.committees.employee.prefix')
                                ->with('order.requisition.committees.employee.position','order.requisition.committees.employee.level')
                                ->with('order.requisition.approvals','order.requisition.approvals.procuring','order.requisition.approvals.supplier')
                                ->find($id);

        // $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
        //                     ->whereIn('id', Member::where('department_id', $requisition->division->department_id)->whereIn('duty_id', [2, 5])->pluck('employee_id'))
        //                     ->where('status', 1)
        //                     ->first();

        $template = 'form.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/inspections/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('deliverDate', convDbDateToLongThDate($inspection->deliver_date));
        /** ================================== HEADER ================================== */

        /** ================================== CONTENT ================================== */
        $word->setValue('objective', $inspection->order->requisition->order_type_id == 1 ? 'ซื้อ' . $inspection->order->requisition->category->name : $inspection->order->requisition->contract_desc);
        $word->setValue('itemCount', $inspection->item_count);

        $word->setValue('considerNo', $inspection->order->requisition->approvals[0]->consider_no);
        $word->setValue('considerDate', convDbDateToLongThDate($inspection->order->requisition->approvals[0]->consider_date));

        $word->setValue('supplier', $inspection->supplier->name);

        $word->setValue('netTotal', number_format($inspection->net_total));
        $word->setValue('netTotalText', baht_text($inspection->net_total));

        $cx = 1;
        $word->cloneRow('inspector', sizeof($inspection->order->requisition->committees));
        foreach($inspection->order->requisition->committees as $inspector => $committee) {
            $word->setValue('no#' . $cx, sizeof($inspection->order->requisition->committees) > 1 ? $cx . '.' : '');
            $word->setValue('inspector#' . $cx, $committee->employee->prefix->name.$committee->employee->firstname . ' ' . $committee->employee->lastname);
            $word->setValue('inspectorPosition#' . $cx, $committee->employee->position->name . ($committee->employee->level ? $committee->employee->level->name : ''));

            if (sizeof($inspection->order->requisition->committees) == 1) {
                $word->setValue('committee#' . $cx, 'ผู้ตรวจรับพัสดุ');
            } else {
                $word->setValue('committee#' . $cx, $cx == 1 ? 'ประธานกรรรมการฯ' : 'กรรรมการฯ');
            }
            $cx++;
        }
        /** ================================== CONTENT ================================== */

        /** ================================== SIGNATURE ================================== */
        // $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        // $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        // $word->setValue('headOfDepartRole', ($headOfDepart->memberOf[0]->duty_id == 2 ? 'หัวหน้า' : $headOfDepart->memberOf[0]->duty->name) . $requisition->division->department->name);
        /** ================================== SIGNATURE ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getReport(Request $req, $id)
    {
        $inspection = Inspection::with('details','details.item','details.unit')
                                ->with('supplier','supplier.tambon','supplier.amphur','supplier.changwat','supplier.bank')
                                ->with('order','order.requisition','order.requisition.category')
                                ->with('order.requisition.requester','order.requisition.requester.prefix')
                                ->with('order.requisition.requester.position','order.requisition.requester.level')
                                ->with('order.requisition.budgets.budget.type','order.requisition.budgets')
                                ->with('order.requisition.budgets.budget.activity','order.requisition.budgets.budget.activity.project')
                                ->with('order.requisition.budgets.budget.activity.project.plan')
                                // ->with('order.requisition.division','order.requisition.division.department')
                                ->with('order.requisition.committees','order.requisition.committees.employee','order.requisition.committees.employee.prefix')
                                ->with('order.requisition.committees.employee.position','order.requisition.committees.employee.level')
                                ->with('order.requisition.approvals','order.requisition.approvals.procuring','order.requisition.approvals.supplier')
                                ->find($id);

        // $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
        //                     ->whereIn('id', Member::where('department_id', $requisition->division->department_id)->whereIn('duty_id', [2, 5])->pluck('employee_id'))
        //                     ->where('status', 1)
        //                     ->first();

        $template = 'report.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/inspections/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('reportNo', $inspection->report_no);
        $word->setValue('reportDate', convDbDateToLongThDate($inspection->report_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        $word->setValue('orderType', $inspection->order->requisition->order_type_id == 1 ? 'ซื้อ' : 'จ้าง');
        $word->setValue('objective', $inspection->order->requisition->order_type_id == 1 ? 'ซื้อ' . $inspection->order->requisition->category->name : $inspection->order->requisition->contract_desc);
        $word->setValue('itemCount', $inspection->item_count);

        $word->setValue('considerNo', $inspection->order->requisition->approvals[0]->consider_no);
        $word->setValue('considerDate', convDbDateToLongThDate($inspection->order->requisition->approvals[0]->consider_date));

        $word->setValue('supplier', $inspection->supplier->name);

        $word->setValue('netTotal', number_format($inspection->net_total));
        $word->setValue('netTotalText', baht_text($inspection->net_total));

        $cx = 1;
        $word->cloneRow('inspector', sizeof($inspection->order->requisition->committees));
        foreach($inspection->order->requisition->committees as $inspector => $committee) {
            $word->setValue('no#' . $cx, sizeof($inspection->order->requisition->committees) > 1 ? $cx . '.' : '');
            $word->setValue('inspector#' . $cx, $committee->employee->prefix->name.$committee->employee->firstname . ' ' . $committee->employee->lastname);
            $word->setValue('inspectorPosition#' . $cx, $committee->employee->position->name . ($committee->employee->level ? $committee->employee->level->name : ''));

            if (sizeof($inspection->order->requisition->committees) == 1) {
                $word->setValue('committee#' . $cx, 'ผู้ตรวจรับพัสดุ');
            } else {
                $word->setValue('committee#' . $cx, $cx == 1 ? 'ประธานกรรรมการฯ' : 'กรรรมการฯ');
            }
            $cx++;
        }
        /** ================================== CONTENT ================================== */
        
        /** ================================== SIGNATURE ================================== */
        // $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        // $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        // $word->setValue('headOfDepartRole', ($headOfDepart->memberOf[0]->duty_id == 2 ? 'หัวหน้า' : $headOfDepart->memberOf[0]->duty->name) . $requisition->division->department->name);
        /** ================================== SIGNATURE ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }
}
