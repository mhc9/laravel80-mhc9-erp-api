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

class ProjectController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $year    = $req->get('year');
        $name       = $req->get('name');
        // $plan       = $req->get('plan');
        // $status     = $req->get('status');

        $activities = Project::with('owner','division','place')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('year', $year);
                    })
                    ->when(!empty($name), function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%');
                    })
                    // ->when(!empty($plan), function($q) use ($plan) {
                    //     $q->whereHas('project.plan', function($sq) use ($plan) {
                    //         $sq->where('plan_id', $plan);
                    //     });
                    // })
                    // ->when($status != '', function($q) use ($status) {
                    //     $q->where('status', $status);
                    // })
                    ->paginate(10);

        return $activities;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $activities = Project::with('type','project','project.plan')
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
        return Project::find($id);
    }

    public function getInitialFormData()
    {
        return [
            'places'    => Place::all(),
            'employees' => Employee::with('prefix')->whereIn('status', [1,2])->get(),
        ];
    }

    public function store(Request $req)
    {
        try {
            $budget = new Project();
            $budget->name       = $req['name'];
            $budget->year       = $req['year'];
            $budget->project_type_id = $req['project_type_id'];
            $budget->owner_id   = $req['owner_id'];
            $budget->place_id   = $req['place_id'];
            $budget->from_date  = $req['from_date'];
            $budget->to_date    = $req['to_date'];
            $budget->status     = 1;

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'Budget'    => $budget
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
            $budget = Project::find($id);
            $budget->name       = $req['name'];
            $budget->year       = $req['year'];
            $budget->project_type_id = $req['project_type_id'];
            $budget->owner_id   = $req['owner_id'];
            $budget->place_id   = $req['place_id'];
            $budget->from_date  = $req['from_date'];
            $budget->to_date    = $req['to_date'];

            if($budget->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'Budget'  => $budget
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
            $budget = Project::find($id);

            if($budget->delete()) {
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
