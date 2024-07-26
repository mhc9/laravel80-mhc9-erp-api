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
use App\Models\Invitation;

class InvitationController extends Controller
{
    public function search(Request $req)
    {
        /** Get params from query string */
        // $pr_no      = $req->get('pr_no');
        // $pr_date    = $req->get('pr_date');
        // $division   = $req->get('division');
        // $category   = $req->get('category');
        // $status     = $req->get('status');
        // $limit      = $req->get('limit') ? $req->get('limit') : 10;

        // $requisitions = Requisition::with('category','budget','budget.project','budget.project.plan','project')
        //                     ->with('division','division.department','details','details.item','details.item.unit')
        //                     ->with('requester','requester.prefix','requester.position','requester.level')
        //                     ->with('committees','committees.employee','committees.employee.prefix')
        //                     ->with('committees.employee.position','committees.employee.level')
        //                     ->with('approvals','approvals.procuring','approvals.supplier')
        //                     ->when(!empty($pr_no), function($q) use ($pr_no) {
        //                         $q->where('pr_no', 'like', '%'.$pr_no.'%');
        //                     })
        //                     ->when(!empty($pr_date), function($q) use ($pr_date) {
        //                         $q->where('pr_date', $pr_date);
        //                     })
        //                     ->when(!empty($division), function($q) use ($division) {
        //                         $q->where('division_id', $division);
        //                     })
        //                     ->when(!empty($category), function($q) use ($category) {
        //                         $q->where('category_id', $category);
        //                     })
        //                     ->when($status != '', function($q) use ($status) {
        //                         $q->where('status', $status);
        //                     })
        //                     ->orderBy('pr_date', 'DESC')
        //                     ->paginate($limit);

        // return $requisitions;
    }

    public function getAll(Request $req)
    {
        /** Get params from query string */
        // $category   = $req->get('category');
        // $requester  = $req->get('requester');
        // $status     = $req->get('status');

        // $requisitions = Requisition::with('category','budget','budget.project','budget.project.plan','project')
        //                     ->with('division','division.department','details','details.item','details.item.unit')
        //                     ->with('requester','requester.prefix','requester.position','requester.level')
        //                     ->with('committees','committees.employee','committees.employee.prefix')
        //                     ->with('committees.employee.position','committees.employee.level')
        //                     ->with('approvals','approvals.procuring','approvals.supplier')
        //                     ->when(!empty($category), function($q) use ($category) {
        //                         $q->where('category_id', $category);
        //                     })
        //                     ->when($status != '', function($q) use ($status) {
        //                         $q->where('status', $status);
        //                     })
        //                     ->get();

        // return $requisitions;
    }

    public function getById($id)
    {
        // return Requisition::with('category','budget','budget.project','budget.project.plan','project')
        //             ->with('division','division.department','details','details.item','details.item.unit')
        //             ->with('requester','requester.prefix','requester.position','requester.level')
        //             ->with('committees','committees.employee','committees.employee.prefix')
        //             ->with('committees.employee.position','committees.employee.level')
        //             ->with('approvals','approvals.procuring','approvals.supplier')
        //             ->find($id);
    }

    public function getInitialFormData()
    {
        // $year = 2566;
        // $types          = AssetType::with('categories')->get();
        // $categories     = AssetCategory::with('type')->get();
        // $departments    = Department::with('divisions')->get();

        // return [
        //     'types'         => $types,
        //     'categories'    => $categories,
        //     'departments'   => $departments,
        //     'divisions'     => Division::all(),
        //     'projects'      => Project::where('year', $year)->get(),
        // ];
    }

