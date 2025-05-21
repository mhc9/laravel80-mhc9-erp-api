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
    public function search(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
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
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
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
                }

                /** อัพเดตสถานะของคำขอเป็น 4=จัดซื้อแล้ว */
                Requisition::where('id', $order->requisition_id)->update(['status' => 4]);

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
            $order->status          = $req['status'];

            if($order->save()) {
                foreach ($req['items'] as $item) {
                    /** ถ้า element ของ $item ไม่มี checkField (รายการใหม่) */
                    if (!array_key_exists('order_id', $item) || empty($item['order_id'])) {
                        $detail  = new OrderDetail();
                        $detail->order_id       = $order->id;
                        $detail->pr_detail_id   = $item['pr_detail_id'];
                        $detail->item_id        = $item['item_id'];
                        $detail->description    = $item['description'];
                        $detail->price          = $item['price'];
                        $detail->amount         = $item['amount'];
                        $detail->unit_id        = $item['unit_id'];
                        $detail->total          = $item['total'];
                        $detail->status         = 0;
                        $detail->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property updated หรือไม่ (รายการที่ต้องแก้ไข) */
                        if (array_key_exists('updated', $item) && $item['updated']) {
                            $detail  = OrderDetail::find($item['id']);
                            $detail->pr_detail_id   = $item['pr_detail_id'];
                            $detail->item_id        = $item['item_id'];
                            $detail->description    = $item['description'];
                            $detail->price          = $item['price'];
                            $detail->amount         = $item['amount'];
                            $detail->unit_id        = $item['unit_id'];
                            $detail->total          = $item['total'];
                            $detail->status         = $item['status'];
                            $detail->save();
                        }

                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed หรือไม่ (รายการที่ต้องลบ) */
                        if (array_key_exists('removed', $item) && $item['removed']) {
                            OrderDetail::find($item['id'])->delete();
                        }
                    }
                }
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
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

    public function destroy(Request $req, $id)
    {
        try {
            $order = Order::find($id);

            if($order->delete()) {
                /** อัพเดตสถานะของคำขอเป็น 3=ประกาศผู้ชนะ */
                Requisition::where('id', $order->requisition_id)->update(['status' => 3]);

                return [
                    'status'    => 1,
                    'message'   => 'Deleting successfully!!',
                    'id'        => $id
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
