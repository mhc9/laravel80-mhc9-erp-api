<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Requisition;
use App\Models\RequisitionDetail;
use App\Models\RequisitionBudget;
use App\Models\Item;
use App\Models\AssetType;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Division;
use App\Models\Committee;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Member;
use App\Models\Approval;

class RequisitionController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        $pr_no      = $req->get('pr_no');
        $pr_date    = $req->get('pr_date');
        $division   = $req->get('division');
        $category   = $req->get('category');
        $status     = $req->get('status');
        $year       = $req->get('year');
        $limit      = $req->get('limit') ? $req->get('limit') : 10;

        $requisitions = Requisition::with('budgets','budgets.budget.activity','budgets.budget.activity.project','budgets.budget.activity.project.plan','budgets.budget.type')
                            // ->with('budget','budget.activity','budget.activity.project','budget.activity.project.plan','budget.type')
                            ->with('division','department','details','details.unit','details.item','details.item.category')
                            ->with('requester','requester.prefix','requester.position','requester.level')
                            ->with('committees','committees.employee','committees.employee.prefix')
                            ->with('committees.employee.position','committees.employee.level')
                            ->with('approvals','approvals.procuring','approvals.supplier','approvals.supplier.tambon')
                            ->with('approvals.supplier.amphur','approvals.supplier.changwat','project','category')
                            ->when((!auth()->user()->isAdmin() && !auth()->user()->isParcel()), function($q) {
                                $q->where('requester_id', auth()->user()->employee_id);
                            })
                            ->when(!empty($pr_no), function($q) use ($pr_no) {
                                $q->where('pr_no', 'like', '%'.$pr_no.'%');
                            })
                            ->when(!empty($pr_date), function($q) use ($pr_date) {
                                $q->where('pr_date', $pr_date);
                            })
                            ->when(!empty($division), function($q) use ($division) {
                                $q->where('division_id', $division);
                            })
                            ->when(!empty($category), function($q) use ($category) {
                                $q->where('category_id', $category);
                            })
                            ->when(!empty($year), function($q) use ($year) {
                                $q->where('year', $year);
                            })
                            ->when($status != '', function($q) use ($status) {
                                $q->where('status', $status);
                            })
                            ->orderBy('pr_date', 'DESC')
                            ->paginate($limit);

        return $requisitions;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        $category   = $req->get('category');
        $requester  = $req->get('requester');
        $status     = $req->get('status');

        $requisitions = Requisition::with('budgets','budgets.budget.activity','budgets.budget.activity.project','budgets.budget.activity.project.plan','budgets.budget.type')
                            // ->with('budget','budget.activity','budget.activity.project','budget.activity.project.plan','budget.type')
                            ->with('division','division.department','details','details.unit','details.item','details.item.category')
                            ->with('requester','requester.prefix','requester.position','requester.level')
                            ->with('committees','committees.employee','committees.employee.prefix')
                            ->with('committees.employee.position','committees.employee.level')
                            ->with('approvals','approvals.procuring','approvals.supplier','project','category')
                            ->when(!empty($category), function($q) use ($category) {
                                $q->where('category_id', $category);
                            })
                            ->when($status != '', function($q) use ($status) {
                                $q->where('status', $status);
                            })
                            ->get();

        return $requisitions;
    }

    public function getById($id)
    {
        return Requisition::with('budgets','budgets.budget.activity','budgets.budget.activity.project','budgets.budget.activity.project.plan','budgets.budget.type')
                    // ->with('budget','budget.activity','budget.activity.project','budget.activity.project.plan','budget.type')
                    ->with('division','department','details','details.unit','details.item','details.item.category')
                    ->with('requester','requester.prefix','requester.position','requester.level')
                    ->with('committees','committees.employee','committees.employee.prefix')
                    ->with('committees.employee.position','committees.employee.level')
                    ->with('approvals','approvals.procuring','approvals.supplier','project','category')
                    ->find($id);
    }

    public function getByIdWithHeadOfDepart($id)
    {
        $requisition = $this->getById($id);

        return [
            'requisition'   => $requisition,
            'headOfDepart'  => Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
                                        ->whereIn('id', Member::where('department_id', $requisition->department_id)->whereIn('duty_id', [2, 5, 6])->pluck('employee_id'))
                                        ->where('status', 1)
                                        ->first()
        ];
    }

    public function getInitialFormData()
    {
        $year           = 2566;
        $types          = AssetType::with('categories')->get();
        $categories     = AssetCategory::with('type')->get();
        $departments    = Department::with('divisions')->get();
        $statuses       = [
            ['id' => 1, 'name' => 'รอดำเนินการ'],
            ['id' => 2, 'name' => 'แต่งตั้งผู้ตรวจรับ'],
            ['id' => 3, 'name' => 'ประกาศผู้ชนะ'],
            ['id' => 4, 'name' => 'จัดซื้อแล้ว'],
            ['id' => 5, 'name' => 'ตรวจรับแล้ว'],
            ['id' => 9, 'name' => 'ยกเลิก'],
        ];

        return [
            'types'         => $types,
            'categories'    => $categories,
            'departments'   => $departments,
            'divisions'     => Division::all(),
            'projects'      => Project::where('year', $year)->get(),
            'statuses'      => $statuses,
        ];
    }

    public function store(Request $req)
    {
        try {
            $requisition = new Requisition();
            $requisition->pr_no         = $req['pr_no'];
            $requisition->pr_date       = $req['pr_date'];
            $requisition->order_type_id = $req['order_type_id'];
            $requisition->category_id   = $req['category_id'];
            $requisition->contract_desc = $req['contract_desc'];
            $requisition->topic         = $req['topic'];
            $requisition->year          = $req['year'];
            $requisition->budget_id     = $req['budget_id'];
            $requisition->project_id    = $req['project_id'];
            $requisition->project_name  = $req['project_name'];
            $requisition->requester_id  = $req['requester_id'];
            $requisition->department_id = $req['department_id'];
            $requisition->division_id   = $req['division_id'];
            $requisition->reason        = $req['reason'];
            $requisition->desired_date  = $req['desired_date'];
            $requisition->item_count    = $req['item_count'];
            $requisition->net_total     = currencyToNumber($req['net_total']);
            $requisition->budget_total  = currencyToNumber($req['budget_total']);
            $requisition->status        = 1;

            if($requisition->save()) {
                /** Insert items to RequisitionDetail */
                foreach($req['items'] as $item) {
                    $detail = new RequisitionDetail();
                    $detail->requisition_id = $requisition->id;
                    $detail->item_id        = $item['item_id'];
                    $detail->description    = $item['description'];
                    $detail->amount         = $item['amount'];
                    $detail->price          = $item['price'];
                    $detail->unit_id        = $item['unit_id'];
                    $detail->total          = $item['total'];
                    $detail->save();
                }

                /** รายการงบประมาณ */
                foreach($req['budgets'] as $budget) {
                    $newBudget = new RequisitionBudget();
                    $newBudget->requisition_id  = $requisition->id;
                    $newBudget->budget_id       = $budget['budget_id'];
                    $newBudget->total           = currencyToNumber($budget['total']);
                    $newBudget->save();
                }

                /** Insert committees */
                foreach($req['committees'] as $employee) {
                    $committee = new Committee();
                    $committee->employee_id         = $employee['employee_id'];
                    $committee->requisition_id      = $requisition->id;
                    $committee->committee_type_id   = 2;
                    $committee->save();
                }

                /** Log info */
                Log::channel('daily')->info('Added new requisition ID:' .$requisition->id. ' by ' . auth()->user()->name);

                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'requisition'   => $requisition->load('budgets','budgets.budget.activity','budgets.budget.activity.project','budgets.budget.activity.project.plan','budgets.budget.type',
                                                        // 'budget','budget.activity','budget.activity.project','budget.activity.project.plan','budget.type',
                                                        'division','division.department','details','details.unit','details.item','details.item.category',
                                                        'requester','requester.prefix','requester.position','requester.level',
                                                        'committees','committees.employee','committees.employee.prefix',
                                                        'committees.employee.position','committees.employee.level',
                                                        'approvals','approvals.procuring','approvals.supplier','project','category')
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
            $requisition = Requisition::find($id);
            $requisition->pr_no         = $req['pr_no'];
            $requisition->pr_date       = $req['pr_date'];
            $requisition->order_type_id = $req['order_type_id'];
            $requisition->category_id   = $req['category_id'];
            $requisition->contract_desc = $req['contract_desc'];
            $requisition->topic         = $req['topic'];
            $requisition->year          = $req['year'];
            $requisition->budget_id     = $req['budget_id'];
            $requisition->project_id    = $req['project_id'];
            $requisition->project_name  = $req['project_name'];
            $requisition->requester_id  = $req['requester_id'];
            $requisition->department_id = $req['department_id'];
            $requisition->division_id   = $req['division_id'];
            $requisition->reason        = $req['reason'];
            $requisition->desired_date  = $req['desired_date'];
            $requisition->item_count    = $req['item_count'];
            $requisition->net_total     = currencyToNumber($req['net_total']);
            $requisition->budget_total  = currencyToNumber($req['budget_total']);
            $requisition->status        = 1;

            if($requisition->save()) {
                /** Update items to RequisitionDetail */
                foreach($req['items'] as $item) {
                    if (!array_key_exists('requisition_id', $item)) {
                        /** กรณีเป็นรายการใหม่ */
                        $detail = new RequisitionDetail();
                        $detail->requisition_id = $requisition->id;
                        $detail->item_id        = $item['item_id'];
                        $detail->description    = $item['description'];
                        $detail->amount         = $item['amount'];
                        $detail->price          = $item['price'];
                        $detail->unit_id        = $item['unit_id'];
                        $detail->total          = $item['total'];
                        $detail->save();
                    } else {
                        /** กรณีเป็นรายการเดิม */
                        /** ============= ตรวจสอบและอัพเดตเฉพาะรายการที่ถูกแก้ไข ============= */
                        if (array_key_exists('updated', $item) && $item['updated']) {
                            $detail = RequisitionDetail::find($item['id']);
                            $detail->description    = $item['description'];
                            $detail->amount         = $item['amount'];
                            $detail->price          = $item['price'];
                            $detail->unit_id        = $item['unit_id'];
                            $detail->total          = $item['total'];
                            $detail->save();
                        }

                        /** ============= ตรวจสอบและลบเฉพาะรายการที่ถูกลบ ============= */
                        if (array_key_exists('removed', $item) && $item['removed']) {
                            RequisitionDetail::find($item['id'])->delete();
                        }
                    }
                }

                /** update รายการงบประมาณ */
                foreach($req['budgets'] as $budget) {
                    /** ถ้า element ของ budgets ไม่มี property id (รายการใหม่) */
                    if (!array_key_exists('requisition_id', $budget)) {
                        $newBudget = new RequisitionBudget();
                        $newBudget->requisition_id  = $requisition->id;
                        $newBudget->budget_id       = $budget['budget_id'];
                        $newBudget->total           = currencyToNumber($budget['total']);
                        $newBudget->save();
                    } else {
                        /** ถ้าเป็นรายการเดิมให้ตรวจสอบว่ามี property flag removed หรือไม่ */
                        if (array_key_exists('removed', $budget) && $budget['removed']) {
                            RequisitionBudget::find($budget['id'])->delete();
                        }
                    }
                }

                /** Update committees */
                foreach($req['committees'] as $committee) {
                    if (!array_key_exists('requisition_id', $committee)) {
                        $newCommittee = new Committee();
                        $newCommittee->employee_id         = $committee['employee_id'];
                        $newCommittee->requisition_id      = $requisition->id;
                        $newCommittee->committee_type_id   = 2;
                        $newCommittee->save();
                    } else {
                        if (array_key_exists('removed', $committee) && $committee['removed']) {
                            Committee::find($committee['id'])->delete();
                        }
                    }
                }

                /** Log info */
                Log::channel('daily')->info('Updated requisition ID:' .$id. ' by ' . auth()->user()->name);

                return [
                    'status'        => 1,
                    'message'       => 'Updating successfully!!',
                    'requisition'   => $requisition->load('budgets','budgets.budget.activity','budgets.budget.activity.project','budgets.budget.activity.project.plan','budgets.budget.type',
                                                        // 'budget','budget.activity','budget.activity.project','budget.activity.project.plan','budget.type',
                                                        'division','division.department','details','details.unit','details.item','details.item.category',
                                                        'requester','requester.prefix','requester.position','requester.level',
                                                        'committees','committees.employee','committees.employee.prefix',
                                                        'committees.employee.position','committees.employee.level',
                                                        'approvals','approvals.procuring','approvals.supplier','project','category')
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
            $requisition = Requisition::find($id);

            if($requisition->delete()) {
                /** ลบรายการในตาราง detail */
                RequisitionDetail::where('requisition_id', $id)->delete();

                /** ลบรายการคณะกรรมต่างๆ ในตาราง committees */
                Committee::where('requisition_id', $id)->delete();

                /** ลบรายการการอนุมัติในตาราง approvals */
                Approval::where('requisition_id', $id)->delete();

                /** Log info */
                Log::channel('daily')->info('Deleted requisition ID:' .$id. ' by ' . auth()->user()->name);

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

    public function printPR(Request $req, $id)
    {
        $requisition = Requisition::with('category','budget','details','project','division','division.department')
                        ->with('details.item','details.unit')
                        ->with('requester','requester.prefix','requester.position','requester.level')
                        ->with('committees','committees.employee','committees.employee.prefix')
                        ->with('committees.employee.position','committees.employee.level')
                        ->find($id);

        $data = [
            "requisition" => $requisition,
        ];

        /** Invoke helper function to return view of pdf instead of laravel's view to client */
        $paper = [
            'size'          => 'a4',
            'orientation'   => 'portrait'
        ];

        return renderPdf('forms.pr-form', $data, $paper);
    }

    public function getDocument(Request $req, $id)
    {
        $requisition = Requisition::with('budgets','budgets.budget.activity','budgets.budget.activity.project','budgets.budget.activity.project.plan','budgets.budget.type')
                        // ->with('budget','budget.activity','budget.activity.project','budget.activity.project.plan','budget.type')
                        ->with('details','project','division','department','details.item','details.item','details.unit')
                        ->with('requester','requester.prefix','requester.position','requester.level')
                        ->with('committees','committees.employee','committees.employee.prefix')
                        ->with('committees.employee.position','committees.employee.level','category')
                        ->find($id);

        $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
                            ->whereIn('id', Member::where('department_id', $requisition->department_id)->whereIn('duty_id', [2,5,6])->pluck('employee_id'))
                            ->where('status', 1)
                            ->first();

        $template = 'form.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates//requisitions/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('department', $requisition->division ? $requisition->division->name : $requisition->department->name);
        $word->setValue('pr_no', $requisition->pr_no);
        $word->setValue('pr_date', convDbDateToLongThDate($requisition->pr_date));
        $word->setValue('topic', $requisition->topic);
        /** ================================== HEADER ================================== */

        /** ================================== CONTENT ================================== */
        $word->setValue('objective', $requisition->order_type_id == 1 ? 'ซื้อ' . $requisition->category->name : $requisition->contract_desc);
        $word->setValue('itemCount', $requisition->item_count);
        $word->setValue('reason', $requisition->reason);
        $word->setValue('year', $requisition->year+543);
        
        /** แผนงาน */
        $budgets = '';
        foreach($requisition->budgets as $data) {
            $budgets .= $data->budget->activity->project->plan->name . ' ' . $data->budget->activity->project->name  . ' ' . $data->budget->activity->name;
            $budgets .= ' จำนวนเงิน ' . number_format($data->total) . ' บาท ';
        }
        $word->setValue('budget', $budgets);

        $word->setValue('netTotal', number_format($requisition->net_total, 2));
        $word->setValue('netTotalText', baht_text($requisition->net_total));
        $word->setValue('requester', $requisition->requester->prefix->name.$requisition->requester->firstname . ' ' . $requisition->requester->lastname);
        $word->setValue('requesterPosition', $requisition->requester->position->name . ($requisition->requester->level ? $requisition->requester->level->name : ''));

        $cx = 1;
        $word->cloneRow('inspector', sizeof($requisition->committees));
        foreach($requisition->committees as $inspector => $committee) {
            $word->setValue('inspector#' . $cx, $committee->employee->prefix->name.$committee->employee->firstname . ' ' . $committee->employee->lastname);
            $word->setValue('inspectorPosition#' . $cx, $committee->employee->position->name . ($committee->employee->level ? $committee->employee->level->name : ''));
            $cx++;
        }

        $no = 1;
        $word->cloneRow('item', sizeof($requisition->details));
        foreach($requisition->details as $item => $detail) {
            $word->setValue('no#' . $no, $no);
            $word->setValue('item#' . $no, $detail->item->name . ' ' . $detail->description);
            $word->setValue('amt#' . $no, number_format($detail->amount, 1));
            $word->setValue('unit#' . $no, $detail->unit->name);
            $word->setValue('price#' . $no, number_format($detail->price, 2));
            $word->setValue('total#' . $no, number_format($detail->total, 2));
            $no++;
        }

        $word->setValue('desiredDate', convDbDateToLongThDate($requisition->desired_date));
        $word->setValue('deliverPlace', array_any($requisition->details->toArray(), function($detail) { return in_array($detail['item_id'], [24]); }) ? 'สถานบริการน้ำมันเชื้อเพลิง' : 'ศูนย์สุขภาพจิตที่ 9');
        /** ================================== CONTENT ================================== */

        /** ================================== SIGNATURE ================================== */
        $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        $word->setValue('headOfDepartRole', $headOfDepart->memberOf[0]->duty->display_name . $requisition->department->name);
        /** ================================== SIGNATURE ================================== */

        /** เงื่อนไขการแสดงรายชื่อผู้กำหนดรายละเอียดขอบเขตของงาน/ผู้ตรวจรับ กรณีเป็นคนเดียวกัน */
        if (sizeof($requisition->committees) == 1 && $requisition->committees[0]->employee_id == $requisition->requester_id) {
            $word->cloneBlock('isSame', 1, true, true);
            $word->cloneBlock('isNotSame', 0, true, true);
        } else {
            $word->cloneBlock('isSame', 0, true, true);
            $word->cloneBlock('isNotSame', 1, true, true);
        }

         /** เงื่อนไขการแสดงรายชื่อคณะกรรมการตรวจรับ */
        if (sizeof($requisition->committees) == 1) {
            $word->cloneBlock('isOneman1', 1, true, true);
            $word->cloneBlock('isOneman2', 1, true, true);
            $word->cloneBlock('isGroup1', 0, true, true);
            $word->cloneBlock('isGroup2', 0, true, true);

            $word->setValue('committee#1', $requisition->committees[0]->employee->prefix->name.$requisition->committees[0]->employee->firstname . ' ' . $requisition->committees[0]->employee->lastname);
        } else {
            $word->cloneBlock('isOneman1', 0, true, true);
            $word->cloneBlock('isOneman2', 0, true, true);
            $word->cloneBlock('isGroup1', 1, true, true);
            $word->cloneBlock('isGroup2', 1, true, true);

            $word->setValue('committee1#1', $requisition->committees[0]->employee->prefix->name.$requisition->committees[0]->employee->firstname . ' ' . $requisition->committees[0]->employee->lastname);
            $word->setValue('committee2#1', $requisition->committees[1]->employee->prefix->name.$requisition->committees[1]->employee->firstname . ' ' . $requisition->committees[1]->employee->lastname);
            $word->setValue('committee3#1', $requisition->committees[2]->employee->prefix->name.$requisition->committees[2]->employee->firstname . ' ' . $requisition->committees[2]->employee->lastname);
        }

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getReport(Request $req, $id)
    {
        $requisition = Requisition::with('category','budget','details','project','division','division.department')
                        ->with('details.item','details.item.unit')
                        ->with('requester','requester.prefix','requester.position','requester.level')
                        ->with('committees','committees.employee','committees.employee.prefix')
                        ->with('committees.employee.position','committees.employee.level')
                        ->with('approvals','approvals.procuring')
                        ->find($id);

        // $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
        //                     ->whereIn('id', Member::where('department_id', $requisition->division->department_id)->whereIn('duty_id', [2, 5])->pluck('employee_id'))
        //                     ->where('status', 1)
        //                     ->first();

        $template = 'report.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/requisitions/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('department', $requisition->division->department->name);
        $word->setValue('report_no', $requisition->approvals[0]->report_no);
        $word->setValue('report_date', convDbDateToLongThDate($requisition->approvals[0]->report_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        $word->setValue('objective', $requisition->order_type_id == 1 ? 'ซื้อ' . $requisition->category->name : $requisition->contract_desc);
        $word->setValue('itemCount', $requisition->item_count);
        $word->setValue('deliver_date', convDbDateToLongThDate($requisition->approvals[0]->deliver_date));
        $word->setValue('division', $requisition->division->name);
        $word->setValue('reason', $requisition->reason);
        $word->setValue('year', $requisition->year);
        $word->setValue('budget', $requisition->budget->activity->project->plan->name . ' ' . $requisition->budget->activity->project->name  . ' ' . $requisition->budget->activity->name);
        $word->setValue('netTotal', number_format($requisition->net_total));
        $word->setValue('netTotalText', baht_text($requisition->net_total));
        $word->setValue('requester', $requisition->requester->prefix->name.$requisition->requester->firstname . ' ' . $requisition->requester->lastname);
        $word->setValue('requesterPosition', $requisition->requester->position->name . ($requisition->requester->level ? $requisition->requester->level->name : ''));
        
        $cx = 1;
        $word->cloneRow('inspector', sizeof($requisition->committees));
        foreach($requisition->committees as $inspector => $committee) {
            $word->setValue('inspector#' . $cx, $committee->employee->prefix->name.$committee->employee->firstname . ' ' . $committee->employee->lastname);
            $word->setValue('inspectorPosition#' . $cx, $committee->employee->position->name . ($committee->employee->level ? $committee->employee->level->name : ''));
            $cx++;
        }
        /** ================================== CONTENT ================================== */
        
        /** ================================== SIGNATURE ================================== */
        // $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        // $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        // $word->setValue('headOfDepartRole', ($headOfDepart->memberOf[0]->duty_id == 2 ? 'หัวหน้า' : $headOfDepart->memberOf[0]->duty->name) . $requisition->division->department->name);
        /** ================================== SIGNATURE ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getDirective(Request $req, $id)
    {
        $requisition = Requisition::with('category','budget','details','project','division','division.department')
                        ->with('details.item','details.item.unit')
                        ->with('requester','requester.prefix','requester.position','requester.level')
                        ->with('committees','committees.employee','committees.employee.prefix')
                        ->with('committees.employee.position','committees.employee.level')
                        ->with('approvals','approvals.procuring')
                        ->find($id);

        // $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
        //                     ->whereIn('id', Member::where('department_id', $requisition->division->department_id)->whereIn('duty_id', [2, 5])->pluck('employee_id'))
        //                     ->where('status', 1)
        //                     ->first();

        $template = 'directive.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/requisitions/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('directiveNo', $requisition->approvals[0]->directive_no);
        $word->setValue('directiveDate', convDbDateToLongThDate($requisition->approvals[0]->directive_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        $word->setValue('objective', $requisition->order_type_id == 1 ? 'ซื้อ' . $requisition->category->name : $requisition->contract_desc);
        $word->setValue('itemCount', $requisition->item_count);
        
        $cx = 1;
        $word->cloneRow('inspector', sizeof($requisition->committees));
        foreach($requisition->committees as $inspector => $committee) {
            $word->setValue('no#' . $cx, sizeof($requisition->committees) > 1 ? $cx . '.' : '');
            $word->setValue('inspector#' . $cx, $committee->employee->prefix->name.$committee->employee->firstname . ' ' . $committee->employee->lastname);
            $word->setValue('inspectorPosition#' . $cx, $committee->employee->position->name . ($committee->employee->level ? $committee->employee->level->name : ''));

            if (sizeof($requisition->committees) == 1) {
                $word->setValue('committee#' . $cx, 'ผู้ตรวจรับพัสดุ');
            } else {
                $word->setValue('committee#' . $cx, $cx == 1 ? 'ประธานกรรรมการฯ' : 'กรรรมการฯ');
            }
            $cx++;
        }
        /** ================================== CONTENT ================================== */
        
        /** ================================== SIGNATURE ================================== */
        // $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        // $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        // $word->setValue('headOfDepartRole', ($headOfDepart->memberOf[0]->duty_id == 2 ? 'หัวหน้า' : $headOfDepart->memberOf[0]->duty->name) . $requisition->division->department->name);
        /** ================================== SIGNATURE ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getConsider(Request $req, $id)
    {
        $requisition = Requisition::with('category','budget','details','project','division','division.department')
                        ->with('details.item','details.item.unit')
                        ->with('requester','requester.prefix','requester.position','requester.level')
                        ->with('committees','committees.employee','committees.employee.prefix')
                        ->with('committees.employee.position','committees.employee.level')
                        ->with('approvals','approvals.procuring')
                        ->find($id);

        // $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
        //                     ->whereIn('id', Member::where('department_id', $requisition->division->department_id)->whereIn('duty_id', [2, 5])->pluck('employee_id'))
        //                     ->where('status', 1)
        //                     ->first();

        $template = 'consideration.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/requisitions/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('considerNo', $requisition->approvals[0]->consider_no);
        $word->setValue('considerDate', convDbDateToLongThDate($requisition->approvals[0]->consider_date));
        $word->setValue('topic', $requisition->topic);
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        $word->setValue('objective', $requisition->order_type_id == 1 ? 'ซื้อ' . $requisition->category->name : $requisition->contract_desc);
        $word->setValue('item', $requisition->order_type_id == 1 ? $requisition->category->name : $requisition->contract_desc);
        $word->setValue('itemCount', $requisition->item_count);
        $word->setValue('supplier', $requisition->approvals[0]->supplier->name);
        $word->setValue('netTotal', number_format($requisition->net_total));
        $word->setValue('netTotalText', baht_text($requisition->net_total));
        /** ================================== CONTENT ================================== */
        
        /** ================================== SIGNATURE ================================== */
        // $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        // $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        // $word->setValue('headOfDepartRole', ($headOfDepart->memberOf[0]->duty_id == 2 ? 'หัวหน้า' : $headOfDepart->memberOf[0]->duty->name) . $requisition->division->department->name);
        /** ================================== SIGNATURE ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }

    public function getNotice(Request $req, $id)
    {
        $requisition = Requisition::with('category','budget','details','project','division','division.department')
                        ->with('details.item','details.item.unit')
                        ->with('requester','requester.prefix','requester.position','requester.level')
                        ->with('committees','committees.employee','committees.employee.prefix')
                        ->with('committees.employee.position','committees.employee.level')
                        ->with('approvals','approvals.procuring')
                        ->find($id);

        // $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
        //                     ->whereIn('id', Member::where('department_id', $requisition->division->department_id)->whereIn('duty_id', [2, 5])->pluck('employee_id'))
        //                     ->where('status', 1)
        //                     ->first();

        $template = 'notice.docx';
        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/requisitions/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('noticeDate', convDbDateToLongThDate($requisition->approvals[0]->notice_date));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        $word->setValue('objective', $requisition->order_type_id == 1 ? 'ซื้อ' . $requisition->category->name : $requisition->contract_desc);
        $word->setValue('item', $requisition->order_type_id == 1 ? $requisition->category->name : $requisition->contract_desc);
        $word->setValue('itemCount', $requisition->item_count);
        $word->setValue('supplier', $requisition->approvals[0]->supplier->name);
        $word->setValue('netTotal', number_format($requisition->net_total));
        $word->setValue('netTotalText', baht_text($requisition->net_total));
        /** ================================== CONTENT ================================== */
        
        /** ================================== SIGNATURE ================================== */
        // $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        // $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        // $word->setValue('headOfDepartRole', ($headOfDepart->memberOf[0]->duty_id == 2 ? 'หัวหน้า' : $headOfDepart->memberOf[0]->duty->name) . $requisition->division->department->name);
        /** ================================== SIGNATURE ================================== */

        $pathToSave = public_path('temp/' . $template);
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }
}
