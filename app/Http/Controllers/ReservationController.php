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

class ReservationController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $date = $req->get('date');
        // $status = $req->get('status');
        $limit = $req->has('limit') ? $req->get('limit') : 10;

        $reservations = Reservation::with('type','assignments','assignments.driver','assignments.vehicle')
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

        return $reservations;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        // $name = $req->get('name');
        // $status = $req->get('status');

        $reservations = Reservation::with('type','assignments','assignments.driver','assignments.vehicle')
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

        return $reservations;
    }

    public function getById($id)
    {
        return Unit::find($id);
    }

    public function getInitialFormData()
    {
        return [
            'drivers'   => Driver::all(),
            'vehicles'  => Vehicle::all(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $reservation = new Reservation();
            $reservation->reserve_date      = $req['reserve_date'];
            $reservation->reserve_time      = $req['reserve_time'];
            $reservation->type_id           = $req['type_id'];
            $reservation->contact_name      = $req['contact_name'];
            $reservation->contact_tel       = $req['contact_tel'];
            $reservation->destination       = $req['destination'];
            $reservation->coordinate        = $req['coordinate'];
            $reservation->passengers        = $req['passengers'];
            $reservation->remark            = $req['remark'];
            $reservation->status            = 1;

            if($reservation->save()) {
                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'reservation'   => $reservation->load('type','assignments','assignments.driver','assignments.vehicle')
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
            $unit = Unit::find($id);
            $unit->name     = $req['name'];
            $unit->status   = $req['status'] ? 1 : 0;

            if($unit->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'unit'      => $unit
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
            $unit = Unit::find($id);

            if($unit->delete()) {
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

    public function assign(Request $req, $id)
    {
        try {
            $reservation = Reservation::find($id);
            $reservation->status  = 2;
            /** สถานะรายการ: 1=รอดำเนินการ,2=จัดรถแล้ว,3=เสร็จแล้ว,9=ยกเลิก */

            if($reservation->save()) {
                $assignment = new ReservationAssignment();
                $assignment->reservation_id = $reservation->id;
                $assignment->driver_id      = $req['driver_id'];
                $assignment->vehicle_id     = $req['vehicle_id'];
                $assignment->remark         = $req['remark'];
                $assignment->save();

                return [
                    'status'        => 1,
                    'message'       => 'Updating successfully!!',
                    'reservation'   => $reservation->load('type','assignments','assignments.driver','assignments.vehicle')
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
