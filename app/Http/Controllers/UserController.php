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
        $name = $req->get('name');
        $status = $req->get('status');

        $users = User::with('permissions','permissions.role','employee')
                    ->with('employee.prefix','employee.position','employee.level')
                    ->with('employee.memberOf','employee.memberOf.duty')
                    ->with('employee.memberOf.department','employee.memberOf.division')
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    // ->when(!empty($name), function($q) use ($name) {
                    //     $q->where('name', 'like', '%'.$name.'%');
                    // })
                    ->paginate(10);

        return $users;
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
                    ->get();

        return $users;
    }

    public function getById($id)
    {
        return User::find($id);
    }

    public function getInitialFormData()
    {
        return [];
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
                    'user'      => $user->load('permissions','permissions.role','employee',
                                                'employee.prefix','employee.position','employee.level',
                                                'employee.memberOf','employee.memberOf.duty',
                                                'employee.memberOf.department','employee.memberOf.division')
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
                    'user'      => $user->load('permissions','permissions.role','employee',
                                                'employee.prefix','employee.position','employee.level',
                                                'employee.memberOf','employee.memberOf.duty',
                                                'employee.memberOf.department','employee.memberOf.division')
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
        try {
            $user = User::find($id);

            if ($user) {
                Mail::to($user->email)->send(new InitialUser($user->email, '1234'));
                $user->update(['is_activated' => 1]);

                return new JsonResponse([
                    'status'    => 1,
                    'message'   => "Email was sent to your user!!",
                    'user'      => $user->load('permissions','permissions.role','employee',
                                                'employee.prefix','employee.position','employee.level',
                                                'employee.memberOf','employee.memberOf.duty',
                                                'employee.memberOf.department','employee.memberOf.division')
                ], 200);
            } else {
                return new JsonResponse([
                    'status'    => 0,
                    'message'   => "This email does not exist"
                ], 400);
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }
}
