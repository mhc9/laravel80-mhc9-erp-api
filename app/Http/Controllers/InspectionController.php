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
use App\Models\Supplier;
use App\Models\Division;
use App\Models\Department;

class InspectionController extends Controller
{
    public function formValidate (Request $request)
    {
        $rules = [
            'name'          => 'required',
            'department_id' => 'required',
        ];

        $messages = [
            'name.required'             => 'กรุณาระบุชื่องาน',
            'department_id.required'    => 'กรุณาเลือกกลุ่มงาน',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $messageBag = $validator->getMessageBag();

            // if (!$messageBag->has('start_date')) {
            //     if ($this->isDateExistsValidation(convThDateToDbDate($request['start_date']), 'start_date') > 0) {
            //         $messageBag->add('start_date', 'คุณมีการลาในวันที่ระบุแล้ว');
            //     }
            // }

            return [
                'success' => 0,
                'errors' => $messageBag->toArray(),
            ];
        } else {
            return [
                'success' => 1,
                'errors' => $validator->getMessageBag()->toArray(),
            ];
        }
    }

    public function search(Request $req)
    {
        /** Get params from query string */
        $po_no          = $req->get('po_no');
        $inspect_date   = $req->get('inspect_date');
        $supplier       = $req->get('supplier');
        $status         = $req->get('status');

        $inspections = Inspection::with('details','details.item','details.unit')
                        ->with('supplier','supplier.tambon','supplier.amphur','supplier.changwat','supplier.bank')
                        ->with('order','order.requisition','order.requisition.category')
                        ->with('order.requisition.requester','order.requisition.requester.prefix')
                        ->with('order.requisition.requester.position','order.requisition.requester.level')
                        // ->with('requisition.budget','requisition.budget.project')
                        // ->with('requisition.budget.project.plan','requisition.project')
                        // ->with('requisition.requester.position','requisition.requester.level')
                        // ->with('requisition.division','requisition.division.department')
                        // ->with('requisition.committees','requisition.committees.employee','requisition.committees.employee.prefix')
                        // ->with('requisition.committees.employee.position','requisition.committees.employee.level')
                        ->when(!empty($po_no), function($q) use ($po_no) {
                            $q->where('po_no', 'like', '%'.$po_no.'%');
                        })
                        ->when(!empty($inspect_date), function($q) use ($inspect_date) {
                            $q->where('inspect_date', $inspect_date);
                        })
                        ->when(!empty($supplier), function($q) use ($supplier) {
                            $q->where('supplier_id', $supplier);
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
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
                        // ->with('requisition.budget','requisition.budget.project')
                        // ->with('requisition.budget.project.plan','requisition.project')
                        // ->with('requisition.requester.position','requisition.requester.level')
                        // ->with('requisition.division','requisition.division.department')
                        // ->with('requisition.committees','requisition.committees.employee','requisition.committees.employee.prefix')
                        // ->with('requisition.committees.employee.position','requisition.committees.employee.level')
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
            $inspection->item_count     = $req['item_count'];
            $inspection->item_received  = $req['item_received'];
            $inspection->total          = currencyToNumber($req['total']);
            $inspection->vat_rate       = currencyToNumber($req['vat_rate']);
            $inspection->vat            = currencyToNumber($req['vat']);
            $inspection->net_total      = currencyToNumber($req['net_total']);
            $inspection->year           = $req['year'];
            $inspection->status         = 1;

            if($inspection->save()) {
                foreach ($req['items'] as $item) {
                    $detail  = new InspectionDetail();
                    $detail->inspection_id  = $inspection->id;
                    $detail->order_detail_id = $item['id'];
                    $detail->item_id        = $item['item_id'];
                    $detail->price          = $item['price'];
                    $detail->amount         = $item['amount'];
                    $detail->unit_id        = $item['unit_id'];
                    $detail->total          = $item['total'];
                    $detail->is_received    = $item['is_received'];
                    $detail->save();

                    /** อัพเดตสถานะของคำขอเป็น 2=ตรวจรับแล้ว */
                    Order::where('id', $inspection->order_id)->update(['status' => 2]);
                }

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
            $inspection = new Inspection();
            $inspection->inspect_date   = $req['inspect_date'];
            $inspection->deliver_no     = $req['deliver_no'];
            $inspection->deliver_date   = $req['deliver_date'];
            $inspection->report_no      = $req['report_no'];
            $inspection->report_date    = $req['report_date'];
            $inspection->order_id       = $req['order_id'];
            $inspection->item_count     = $req['item_count'];
            $inspection->item_received  = $req['item_received'];
            $inspection->total          = currencyToNumber($req['total']);
            $inspection->vat_rate       = currencyToNumber($req['vat_rate']);
            $inspection->vat            = currencyToNumber($req['vat']);
            $inspection->net_total      = currencyToNumber($req['net_total']);
            $inspection->year           = $req['year'];
            $inspection->status         = 1;

            if($inspection->save()) {
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
}
