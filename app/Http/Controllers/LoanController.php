<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use PhpOffice\PhpWord\Element\Field;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpWord\ComplexType\TblWidth as IndentWidth;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\LoanBudget;
use App\Models\ProjectCourse;
use App\Models\Expense;
use App\Models\Department;
use App\Models\Budget;

class LoanController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $status     = $req->get('status');

        $loans = Loan::with('details','details.expense','department','division')
                        ->with('employee','employee.prefix','employee.position','employee.level')
                        ->with('budgets','budgets.budget','budgets.budget.activity','budgets.budget.type')
                        ->with('budgets.budget.activity.project','budgets.budget.activity.project.plan')
                        ->with('courses','courses.place','courses.place.changwat')
                        ->when((!auth()->user()->isAdmin() && !auth()->user()->isFinancial()), function($q) {
                            $q->where('employee_id', auth()->user()->employee_id);
                        })
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->when(!empty($status), function($q) use ($status) {
                            $q->where('status', $status);
                        })
                        ->orderBy('doc_date', 'DESC')
                        ->paginate(10);

        return $loans;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $project    = $req->get('project');
        $plan       = $req->get('plan');
        $name       = $req->get('name');
        $status     = $req->get('status');

        $loans = Loan::with('details','details.expense','department','division')
                        ->with('employee','employee.prefix','employee.position','employee.level')
                        ->with('budgets','budgets.budget','budgets.budget.activity','budgets.budget.type')
                        ->with('budgets.budget.activity.project','budgets.budget.activity.project.plan')
                        ->with('courses','courses.place','courses.place.changwat')
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

        return $loans;
    }

    public function getById($id)
    {
        return Loan::with('details','details.expense','department','division')
                    ->with('employee','employee.prefix','employee.position','employee.level')
                    ->with('budgets','budgets.budget','budgets.budget.activity','budgets.budget.type')
                    ->with('budgets.budget.activity.project','budgets.budget.activity.project.plan')
                    ->with('courses','courses.place','courses.place.changwat')
                    ->find($id);
    }

    public function getInitialFormData()
    {
        $loanTypes = [
            ['id' => 1, 'name' => 'ยืมเงินโครงการ'],
            ['id' => 2, 'name' => 'ยืมเงินเดินทางไปราชการ'],
        ];

        $moneyTypes = [
            ['id' => 1, 'name' => 'เงินทดลองราชการ'],
            ['id' => 2, 'name' => 'เงินยืมนอกงบประมาณ'],
            ['id' => 3, 'name' => 'เงินยืมราชการ'],
        ];

        $statuses = [
            ['id' => 1, 'name' => 'รอดำเนินการ'],
            ['id' => 2, 'name' => 'ส่งสัญญาแล้ว'],
            ['id' => 3, 'name' => 'อนุมัติแล้ว'],
            ['id' => 4, 'name' => 'เงินเข้าแล้ว'],
            ['id' => 5, 'name' => 'เคลียร์แล้ว'],
            ['id' => 9, 'name' => 'ยกเลิก'],
        ];

        return [
            'departments'   => Department::with('divisions')->get(),
            'expenses'      => Expense::all(),
            'budgets'       => Budget::with('activity','type')->get(),
            'loanTypes'     => $loanTypes,
            'moneyTypes'    => $moneyTypes,
            'statuses'      => $statuses
        ];
    }

    public function store(Request $req)
    {
        try {
            $loan = new Loan();
            $loan->doc_no           = $req['doc_no'];
            $loan->doc_date         = $req['doc_date'];
            $loan->loan_type_id     = $req['loan_type_id'];
            $loan->money_type_id    = $req['money_type_id'];
            $loan->year             = $req['year'];
            $loan->department_id    = $req['department_id'];
            $loan->division_id      = $req['division_id'];
            $loan->employee_id      = $req['employee_id'];
            $loan->project_no       = $req['project_no'];
            $loan->project_date     = $req['project_date'];
            $loan->project_owner    = $req['project_owner'];
            $loan->project_name     = $req['project_name'];
            $loan->project_sdate    = $req['project_sdate'];
            $loan->project_edate    = $req['project_edate'];
            $loan->expense_calc     = $req['expense_calc'];
            $loan->budget_total     = currencyToNumber($req['budget_total']);
            $loan->item_total       = currencyToNumber($req['item_total']);
            $loan->order_total      = currencyToNumber($req['order_total']);
            $loan->net_total        = currencyToNumber($req['net_total']);
            $loan->remark           = $req['remark'];
            $loan->status           = 1;

            if($loan->save()) {
                foreach($req['courses'] as $item) {
                    $course = new ProjectCourse();
                    $course->seq_no         = $item['id'];
                    $course->loan_id        = $loan->id;
                    $course->course_date    = $item['course_date'];
                    $course->place_id       = $item['place_id'];
                    $course->save();
                }

                foreach($req['budgets'] as $item) {
                    $budget = new LoanBudget();
                    $budget->loan_id    = $loan->id;
                    $budget->budget_id  = $item['budget_id'];
                    $budget->total      = currencyToNumber($item['total']);
                    $budget->save();
                }

                foreach($req['items'] as $item) {
                    /** ดึงข้อมูลรุ่นของโครงการ */
                    $course = $item['expense_group'] == '1' ? ProjectCourse::where('loan_id', $loan->id)->where('seq_no', $item['course_id'])->first() : null;

                    $detail = new LoanDetail();
                    $detail->loan_id        = $loan->id;
                    $detail->course_id      = $item['expense_group'] == '1' ? $course->id : $course;
                    $detail->expense_id     = $item['expense_id'];
                    $detail->expense_group  = $item['expense_group'];
                    $detail->description    = $item['description'];
                    $detail->total          = currencyToNumber($item['total']);
                    $detail->save();
                }

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully!!',
                    'loan'      => $loan->load('details','details.expense','department',
                                                'employee','employee.prefix','employee.position','employee.level',
                                                'budgets','budgets.budget','budgets.budget.activity','budgets.budget.type',
                                                'budgets.budget.activity.project','budgets.budget.activity.project.plan',
                                                'courses','courses.place','courses.place.changwat')
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
            $loan = Loan::find($id);
            $loan->doc_no           = $req['doc_no'];
            $loan->doc_date         = $req['doc_date'];
            $loan->loan_type_id     = $req['loan_type_id'];
            $loan->money_type_id    = $req['money_type_id'];
            $loan->year             = $req['year'];
            $loan->department_id    = $req['department_id'];
            $loan->division_id      = $req['division_id'];
            $loan->employee_id      = $req['employee_id'];
            $loan->project_no       = $req['project_no'];
            $loan->project_date     = $req['project_date'];
            $loan->project_owner    = $req['project_owner'];
            $loan->project_name     = $req['project_name'];
            $loan->project_sdate    = $req['project_sdate'];
            $loan->project_edate    = $req['project_edate'];
            $loan->expense_calc     = $req['expense_calc'];
            $loan->budget_total     = currencyToNumber($req['budget_total']);
            $loan->item_total       = currencyToNumber($req['item_total']);
            $loan->order_total      = currencyToNumber($req['order_total']);
            $loan->net_total        = currencyToNumber($req['net_total']);
            $loan->remark           = $req['remark'];

            if($loan->save()) {
                foreach($req['courses'] as $course) {
                    /** ถ้า element ของ courses ไม่มี property loan_id (รายการใหม่) */
                    if (!array_key_exists('loan_id', $course)) {
                        $newCourse = new ProjectCourse();
                        $newCourse->seq_no         = $course['id'];
                        $newCourse->loan_id        = $loan->id;
                        $newCourse->course_date    = $course['course_date'];
                        $newCourse->place_id       = $course['place_id'];
                        $newCourse->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag removed หรือไม่ */
                        if (array_key_exists('removed', $course) && $course['removed']) {
                            ProjectCourse::find($course['id'])->delete();
                        }
                    }
                }

                foreach($req['budgets'] as $budget) {
                    /** ถ้า element ของ budgets ไม่มี property id (รายการใหม่) */
                    if (!array_key_exists('loan_id', $budget)) {
                        $newBudget = new LoanBudget();
                        $newBudget->loan_id    = $loan->id;
                        $newBudget->budget_id  = $budget['budget_id'];
                        $newBudget->total      = currencyToNumber($budget['total']);
                        $newBudget->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag removed หรือไม่ */
                        if (array_key_exists('removed', $budget) && $budget['removed']) {
                            LoanBudget::find($budget['id'])->delete();
                        }
                    }
                }

                foreach($req['items'] as $item) {
                    $course = $item['expense_group'] == '1' ? ProjectCourse::where('id', $item['course_id'])->where('loan_id', $loan->id)->first() : null;

                    /** ถ้า element ของ items ไม่มี property id (รายการใหม่) */
                    if (empty($item['loan_id'])) {
                        $detail = new LoanDetail();
                        $detail->loan_id        = $loan->id;
                        $detail->course_id      = $item['expense_group'] == '1' ? $course->id : $course;
                        $detail->expense_id     = $item['expense_id'];
                        $detail->expense_group  = $item['expense_group'];
                        $detail->description    = $item['description'];
                        $detail->total          = currencyToNumber($item['total']);
                        $detail->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag updated หรือ removed หรือไม่ */
                        if (array_key_exists('updated', $item) && $item['updated']) {
                            /** This is item to update */
                            $updated = LoanDetail::find($item['id']);
                            $updated->loan_id       = $item['loan_id'];

                            if ($item['expense_group'] == '1') {
                                $updated->course_id     = $item['course_id'];
                            }

                            $updated->expense_id    = $item['expense_id'];
                            $updated->expense_group = $item['expense_group'];
                            $updated->description   = $item['description'];
                            $updated->total         = currencyToNumber($item['total']);
                            $updated->save();
                        }

                        if (array_key_exists('removed', $item) && $item['removed']) {
                            /** This is item to remove */
                            LoanDetail::find($item['id'])->delete();
                        }
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'loan'      => $loan->load('details','details.expense','department',
                                                'employee','employee.prefix','employee.position','employee.level',
                                                'budgets','budgets.budget','budgets.budget.activity','budgets.budget.type',
                                                'budgets.budget.activity.project','budgets.budget.activity.project.plan',
                                                'courses','courses.place','courses.place.changwat')
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
            $loan = Loan::find($id);

            if($loan->delete()) {
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
        /** =================== รายละเอียดโครงการ =================== */
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
        $word->setValue('place', $loan->courses[0]->place->name . ' จังหวัด' .$loan->courses[0]->place->changwat->name);

        /** =================== แผนงาน =================== */
        $budgets = '';
        foreach($loan->budgets as $data) {
            $budgets .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
        }

        $word->setValue('budget', $budgets);
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
                ->addText('วันที่ ' . convDbDateToLongThDate($cs->course_date) . ' ณ ' . $cs->place->name, $couseFontStyle);
                
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
            $word->setValue('projectName', 'ตามหนังสือ ' . ($loan->division ? $loan->division->name : $loan->department->name) . ' ที่ ' . $loan->doc_no . ' ลงวันที่ ' . convDbDateToLongThDate($loan->doc_date) . 'เรื่อง ขออนุมัติยืมเงินราชการ เพื่อเป็นค่าใช้จ่ายในการเดินทางไปราชการเข้าร่วม' . $loan->project_name);
        }

        $word->setValue('projectSDate', convDbDateToLongThDate($loan->project_sdate));
        $word->setValue('projectEDate', convDbDateToLongThDate($loan->project_edate));
        $word->setValue('place', $loan->courses[0]->place->name . ' จังหวัด' .$loan->courses[0]->place->changwat->name);

        /** =================== แผนงาน =================== */
        $budgets = '';
        foreach($loan->budgets as $data) {
            $budgets .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
        }

        $word->setValue('budget', $budgets);
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
                    ->addText('วันที่ ' . convDbDateToLongThDate($cs->course_date) . ' ณ ' . $cs->place->name, $couseFontStyle, ['spaceAfter' => 0]);
                
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
