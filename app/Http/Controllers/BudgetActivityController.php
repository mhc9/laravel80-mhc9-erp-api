<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Budget;
use App\Models\BudgetType;
use App\Models\BudgetPlan;
use App\Models\BudgetProject;
use App\Models\BudgetActivity;

class BudgetActivityController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $year       = $req->get('year');
        $status     = $req->get('status');
        $limit      = $req->filled('limit') ? $req->get('limit') : 10;

        $activities = BudgetActivity::select('budget_activities.*')
                        ->with('project','project.plan','budgets','budgets.type')
                        ->leftJoin('budget_projects','budget_activities.project_id','=','budget_projects.id')
                        ->leftJoin('budget_plans','budget_projects.plan_id','=','budget_plans.id')
                        ->when(!empty($project), function($q) use ($project) {
                            $q->where('project_id', $project);
                        })
                        ->when(!empty($plan), function($q) use ($plan) {
                            $q->whereHas('project.plan', function($sq) use ($plan) {
                                $sq->where('plan_id', $plan);
                            });
                        })
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('budget_activities.year', $year);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('budget_activities.name', 'like', '%'.$name.'%');
                        })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        ->orderBy('budget_plans.plan_no')
                        ->orderBy('budget_projects.name')
                        ->orderBy('budget_activities.activity_no')
                        ->paginate($limit);

        return $activities;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = BudgetActivity::with('project','project.plan','budgets','budgets.type')
                        ->when(!empty($project), function($q) use ($project) {
                            $q->where('project_id', $project);
                        })
                        ->when(!empty($plan), function($q) use ($plan) {
                            $q->whereHas('project.plan', function($sq) use ($plan) {
                                $sq->where('plan_id', $plan);
                            });
                        })
                        // ->when($status != '', function($q) use ($status) {
                        //     $q->where('status', $status);
                        // })
                        // ->when(!empty($name), function($q) use ($name) {
                        //     $q->where('name', 'like', '%'.$name.'%');
                        // })
                        ->get();

        return $activities;
    }

    public function getById($id)
    {
        return BudgetActivity::with('project','project.plan','budgets','budgets.type')->find($id);
    }

    public function getInitialFormData(Request $req)
    {
        $year = $req->get('year');

        return [
            'plans'     => BudgetPlan::where('year', $year)->orderBy('plan_type_id')->orderBy('plan_no')->get(),
            'projects'  => BudgetProject::where('year', $year)->get(),
            'types'     => BudgetType::all(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $activity = new BudgetActivity();
            $activity->name         = $req['name'];
            $activity->activity_no  = $req['activity_no'];
            $activity->year         = $req['year'];
            $activity->project_id   = $req['project_id'];
            $activity->gfmis_id     = $req['gfmis_id'];
            $activity->status       = 1;

            if($activity->save()) {
                foreach($req['budgets'] as $type) {
                    $newBudget = new Budget();
                    $newBudget->activity_id     = $activity->id;
                    $newBudget->budget_type_id  = $type['budget_type_id'];
                    $newBudget->total           = $type['total'];
                    $newBudget->save();
                }

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'activity'  => $activity->load('project','project.plan','budgets','budgets.type')
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
            $activity = BudgetActivity::find($id);
            $activity->name         = $req['name'];
            $activity->activity_no  = $req['activity_no'];
            $activity->year         = $req['year'];
            $activity->project_id   = $req['project_id'];
            $activity->gfmis_id     = $req['gfmis_id'];

            if($activity->save()) {
                foreach($req['budgets'] as $item) {
                    if (empty($item['activity_id'])) {
                        $newBudget = new Budget();
                        $newBudget->activity_id     = $activity->id;
                        $newBudget->budget_type_id  = $item['budget_type_id'];
                        $newBudget->total           = $item['total'];
                        $newBudget->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag updated หรือไม่ */
                        if (array_key_exists('updated', $item) && $item['updated']) {
                            $updated = Budget::find($item['id']);
                            $updated->budget_type_id  = $item['budget_type_id'];
                            $updated->total           = $item['total'];
                            $updated->save();
                        }

                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag removed หรือไม่ */
                        if (array_key_exists('removed', $item) && $item['removed']) {
                            Budget::find($item['id'])->delete();
                        }
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'activity'  => $activity->load('project','project.plan','budgets','budgets.type')
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
            $activity = BudgetActivity::find($id);
            /** Store deleted budget's id to variable */
            $deletedId = $activity->id;

            if($activity->delete()) {
                /** ลบข้อมูลในตาราง budgets ด้วย */
                Budget::where('activity_id', $deletedId)->delete();

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

    public function toggle(Request $req, $id)
    {
        try {
            $activity = BudgetActivity::find($id);
            $activity->status = $req['status'];

            if($activity->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating status successfully!!',
                    'activity'  => $activity->load('project','project.plan','budgets','budgets.type')
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
