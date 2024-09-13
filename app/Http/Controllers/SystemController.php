<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\BudgetYear;

class SystemController extends Controller
{
    public function getAll(Request $req)
    {
        return [
            'budgetYear' => BudgetYear::where('actived', 1)->first(),
        ];
    }

    // public function getById($id)
    // {
    //     return Budget::find($id);
    // }

    // public function getInitialFormData()
    // {
    //     return [
    //         'plans'     => BudgetPlan::orderBy('plan_type_id')->orderBy('plan_no')->get(),
    //         'projects'  => BudgetProject::all(),
    //         'types'     => BudgetType::all(),
    //     ];
    // }

    // public function store(Request $req)
    // {
    //     try {
    //         $budget = new Budget();
    //         $budget->name       = $req['name'];
    //         $budget->type_id    = $req['type_id'];
    //         $budget->project_id = $req['project_id'];
    //         $budget->gfmis_id   = $req['gfmis_id'];
    //         $budget->year       = $req['year'];
    //         $budget->status     = 1;

    //         if($budget->save()) {
    //             return [
    //                 'status'    => 1,
    //                 'message'   => 'Insertion successfully!!',
    //                 'Budget'  => $budget
    //             ];
    //         } else {
    //             return [
    //                 'status'    => 0,
    //                 'message'   => 'Something went wrong!!'
    //             ];
    //         }
    //     } catch (\Exception $ex) {
    //         return [
    //             'status'    => 0,
    //             'message'   => $ex->getMessage()
    //         ];
    //     }
    // }

    // public function update(Request $req, $id)
    // {
    //     try {
    //         $budget = Budget::find($id);
    //         $budget->name       = $req['name'];
    //         $budget->type_id    = $req['type_id'];
    //         $budget->project_id = $req['project_id'];
    //         $budget->gfmis_id   = $req['gfmis_id'];
    //         $budget->year       = $req['year'];
    //         $budget->status     = $req['status'] ? 1 : 0;

    //         if($budget->save()) {
    //             return [
    //                 'status'    => 1,
    //                 'message'   => 'Updating successfully!!',
    //                 'Budget'  => $budget
    //             ];
    //         } else {
    //             return [
    //                 'status'    => 0,
    //                 'message'   => 'Something went wrong!!'
    //             ];
    //         }
    //     } catch (\Exception $ex) {
    //         return [
    //             'status'    => 0,
    //             'message'   => $ex->getMessage()
    //         ];
    //     }
    // }

    // public function destroy(Request $req, $id)
    // {
    //     try {
    //         $budget = Budget::find($id);

    //         if($budget->delete()) {
    //             return [
    //                 'status'    => 1,
    //                 'message'   => 'Deleting successfully!!',
    //                 'id'        => $id
    //             ];
    //         } else {
    //             return [
    //                 'status'    => 0,
    //                 'message'   => 'Something went wrong!!'
    //             ];
    //         }
    //     } catch (\Exception $ex) {
    //         return [
    //             'status'    => 0,
    //             'message'   => $ex->getMessage()
    //         ];
    //     }
    // }
}
