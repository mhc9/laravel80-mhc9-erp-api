<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
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
        $limit = $req->query('limit', 10);

        return $this->employeeService->search($req->all(), false, $limit)->makeHidden(['face_descriptor','created_at','updated_at']);
    }

    public function getAll()
    {
        return $this->employeeService
                    ->getAll()
                    ->map(function($employee) {
                        $employee->isRegistered = $employee->face_descriptor ? true : false;

                        return $employee->makeHidden(['face_descriptor','created_at','updated_at']);
                    });
    }

    public function getById($id)
    {
        if ($employee = $this->employeeService->getById($id)) {
            $employee->isRegistered = $employee->face_descriptor ? true : false;
        }

        return $employee ? $employee->makeHidden(['face_descriptor','created_at','updated_at']) : null;
    }

    public function getInitialFormData()
    {
        return $this->employeeService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            $employeeData = addMultipleInputs(
                $req->except(['id','avatar_url']),
                [
                    'avatar_url'    => $this->employeeService->saveImage($req->file('avatar_url'), 'employees'),
                    'status'        => 1, 
                ]
            );

            if($newEmployee = $this->employeeService->create($employeeData)) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'employee'  => $newEmployee
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
            $memberData = $req->only(['department_id','division_id', 'duty_id']);
            $employeeData = $req->except(['department_id','division_id', 'duty_id','prefix','changwat','amphur','tambon','position','level','member_of','isRegistered']);

            if($updatedEmployee = $this->employeeService->update($id, $employeeData)) {
                $member = Member::where(['employee_id' => $id, 'is_primary' => 1])->first();
                $member->update($memberData);

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'employee'  => $updatedEmployee
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
            if($this->employeeService->destroy($id)) {
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

    public function updateDescriptor(Request $req, $id)
    {
        try {
            if($updatedEmployee = $this->employeeService->update($id, $req->all())) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'employee'  => $updatedEmployee
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
