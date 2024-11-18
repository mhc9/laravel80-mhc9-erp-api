<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Reservation;
use App\Models\ReservationAssignment;
use App\Models\Driver;
use App\Models\Vehicle;

class ReservationAssignmentController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $date = $req->get('date');
        // $status = $req->get('status');
        $limit = $req->has('limit') ? $req->get('limit') : 10;

        $assignments = ReservationAssignment::with('type','assignments','assignments.driver','assignments.vehicle')
                                    ->when(!empty($date), function($q) use ($date) {
                                        $q->where('reserve_date', $date);
                                    })
                                    // ->when(!empty($group), function($q) use ($group) {
                                    //     $q->where('group_id', $group);
                                    // })
                                    // ->when($status != '', function($q) use ($status) {
                                    //     $q->where('status', $status);
                                    // })
                                    // ->when(!empty($name), function($q) use ($name) {
                                    //     $q->where(function($query) use ($name) {
                                    //         $query->where('item_name', 'like', '%'.$name.'%');
                                    //         $query->orWhere('en_name', 'like', '%'.$name.'%');
                                    //     });
                                    // })
                                    ->orderBy('reserve_date', 'desc')
                                    ->orderBy('reserve_time', 'desc')
                                    ->paginate(5);

        return $assignments;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        // $name = $req->get('name');
        // $status = $req->get('status');

        $assignments = ReservationAssignment::with('type','assignments','assignments.driver','assignments.vehicle')
                    // ->when(!empty($type), function($q) use ($type) {
                    //     $q->where('plan_type_id', $type);
                    // })
                    // ->when(!empty($group), function($q) use ($group) {
                    //     $q->where('group_id', $group);
                    // })
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    // ->when(!empty($name), function($q) use ($name) {
                    //     $q->where(function($query) use ($name) {
                    //         $query->where('item_name', 'like', '%'.$name.'%');
                    //         $query->orWhere('en_name', 'like', '%'.$name.'%');
                    //     });
                    // })
                    ->get();

        return $assignments;
    }

    public function getById($id)
    {
        return ReservationAssignment::find($id);
    }

    public function getInitialFormData(Request $req)
    {
        $date = $req->get('date');

        return [
            'drivers'   => $date == '2024-11-27' ? Driver::with('member_of')->get() : Driver::with('member_of')->whereNotIn('id', [13,14,15,16])->get(),
            'vehicles'  => Vehicle::with('type','owner')->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $assignment = new ReservationAssignment();
            $assignment->reservation_id = $req['reservation_id'];
            $assignment->driver_id      = $req['driver_id'];
            $assignment->vehicle_id     = $req['vehicle_id'];
            $assignment->remark         = $req['remark'];
            $assignment->save();

            if($assignment->save()) {
                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'assignment'    => $assignment
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
            $assignment = ReservationAssignment::find($id);
            $assignment->reservation_id = $req['reservation_id'];
            $assignment->driver_id      = $req['driver_id'];
            $assignment->vehicle_id     = $req['vehicle_id'];
            $assignment->remark         = $req['remark'];
            $assignment->save();

            if($assignment->save()) {
                return [
                    'status'        => 1,
                    'message'       => 'Updating successfully!!',
                    'assignment'    => $assignment
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
            $assignment = ReservationAssignment::find($id);

            if($assignment->delete()) {
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
