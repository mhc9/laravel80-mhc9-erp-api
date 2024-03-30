<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Car;

class CarController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $type = $req->get('type');
        // $group = $req->get('group');
        // $name = $req->get('name');
        // $status = $req->get('status');

        // $types = Car::with('type','group')
        //             ->when(!empty($type), function($q) use ($type) {
        //                 $q->where('plan_type_id', $type);
        //             })
        //             ->when(!empty($group), function($q) use ($group) {
        //                 $q->where('group_id', $group);
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

        // return $types;
    }

    public function index(Request $req)
    {
        /** Get params from query string */
        // $name = $req->get('name');
        // $status = $req->get('status');

        $cars = Car::where('CarHos', '18271')
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    // ->when(!empty($name), function($q) use ($name) {
                    //     $q->where('name', 'like', '%'.$name.'%');
                    // })
                    ->paginate(10);

        return view('car.index', ['cars' => $cars]);
    }

    public function getCar($id)
    {
        // return Car::find($id);
    }

    public function store(Request $req)
    {
        try {
            $type = new Car();
            $type->name      = $req['name'];
            $type->status    = $req['status'] ? 1 : 0;

            if($type->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'type'      => $type
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
            $type = Car::find($id);
            $type->name     = $req['name'];
            $type->status   = $req['status'] ? 1 : 0;

            if($type->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'type'      => $type
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
            $type = Car::find($id);

            if($type->delete()) {
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
