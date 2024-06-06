<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Comset;
use App\Models\ComsetEquipment;
use App\Models\ComsetAsset;
use App\Models\Brand;
use App\Models\EquipmentType;

class ComsetController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $type = $req->get('type');
        // $group = $req->get('group');

        $comsets = Comset::with('asset','equipments','equipments.type','equipments.brand')
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

        $comsets = Comset::with('asset','equipments','equipments.type','equipments.brand')
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    ->paginate(10);

        return $comsets;
    }

    public function getById($id)
    {
        return Comset::with('asset','equipments','equipments.type','equipments.brand')->find($id);
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
            $comset->name           = $req['name'];
            $comset->description    = $req['description'];
            $comset->asset_id       = $req['asset_id'];
            $comset->remark         = $req['remark'];
            $comset->status         = 1;

            if($comset->save()) {
                if (array_key_exists('equipments', get_object_vars($req)) && count($req['equipments']) > 0) {
                    foreach ($req['equipments'] as $equipment) {
                        $newEquipment = new ComsetEquipment();
                        $newEquipment->comset_id            = $comset->id;
                        $newEquipment->equipment_type_id    = $equipment['equipment_type_id'];
                        $newEquipment->brand_id             = $equipment['brand_id'];
                        $newEquipment->model                = $equipment['model'];
                        $newEquipment->capacity             = $equipment['capacity'];
                        $newEquipment->description          = $equipment['description'];
                        $newEquipment->price                = $equipment['price'];
                        $newEquipment->save();
                    }
                }

                if (array_key_exists('assets', get_object_vars($req)) && count($req['assets']) > 0) {
                    foreach ($req['assets'] as $asset) {
                        $newAsset = new ComsetAsset();
                        $newAsset->comset_id    = $comset->id;
                        $newAsset->asset_id     = $asset['asset_id'];
                        $newAsset->save();
                    }
                }

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
            $comset = Comset::find($id);
            $comset->name           = $req['name'];
            $comset->description    = $req['description'];
            $comset->asset_id       = $req['asset_id'];
            $comset->remark         = $req['remark'];
            $comset->status         = 1;

            if($comset->save()) {
                if (array_key_exists('equipments', get_object_vars($req)) && count($req['equipments']) > 0) {
                    foreach ($req['equipments'] as $equipment) {
                        $newEquipment = new ComsetEquipment();
                        $newEquipment->comset_id            = $comset->id;
                        $newEquipment->equipment_type_id    = $equipment['equipment_type_id'];
                        $newEquipment->brand_id             = $equipment['brand_id'];
                        $newEquipment->model                = $equipment['model'];
                        $newEquipment->capacity             = $equipment['capacity'];
                        $newEquipment->description          = $equipment['description'];
                        $newEquipment->price                = $equipment['price'];
                        $newEquipment->save();
                    }
                }

                if (array_key_exists('assets', get_object_vars($req)) && count($req['assets']) > 0) {
                    foreach ($req['assets'] as $asset) {
                        $newAsset = new ComsetAsset();
                        $newAsset->comset_id    = $comset->id;
                        $newAsset->asset_id     = $asset['asset_id'];
                        $newAsset->save();
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'comset'    => $comset->load('asset','equipments','equipments.type','equipments.brand')
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
