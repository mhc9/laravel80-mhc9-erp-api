<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Agency;

class AgencyController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $name       = $req->get('name');
        $status     = $req->get('status');
        $limit      = $req->filled('limit') ? $req->get('limit') : 10;

        $agencies = Agency::when(!empty($name), function($q) use ($name) {
                                $q->where('name', 'like', '%'.$name.'%');
                            })
                            ->when(!empty($status), function($q) use ($status) {
                                $q->where('status', $status);
                            })
                            ->paginate($limit);

        return $agencies;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $name       = $req->get('name');
        $status     = $req->get('status');

        $agencies = Agency::when(!empty($name), function($q) use ($name) {
                                $q->where('name', 'like', '%'.$name.'%');
                            })
                            ->when(!empty($status), function($q) use ($status) {
                                $q->where('status', $status);
                            })
                            ->get();

        return $agencies;
    }

    public function getById($id)
    {
        return Agency::find($id);
    }

    public function getInitialFormData(Request $req)
    {
        return [];
    }

    public function store(Request $req)
    {
        try {
            $agency = new Agency();
            $agency->name       = $req['name'];
            $agency->is_dmh     = $req['is_dmh'];
            $agency->is_moph    = $req['is_moph'];
            $agency->status     = 1;

            if($agency->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'agency'    => $agency
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
            $agency = Agency::find($id);
            $agency->name       = $req['name'];
            $agency->is_dmh     = $req['is_dmh'];
            $agency->is_moph    = $req['is_moph'];

            if($agency->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'agency'    => $agency
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
            $agency = Agency::find($id);

            if($agency->delete()) {
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
