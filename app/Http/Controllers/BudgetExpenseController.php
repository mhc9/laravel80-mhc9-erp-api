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
                /** Update the main budget expense amount */
                $budgetExpense = $this->budgetExpenseService->getById($id);
                $budgetExpense->amount = $budgetExpense->net_total;
                $budgetExpense->save();

                return [
                    'status'            => 1,
                    'message'           => 'Insertion successfully!!',
                    'budget_expense'    => $budgetExpense
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
                /** Update the main budget expense amount */
                $budgetExpense = $this->budgetExpenseService->getById($id);
                $budgetExpense->amount = $budgetExpense->net_total;
                $budgetExpense->save();

                return [
                    'status'            => 1,
                    'message'           => 'Update successfully!!',
                    'budget_expense'    => $budgetExpense
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

    public function destroyDetails(Request $req, $id, $detailId)
    {
        try {
            if($this->budgetExpenseService->destroyDetails($id, $detailId)) {
                /** Update the main budget expense amount */
                $budgetExpense = $this->budgetExpenseService->getById($id);
                $budgetExpense->amount = $budgetExpense->net_total;
                $budgetExpense->save();

                return [
                    'status'            => 1,
                    'message'           => 'Delete successfully!!',
                    'budget_expense'    => $budgetExpense
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

    public function getWithholdingTax(Request $req, $id, $detailId)
    {
        try {
            if($withholdingTax = $this->budgetExpenseService->getWithholdingTax($id, $detailId)) {
                $supplier = $withholdingTax->supplier->load('changwat','amphur','tambon','bank');

                return [
                    'id'    => $withholdingTax->id,
                    'certificate_no' => $withholdingTax->voucher_no . ' / ' . $withholdingTax->year,
                    'fiscal_year'   => $withholdingTax->year,
                    'payer' => [
                        'name'      => 'ศูนย์สุขภาพจิตที่ 9',
                        'address'   => '86 ถ.ช้างเผือก ต.ในเมือง อ.เมือง จ.นครราชสีมา 30000',
                        'tax_id'    => '0994002880291'
                    ],
                    'payment_document' => [
                        'document_no'   => $withholdingTax->voucher_no . ' / ' . $withholdingTax->year,
                        'document_date' => ''
                    ],
                    'payee' => [
                        'name'      => $supplier->name,
                        'address'   => $supplier->full_address,
                        'tax_id'    => $supplier->tax_no
                    ],
                    'tax_items' => [[
                        'id'            => $withholdingTax->id,
                        'tax_type'      => $withholdingTax->supplier->tax_type_id == 1 ? 'PERSONAL_INCOME_TAX' : 'CORPORATE_INCOME_TAX',
                        'income_type'   => '',
                        'payment_date'  => $withholdingTax->paid_at,
                        'amount'        => $withholdingTax->supplier->tax_type_id == 1 ? $withholdingTax->amount : $withholdingTax->base_amount,
                        'tax_amount'    => $withholdingTax->tax_amount
                    ]],
                    'total_income' => $withholdingTax->supplier->tax_type_id == 1 ? $withholdingTax->amount : $withholdingTax->base_amount,
                    'total_tax' => $withholdingTax->tax_amount,
                    'total_tax_text' => baht_text($withholdingTax->tax_amount)
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

    public function getVoucherAttachment(Request $req, $id, $detailId)
    {
        try {
            if($voucherAttachment = $this->budgetExpenseService->getVoucherAttachment($id, $detailId)) {
                return [
                    'status'            => 1,
                    'message'           => 'Get voucher attachment successfully!!',
                    'voucher_attachment'   => $voucherAttachment
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
