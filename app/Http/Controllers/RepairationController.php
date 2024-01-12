<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Repairation;
use App\Models\RepairationExpense;
use App\Models\Task;
use App\Models\Employee;
use App\Models\Supplier;

class RepairationController extends Controller
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

    private function getEmployeeList($fname, $lname)
    {
        return Employee::where('firstname', 'like', '%'.$fname.'%')
                    ->when(!empty($lname), function($q) use ($lname) {
                        $q->where('lastname', 'like', '%'.$lname.'%');
                    })
                    ->pluck('id');
    }

    public function search(Request $req)
    {
        /** Get params from query string */
        $date       = $req->get('date');
        $type       = $req->get('type');
        // $group      = $req->get('group');
        $reporter   = $req->get('reporter');
        $status     = $req->get('status');

        $tasks = Repairation::with('task','asset','asset.brand','expenses')
                    ->with('requester','requester.prefix','requester.position','requester.level')
                    ->with('staff','staff.prefix','staff.position','staff.level')
                    // ->when(!empty($date), function($q) use ($date) {
                    //     $q->where('task_date', $date);
                    // })
                    // ->when(!empty($reporter), function($q) use ($reporterList) {
                    //     $q->whereIn('reporter_id', $reporterList);
                    // })
                    // ->when(!empty($type), function($q) use ($groupList) {
                    //     $q->whereIn('task_group_id', $groupList);
                    // })
                    // ->when(!empty($group), function($q) use ($group) {
                    //     $q->where('task_group_id', $group);
                    // })
                    ->when(!empty($status), function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->orderBy('request_date', 'desc')
                    ->orderBy('request_time', 'desc')
                    ->paginate(10);

        return $tasks;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $group      = $req->get('group');
        $type       = $req->get('type');
        $reporter   = $req->get('reporter');
        $status     = $req->get('status');

        $tasks = Repairation::with('group','group.type','reporter','assets','assets.asset')
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('task_type_id', $type);
                    })
                    ->when(!empty($group), function($q) use ($group) {
                        $q->where('task_group_id', $group);
                    })
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    ->get();

        return $tasks;
    }

    public function getById($id)
    {
        return Repairation::with('task','task.group','task.group.type','asset','asset.brand','expenses')
                    ->with('task.reporter','task.reporter.prefix','task.reporter.position','task.reporter.level')
                    ->with('requester','requester.prefix','requester.position','requester.level')
                    ->with('staff','staff.prefix','staff.position','staff.level')
                    ->find($id);
    }

    public function getByAsset($assetId)
    {
        return Repairation::with('task','task.group','task.group.type','asset','asset.brand','expenses')
                    ->with('task.reporter','task.reporter.prefix','task.reporter.position','task.reporter.level')
                    ->with('requester','requester.prefix','requester.position','requester.level')
                    ->with('staff','staff.prefix','staff.position','staff.level')
                    ->where('asset_id', $assetId)
                    ->where('status', 4)
                    ->paginate(10);
    }

    public function getInitialFormData()
    {
        $failures = [
            ['id' => '1', 'name'  => 'อุปกรณ์หลัก'],
            ['id' => '2', 'name'  => 'อะไหล่/อุปกรณ์ประกอบ'],
            ['id' => '3', 'name'  => 'ระบบปฏิบัติการ/โปรแกรม'],
            ['id' => '4', 'name'  => 'การตั้งค่าการใช้งาน (Config)'],
            ['id' => '5', 'name'  => 'เฟิร์มแวร์'],
        ];

        $methods = [
            ['id' => '1', 'name'  => 'ติดตั้งอุปกรณ์ใหม่'],
            ['id' => '2', 'name'  => 'ติดตั้งอุปกรณ์เพิ่ม'],
            ['id' => '3', 'name'  => 'ถอนการติดตั้งอุปกรณ์'],
            ['id' => '4', 'name'  => 'เปลี่ยนอะไหล่/เบิกวัสดุ'],
            ['id' => '5', 'name'  => 'ซ่อมแผงวงจร/เมนบอร์ด'],
            ['id' => '6', 'name'  => 'ติดตั้งโปรแกรม/ระบบปฏิบัติการใหม่'],
            ['id' => '7', 'name'  => 'ติดตั้งโปรแกรมเพิ่ม'],
            ['id' => '8', 'name'  => 'ถอนการติดตั้งโปรแกรม'],
            ['id' => '9', 'name'  => 'ตั้งค่าใหม่ (Config)'],
            ['id' => '10', 'name' => 'อัพเดตเฟิร์มแวร์ (Firmware)'],
        ];

        $types = [
            ['id' => '1', 'name'  => 'ซ่อมเอง'],
            ['id' => '2', 'name'  => 'ส่งภายนอก (มีประกันฯ)'],
            ['id' => '3', 'name'  => 'ส่งภายนอก (ไม่มีประกันฯ)'],
        ];

        $statuses = [
            ['id' => '1', 'name'  => 'รอซ่อม'],
            ['id' => '2', 'name'  => 'สั่งซื้ออะไหล่'],
            ['id' => '3', 'name'  => 'ส่งภายนอก'],
            ['id' => '4', 'name'  => 'ส่งมอบแล้ว'],
            ['id' => '9', 'name'  => 'ยกเลิก'],
        ];

        return [
            'types'     => $types,
            'failures'  => $failures,
            'methods'   => $methods,
            'statuses'  => $statuses,
            'employees' => Employee::with('prefix')->whereIn('status', [1,2])->get(),
            'suppliers' => Supplier::where('status', 1)->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $repair = new Repairation();
            $repair->request_date   = $req['request_date'];
            $repair->request_time   = $req['request_time'];
            $repair->deliver_date   = $req['deliver_date'];
            $repair->requester_id   = $req['requester_id'];
            $repair->task_id        = $req['task_id'];
            $repair->asset_id       = $req['asset_id'];
            $repair->remark         = $req['remark'];
            $repair->status         = 1;

            if($repair->save()) {
                Task::where('id', $repair->task_id)->update(['status' => 4]);

                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'repairation'   => $repair
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

    public function repair(Request $req, $id)
    {
        try {
            $repair = Repairation::find($id);
            $repair->repair_date    = $req['repair_date'];
            $repair->repair_time    = $req['repair_time'];
            $repair->repair_failure_id  = $req['repair_failure_id'];
            $repair->repair_method_id   = $req['repair_method_id'];
            $repair->root_cause     = $req['root_cause'];
            $repair->solution       = $req['solution'];
            $repair->repair_type_id = $req['repair_type_id'];
            $repair->total_cost     = $req['total_cost'];
            $repair->supplier_id    = $req['supplier_id'];
            $repair->overall_time   = $req['overall_time'];
            $repair->staff_id       = $req['staff_id'];
            $repair->status         = 4;

            if($repair->save()) {
                if(count($req['expenses']) > 0) {
                    foreach($req['expenses'] as $expense) {
                        $newExpense = new RepairationExpense();
                        $newExpense->repair_id      = $repair->id;
                        $newExpense->expense_id     = $expense['id'];
                        // $newExpense->description    = $expense['description'];
                        $newExpense->total          = $expense['total'];
                        $newExpense->save();
                    }
                }

                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'repairation'   => Repairation::with('task','task.group','task.group.type','asset','asset.brand','expenses')
                                            ->with('task.reporter','task.reporter.prefix','task.reporter.position','task.reporter.level')
                                            ->with('requester','requester.prefix','requester.position','requester.level')
                                            ->with('staff','staff.prefix','staff.position','staff.level')
                                            ->find($id)
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
            // $item = Item::find($id);
            // $item->plan_type_id = $req['plan_type_id'];
            // $item->category_id  = $req['category_id'];
            // $item->group_id     = $req['group_id'];
            // $item->asset_no     = $req['asset_no'];
            // $item->item_name    = $req['item_name'];
            // $item->en_name      = $req['en_name'];
            // $item->price_per_unit = currencyToNumber($req['price_per_unit']);
            // $item->unit_id      = $req['unit_id'];
            // $item->in_stock     = $req['in_stock'];
            // $item->calc_method  = $req['calc_method'];
            // $item->have_subitem = $req['have_subitem'];
            // $item->is_fixcost   = $req['is_fixcost'];
            // $item->is_repairing_item = $req['is_repairing_item'];
            // $item->is_addon     = $req['is_addon'];
            // $item->first_year   = $req['first_year'];
            // $item->remark       = $req['remark'];

            // if($item->save()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Updating successfully!!',
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
}
