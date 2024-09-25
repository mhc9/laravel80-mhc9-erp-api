<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Services\EmployeeService;
use App\Models\Employee;
use App\Models\Member;

class EmployeeController extends Controller
{
    public function __construct (protected EmployeeService $employeeService) 
    {
        // code ...
    }

    public function search(Request $req)
    {
        /** Get params from query string */
        $position   = $req->get('position');
        $level      = $req->get('level');
        $name       = $req->get('name');
        $department = $req->get('department');
        $status     = $req->get('status');
        $limit      = $req->filled('limit') ? $req->get('limit') : 10;

        $memberLists = [];
        if (!empty($department)) {
            $memberLists = Member::where('department_id', $department)->pluck('employee_id');
        }

        $employees = Employee::with('prefix','changwat','amphur','tambon','position','level')
                        ->with('memberOf','memberOf.duty','memberOf.department','memberOf.division')
                        ->when(!empty($position), function($q) use ($position) {
                            $q->where('position_id', $position);
                        })
                        ->when(!empty($level), function($q) use ($level) {
                            $q->where('level_id', $level);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('firstname', 'like', '%'.$name.'%');
                        })
                        ->when(!empty($department), function($q) use ($memberLists) {
                            $q->whereIn('id', $memberLists);
                        })
                        ->when(!empty($status), function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->paginate($limit);

        return $employees;
    }

    public function getAll()
    {
        return $this->employeeService->findAll();
    }

    public function getById($id)
    {
        return $this->employeeService->findById($id);
    }

    public function getInitialFormData()
    {
        return $this->employeeService->initForm();
    }

    public function store(Request $req)
    {
        try {
            $employee = new Employee();
            $employee->employee_no  = $req['employee_no'];
            $employee->prefix_id    = $req['prefix_id'];
            $employee->firstname    = $req['firstname'];
            $employee->lastname     = $req['lastname'];
            $employee->cid          = $req['cid'];
            $employee->sex          = $req['sex'];
            $employee->birthdate    = $req['birthdate'];
            $employee->address_no   = $req['address_no'];
            $employee->moo          = $req['moo'];
            $employee->road         = $req['road'];
            $employee->changwat_id  = $req['changwat_id'];
            $employee->amphur_id    = $req['amphur_id'];
            $employee->tambon_id    = $req['tambon_id'];
            $employee->zipcode      = $req['zipcode'];
            $employee->tel          = $req['tel'];
            $employee->email        = $req['email'];
            $employee->line_id      = $req['line_id'];
            $employee->position_id  = $req['position_id'];
            $employee->level_id     = $req['level_id'];
            $employee->assigned_at  = $req['assigned_at'];
            $employee->started_at   = $req['started_at'];
            $employee->remark       = $req['remark'];
            $employee->status       = 1;
            $employee->avatar_url   = $this->employeeService->saveImage($req->file('avatar_url'), 'employees');

            if($employee->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'employee'  => $employee
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
            $employee = Employee::find($id);
            $employee->employee_no  = $req['employee_no'];
            $employee->prefix_id    = $req['prefix_id'];
            $employee->firstname    = $req['firstname'];
            $employee->lastname     = $req['lastname'];
            $employee->cid          = $req['cid'];
            $employee->sex          = $req['sex'];
            $employee->birthdate    = $req['birthdate'];
            $employee->address_no   = $req['address_no'];
            $employee->moo          = $req['moo'];
            $employee->road         = $req['road'];
            $employee->changwat_id  = $req['changwat_id'];
            $employee->amphur_id    = $req['amphur_id'];
            $employee->tambon_id    = $req['tambon_id'];
            $employee->zipcode      = $req['zipcode'];
            $employee->tel          = $req['tel'];
            $employee->email        = $req['email'];
            $employee->line_id      = $req['line_id'];
            $employee->position_id  = $req['position_id'];
            $employee->level_id     = $req['level_id'];
            $employee->assigned_at  = $req['assigned_at'];
            $employee->started_at   = $req['started_at'];
            $employee->remark       = $req['remark'];

            if($employee->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'employee'  => $employee
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
            if($this->employeeService->delete($id)) {
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

    public function uploadAvatar(Request $req, $id)
    {
        try {
            if($employee = $this->employeeService->updateImage($id, $req->file('avatar_url'))) {
                return [
                    'status'        => 1,
                    'message'       => 'Uploading avatar successfully!!',
                    'avatar_url'    => $employee->avatar_url
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
