<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Services\AttendanceService;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\WpmCheckTime;

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

    public function getCheckTimeDaily($date)
    {
        return [
            'daily' => Attendance::with('employee')->whereRaw('CAST(check_time AS DATE) = ?', [$date])->get(),
        ];
    }

    public function getCheckTimeByEmployee($date, $employeeId)
    {
        return Attendance::with('employee')
                    ->whereRaw('CAST(check_time AS DATE) = ?', [$date])
                    ->where('employee_id', $employeeId)
                    ->orderBy('check_type', 'asc')
                    ->get();
    }

    public function getCheckTypeByEmployee($date, $type, $employeeId)
    {
        return Attendance::with('employee')
                    ->whereRaw('CAST(check_time AS DATE) = ?', [$date])
                    ->where('employee_id', $employeeId)
                    ->where('check_type', $type)
                    ->orderBy('check_type', 'asc')
                    ->first();
    }

    public function getInitialFormData()
    {
        return $this->attendanceService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            $attendanceData = addMultipleInputs(
                $req->except(['id','check_image']),
                [
                    'check_image' => $this->attendanceService->saveImage($req->file('check_image'), 'attendances'),
                ]
            );

            if($newAtt = $this->attendanceService->create($attendanceData)) {
                // TODO: บันทึกข้อมูลเข้าระบบ WPM
                $employee = Employee::find($req['employee_id']);

                $chkTime = new WpmCheckTime();
                $chkTime->CheTmEmId     = $employee->employee_no;
                $chkTime->CheTmDate     = $newAtt->check_time;
                $chkTime->CheTmType     = $newAtt->check_type === '1' ? 'เข้า' : 'ออก';
                $chkTime->CheTmPic      = $newAtt->check_image;
                $chkTime->CheTmLastDate = Carbon::now();
                $chkTime->CheTmMark     = '';

                if ($chkTime->save()) {
                    return [
                        'status'        => 1,
                        'message'       => 'Insertion successfully!!',
                        'attendance'    => $newAtt
                    ];
                }
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
            $attendanceData = addMultipleInputs(
                $req->except(['id','check_image']),
                [
                    'check_image' => $this->attendanceService->updateImage($id, $req->file('check_image'))->check_image,
                ]
            );

            if($updatedAtt = $this->attendanceService->update($id, $attendanceData)) {
                // TODO: อัพเดตข้อมูลในระบบ WPM
                $employee = Employee::find($req['employee_id']);
                $updatedChkTime = WpmCheckTime::where('CheTmEmId', $employee->employee_no)
                                    ->whereRaw('CAST(CheTmDate AS DATE) = ?', [Carbon::parse($updatedAtt->check_time)->format('Y-m-d')])
                                    ->where('CheTmType', $updatedAtt->check_type === '1' ? 'เข้า' : 'ออก')
                                    ->first();

                $updatedChkTime->CheTmDate     = $updatedAtt->check_time;
                $updatedChkTime->CheTmType     = $updatedAtt->check_type === '1' ? 'เข้า' : 'ออก';
                $updatedChkTime->CheTmPic      = $updatedAtt->check_image;
                $updatedChkTime->CheTmLastDate = Carbon::now();
                $updatedChkTime->CheTmMark     = '';

                if ($updatedChkTime->save()) {
                    return [
                        'status'        => 1,
                        'message'       => 'Updating successfully!!',
                        'attendance'    => $updatedAtt
                    ];
                }
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
