<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use App\Services\AttendanceService;
use App\Models\Employee;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function __construct (protected AttendanceService $attendanceService) 
    {
        // code ...
    }

    public function search(Request $req)
    {
        return $this->attendanceService->search($req->all());
    }

    public function getAll()
    {
        return $this->attendanceService->getAll();
    }

    public function getById($id)
    {
        return $this->attendanceService->getById($id);
    }

    public function getFaceRecognize()
    {
        return $this->attendanceService->getEmployeeDescriptor();
    }

    public function getInitialFormData()
    {
        return $this->attendanceService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            $employeeData = addMultipleInputs(
                $req->except(['id','avatar_url']),
                [
                    'avatar_url'    => $this->attendanceService->saveImage($req->file('avatar_url'), 'employees'),
                    'status'        => 1, 
                ]
            );

            if($newEmployee = $this->attendanceService->create($employeeData)) {
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
            if($updatedEmployee = $this->attendanceService->update($id, $req->all())) {
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
            if($this->attendanceService->destroy($id)) {
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
            if($employee = $this->attendanceService->updateImage($id, $req->file('avatar_url'))) {
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
