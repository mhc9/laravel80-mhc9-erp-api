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

    public function storePayments(Request $req, $id)
    {
        try {
            if($this->budgetExpenseService->storePayments($id, $req->all())) {
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

    public function updatePayments(Request $req, $id, $paymentId)
    {
        try {
            if($this->budgetExpenseService->updatePayments($id, $paymentId, $req->all())) {
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

    public function destroyPayments(Request $req, $id, $paymentId)
    {
        try {
            if($this->budgetExpenseService->destroyPayments($id, $paymentId)) {
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

    public function getWithholdingTax(Request $req, $id, $paymentId)
    {
        try {
            if($withholdingTax = $this->budgetExpenseService->getWithholdingTax($id, $paymentId)) {
                $supplier = $withholdingTax->supplier->load('changwat','amphur','tambon','bank');

                return [
                    'id'    => $withholdingTax->id,
                    'certificate_no' => $withholdingTax->voucher_no . ' / ' . ($withholdingTax->year + 543),
                    'fiscal_year'   => $withholdingTax->year + 543,
                    'payer' => [
                        'name'      => 'ศูนย์สุขภาพจิตที่ 9',
                        'address'   => '86 ถ.ช้างเผือก ต.ในเมือง อ.เมือง จ.นครราชสีมา 30000',
                        'tax_id'    => '0994002880291'
                    ],
                    'payment_document' => [
                        'document_no'   => $withholdingTax->voucher_no . ' / ' . ($withholdingTax->year + 543),
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

    public function getVoucherAttachment(Request $req, $id, $paymentId)
    {
        try {
            if($voucherAttachment = $this->budgetExpenseService->getVoucherAttachment($id, $paymentId)) {
                $supplier = $voucherAttachment->supplier->load('changwat','amphur','tambon','bank');

                return [
                    'id'                => $voucherAttachment->id,
                    'formCode'          => '4117',
                    'taxpayerEntityType' => $voucherAttachment->supplier->tax_type_id == 1 ? 'INDIVIDUAL' : 'JURISTIC_PERSON',
                    'agency' => [
                        'name'          => 'ศูนย์สุขภาพจิตที่ 9',
                        'address'       => '86 ถ.ช้างเผือก ต.ในเมือง อ.เมือง จ.นครราชสีมา',
                        'postalCode'    => '30000',
                        'telephone'     => '044-253-000',
                        'fax'           => '044-253-000'
                    ],
                    'paymentRequestNo'  => $voucherAttachment->voucher_no,
                    'receiptNo'         => null,
                    'month'             => $voucherAttachment->month,
                    'buddhistYear'      => (int)$voucherAttachment->year + 543,
                    'items' => [[
                        'id'            => $voucherAttachment->id,
                        'sequence'      => '1',
                        'taxId'         => $supplier->tax_no,
                        'branchNo'      => '',
                        'vendorName'    => $supplier->name,
                        'vendorAddress' => $supplier->full_address,
                        'incomeType'    => $voucherAttachment->source->name ?? '',
                        'voucherAmount' => $voucherAttachment->amount,
                        'vatAmount'     => $voucherAttachment->supplier->tax_type_id == 1 ? '0.00' : $voucherAttachment->vat_amount,
                        'goodsServiceAmount' => $voucherAttachment->supplier->tax_type_id == 1 ? $voucherAttachment->amount : $voucherAttachment->base_amount,
                        'withholdingTaxAmount' => $voucherAttachment->tax_amount,
                        'penaltyAmount' => '0.00',
                        'netAmount'     => $voucherAttachment->net_total
                    ]],
                    'totals' => [
                        'voucherAmount' => $voucherAttachment->amount,
                        'vatAmount' => $voucherAttachment->supplier->tax_type_id == 1 ? '0.00' : $voucherAttachment->vat_amount,
                        'goodsServiceAmount' => $voucherAttachment->supplier->tax_type_id == 1 ? $voucherAttachment->amount : $voucherAttachment->base_amount,
                        'withholdingTaxAmount' => $voucherAttachment->tax_amount,
                        'penaltyAmount' => '0.00',
                        'netAmount' => $voucherAttachment->net_total
                    ],
                    'signer'            => null
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

    public function getDisbursementMemo(Request $req, $id, $paymentId)
    {
        try {
            if($memo = $this->budgetExpenseService->getDisbursementMemo($id, $paymentId)) {
                $supplier = $memo->supplier->load('changwat','amphur','tambon','bank');
                $expense = $this->budgetExpenseService->getById($id);
                $userEmployee = auth()->user()->employee->load('prefix','position','level');
                $fiscalYear = thainumDigit((int)$expense->year + 543);

                return response()->json([
                    'id'                => $memo->id,
                    'document_no'       => thainumDigit($memo->doc_no),
                    'document_date'     => $memo->doc_date,
                    'department'        => thainumDigit('ศูนย์สุขภาพจิตที่ 9'),
                    'department_unit'   => 'งานการเงินและบัญชี',
                    'phone'             => thainumDigit('0 4425 6729'),
                    'fax'               => thainumDigit('0 4425 6730'),
                    'subject'           => 'ขออนุมัติเบิกเงิน' . $expense->budget->type->name . ' ประจำปีงบประมาณ ' . $fiscalYear,
                    'recipient'         => 'อธิบดีกรมสุขภาพจิต',
                    'fiscal_year'       => $fiscalYear,
                    'plan'              => thainumDigit($expense->budget->activity->project->plan->name),
                    'output'            => thainumDigit($expense->budget->activity->project->name),
                    'activity'          => thainumDigit($expense->budget->activity->name),
                    'budget_type'       => thainumDigit($expense->budget->type->name),
                    'payee_name'        => thainumDigit($supplier->name),
                    'expense_description' => thainumDigit($expense->expenseType->name . ($expense->project->project_type_id == 1 ? ' ' . $expense->project->name : ' (' . $expense->project->name . ')')),
                    'expense_period'    => MONTH_LONG_NAMES[str_pad($memo->month, 2, '0', STR_PAD_LEFT)] . ' ' . thainumDigit((int)$memo->year + 543),
                    'amount'            => $memo->amount,
                    'requester_name'    => $userEmployee->prefix->name . $userEmployee->firstname . ' ' . $userEmployee->lastname,
                    'requester_position' => $userEmployee->position->name . $userEmployee->level->name,
                    'approver_name'     => 'นางสาวจุฑามาศ วรรณศิลป์',
                    'approver_position' => thainumDigit('ผู้อำนวยการศูนย์สุขภาพจิตที่ 9'),
                    'approved_date'     => MONTH_LONG_NAMES[str_pad(Carbon::now()->month, 2, '0', STR_PAD_LEFT)] . ' ' . thainumDigit((int)Carbon::now()->year + 543),
                    'reference_no'      => thainumDigit($memo->ref_no),
                    'disbursement_no'   => thainumDigit($memo->voucher_no),
                ]);
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
