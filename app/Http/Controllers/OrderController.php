<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Supplier;
use App\Models\Requisition;
use App\Models\Division;
use App\Models\Department;

class OrderController extends Controller
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
        $po_no      = $req->get('po_no');
        $po_date    = $req->get('po_date');
        $supplier   = $req->get('supplier');
        $status     = $req->get('status');

        $orders = Order::with('details','details.item','details.unit','supplier')
                        ->with('supplier.tambon','supplier.amphur','supplier.changwat','supplier.bank')
                        ->with('requisition','requisition.requester','requisition.requester.prefix')
                        ->with('requisition.requester.position','requisition.requester.level')
                        ->with('requisition.category','requisition.budget','requisition.budget.project')
                        ->with('requisition.budget.project.plan','requisition.project')
                        ->with('requisition.requester.position','requisition.requester.level')
                        ->with('requisition.division','requisition.division.department')
                        ->with('requisition.committees','requisition.committees.employee','requisition.committees.employee.prefix')
                        ->with('requisition.committees.employee.position','requisition.committees.employee.level')
                        ->when(!empty($po_no), function($q) use ($po_no) {
                            $q->where('po_no', 'like', '%'.$po_no.'%');
                        })
                        ->when(!empty($po_date), function($q) use ($po_date) {
                            $q->where('po_date', $po_date);
                        })
                        ->when(!empty($supplier), function($q) use ($supplier) {
                            $q->where('supplier_id', $supplier);
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->orderBy('po_date','DESC')
                        ->paginate(10);

        return $orders;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $department = $req->get('department');
        $status     = $req->get('status');

        $orders = Order::with('details','details.item','details.unit','supplier')
                        ->with('requisition','requisition.requester','requisition.requester.prefix')
                        ->with('requisition.requester.position','requisition.requester.level')
                        // ->when(!empty($department), function($q) use ($department) {
                        //     $q->where('department_id', $department);
                        // })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        ->get();

        return $orders;
    }

    public function getById($id)
    {
        $order = Order::with('details','details.item','details.unit','supplier')
                        ->with('supplier.tambon','supplier.amphur','supplier.changwat','supplier.bank')
                        ->with('requisition','requisition.requester','requisition.requester.prefix')
                        ->with('requisition.category','requisition.budget','requisition.budget.project')
                        ->with('requisition.budget.project.plan','requisition.project')
                        ->with('requisition.requester.position','requisition.requester.level')
                        ->with('requisition.division','requisition.division.department')
                        ->with('requisition.committees','requisition.committees.employee','requisition.committees.employee.prefix')
                        ->with('requisition.committees.employee.position','requisition.committees.employee.level')
                        ->find($id);

        return $order;
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
            $order = new Order();
            $order->po_no           = $req['po_no'];
            $order->po_date         = $req['po_date'];
            $order->requisition_id  = $req['requisition_id'];
            $order->supplier_id     = $req['supplier_id'];
            $order->item_count      = $req['item_count'];
            $order->total           = currencyToNumber($req['total']);
            $order->vat_rate        = currencyToNumber($req['vat_rate']);
            $order->vat             = currencyToNumber($req['vat']);
            $order->net_total       = currencyToNumber($req['net_total']);
            $order->deliver_days    = $req['deliver_days'];
            $order->deliver_date    = convThDateToDbDate($req['deliver_date']);
            $order->year            = $req['year'];
            $order->status          = 1;

            if($order->save()) {
                foreach ($req['items'] as $item) {
                    $detail  = new OrderDetail();
                    $detail->order_id       = $order->id;
                    $detail->pr_detail_id   = $item['id'];
                    $detail->item_id        = $item['item_id'];
                    $detail->description    = $item['description'];
                    $detail->price          = $item['price'];
                    $detail->amount         = $item['amount'];
                    $detail->unit_id        = $item['unit_id'];
                    $detail->total          = $item['total'];
                    $detail->status         = 0;
                    $detail->save();

                    /** อัพเดตสถานะของคำขอเป็น 4=จัดซื้อแล้ว */
                    Requisition::where('id', $order->requisition_id)->update(['status' => 4]);
                }

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'order'     => $order
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
            $division = Division::find($id);
            $division->name             = $req['name'];
            $division->department_id    = $req['department_id'];
            $division->status           = $req['status'] ? 1 : 0;

            if($division->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'division'  => $division
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
            // $item = Item::find($id);

            // if($item->delete()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Deleting successfully!!',
            //         'item'      => $item
            //     ];
            // } else {
            //     return [
            //         'status'    => 0,
            //         'message'   => 'Something went wrong!!'
            //     ];
            // }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }
}
