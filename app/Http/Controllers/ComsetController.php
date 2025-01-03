<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Comset;
use App\Models\ComsetEquipment;
use App\Models\ComsetLicense;
use App\Models\ComsetAsset;
use App\Models\Brand;
use App\Models\EquipmentType;

class ComsetController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $name = $req->get('name');
        $status = $req->get('status');

        $comsets = Comset::with('asset','asset.brand')
                    ->with('equipments','equipments.type','equipments.brand')
                    ->with('assets','licenses')
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%');
                    })
                    ->when(!empty($status), function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->orderBy('name')
                    ->paginate(10);

        return $comsets;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        // $type = $req->get('type');
        // $group = $req->get('group');

        $comsets = Comset::with('asset','asset.brand')
                    ->with('equipments','equipments.type','equipments.brand')
                    ->with('assets','licenses')
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    ->get();

        return $comsets;
    }

    public function getById($id)
    {
        return Comset::with('asset','asset.brand')
                ->with('equipments','equipments.type','equipments.brand')
                ->with('assets','licenses')
                ->find($id);
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
                /** ================== Equipments ================== */
                if ($req->exists('equipments') && count($req['equipments']) > 0) {
                    foreach ($req['equipments'] as $equipment) {
                        $newEquipment = new ComsetEquipment();
                        $newEquipment->comset_id            = $comset->id;
                        $newEquipment->equipment_type_id    = $equipment['equipment_type_id'];
                        $newEquipment->brand_id             = $equipment['brand_id'];
                        $newEquipment->model                = $equipment['model'];
                        $newEquipment->capacity             = $equipment['capacity'];
                        $newEquipment->description          = $equipment['description'];
                        $newEquipment->price                = $equipment['price'];
                        $newEquipment->status               = $equipment['status'];
                        $newEquipment->save();
                    }
                }

                if ($req->exists('licenses') && count($req['licenses']) > 0) {
                    foreach ($req['licenses'] as $license) {
                        $newLicense = new ComsetLicense();
                        $newLicense->comset_id        = $comset->id;
                        $newLicense->description      = $license['description'];
                        $newLicense->purchased_date   = $license['purchased_date'];
                        $newLicense->registered_date  = $license['registered_date'];
                        $newLicense->registered_email = $license['registered_email'];
                        $newLicense->license_no       = $license['license_no'];
                        $newLicense->price            = $license['price'];
                        $newLicense->status           = $license['status'];
                        $newLicense->save();
                    }
                }

                /** ================== Assets ================== */
                if ($req->exists('assets') && count($req['assets']) > 0) {
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
                    'comset'    => $comset->load('asset','asset.brand','equipments','equipments.type','equipments.brand')
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

            if($comset->save()) {
                /** ================== Equipments ================== */
                if ($req->exists('equipments') && count($req['equipments']) > 0) {
                    foreach ($req['equipments'] as $equipment) {
                        /** ถ้า element ของ equipments ไม่มี property comset_id (รายการใหม่) */
                        if (!array_key_exists('comset_id', $equipment)) {
                            $newEquipment = new ComsetEquipment();
                            $newEquipment->comset_id            = $comset->id;
                            $newEquipment->equipment_type_id    = $equipment['equipment_type_id'];
                            $newEquipment->brand_id             = $equipment['brand_id'];
                            $newEquipment->model                = $equipment['model'];
                            $newEquipment->capacity             = $equipment['capacity'];
                            $newEquipment->description          = $equipment['description'];
                            $newEquipment->price                = $equipment['price'];
                            $newEquipment->status               = $equipment['status'];
                            $newEquipment->save();
                        } else {
                            /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property updated ให้ทำการแก้ไขรายการ */
                            if (array_key_exists('updated', $equipment) && $equipment['updated']) {
                                $newEquipment = ComsetEquipment::find($equipment['id']);
                                $newEquipment->equipment_type_id    = $equipment['equipment_type_id'];
                                $newEquipment->brand_id             = $equipment['brand_id'];
                                $newEquipment->model                = $equipment['model'];
                                $newEquipment->capacity             = $equipment['capacity'];
                                $newEquipment->description          = $equipment['description'];
                                $newEquipment->price                = $equipment['price'];
                                $newEquipment->status               = $equipment['status'];
                                $newEquipment->save();
                            }

                            /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed ให้ทำการลบรายการ */
                            if (array_key_exists('removed', $equipment) && $equipment['removed']) {
                                ComsetEquipment::find($equipment['id'])->delete();
                            }
                        }
                    }
                }

                /** ================== Licenses ================== */
                if ($req->exists('licenses') && count($req['licenses']) > 0) {
                    foreach ($req['licenses'] as $license) {
                        /** ถ้า element ของ licenses ไม่มี property comset_id (รายการใหม่) */
                        if (!array_key_exists('comset_id', $license)) {
                            $newLicense = new ComsetLicense();
                            $newLicense->comset_id        = $comset->id;
                            $newLicense->description      = $license['description'];
                            $newLicense->purchased_date   = $license['purchased_date'];
                            $newLicense->registered_date  = $license['registered_date'];
                            $newLicense->registered_email = $license['registered_email'];
                            $newLicense->license_no       = $license['license_no'];
                            $newLicense->price            = $license['price'];
                            $newLicense->status           = $license['status'];
                            $newLicense->save();
                        } else {
                            /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property updated ให้ทำการแก้ไขรายการ */
                            if (array_key_exists('updated', $license) && $license['updated']) {
                                $newLicense = ComsetLicense::find($license['id']);
                                $newLicense->purchased_date   = $license['purchased_date'];
                                $newLicense->registered_date  = $license['registered_date'];
                                $newLicense->registered_email = $license['registered_email'];
                                $newLicense->license_no       = $license['license_no'];
                                $newLicense->description      = $license['description'];
                                $newLicense->price            = $license['price'];
                                $newLicense->status           = $license['status'];
                                $newLicense->save();
                            }

                            /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี flag property removed ให้ทำการลบรายการ */
                            if (array_key_exists('removed', $license) && $license['removed']) {
                                ComsetLicense::find($license['id'])->delete();
                            }
                        }
                    }
                }

                /** ================== Assets ================== */
                if ($req->exists('assets') && count($req['assets']) > 0) {
                    foreach ($req['assets'] as $asset) {
                        if (array_key_exists('id', $asset)) {
                            
                        } else {
                            $newAsset = new ComsetAsset();
                            $newAsset->comset_id    = $comset->id;
                            $newAsset->asset_id     = $asset['asset_id'];
                            $newAsset->save();
                        }
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'comset'    => $comset->load('asset','asset.brand','equipments','equipments.type','equipments.brand')
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
            $comset = Comset::find($id);

            if($comset->delete()) {
                return [
                    'status'    => 1,
                    'message'   => 'Deleting successfully!!',
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
