<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\InitialUser;

class UserController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $name = $req->get('name');
        // $status = $req->get('status');

        // $users = User::with('type','group')
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

        // return $users;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $name = $req->get('name');
        $status = $req->get('status');

        $users = User::when($status != '', function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%');
                    })
                    ->paginate(10);

        return $users;
    }

    public function getById($id)
    {
        return User::find($id);
    }

    public function store(Request $req)
    {
        try {
            $user = new User();
            $user->name      = $req['name'];
            $user->status    = $req['status'] ? 1 : 0;

            if($user->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'user'      => $user
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
            $user = User::find($id);
            $user->name     = $req['name'];
            $user->status   = $req['status'] ? 1 : 0;

            if($user->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'user'      => $user
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
            $user = User::find($id);

            if($user->delete()) {
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

    public function sendMail(Request $req, $id)
    {
        $user = User::find($id);

        if ($user) {
            Mail::to($user->email)->send(new InitialUser($user->email, '1234'));

            return new JsonResponse([
                'success' => true,
                'message' => "Email was sent to your user!!",
            ], 200);
        } else {
            return new JsonResponse([
                'success' => false, 
                'message' => "This email does not exist"
            ], 400);
        }
    }
}
