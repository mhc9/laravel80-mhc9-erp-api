<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Driver;
use App\Models\ReservationAssignment;
use App\Models\VehicleOwner;

class DriverController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $name = $req->get('name');
        // $status = $req->get('status');

        $drivers = Driver::with('member_of')
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
                    ->paginate(6);

        return $drivers;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $name = $req->get('name');
        $status = $req->get('status');

        $units = Unit::when($status != '', function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%');
                    })
                    ->paginate(10);

        return $units;
    }

    public function getById($id)
    {
        return Driver::with('member_of','assignments','assignments.reservation','assignments.reservation.type')->find($id);
    }
    
    public function getAssignments($id, $date)
    {
        $assignments = ReservationAssignment::with('reservation','reservation.type')
                                            ->where('driver_id', $id)
                                            ->whereHas('reservation', function($q) use ($date) {
                                                $q->where('reserve_date', $date);
                                            })
                                            ->get();

        $driver = Driver::with('member_of','vehicles')->where('id', $id)->first();
        $driver['assignments'] = $assignments;

        return $driver;
    }

    public function getInitialFormData()
    {
        return [
            'owners' => VehicleOwner::all()
        ];
    }

    public function store(Request $req)
    {
        try {
            $driver = new Driver();
            $driver->firstname  = $req['firstname'];
            $driver->lastname   = $req['lastname'];
            $driver->nickname   = $req['nickname'];
            $driver->owner_id   = $req['owner_id'];
            $driver->tel        = $req['tel'];
            $driver->line_id    = $req['line_id'];
            // $driver->status    = $req['status'] ? 1 : 0;

            if($driver->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'driver'    => $driver
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
            $driver = Driver::find($id);
            $driver->firstname  = $req['firstname'];
            $driver->lastname   = $req['lastname'];
            $driver->nickname   = $req['nickname'];
            $driver->owner_id   = $req['owner_id'];
            $driver->tel        = $req['tel'];
            $driver->line_id    = $req['line_id'];
            // $driver->status   = $req['status'] ? 1 : 0;

            if($driver->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'driver'    => $driver
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
}
