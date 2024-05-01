<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\AssetOwnership;
use App\Models\Asset;
use App\Models\Employee;

class AssetOwnershipController extends Controller
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
        // $type = $req->get('type');
        // $category = $req->get('category');
        // $name = $req->get('name');
        // $status = $req->get('status');

        // $assets = Asset::with('type','category')
        //             ->when(!empty($type), function($q) use ($type) {
        //                 $q->where('asset_type_id', $type);
        //             })
        //             ->when(!empty($category), function($q) use ($category) {
        //                 $q->where('asset_category_id', $category);
        //             })
        //             ->when($status != '', function($q) use ($status) {
        //                 $q->where('status', $status);
        //             })
        //             ->when(!empty($name), function($q) use ($name) {
        //                 $q->where(function($query) use ($name) {
        //                     $query->where('item_name', 'like', '%'.$name.'%');
        //                     $query->orWhere('en_name', 'like', '%'.$name.'%');
        //                 });
        //             })
        //             ->paginate(10);

        // return $assets;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $type = $req->get('type');
        $category = $req->get('category');

        $ownerships = AssetOwnership::with('asset','owner')
                    // ->when(!empty($type), function($q) use ($type) {
                    //     $q->where('asset_type_id', $type);
                    // })
                    // ->when(!empty($category), function($q) use ($category) {
                    //     $q->where('asset_category_id', $category);
                    // })
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    ->paginate(10);

        return $ownerships;
    }

    public function getById($id)
    {
        return AssetOwnership::with('asset','owner')->find($id);
    }

    public function getByAsset($assetId)
    {
        return AssetOwnership::with('asset','owner')->where('asset_id', $assetId)->orderBy('owned_at', 'DESC')->paginate(10);
    }

    public function getByOwner($ownerId)
    {
        return AssetOwnership::with('asset','owner')->where('owner_id', $ownerId)->orderBy('owned_at', 'DESC')->paginate(10);
    }

    public function getInitialFormData()
    {
        return [
            'assets'    => Asset::all(),
            'employees' => Employee::whereIn('status', [1,2])->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $ownership = new AssetOwnership();
            $ownership->asset_id        = $req['asset_id'];
            $ownership->owner_id        = $req['owner_id'];
            $ownership->owned_at        = $req['owned_at'];
            $ownership->condition       = $req['condition'];
            $ownership->status          = $req['status'];
            $ownership->remark          = $req['remark'];

            if($ownership->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'ownership' => $ownership
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