    public function store(Request $req)
    {
        try {
            // $requisition = new Requisition();
            // $requisition->pr_no         = $req['pr_no'];
            // $requisition->pr_date       = $req['pr_date'];
            // $requisition->order_type_id = $req['order_type_id'];
            // $requisition->category_id   = $req['category_id'];
            // $requisition->contract_desc = $req['contract_desc'];
            // $requisition->topic         = $req['topic'];
            // $requisition->year          = $req['year'];
            // $requisition->budget_id     = $req['budget_id'];
            // $requisition->project_id    = $req['project_id'];
            // $requisition->requester_id  = $req['requester_id'];
            // $requisition->division_id   = $req['division_id'];
            // $requisition->reason        = $req['reason'];
            // $requisition->item_count    = $req['item_count'];
            // $requisition->net_total     = currencyToNumber($req['net_total']);
            // $requisition->status        = 1;

            // if($requisition->save()) {
            //     /** Insert items to RequisitionDetail */
            //     foreach($req['items'] as $item) {
            //         $detail = new RequisitionDetail();
            //         $detail->pr_id      = $requisition->id;
            //         $detail->item_id    = $item['item_id'];
            //         $detail->description = $item['description'];
            //         $detail->amount     = $item['amount'];
            //         $detail->price      = $item['price'];
            //         $detail->unit_id    = $item['unit_id'];
            //         $detail->total      = $item['total'];
            //         $detail->save();
            //     }

            //     /** Insert committees */
            //     foreach($req['committees'] as $employee) {
            //         $committee = new Committee();
            //         $committee->employee_id         = $employee['employee_id'];
            //         $committee->requisition_id      = $requisition->id;
            //         $committee->committee_type_id   = 2;
            //         $committee->save();
            //     }

            //     return [
            //         'status'        => 1,
            //         'message'       => 'Insertion successfully!!',
            //         'requisition'   => $requisition
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

    public function update(Request $req, $id)
    {
        try {
            // $division = Division::find($id);
            // $division->name             = $req['name'];
            // $division->department_id    = $req['department_id'];
            // $division->status           = $req['status'] ? 1 : 0;

            // if($division->save()) {
            //     return [
            //         'status'    => 1,
            //         'message'   => 'Updating successfully!!',
            //         'division'  => $division
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

    public function getReport(Request $req)
    {
        $doc_no = $req->get('doc_no');
        $doc_date = $req->get('doc_date');

        $invitation = Invitation::leftJoin('Employee', 'Employee.EmId', '=', 'OT.OTEmid')
                            ->leftJoin('Position', 'Employee.EmPosition', '=', 'Position.PosId')
                            ->where('OTType', 'วิทยากร')
                            ->where('OTNumberOrg', $doc_no)
                            ->where('OTOrgDate', $doc_date)
                            ->get();

        $template = '';
        /** ตรวจสอบว่าเป็นหน่วยงานภายนอกกรมสุขภาพจิตหรือไม่ */
        if ($invitation[0]->IsOutDmhOrg == '1') {
            if (count($invitation) > 1) {
                $template = 'out-dmh2.docx';
            } else {
                $template = 'out-dmh1.docx';
            }
        } else {
            if (count($invitation) > 1) {
                $template = 'in-dmh2.docx';
            } else {
                $template = 'in-dmh1.docx';
            }
        }

        $word = new \PhpOffice\PhpWord\TemplateProcessor(public_path('uploads/templates/speakers/' . $template));

        /** ================================== HEADER ================================== */
        $word->setValue('memoMonth', convDbDateToLongThMonth(date('Y-m-d')));
        /** ================================== HEADER ================================== */
        
        /** ================================== CONTENT ================================== */
        $word->setValue('docNo', $invitation[0]->OTNumberOrg);
        $word->setValue('docDate', convDbDateToLongThDate($invitation[0]->OTOrgDate));
        $word->setValue('replyTo', $invitation[0]->ReplyTo);
        $word->setValue('requester', $invitation[0]->OTOrganism);
        $word->setValue('projectName', $invitation[0]->OTName);
        $word->setValue(
            'projectDate',
            ($invitation[0]->OTDateProject2
                ? 'ระหว่างวันที่ ' . convDbDateToLongThDate($invitation[0]->OTDateProject) . ' ถึงวันที่ ' . convDbDateToLongThDate($invitation[0]->OTDateProject2)
                : 'ในวันที่ ' . convDbDateToLongThDate($invitation[0]->OTDateProject))
        );
        $word->setValue('place', $invitation[0]->OTLocation);

        if (count($invitation) > 1) {
            $cx = 1;
            $word->cloneRow('speaker', sizeof($invitation));
            foreach($invitation as $speaker => $val) {
                $word->setValue('no#' . $cx, $cx);
                $word->setValue('speaker#' . $cx, $val->EmPerfix . $val->EmName);
                $word->setValue('speakerPosition#' . $cx, $val->PosName);
                $cx++;
            }
        } else {
            $word->setValue('speaker', $invitation[0]->EmPerfix . $invitation[0]->EmName);
            $word->setValue('speakerPosition', $invitation[0]->PosName);
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
}
