<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Comset;
use App\Models\Brand;
use App\Models\EquipmentType;

class ComsetController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $type = $req->get('type');
        // $group = $req->get('group');

        $comsets = Comset::with('asset','details')
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    // ->when(!empty($name), function($q) use ($name) {
                    //     $q->where(function($query) use ($name) {
                    //         $query->where('item_name', 'like', '%'.$name.'%');
                    //         $query->orWhere('en_name', 'like', '%'.$name.'%');
                    //     });
                    // })
                    ->paginate(10);

        return $comsets;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        // $type = $req->get('type');
        // $group = $req->get('group');

        $comsets = Comset::with('asset','details')
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    ->paginate(10);

        return $comsets;
    }

    public function getById($id)
    {
        return Comset::with('asset','details')->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'types'     => EquipmentType::all(),
            'brands'    => Brand::all(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $comset = new Comset();
            $comset->asset_id       = $req['asset_id'];
            $comset->description    = $req['description'];
            $comset->remark         = $req['remark'];
            $comset->status         = 1;

            if($comset->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'comset'    => $comset
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
            // $comset = Comset::find($id);
            // $comset->category_id  = $req['category_id'];
            // $comset->group_id     = $req['group_id'];
            // $comset->asset_no     = $req['asset_no'];
            // $comset->en_name      = $req['en_name'];
            // $comset->price_per_unit = currencyToNumber($req['price_per_unit']);
            // $comset->unit_id      = $req['unit_id'];
            // $comset->in_stock     = $req['in_stock'];
            // $comset->calc_method  = $req['calc_method'];
            // $comset->is_addon     = $req['is_addon'];
            // $comset->first_year   = $req['first_year'];
            // $comset->remark       = $req['remark'];
            // $comset->status       = $req['status'];

            // if($comset->save()) {
            //     return [
                    // 'status'    => 1,
                    // 'message'   => 'Updating successfully!!',
                    // 'comset'    => $comset
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
            // $comset = Comset::find($id);

            // if($comset->delete()) {
            //     return [
                    // 'status'    => 1,
                    // 'message'   => 'Deleting successfully!!',
                    // 'comset'    => $comset
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
