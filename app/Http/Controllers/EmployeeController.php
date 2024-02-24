<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Employee;
use App\Models\Prefix;
use App\Models\Position;
use App\Models\Level;
use App\Models\Department;
use App\Models\Division;
use App\Models\Member;
use App\Models\Changwat;
use App\Models\Amphur;
use App\Models\Tambon;

class EmployeeController extends Controller
{
    public function formValidate (Request $request)
    {
        $rules = [
            'plan_type_id'      => 'required',
            'item_name'         => 'required',
            'unit_id'           => 'required',
        ];

        if ($request['is_addon'] != '1') {
            $rules['category_id'] = 'required';
            $rules['price_per_unit'] = 'required';
        }

        $messages = [
            'plan_type_id.required'     => 'กรุณาเลือกประเภทแผน',
            'category_id.required'        => 'กรุณาเลือกประเภทสินค้า/บริการ',
            'item_name.required'        => 'กรุณาระบุชื่อสินค้า/บริการ',
            'price_per_unit.required'   => 'กรุณาระบุราคาต่อหน่วย',
            'unit_id.required'          => 'กรุณาเลือกหน่วยนับ',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $messageBag = $validator->getMessageBag();

            // if (!$messageBag->has('start_date')) {
            //     if ($this->isDateExistsValidation(convThDateToDbDate($request['start_date']), 'start_date') > 0) {
            //         $messageBag->add('start_date', 'คุณมีการลาในวันที่ระบุแล้ว');
            //     }
            // }

            return [
                'success' => 0,
                'errors' => $messageBag->toArray(),
            ];
        } else {
            return [
                'success' => 1,
                'errors' => $validator->getMessageBag()->toArray(),
            ];
        }
    }

    public function search(Request $req)
    {
        /** Get params from query string */
        $position   = $req->get('position');
        $level      = $req->get('level');
        $name       = $req->get('name');
        $department = $req->get('department');
        $status     = $req->get('status');

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
                        ->paginate(10);

        return $employees;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $position   = $req->get('position');
        $level      = $req->get('level');
        $status     = $req->get('status');

        $employees = Employee::with('prefix','changwat','amphur','tambon','position','level')
                        ->with('memberOf','memberOf.department','memberOf.division')
                        ->when(!empty($position), function($q) use ($position) {
                            $q->where('position_id', $position);
                        })
                        ->when(!empty($level), function($q) use ($level) {
                            $q->where('level_id', $level);
                        })
                        ->when(!empty($status), function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->get();

        return $employees;
    }

    public function getById($id)
    {
        return Employee::with('prefix','changwat','amphur','tambon','position','level')
                    ->with('memberOf','memberOf.department','memberOf.division')
                    ->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'prefixes'      => Prefix::all(),
            'positions'     => Position::all(),
            'levels'        => Level::all(),
            'departments'   => Department::with('divisions')->get(),
            'divisions'     => Division::all(),
            'changewats'    => Changwat::all(),
            'amphurs'       => Amphur::all(),
            'tambons'       => Tambon::all(),
        ];
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

            if ($req->file('avatar_url')) {
                $file = $req->file('avatar_url');
                $fileName = $req['cid']. '.' .$file->getClientOriginalExtension();
                $destinationPath = 'uploads/employees/';

                if ($file->move($destinationPath, $fileName)) {
                    $employee->avatar_url = $fileName;
                }
            }

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
            // $item = Item::find($id);

            // if($item->delete()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Deleting successfully!!',
            //         'item'      => $item
            //     ];
            // } else {
            //     return [
            //         'status'    => 0,
            //         'message'   => 'Something went wrong!!'
            //     ];
            // }
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
            $employee = Employee::find($id);

            if ($req->file('avatar_url')) {
                $file = $req->file('avatar_url');
                $fileName = date('mdYHis') . uniqid(). '.' .$file->getClientOriginalExtension();
                $destinationPath = 'uploads/employees/';

                /** Remove old uploaded file */
                if (\File::exists($destinationPath . $employee->avatar_url)) {
                    \File::delete($destinationPath . $employee->avatar_url);
                }

                /** Upload new file */
                if ($file->move($destinationPath, $fileName)) {
                    $employee->avatar_url = $fileName;
                }
            }

            if($employee->save()) {
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
