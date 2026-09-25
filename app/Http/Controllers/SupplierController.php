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
                        ->when(!empty($status), function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->paginate(10);

        return $suppliers;
    }

    public function getAll(Request $req)
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
                        ->when(!empty($status), function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->paginate(10);

        return $suppliers;
    }

    public function getById($id)
    {
        return Supplier::with('changwat','amphur','tambon','bank')->find($id);
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
            $supplier = Supplier::find($id);
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

            if($supplier->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
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

    public function destroy(Request $req, $id)
    {
        try {
            $supplier = Supplier::find($id);

            if($supplier->delete()) {
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
