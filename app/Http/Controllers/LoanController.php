<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use PhpOffice\PhpWord\Element\Field;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\ComplexType\TblWidth as IndentWidth;
use App\Services\LoanService;
use App\Services\LoanDetailService;
use App\Services\LoanBudgetService;
use App\Services\LoanContractService;
use App\Services\ProjectCourseService;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\LoanBudget;
use App\Models\ProjectCourse;

/**
 * [Description LoanController]
 */
class LoanController extends Controller
{   
    /**
     * @var LoanService
     */
    protected LoanService $loanService;

    protected LoanDetailService $detailService;

    /**
     * @var LoanContractService
     */
    protected LoanContractService $contractService;

    /**
     * @var ProjectCourseService
     */
    protected ProjectCourseService $courseService;

    /**
     * @var LoanBudgetService
     */
    protected LoanBudgetService $budgetService;

    public function __construct(
        LoanService $loanService,
        LoanContractService $contractService,
        ProjectCourseService $courseService,
        LoanBudgetService $budgetService,
        LoanDetailService $detailService
    ) {
        $this->loanService  = $loanService;
        $this->contractService = $contractService;
        $this->courseService = $courseService;
        $this->budgetService = $budgetService;
        $this->detailService = $detailService;
    }

    /**
     * @param Request $req
     * 
     * @return [type]
     */
    public function search(Request $req)
    {
        /** ส่งแจ้งเตือนไลน์กลุ่ม "สัญญาเงินยืม09" */
        $this->contractService->notifyRefund();

        return $this->loanService->search($req->all());
    }

    public function getAll(Request $req)
    {
        return $this->loanService->getAll();
    }

    public function getById($id)
    {
        return $this->loanService->getById($id);
    }

    public function getInitialFormData()
    {
        return $this->loanService->getFormData();
    }

