<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Services\BudgetExpenseService;
use App\Models\BudgetExpense;
use App\Models\BudgetExpenseDetail;
use App\Models\BudgetExpenseType;

class BudgetExpenseController extends Controller
{
    public function __construct (protected BudgetExpenseService $budgetExpenseService) 
    {
        // code ...
    }

    public function search(Request $req)
    {
        return $this->budgetExpenseService->search($req->all());
    }

    public function getAll()
    {
        return $this->budgetExpenseService->getAll();
    }

    public function getById($id)
    {
        return $this->budgetExpenseService->getById($id);
    }

    public function getInitialFormData()
    {
        return $this->budgetExpenseService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            if($newExpense = $this->budgetExpenseService->create($req->all())) {
                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'expense'       => $newExpense
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
            $attendanceData = addMultipleInputs(
                $req->except(['id','check_image']),
                $req->hasFile('check_image') ? [
                    'check_image' => $this->budgetExpenseService->updateImage($id, $req->file('check_image'))->check_image,
                ] : []
            );

            if($updatedAtt = $this->budgetExpenseService->update($id, $attendanceData)) {
                return [
                    'status'        => 1,
                    'message'       => 'Updating successfully!!',
                    'attendance'    => $updatedAtt
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
            if($this->budgetExpenseService->destroy($id)) {
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

    public function storeDetails(Request $req, $id)
    {
        try {
            if($this->budgetExpenseService->storeDetails($id, $req->all())) {
                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
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

    public function updateDetails(Request $req, $id, $detailId)
    {
        try {
            if($this->budgetExpenseService->updateDetails($id, $detailId, $req->all())) {
                return [
                    'status'    => 1,
                    'message'   => 'Update successfully!!',
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
