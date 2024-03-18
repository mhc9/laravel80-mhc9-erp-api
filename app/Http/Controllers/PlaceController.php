<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Services\PlaceService;

class PlaceController extends Controller
{
    public function __construct(protected PlaceService $placeService)
    {
        //
    }

    public function search(Request $req)
    {
        return $this->placeService->search($req->query());
    }

    public function getAll(Request $req)
    {
        return $this->placeService->findAll($req->query());
    }

    public function getById($id)
    {
        return $this->placeService->find($id);
    }

    public function getInitialFormData()
    {
        return $this->placeService->initForm();
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
