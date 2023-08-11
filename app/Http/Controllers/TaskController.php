<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\TaskGroup;
use App\Models\TaskAsset;
use App\Models\Employee;

class TaskController extends Controller
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
        $group      = $req->get('group');
        $type       = $req->get('type');
        $reporter   = $req->get('reporter');
        $status     = $req->get('status');

        $tasks = Task::with('group','group.type','reporter','assets','assets.asset')
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('task_type_id', $type);
                    })
                    ->when(!empty($group), function($q) use ($group) {
                        $q->where('task_group_id', $group);
                    })
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
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

        $tasks = Task::with('group','group.type','reporter','assets','assets.asset')
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
        return Task::with('group','group.type','reporter','assets','assets.asset')->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'types'         => TaskType::all(),
            'groups'        => TaskGroup::all(),
            'employees'     => Employee::whereIn('status', [1,2])->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $task = new Task();
            $task->task_date        = $req['task_date'];
            $task->task_time        = $req['task_time'];
            $task->task_group_id    = $req['task_group_id'];
            $task->priority_id      = $req['priority_id'];
            $task->description      = $req['description'];
            $task->reporter_id      = $req['reporter_id'];
            $task->status           = $req['status'];
            $task->remark           = $req['remark'];

            if($task->save()) {
                if (count($req['assets']) > 0) {
                    foreach($req['assets'] as $asset) {
                        $taskAsset = new TaskAsset();
                        $taskAsset->task_id     = $task->id;
                        $taskAsset->asset_id    = $asset;
                        $taskAsset->save();
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'task'      => $task
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
