<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Element\Field;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\ComplexType\TblWidth as IndentWidth;
use App\Models\Loan;
use App\Models\LoanContract;
use App\Models\LoanContractDetail;
use App\Models\LoanRefund;
use App\Models\LoanRefundDetail;
use App\Models\LoanRefundBudget;
use App\Models\Expense;
use App\Models\Department;
use App\Services\LoanRefundService;

class LoanRefundController extends Controller
{
    public function __construct(
        protected LoanRefundService $refundService
    ) {

    }

    public function search(Request $req)
    {
        return $this->refundService->search($req->all());
    }

    public function getAll(Request $req)
    {
        return $this->refundService->getAll();
    }

    public function getById($id)
    {
        return $this->refundService->getById($id);
    }

    public function getInitialFormData()
    {
        return $this->refundService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            $refund = new LoanRefund();
            $refund->doc_no         = $req['doc_no'];
            $refund->doc_date       = $req['doc_date'];
            $refund->contract_id    = $req['contract_id'];
            $refund->refund_type_id = $req['refund_type_id'];
            $refund->employee_id    = $req['employee_id'];
            $refund->year           = $req['year'];
            $refund->budget_total   = currencyToNumber($req['budget_total']);
            $refund->item_total     = currencyToNumber($req['item_total']);
            $refund->order_total    = currencyToNumber($req['order_total']);
            $refund->net_total      = currencyToNumber($req['net_total']);
            $refund->balance        = currencyToNumber($req['balance']);
            $refund->is_over20      = $req['is_over20'];
            $refund->over20_no      = $req['over20_no'];
            $refund->over20_date    = $req['over20_date'];
            $refund->over20_reason  = $req['over20_reason'];
            $refund->return_no      = $req['return_no'];
            $refund->return_date    = $req['return_date'];
            $refund->return_topic   = $req['return_topic'];
            $refund->return_reason  = $req['return_reason'];
            // $refund->remark         = $req['remark'];
            $refund->status         = 'N';

            if($refund->save()) {
                /** เพิ่มรายการค่าใช้จ่ายจริง */
                foreach($req['items'] as $item) {
                    $newDetail = new LoanRefundDetail();
                    $newDetail->refund_id          = $refund->id;
                    $newDetail->contract_detail_id = $item['contract_detail_id'];
                    $newDetail->description        = $item['description'];
                    $newDetail->total              = currencyToNumber($item['total']);
                    $newDetail->save();
                }

                /** เพิ่มรายการงบประมาณ */
                foreach($req['budgets'] as $budget) {
                    $newBudget = new LoanRefundBudget();
                    $newBudget->refund_id       = $refund->id;
                    $newBudget->loan_budget_id  = $budget['loan_budget_id'];
                    $newBudget->budget_id       = $budget['budget_id'];
                    $newBudget->total           = currencyToNumber($budget['total']);
                    $newBudget->save();
                }

                /** อัตเดต status ของตาราง loan_contracts เป็น 3=รอเคลียร์ **/
                $contract = LoanContract::find($req['contract_id'])->update(['status' => 3]);

                /** Log info */
                Log::channel('daily')->info('Added new refund ID:' .$refund->id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'refund'    => $refund->load($this->refundService->getRelations())
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
            $refund = LoanRefund::find($id);
            $refund->doc_no         = $req['doc_no'];
            $refund->doc_date       = $req['doc_date'];
            $refund->contract_id    = $req['contract_id'];
            $refund->refund_type_id = $req['refund_type_id'];
            $refund->employee_id    = $req['employee_id'];
            $refund->year           = $req['year'];
            $refund->budget_total   = currencyToNumber($req['budget_total']);
            $refund->item_total     = currencyToNumber($req['item_total']);
            $refund->order_total    = currencyToNumber($req['order_total']);
            $refund->net_total      = currencyToNumber($req['net_total']);
            $refund->balance        = currencyToNumber($req['balance']);
            $refund->is_over20      = $req['is_over20'];
            $refund->over20_no      = $req['over20_no'];
            $refund->over20_date    = $req['over20_date'];
            $refund->over20_reason  = $req['over20_reason'];
            $refund->return_no      = $req['return_no'];
            $refund->return_date    = $req['return_date'];
            $refund->return_topic   = $req['return_topic'];
            $refund->return_reason  = $req['return_reason'];
            // $refund->remark         = $req['remark'];

            if($refund->save()) {
                foreach($req['items'] as $item) {
                    if (!array_key_exists('refund_id', $item)) {
                        $detail = new LoanRefundDetail();
                        $detail->refund_id            = $refund->id;
                        $detail->contract_detail_id   = $item['contract_detail_id'];
                        $detail->description          = $item['description'];
                        $detail->total                = currencyToNumber($item['total']);
                        $detail->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag removed หรือไม่ */
                        if (array_key_exists('removed', $item) && $item['removed']) {
                            LoanRefundDetail::find($item['id'])->delete();
                        }
                    }
                }

                foreach($req['budgets'] as $bg) {
                    if (!array_key_exists('refund_id', $bg)) {
                        $budget = new LoanRefundBudget();
                        $budget->refund_id        = $refund->id;
                        $budget->loan_budget_id   = $bg['loan_budget_id'];
                        $budget->budget_id        = $bg['budget_id'];
                        $budget->total            = currencyToNumber($bg['total']);
                        $budget->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag removed หรือไม่ */
                        if (array_key_exists('removed', $bg) && $bg['removed']) {
                            LoanRefundBudget::find($bg['id'])->delete();
                        }
                    }
                }

                /** Log info */
                Log::channel('daily')->info('Updated refund ID:' .$id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'refund'    => $refund->load($this->refundService->getRelations())
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
            $refund = LoanRefund::find($id);

            /** สร้างออบเจ็ค contract จากรหัส contract_id ของ $refund */
            $contract = LoanContract::find($refund->contract_id);

            if($refund->delete()) {
                /** ลบรายการในตาราง loan_refund_details */
                LoanRefundDetail::where('refund_id', $id)->delete();

                /** ลบรายการในตาราง loan_refund_budgets */
                LoanRefundBudget::where('refund_id', $id)->delete();

                /** Revert status ของตาราง loan_contracts เป็น 2=เงินเข้าแล้ว **/
                $contract->update(['status' => 2]);

                /** Revert status ของตาราง loans เป็น 4=เงินเข้าแล้ว **/
                Loan::find($contract->loan_id)->update(['status' => 4]);

                /** Log info */
                Log::channel('daily')->info('Deleted refund ID:' .$id. ' by ' . auth()->user()->name);

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

    public function approve(Request $req, $id)
    {
        try {
            $refund = LoanRefund::find($id);
            $refund->approved_date  = $req['approved_date'];
            $refund->bill_no        = $req['bill_no'];
            $refund->bill_date      = $req['bill_date'];
            $refund->status         = 'Y';

            if($refund->save()) {
                /** อัตเดต status ของตาราง loan_contracts เป็น 4=เคลียร์แล้ว **/
                $contract = LoanContract::find($req['contract_id']);
                $contract->status = 4;
                $contract->save();

                /** อัตเดต status ของตาราง loans เป็น 5=เคลียร์แล้ว **/
                Loan::find($contract->loan_id)->update(['status' => 5]);

                /** Log info */
                Log::channel('daily')->info('Approval of refund ID:' .$id. ' was operated by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Approval successfully!!',
                    'refund'    => $refund->load($this->refundService->getRelations())
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

    public function receipt(Request $req, $id)
    {
        try {
            $refund = LoanRefund::find($id);
            $refund->receipt_no     = $req['receipt_no'];
            $refund->receipt_date   = $req['receipt_date'];

            if($refund->save()) {
                /** อัตเดต status ของตาราง loan_contracts เป็น 4=เคลียร์แล้ว **/
                // $contract = LoanContract::find($req['contract_id']);
                // $contract->status = 4;
                // $contract->save();

                /** อัตเดต status ของตาราง loans เป็น 5=เคลียร์แล้ว **/
                // Loan::find($contract->loan_id)->update(['status' => 5]);

                /** Log info */
                Log::channel('daily')->info('Receipt of refund ID:' .$id. ' was operated by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'refund'    => $refund->load($this->refundService->getRelations())
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

    public function getForm(Request $req, $id)
    {
        $refund = $this->refundService->getById($id);

        $template = 'refund.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/loans/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('department', $refund->contract->loan->division ? $refund->contract->loan->division->name : $refund->contract->loan->department->name);
        $word->setValue('docNo', $refund->doc_no);
        $word->setValue('docDate', convDbDateToLongThDate($refund->doc_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        /** =================== รายละเอียดโครงการ =================== */
        $word->setValue('loanDocNo', $refund->contract->loan->doc_no);
        $word->setValue('loanDocDate', convDbDateToLongThDate($refund->contract->loan->doc_date));

        if ($refund->contract->loan->loan_type_id == 1) {
            $word->setValue('objective', 'ได้ขออนุมัติยืมเงินราชการในการจัด' . $refund->contract->loan->project_name);
        } else {
            $word->setValue('objective', 'เรื่อง ขออนุมัติยืมเงินราชการ เพื่อเป็นค่าใช้จ่ายในการเดินทางไปราชการเข้าร่วม' . $refund->contract->loan->project_name);
        }

        $word->setValue('projectSDate', convDbDateToLongThDate($refund->contract->loan->project_sdate));
        $word->setValue('projectEDate', convDbDateToLongThDate($refund->contract->loan->project_edate));

        /** สถานที่จัด */
        $placeText = '';
        foreach($refund->contract->loan->courses as $key => $course) {
            $placeText .= ($key > 0 ? 'และ' : '') . $course->place->name . ' จังหวัด' .$course->place->changwat->name;
        }
        $word->setValue('place', $placeText);

        /** แผนงาน */
        $budgetText = '';
        foreach($refund->contract->loan->budgets as $data) {
            $budgetText .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
            $budgetText .= sizeof($refund->contract->loan->budgets) > 1 ? ' จำนวนเงิน ' . number_format($data->total) . ' บาท ' : '';
        }
        $word->setValue('budget', $budgetText);

        $word->setValue('budgetTotal', number_format($refund->contract->loan->budget_total));
        $word->setValue('budgetTotalText', baht_text($refund->contract->loan->budget_total));
        $word->setValue('completed', $refund->contract->loan->loan_type_id == 1 ? 'ได้ดำเนินการจัดโครงการฯ เสร็จสิ้นแล้ว ' : '');

        /** Style ของตาราง */
        $tableStyle = [
            'borderSize' => 'none',
            'width' => 93 * 50,
            'indent' => new IndentWidth(700),
            'unit' => TblWidth::PERCENT, //TWIP | PERCENT
        ];
        $couseFontStyle = ['name' => 'TH SarabunIT๙', 'size' => 14, 'bold' => true];
        $itemFontStyle = ['name' => 'TH SarabunIT๙', 'size' => 14];

        /** =================== รายการจัดซือจัดจ้าง =================== */
        $orders = array_filter($refund->details->toArray(), function($detail) { return $detail['contract_detail']['expense_group'] == 2; });
        $orderTable = new Table($tableStyle);

        foreach($orders as $order => $detail) {
            $orderTable->addRow();
            $orderTable
                ->addCell(50 * 50)
                ->addText('- ' . $detail['contract_detail']['expense']['name'] . ' ' . $detail['description'], $itemFontStyle, ['spaceAfter' => 0]);
            $orderTable
                ->addCell(50 * 50)
                ->addText('เป็นเงิน  ' . number_format($detail['total']) . ' บาท', $itemFontStyle, ['spaceAfter' => 0, 'align' => 'right']);
        }

        /** เพิ่มแถวยอดรวมเป็นเงิน */
        $orderTable->addRow();
        $orderTable
            ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
            ->addText('รวมเป็นเงิน ' . number_format($refund->order_total) . ' บาท ', $couseFontStyle, ['spaceAfter' => 0, 'align' => 'right']);

        $word->setComplexBlock('orders', $orderTable);

        $word->setValue('orderNetTotal', number_format($refund->order_total));
        $word->setValue('orderNetTotalText', baht_text($refund->order_total));

        if (!array_any($refund->details->toArray(), function($detail) { return $detail['contract_detail']['expense_group'] == 2; })) {
            $word->cloneBlock('haveOrders', 0, true, true);
        } else {
            $word->cloneBlock('haveOrders', 1, true, true);
        }

        /** =================== รายการค่าใช้จ่าย =================== */
        $itemTable = new Table($tableStyle);
        $courseTotal = 0;

        if ($refund->contract->loan->expense_calc == 1) {
            /** คิดรวม */
            $items = array_filter($refund->details->toArray(), function($detail) { return $detail['contract_detail']['expense_group'] == 1; });
            foreach($items as $item => $detail) {
                $description = ($detail['description'] != '' && $detail['contract_detail']['expense']['pattern'] != '')
                                ? replaceExpensePatternFromDesc($detail['contract_detail']['expense']['pattern'], $detail['description'])
                                : '';

                $itemTable->addRow();
                $itemTable
                    ->addCell(50 * 50)
                    ->addText('- ' . $detail['contract_detail']['expense']['name'] . ' ' . $description, $itemFontStyle, ['spaceAfter' => 0]);
                $itemTable
                    ->addCell(50 * 50)
                    ->addText('เป็นเงิน  ' . number_format($detail['total']) . ' บาท', $itemFontStyle, ['spaceAfter' => 0, 'align' => 'right']);

                /** คำนวณยอดรวมเป็นเงิน */
                $courseTotal += $detail['total'];
            }

            /** เพิ่มแถวยอดรวมเป็นเงิน */
            $itemTable->addRow();
            $itemTable
                ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
                ->addText('รวมเป็นเงิน ' . number_format($courseTotal) . ' บาท ', $couseFontStyle, ['spaceAfter' => 0, 'align' => 'right']);
        } else {
            /** คิดแยกวันที่ */
            foreach($refund->contract->loan->courses as $course => $cs) {
                $courseTotal = 0;

                /** เพิ่มแถวในตาราง */
                $itemTable->addRow();
                $itemTable
                    ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
                    ->addText('วันที่ ' . convDbDateToLongThDateRange($cs->course_date, $cs->course_edate) . ' ณ ' . $cs->place->name, $couseFontStyle);

                $items = array_filter($refund->details->toArray(), function($detail) use ($cs) { return $detail['contract_detail']['expense_group'] == 1 && $detail['contract_detail']['loan_detail']['course_id'] == $cs->id; });
                foreach($items as $item => $detail) {
                    /** สร้างรายละเอียดของค่าใช้จ่ายจากสูตร */
                    $description = ($detail['description'] != '' && $detail['contract_detail']['expense']['pattern'])
                                    ? replaceExpensePatternFromDesc($detail['contract_detail']['expense']['pattern'], $detail['description'])
                                    : '';

                    /** เพิ่มแถวในตาราง */
                    $itemTable->addRow();
                    $itemTable
                        ->addCell(50 * 50)
                        ->addText('- ' . $detail['contract_detail']['expense']['name'] . ' ' . $description, $itemFontStyle, ['spaceAfter' => 0]);
                    $itemTable
                        ->addCell(50 * 50)
                        ->addText('เป็นเงิน  ' . number_format($detail['total']) . 'บาท', $itemFontStyle, ['spaceAfter' => 0, 'align' => 'right']);

                    /** คำนวณยอดรวมเป็นเงิน */
                    $courseTotal += $detail['total'];
                }

                /** เพิ่มแถวยอดรวมเป็นเงิน */
                $itemTable->addRow();
                $itemTable
                    ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
                    ->addText('รวมเป็นเงิน ' . number_format($courseTotal) . ' บาท ', $couseFontStyle, ['spaceAfter' => 0, 'align' => 'right']);
            }
        }

        /** เพิ่มรายการลงในตาราง */
        $word->setComplexBlock('items', $itemTable);

        if (sizeof($refund->details) == 1) {
            $word->cloneBlock('isProject', 0, true, true);
        } else {
            $word->cloneBlock('isProject', 1, true, true);
        }

        /** =================== ยอดรวมทั้งสิ้น =================== */
        $word->setValue('netTotal', number_format($refund->net_total, 2));
        $word->setValue('netTotalText', baht_text($refund->net_total, 2));

        /** แผนงาน */
        $_budget = '';
        foreach($refund->budgets as $data) {
            $_budget .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
            $_budget .= sizeof($refund->contract->loan->budgets) > 1 ? ' จำนวนเงิน ' . number_format($data->total) . ' บาท ' : '';
        }

        /** =================== เงื่อนไขการคืนเงิน =================== */
        if ($refund->refund_type_id == 1) {
            $word->setValue('refundType', 'และคืนเงินยืม' .$_budget. ' เป็นจำนวนเงินทั้งสิ้น ' . number_format($refund->balance, 2) . ' บาท (' . baht_text($refund->balance, 2) . ')');
        } else if ($refund->refund_type_id == 2) {
            $word->setValue('refundType', 'และเบิกเงินเพิ่ม เป็นจำนวนเงินทั้งสิ้น ' . number_format(abs($refund->balance)) . ' บาท (' . baht_text(abs($refund->balance)) . ')');
        } else {
            $word->setValue('refundType', '');
        }

        /** =================== ผู้ขอ =================== */
        $word->setValue('requester', $refund->contract->loan->employee->prefix->name.$refund->contract->loan->employee->firstname . ' ' . $refund->contract->loan->employee->lastname);
        $word->setValue('requesterPosition', $refund->contract->loan->employee->position->name . ($refund->contract->loan->employee->level ? $refund->contract->loan->employee->level->name : ''));
        /** ================================== CONTENT ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getOver20(Request $req, $id)
    {
        $refund = $this->refundService->getById($id);

        $template = 'over20.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/loans/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('department', $refund->contract->loan->department->name);
        $word->setValue('docNo', $refund->over20_no);
        $word->setValue('docDate', convDbDateToLongThDate($refund->over20_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        /** =================== รายละเอียดโครงการ =================== */
        $word->setValue('loanDocNo', $refund->contract->loan->doc_no);
        $word->setValue('loanDocDate', convDbDateToLongThDate($refund->contract->loan->doc_date));

        if ($refund->contract->loan->loan_type_id == 1) {
            $word->setValue('objective', 'ได้ขออนุมัติยืมเงินราชการในการจัด' . $refund->contract->loan->project_name);
        } else {
            $word->setValue('objective', 'ขออนุมัติยืมเงินราชการ เพื่อเป็นค่าใช้จ่ายในการเดินทางไปราชการเข้าร่วม' . $refund->contract->loan->project_name);
        }

        $word->setValue('projectSDate', convDbDateToLongThDate($refund->contract->loan->project_sdate));
        $word->setValue('projectEDate', convDbDateToLongThDate($refund->contract->loan->project_edate));

        /** สถานที่จัด */
        $placeText = '';
        foreach($refund->contract->loan->courses as $key => $course) {
            $placeText .= ($key > 0 ? 'และ' : '') . $course->place->name . ' จังหวัด' .$course->place->changwat->name;
        }
        $word->setValue('place', $placeText);

        /** แผนงาน */
        $budgetText = '';
        foreach($refund->contract->loan->budgets as $data) {
            $budgetText .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
            $budgetText .= sizeof($refund->contract->loan->budgets) > 1 ? ' จำนวนเงิน ' . number_format($data->total) . ' บาท ' : '';
        }
        $word->setValue('budget', $budgetText);

        // $word->setValue('place', $refund->contract->loan->courses[0]->place->name . ' จังหวัด' .$refund->contract->loan->courses[0]->place->changwat->name);

        /** =================== แผนงาน =================== */
        // $budgets = '';
        // foreach($refund->contract->loan->budgets as $data) {
        //     $budgets .= $data->budget->project->plan->name . ' ' . $data->budget->project->name  . ' ' . $data->budget->name;
        // }
        // $word->setValue('budget', $budgets);

        $word->setValue('budgetTotal', number_format($refund->contract->loan->budget_total));
        $word->setValue('budgetTotalText', baht_text($refund->contract->loan->budget_total));
        $word->setValue('over20Reason', $refund->over20_reason);

        /** =================== รวมทั้งสิ้น =================== */
        $word->setValue('netTotal', number_format($refund->net_total,2 ));
        $word->setValue('netTotalText', baht_text($refund->net_total,2 ));

        /** =================== ยอดคืน =================== */
        $word->setValue('balance', number_format($refund->balance, 2));
        $word->setValue('balanceText', baht_text($refund->balance, 2));

        /** =================== ผู้ขอ =================== */
        $word->setValue('requester', $refund->contract->loan->employee->prefix->name.$refund->contract->loan->employee->firstname . ' ' . $refund->contract->loan->employee->lastname);
        $word->setValue('requesterPosition', $refund->contract->loan->employee->position->name . ($refund->contract->loan->employee->level ? $refund->contract->loan->employee->level->name : ''));
        /** ================================== CONTENT ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getReturn(Request $req, $id)
    {
        $refund = $this->refundService->getById($id);

        $template = 'return.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/loans/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('department', $refund->contract->loan->department->name);
        $word->setValue('docNo', $refund->doc_no);
        $word->setValue('docDate', convDbDateToLongThDate($refund->doc_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        /** =================== รายละเอียดโครงการ =================== */
        $word->setValue('returnNo', $refund->return_no);
        $word->setValue('returnDate', convDbDateToLongThDate($refund->return_date));
        $word->setValue('returnTopic', $refund->return_topic);

        if ($refund->contract->loan->loan_type_id == 1) {
            $word->setValue('objective', 'ได้ขออนุมัติยืมเงินราชการในการจัด' . $refund->contract->loan->project_name);
        } else {
            $word->setValue('objective', 'เรื่อง ขออนุมัติยืมเงินราชการ เพื่อเป็นค่าใช้จ่ายในการเดินทางไปราชการเข้าร่วม' . $refund->contract->loan->project_name);
        }

        $word->setValue('projectSDate', convDbDateToLongThDate($refund->contract->loan->project_sdate));
        $word->setValue('projectEDate', convDbDateToLongThDate($refund->contract->loan->project_edate));

        /** สถานที่จัด */
        $placeText = '';
        foreach($refund->contract->loan->courses as $key => $course) {
            $placeText .= ($key > 0 ? 'และ' : '') . $course->place->name . ' จังหวัด' .$course->place->changwat->name;
        }
        $word->setValue('place', $placeText);

        /** แผนงาน */
        $budgetText = '';
        foreach($refund->contract->loan->budgets as $data) {
            $budgetText .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
            $budgetText .= sizeof($refund->contract->loan->budgets) > 1 ? ' จำนวนเงิน ' . number_format($data->total) . ' บาท ' : '';
        }
        $word->setValue('budget', $budgetText);

        $word->setValue('budgetTotal', number_format($refund->contract->loan->budget_total));
        $word->setValue('budgetTotalText', baht_text($refund->contract->loan->budget_total));
        $word->setValue('returnReason', $refund->return_reason);

        /** =================== รวมทั้งสิ้น =================== */
        $word->setValue('netTotal', number_format($refund->net_total));
        $word->setValue('netTotalText', baht_text($refund->net_total));

        /** =================== ยอดคืน =================== */
        $word->setValue('balance', number_format($refund->balance));
        $word->setValue('balanceText', baht_text($refund->balance));

        /** =================== ผู้ขอ =================== */
        $word->setValue('requester', $refund->contract->loan->employee->prefix->name.$refund->contract->loan->employee->firstname . ' ' . $refund->contract->loan->employee->lastname);
        $word->setValue('requesterPosition', $refund->contract->loan->employee->position->name . ($refund->contract->loan->employee->level ? $refund->contract->loan->employee->level->name : ''));
        /** ================================== CONTENT ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }
}