    public function store(Request $req)
    {
        try {
            $loanData = Arr::add($req->except(['courses', 'budgets', 'items']), 'status', 1);

            if($loan = $this->loanService->create($loanData)) {
                $this->courseService->createMany(
                    transformManyInputs(
                        $req['courses'],
                        ['id','course_date','course_edate','room','place_id','remark'],
                        ['loan_id' => $loan->id]
                    )
                );

                $this->budgetService->createMany(
                    transformManyInputs(
                        $req['budgets'],
                        ['budget_id','total'],
                        ['loan_id' => $loan->id]
                    )
                );

                $this->detailService->createMany(
                    transformManyInputs(
                        $req['items'],
                        ['course_id','expense_id','expense_group','description','total'],
                        ['loan_id' => $loan->id]
                    ),
                    $this->courseService->getAllWithConditions(['loan_id' => $loan->id])
                );

                /** Log info */
                Log::channel('daily')->info('Added new loan ID:' .$loan->id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'loan'      => $loan->load($this->loanService->getRelations())
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
            $loanData = formatCurrency(
                $req->except(['courses', 'budgets', 'items']),
                ['budget_total','item_total','order_total','net_total']
            );
            
            if($loan = $this->loanService->update($id, $loanData)) {
                $this->courseService->updateMany(
                    onlyInputs($req['courses'], ['id','loan_id','course_date','course_edate','room','place_id','remark','removed']),
                    'loan_id',
                    ['loan_id' => $loan->id]
                );

                $this->budgetService->updateMany(
                    onlyInputs($req['budgets'], ['id','loan_id','budget_id','total','removed']),
                    'loan_id',
                    ['loan_id' => $loan->id]
                );

                $this->detailService->updateMany(
                    onlyInputs($req['items'], ['id','loan_id','course_id','expense_id','expense_group','description','total','updated','removed']),
                    'loan_id',
                    ['loan_id' => $loan->id],
                    $this->courseService->getAllWithConditions(['loan_id' => $loan->id])
                );

                /** Log info */
                Log::channel('daily')->info('Updated loan ID:' .$id. ' by ' . auth()->user()->name);

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'loan'      => $loan->load($this->loanService->getRelations())
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
            if($this->loanService->destroy($id)) {
                /** Delete loan_details data according to $id */
                $this->detailService->destroyBy(['loan_id' => $id]);

                /** Delete loan_budgets data according to $id */
                $this->budgetService->destroyBy(['loan_id' => $id]);

                /** Delete project_courses data according to $id */
                $this->courseService->destroyBy(['loan_id' => $id]);

                /** Log info */
                Log::channel('daily')->info('Deleted loan ID:' .$id. ' by ' . auth()->user()->name);

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

    public function getForm(Request $req, $id)
    {
        $loan = Loan::with('details','details.expense','department','division')
                            ->with('employee','employee.prefix','employee.position','employee.level')
                            ->with('budgets','budgets.budget','budgets.budget.activity','budgets.budget.type')
                            ->with('budgets.budget.activity.project','budgets.budget.activity.project.plan')
                            ->with('courses','courses.place','courses.place.changwat')
                            ->find($id);

        $template = 'form.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/loans/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('department', $loan->division ? $loan->division->name : $loan->department->name);
        $word->setValue('docNo', $loan->doc_no);
        $word->setValue('docDate', convDbDateToLongThDate($loan->doc_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        /** รายละเอียดโครงการ */
        $word->setValue('projectNo', $loan->project_no);
        $word->setValue('projectDate', convDbDateToLongThDate($loan->project_date));
        $word->setValue('projectOwner', $loan->project_owner);

        if ($loan->loan_type_id == 1) {
            $word->setValue('projectName', 'กำหนดจัด' . $loan->project_name);
        } else {
            $word->setValue('projectName', 'เรื่อง ขออนุมัติเดินทางไปราชการเพื่อเข้าร่วม' . $loan->project_name);
        }

        $word->setValue('projectSDate', convDbDateToLongThDate($loan->project_sdate));
        $word->setValue('projectEDate', convDbDateToLongThDate($loan->project_edate));

        /** สถานที่จัด */
        $placeText = '';
        foreach($loan->courses as $key => $course) {
            $placeText .= ($key > 0 ? 'และ' : '') . $course->place->name . ' จังหวัด' .$course->place->changwat->name;
        }
        $word->setValue('place', $placeText);

        /** แผนงาน */
        $budgetText = '';
        foreach($loan->budgets as $data) {
            $budgetText .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
            $budgetText .= sizeof($loan->budgets) > 1 ? ' จำนวนเงิน ' . number_format($data->total) . ' บาท ' : '';
        }
        $word->setValue('budget', $budgetText);

        $word->setValue('budgetTotal', number_format($loan->budget_total));
        $word->setValue('budgetTotalText', baht_text($loan->budget_total));

        /** Style ของตาราง */
        $tableStyle = [
            'borderSize' => 'none',
            'width' => 93 * 50,
            'indent' => new IndentWidth(700),
            'unit' => TblWidth::PERCENT, //TWIP | PERCENT
        ];
        $couseFontStyle = ['name' => 'TH SarabunIT๙', 'size' => 14, 'bold' => true];
        $itemFontStyle = ['name' => 'TH SarabunIT๙', 'size' => 14];

        /** รายการจัดซือจัดจ้าง */
        $orders = array_filter($loan->details->toArray(), function($detail) { return $detail['expense_group'] == 2; });
        $orderTable = new Table($tableStyle);

        foreach($orders as $order => $detail) {
            /** เพิ่มแถวในตาราง */
            $orderTable->addRow();
            $orderTable
                ->addCell(50 * 50)
                ->addText('- ' . $detail['expense']['name'] . ' ' . $detail['description'], $itemFontStyle, ['spaceAfter' => 0]);
            $orderTable
                ->addCell(50 * 50)
                ->addText('เป็นเงิน  ' . number_format($detail['total']) . 'บาท', $itemFontStyle, ['spaceAfter' => 0, 'align' => 'right']);
        }

        $orderTable->addRow();
        $orderTable
            ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
            ->addText('รวมเป็นเงิน ' . number_format($loan->order_total) . ' บาท ', $couseFontStyle, ['spaceAfter' => 0, 'align' => 'right']);

        /** เพิ่มรายการลงในตาราง */
        $word->setComplexBlock('orders', $orderTable);

        /** เงื่อนไขการแสดงรายการจัดซือจัดจ้าง */
        if (!array_any($loan->details->toArray(), function($detail) { return $detail['expense_group'] == 2; })) {
            $word->cloneBlock('haveOrders', 0, true, true);
        } else {
            $word->cloneBlock('haveOrders', 1, true, true);
        }

        /** =================== รายการค่าใช้จ่าย =================== */
        $itemTable = new Table($tableStyle);

        if ($loan->expense_calc == 1) {
            /** คิดรวม */
            $courseTotal = 0;
            $items = array_filter($loan->details->toArray(), function($detail) { return $detail['expense_group'] == 1; });

            foreach($items as $item => $detail) {
                /** สร้างรายละเอียดของค่าใช้จ่ายจากสูตร */
                $description = $detail['description'] != '' ? replaceExpensePatternFromDesc($detail['expense']['pattern'], $detail['description']) : '';

                /** เพิ่มแถวในตาราง */
                $itemTable->addRow();
                $itemTable
                    ->addCell(50 * 50)
                    ->addText('- ' . $detail['expense']['name'] . ' ' . $description, $itemFontStyle, ['spaceAfter' => 0]);
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
        } else {
            /** คิดแยกวันที่ */
            foreach($loan->courses as $course => $cs) {
                $courseTotal = 0;

                /** เพิ่มแถวในตาราง */
                $itemTable->addRow();
                $itemTable
                ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
                ->addText('วันที่ ' . convDbDateToLongThDateRange($cs->course_date, $cs->course_edate) . ' ณ ' . $cs->place->name, $couseFontStyle);
                
                $items = array_filter($loan->details->toArray(), function($detail) use ($cs) { return $detail['expense_group'] == 1 && $detail['course_id'] == $cs->id; });
                foreach($items as $item => $detail) {
                    /** สร้างรายละเอียดของค่าใช้จ่ายจากสูตร */
                    $description = $detail['description'] != '' ? replaceExpensePatternFromDesc($detail['expense']['pattern'], $detail['description']) : '';

                    /** เพิ่มแถวในตาราง */
                    $itemTable->addRow();
                    $itemTable
                        ->addCell(50 * 50)
                        ->addText('- ' . $detail['expense']['name'] . ' ' . $description, $itemFontStyle, ['spaceAfter' => 0]);
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

        $word->setComplexBlock('items', $itemTable);

        /** =================== ยอดรวมทั้งสิ้น =================== */
        $word->setValue('netTotal', number_format($loan->net_total));
        $word->setValue('netTotalText', baht_text($loan->net_total));

        /** เงื่อนไขการแสดงหมายเหตุ */
        if (sizeof($loan->details) == 1) {
            $word->cloneBlock('isProject', 0, true, true);
        } else {
            $word->cloneBlock('isProject', 1, true, true);
        }

        /** =================== ผู้ขอ =================== */
        $word->setValue('requester', $loan->employee->prefix->name.$loan->employee->firstname . ' ' . $loan->employee->lastname);
        $word->setValue('requesterPosition', $loan->employee->position->name . ($loan->employee->level ? $loan->employee->level->name : ''));
        /** ================================== CONTENT ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getContract(Request $req, $id)
    {
        $loan = Loan::with('details','details.expense','department','division')
                            ->with('employee','employee.prefix','employee.position','employee.level')
                            ->with('budgets','budgets.budget','budgets.budget.activity','budgets.budget.type')
                            ->with('budgets.budget.activity.project','budgets.budget.activity.project.plan')
                            ->with('courses','courses.place','courses.place.changwat')
                            ->find($id);

        $template = 'contract.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/loans/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('requester', $loan->employee->prefix->name.$loan->employee->firstname . ' ' . $loan->employee->lastname);
        $word->setValue('requesterPosition', $loan->employee->position->name . ($loan->employee->level ? $loan->employee->level->name : ''));
        // $word->setValue('department', $loan->department->name);
        $word->setValue('moneyType1', $loan->money_type_id == 1 ? '/' : '');
        $word->setValue('moneyType2', $loan->money_type_id == 2 ? '/' : '');
        $word->setValue('moneyType3', $loan->money_type_id == 3 ? '/' : '');
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        /** =================== รายละเอียดโครงการ =================== */
        if ($loan->loan_type_id == 1) {
            $word->setValue('projectName', 'เพื่อเป็นค่าใช้จ่ายใน' . $loan->project_name);
        } else {
            // $word->setValue('projectName', 'ตามหนังสือ ' . ($loan->division ? $loan->division->name : $loan->department->name) . ' ที่ ' . $loan->doc_no . ' ลงวันที่ ' . convDbDateToLongThDate($loan->doc_date) . 'เรื่อง ขออนุมัติยืมเงินราชการ เพื่อเป็นค่าใช้จ่ายในการเดินทางไปราชการเข้าร่วม' . $loan->project_name);
            $word->setValue('projectName', 'เพื่อเป็นค่าใช้จ่ายในการเดินทางไปราชการเข้าร่วม' . $loan->project_name);
        }

        $word->setValue('projectSDate', convDbDateToLongThDate($loan->project_sdate));
        $word->setValue('projectEDate', convDbDateToLongThDate($loan->project_edate));

        /** สถานที่จัด */
        $placeText = '';
        foreach($loan->courses as $key => $course) {
            $placeText .= ($key > 0 ? 'และ' : '') . $course->place->name . ' จังหวัด' .$course->place->changwat->name;
        }
        $word->setValue('place', $placeText);

        /** แผนงาน */
        $budgetText = '';
        foreach($loan->budgets as $data) {
            $budgetText .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
            $budgetText .= sizeof($loan->budgets) > 1 ? ' จำนวนเงิน ' . number_format($data->total) . ' บาท ' : '';
        }
        $word->setValue('budget', $budgetText);

        $word->setValue('budgetTotal', number_format($loan->budget_total));
        $word->setValue('budgetTotalText', baht_text($loan->budget_total));

        
        /** Style ของตาราง */
        $tableStyle = [
            'borderSize' => 'none',
            'width' => 93 * 50,
            'indent' => new IndentWidth(700),
            'unit' => TblWidth::PERCENT, //TWIP | PERCENT
        ];
        $couseFontStyle = ['name' => 'TH SarabunIT๙', 'size' => 12, 'bold' => true];
        $itemFontStyle = ['name' => 'TH SarabunIT๙', 'size' => 12];

        /** =================== รายการจัดซือจัดจ้าง =================== */
        $orders = array_filter($loan->details->toArray(), function($detail) { return $detail['expense_group'] == 2; });
        $orderTable = new Table($tableStyle);

        foreach($orders as $order => $detail) {
            /** เพิ่มแถวในตาราง */
            $orderTable->addRow();
            $orderTable
                ->addCell(50 * 50)
                ->addText('- ' . $detail['expense']['name'] . ' ' . $detail['description'], $itemFontStyle, ['spaceAfter' => 0]);
            $orderTable
                ->addCell(50 * 50)
                ->addText('เป็นเงิน  ' . number_format($detail['total']) . ' บาท', $itemFontStyle, ['spaceAfter' => 0, 'align' => 'right']);
        }

        $orderTable->addRow();
        $orderTable
            ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
            ->addText('รวมเป็นเงิน ' . number_format($loan->order_total) . ' บาท ', $couseFontStyle, ['spaceAfter' => 0, 'align' => 'right']);

        /** เพิ่มรายการลงในตาราง */
        $word->setComplexBlock('orders', $orderTable);

        /** เงื่อนไขการแสดงรายการจัดซือจัดจ้าง */
        if (!array_any($loan->details->toArray(), function($detail) { return $detail['expense_group'] == 2; })) {
            $word->cloneBlock('haveOrders', 0, true, true);
        } else {
            $word->cloneBlock('haveOrders', 1, true, true);
        }

        /** =================== รายการค่าใช้จ่าย =================== */
        $itemTable = new Table($tableStyle);

        if ($loan->expense_calc == 1) {
            /** คิดรวม */
            $courseTotal = 0;
            $items = array_filter($loan->details->toArray(), function($detail) { return $detail['expense_group'] == 1; });

            foreach($items as $item => $detail) {
                $description = $detail['description'] != '' ? replaceExpensePatternFromDesc($detail['expense']['pattern'], $detail['description']) : '';
                /** เพิ่มแถวในตาราง */
                $itemTable->addRow();
                $itemTable
                    ->addCell(50 * 50)
                    ->addText('- ' . $detail['expense']['name'] . ' ' . $description, $itemFontStyle, ['spaceAfter' => 0]);
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
            foreach($loan->courses as $course => $cs) {
                $courseTotal = 0;

                /** เพิ่มแถวในตาราง */
                $itemTable->addRow();
                $itemTable
                    ->addCell(100 * 50, ['gridSpan' => 2, 'valign' => 'center'])
                    ->addText('วันที่ ' . convDbDateToLongThDateRange($cs->course_date, $cs->course_edate) . ' ณ ' . $cs->place->name, $couseFontStyle, ['spaceAfter' => 0]);
                
                $items = array_filter($loan->details->toArray(), function($detail) use ($cs) { return $detail['expense_group'] == 1 && $detail['course_id'] == $cs->id; });
                foreach($items as $item => $detail) {
                    /** สร้างรายละเอียดของค่าใช้จ่ายจากสูตร */
                    $description = $detail['description'] != '' ? replaceExpensePatternFromDesc($detail['expense']['pattern'], $detail['description']) : '';

                    /** เพิ่มแถวในตาราง */
                    $itemTable->addRow();
                    $itemTable
                        ->addCell(50 * 50)
                        ->addText('- ' . $detail['expense']['name'] . ' ' . $description, $itemFontStyle, ['spaceAfter' => 0]);
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
            }
        }

        /** เพิ่มรายการลงในตาราง */
        $word->setComplexBlock('items', $itemTable);

        /** =================== ยอดรวมทั้งสิ้น =================== */
        $word->setValue('netTotal', number_format($loan->net_total));
        $word->setValue('netTotalText', baht_text($loan->net_total));

        /** รวมรายการ */
        $word->setValue('sumRecord', number_format(count($loan->details)));

        /** เงื่อนไขการแสดงหมายเหตุ */
        if (sizeof($loan->details) == 1) {
            $word->cloneBlock('isProject', 0, true, true);
        } else {
            $word->cloneBlock('isProject', 1, true, true);
        }

        /** =================== เงื่อนไขการคืนเงิน =================== */
        $word->setValue('refundDays', $loan->loan_type_id == 1 ? 30 : 15);
        $word->setValue('refundCondition', $loan->loan_type_id == 1 ? 'ได้รับเงิน' : 'เดินทางกลับจากราชการ');
        /** ================================== CONTENT ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }
}
