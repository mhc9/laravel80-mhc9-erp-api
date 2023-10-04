<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Supplier;
use App\Models\Changwat;
use App\Models\Amphur;
use App\Models\Tambon;
use App\Models\Bank;

class SupplierController extends Controller
{
    public function formValidate (Request $request)
    {
        $rules = [
            'plan_type_id'      => 'required',
            'item_name'         => 'required',
            'unit_id'           => 'required',
        ];

        if ($request['is_addon'] != '1') {
            $rules['category_id'] = 'required';
            $rules['price_per_unit'] = 'required';
        }

        $messages = [
            'plan_type_id.required'     => 'กรุณาเลือกประเภทแผน',
            'category_id.required'        => 'กรุณาเลือกประเภทสินค้า/บริการ',
            'item_name.required'        => 'กรุณาระบุชื่อสินค้า/บริการ',
            'price_per_unit.required'   => 'กรุณาระบุราคาต่อหน่วย',
            'unit_id.required'          => 'กรุณาเลือกหน่วยนับ',
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
        $changwat = $req->get('changwat');
        $name  = $req->get('name');
        $status = $req->get('status');

        $suppliers = Supplier::with('changwat','amphur','tambon','bank')
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->when(!empty($changwat), function($q) use ($changwat) {
                            $q->where('changwat_id', $changwat);
                        })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        ->paginate(10);

        return $suppliers;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $changwat = $req->get('changwat');
        $name  = $req->get('name');

        $suppliers = Supplier::with('changwat','amphur','tambon','bank')
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->when(!empty($changwat), function($q) use ($changwat) {
                            $q->where('changwat_id', $changwat);
                        })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        ->paginate(10);

        return $suppliers;
    }

    public function getById($id)
    {
        return Supplier::with('type','group')->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'changwats' => Changwat::all(),
            'amphurs'   => Amphur::all(),
            'tambons'   => Tambon::all(),
            'banks'     => Bank::all()
        ];
    }

    public function store(Request $req)
    {
        try {
            $supplier = new Supplier();
            $supplier->tax_no           = $req['tax_no'];
            $supplier->name             = $req['name'];
            $supplier->address          = $req['address'];
            $supplier->moo              = $req['moo'];
            $supplier->raod             = $req['raod'];
            $supplier->changwat_id      = $req['changwat_id'];
            $supplier->amphur_id        = $req['amphur_id'];
            $supplier->tambon_id        = $req['tambon_id'];
            $supplier->zipcode          = $req['zipcode'];
            $supplier->tel              = $req['tel'];
            $supplier->fax              = $req['fax'];
            $supplier->email            = $req['email'];
            $supplier->owner_name       = $req['owner_name'];
            $supplier->manager_name     = $req['manager_name'];
            $supplier->bank_id          = $req['bank_id'];
            $supplier->bank_acc_no      = $req['bank_acc_no'];
            $supplier->bank_acc_name    = $req['bank_acc_name'];
            $supplier->bank_acc_branch  = $req['bank_acc_branch'];
            $supplier->tax_type_id      = $req['tax_type_id'];
            $supplier->remark           = $req['remark'];
            $supplier->status           = 1;

            if($supplier->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'supplier'  => $supplier
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
            // $item = Item::find($id);
            // $item->plan_type_id = $req['plan_type_id'];
            // $item->category_id  = $req['category_id'];
            // $item->group_id     = $req['group_id'];
            // $item->asset_no     = $req['asset_no'];
            // $item->item_name    = $req['item_name'];
            // $item->en_name      = $req['en_name'];
            // $item->price_per_unit = currencyToNumber($req['price_per_unit']);
            // $item->unit_id      = $req['unit_id'];
            // $item->in_stock     = $req['in_stock'];
            // $item->calc_method  = $req['calc_method'];
            // $item->have_subitem = $req['have_subitem'];
            // $item->is_fixcost   = $req['is_fixcost'];
            // $item->is_repairing_item = $req['is_repairing_item'];
            // $item->is_addon     = $req['is_addon'];
            // $item->first_year   = $req['first_year'];
            // $item->remark       = $req['remark'];

            // if($item->save()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Updating successfully!!',
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
