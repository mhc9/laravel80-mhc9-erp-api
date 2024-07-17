<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Requisition;
use App\Models\RequisitionDetail;
use App\Models\Item;
use App\Models\AssetType;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Division;
use App\Models\Committee;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Member;

class RequisitionController extends Controller
{
    public function formValidate (Request $request)
    {
        $rules = [
            'name'          => 'required',
            'department_id' => 'required',
        ];

        $messages = [
            'name.required'             => 'กรุณาระบุชื่องาน',
            'department_id.required'    => 'กรุณาเลือกกลุ่มงาน',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $messageBag = $validator->getMessageBag();

            // if (!$messageBag->has('start_date')) {
            //     if ($this->isDateExistsValidation(convThDateToDbDate($request['start_date']), 'start_date') > 0) {
            //         $messageBag->add('start_date', 'คุณมีการลาในวันที่ระบุแล้ว');
            //     }
            // }

            return [
                'success' => 0,
                'errors' => $messageBag->toArray(),
            ];
        } else {
            return [
                'success' => 1,
                'errors' => $validator->getMessageBag()->toArray(),
            ];
        }
    }

    public function search(Request $req)
    {
        /** Get params from query string */
        $pr_no      = $req->get('pr_no');
        $pr_date    = $req->get('pr_date');
        $division   = $req->get('division');
        $category   = $req->get('category');
        $status     = $req->get('status');
        $limit      = $req->get('limit') ? $req->get('limit') : 10;

        $requisitions = Requisition::with('category','budget','budget.project','budget.project.plan','project')
                            ->with('division','division.department','details','details.item','details.item.unit')
                            ->with('requester','requester.prefix','requester.position','requester.level')
                            ->with('committees','committees.employee','committees.employee.prefix')
                            ->with('committees.employee.position','committees.employee.level')
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

        $requisitions = Requisition::with('category','budget','budget.project','budget.project.plan','project')
                            ->with('division','division.department','details','details.item','details.item.unit')
                            ->with('requester','requester.prefix','requester.position','requester.level')
                            ->with('committees','committees.employee','committees.employee.prefix')
                            ->with('committees.employee.position','committees.employee.level')
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
        return Requisition::with('category','budget','budget.project','budget.project.plan','project')
                    ->with('division','division.department','details','details.item','details.item.unit')
                    ->with('requester','requester.prefix','requester.position','requester.level')
                    ->with('committees','committees.employee','committees.employee.prefix')
                    ->with('committees.employee.position','committees.employee.level')
                    ->with('approvals','approvals.procuring')
                    ->find($id);
    }

    public function getInitialFormData()
    {
        $year = 2566;
        $types          = AssetType::with('categories')->get();
        $categories     = AssetCategory::with('type')->get();
        $departments    = Department::with('divisions')->get();

        return [
            'types'         => $types,
            'categories'    => $categories,
            'departments'   => $departments,
            'divisions'     => Division::all(),
            'projects'      => Project::where('year', $year)->get(),
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
            $requisition->requester_id  = $req['requester_id'];
            $requisition->division_id   = $req['division_id'];
            $requisition->reason        = $req['reason'];
            $requisition->item_count    = $req['item_count'];
            $requisition->net_total     = currencyToNumber($req['net_total']);
            $requisition->status        = 1;

            if($requisition->save()) {
                /** Insert items to RequisitionDetail */
                foreach($req['items'] as $item) {
                    $detail = new RequisitionDetail();
                    $detail->pr_id      = $requisition->id;
                    $detail->item_id    = $item['item_id'];
                    $detail->description = $item['description'];
                    $detail->amount     = $item['amount'];
                    $detail->price      = $item['price'];
                    $detail->unit_id    = $item['unit_id'];
                    $detail->total      = $item['total'];
                    $detail->save();
                }

                /** Insert committees */
                foreach($req['committees'] as $employee) {
                    $committee = new Committee();
                    $committee->employee_id         = $employee['employee_id'];
                    $committee->requisition_id      = $requisition->id;
                    $committee->committee_type_id   = 2;
                    $committee->save();
                }

                return [
                    'status'        => 1,
                    'message'       => 'Insertion successfully!!',
                    'requisition'   => $requisition
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
            $division = Division::find($id);
            $division->name             = $req['name'];
            $division->department_id    = $req['department_id'];
            $division->status           = $req['status'] ? 1 : 0;

            if($division->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Updating successfully!!',
                    'division'  => $division
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
            // $item = Item::find($id);

            // if($item->delete()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Deleting successfully!!',
            //         'item'      => $item
            //     ];
            // } else {
            //     return [
            //         'status'    => 0,
            //         'message'   => 'Something went wrong!!'
            //     ];
            // }
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
                        ->with('details.item','details.item.unit')
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
        $requisition = Requisition::with('category','budget','details','project','division','division.department')
                        ->with('details.item','details.item.unit')
                        ->with('requester','requester.prefix','requester.position','requester.level')
                        ->with('committees','committees.employee','committees.employee.prefix')
                        ->with('committees.employee.position','committees.employee.level')
                        ->find($id);

        $headOfDepart = Employee::with('prefix','position','level','memberOf','memberOf.duty','memberOf.department')
                            ->whereIn('id', Member::where('department_id', $requisition->division->department_id)->whereIn('duty_id', [2, 5])->pluck('employee_id'))
                            ->where('status', 1)
                            ->first();

        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/requisition.docx'));

        /** ================================== HEADER ================================== */
        $word->setValue('department', $requisition->division->department->name);
        $word->setValue('pr_no', $requisition->pr_no);
        $word->setValue('pr_date', convDbDateToLongThDate($requisition->pr_date));
        $word->setValue('topic', $requisition->topic);
        /** ================================== HEADER ================================== */

        /** ================================== CONTENT ================================== */
        $word->setValue('division', $requisition->division->name);
        $word->setValue('objective', $requisition->order_type_id == 1 ? 'ซื้อ' . $requisition->category->name : $requisition->contract_desc);
        $word->setValue('itemCount', $requisition->item_count);
        $word->setValue('reason', $requisition->reason);
        $word->setValue('year', $requisition->year);
        $word->setValue('budget', $requisition->budget->project->plan->name . ' ' . $requisition->budget->project->name  . ' ' . $requisition->budget->name);
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
        $word->setValue('headOfDepart', $headOfDepart->prefix->name.$headOfDepart->firstname . ' ' . $headOfDepart->lastname);
        $word->setValue('headOfDepartPosition', $headOfDepart->position->name . $headOfDepart->level->name);
        $word->setValue('headOfDepartRole', ($headOfDepart->memberOf[0]->duty_id == 2 ? 'หัวหน้า' : $headOfDepart->memberOf[0]->duty->name) . $requisition->division->department->name);
        /** ================================== SIGNATURE ================================== */

        $pathToSave = public_path('temp/requisition.docx');
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

        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/requisitionReport.docx'));

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
        $word->setValue('budget', $requisition->budget->project->plan->name . ' ' . $requisition->budget->project->name  . ' ' . $requisition->budget->name);
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

        $pathToSave = public_path('temp/requisitionReport.docx');
        $filepath = $word->saveAs($pathToSave);

        return response()->download($pathToSave);
    }
}
