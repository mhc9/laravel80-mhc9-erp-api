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
        return [
        ];
    }

    public function store(Request $req)
    {
        try {
            $budget = new Budget();
            $budget->activity_id    = $req['activity_id'];
            $budget->budget_type_id = $req['budget_type_id'];
            $budget->total          = $req['total'];

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'budget'    => $budget->load('activity','type')
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
            $budget = Budget::find($id);
            $budget->activity_id    = $req['activity_id'];
            $budget->budget_type_id = $req['budget_type_id'];
            $budget->total          = $req['total'];

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'budget'    => $budget->load('activity','type')
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
            $budget = Budget::find($id);

            if($budget->delete()) {
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
