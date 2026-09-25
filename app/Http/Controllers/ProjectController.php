<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Project;
use App\Models\Place;
use App\Models\Employee;
use App\Models\Department;

class ProjectController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $year    = $req->get('year');
        $name       = $req->get('name');
        $plan       = $req->get('plan');
        $status     = $req->get('status');

        $projects = Project::with('owner','division','budget','budget.type','budget.activity')
                        ->with('budget.activity.project','budget.activity.project.plan')
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->when(!empty($plan), function($q) use ($plan) {
                            $q->whereRelation('budget.activity.project', 'plan_id', $plan);
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->paginate(10);

        return $projects;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $year    = $req->get('year');
        $name       = $req->get('name');
        $plan       = $req->get('plan');
        $status     = $req->get('status');

        $projects = Project::with('owner','division','budget','budget.type','budget.activity')
                        ->with('budget.activity.project','budget.activity.project.plan')
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%');
                        })
                        ->when(!empty($plan), function($q) use ($plan) {
                            $q->whereRelation('budget.activity.project', 'plan_id', $plan);
                        })
                        ->when($status != '', function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->get();

        return $projects;
    }

    public function getById($id)
    {
        return Project::with('owner','division','budget','budget.type','budget.activity')
                    ->with('budget.activity.project','budget.activity.project.plan')
                    ->find($id);
    }

    public function getInitialFormData()
    {
        return [
            'employees'     => Employee::with('prefix')->whereIn('status', [1,2])->get(),
            'departments'   => Department::with('divisions')->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $project = new Project();
            $project->name           = $req['name'];
            $project->year           = $req['year'];
            $project->project_type_id = $req['project_type_id'];
            $project->budget_id      = $req['budget_id'];
            $project->department_id  = $req['department_id'];
            $project->division_id    = $req['division_id'];
            $project->owner_id       = $req['owner_id'];
            $project->from_date      = $req['from_date'];
            $project->to_date        = $req['to_date'];
            $project->remark         = $req['remark'];
            $project->status         = 1;

            if($project->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'project'    => $project
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
            $project = Project::find($id);
            $project->name           = $req['name'];
            $project->year           = $req['year'];
            $project->project_type_id = $req['project_type_id'];
            $project->budget_id      = $req['budget_id'];
            $project->department_id  = $req['department_id'];
            $project->division_id    = $req['division_id'];
            $project->owner_id       = $req['owner_id'];
            $project->from_date      = $req['from_date'];
            $project->to_date        = $req['to_date'];
            $project->remark         = $req['remark'];

            if($project->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'project'   => $project
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
            $project = Project::find($id);

            if($project->delete()) {
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
}
