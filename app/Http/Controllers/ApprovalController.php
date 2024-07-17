<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Approval;
use App\Models\Procuring;
use App\Models\Requisition;

class ApprovalController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $type       = $req->get('type');
        $category   = $req->get('category');
        $group       = $req->get('group');
        $name       = $req->get('name');
        $owner      = $req->get('owner');
        $status     = $req->get('status');

        $assets = Asset::with('group','group.category','brand','budget','obtaining','unit','room')
                    ->with('currentOwner','currentOwner.owner','currentOwner.owner.prefix')
                    // ->when(!empty($type), function($q) use ($type) {
                    //     $q->where('asset_type_id', $type);
                    // })
                    ->when(!empty($category), function($q) use ($category) {
                        $q->where('asset_category_id', $category);
                    })
                    ->when(!empty($group), function($q) use ($group) {
                        $q->where('asset_group_id', $group);
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%');
                    })
                    ->when(!empty($owner), function($q) use ($owner) {
                        $q->whereHas('currentOwner', function($sq) use ($owner) {
                            $sq->where('owner_id', $owner);
                        });
                    })
                    ->when(!empty($status), function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->paginate(10);

        return $assets;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $type       = $req->get('type');
        $category   = $req->get('category');
        $group      = $req->get('group');
        $name       = $req->get('name');
        $owner      = $req->get('owner');
        $status     = $req->get('status');

        $assets = Asset::with('group','group.category','brand','budget','obtaining','unit','room')
                    ->with('currentOwner','currentOwner.owner','currentOwner.owner.prefix')
                    // ->when(!empty($type), function($q) use ($type) {
                    //     $q->where('asset_type_id', $type);
                    // })
                    ->when(!empty($category), function($q) use ($category) {
                        $q->where('asset_category_id', $category);
                    })
                    ->when(!empty($group), function($q) use ($group) {
                        $q->where('asset_group_id', $group);
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%');
                    })
                    ->when(!empty($owner), function($q) use ($owner) {
                        $q->whereHas('currentOwner', function($sq) use ($owner) {
                            $sq->where('owner_id', $owner);
                        });
                    })
                    ->when(!empty($status), function($q) use ($status) {
                        $q->where('status', $status);
                    })
                    ->get();

        return $assets;
    }

    public function getById($id)
    {
        return Approval::with('requisition','procuring')->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'procurings' => Procuring::all(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $approval = new Approval();
            $approval->requisition_id   = $req['requisition_id'];
            $approval->procuring_id     = $req['procuring_id'];
            $approval->deliver_date     = $req['deliver_date'];
            $approval->report_no        = $req['report_no'];
            $approval->report_date      = $req['report_date'];
            $approval->directive_no     = $req['directive_no'];
            $approval->directive_date   = $req['directive_date'];

            if($approval->save()) {
                /** อัพเดตสถานะของรายการตาราง requisitions เป็น 2 (แต่งตั้งผู้ตรวจรับ) */
                Requisition::find($approval->requisition_id)->update(['status' => 2]);

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'approval'  => $approval->load('requisition','procuring')
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
            $approval = Approval::find($id);
            $approval->requisition_id   = $req['requisition_id'];
            $approval->procuring_id     = $req['procuring_id'];
            $approval->deliver_date     = $req['deliver_date'];
            $approval->report_no        = $req['report_no'];
            $approval->report_date      = $req['report_date'];
            $approval->directive_no     = $req['directive_no'];
            $approval->directive_date   = $req['directive_date'];

            if($approval->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'approval'  => $approval
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
            $approval = Approval::find($id);

            if($approval->delete()) {
                return [
                    'status'     => 1,
                    'message'    => 'Deleting successfully!!',
                    'id'         => $id
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
