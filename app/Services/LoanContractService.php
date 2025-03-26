<?php

namespace App\Services;

use Phattarachai\LineNotify\Facade\Line;
use Carbon\Carbon;
use App\Repositories\PlaceRepository;
use App\Models\Loan;
use App\Models\LoanContract;
use App\Models\LoanContractDetail;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\Department;

class LoanContractService
{
    /**
     * @var $contractRepo
     */
    protected $contractRepo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    public function __construct(PlaceRepository $contractRepo)
    {
        $this->contractRepo = $contractRepo;

        // $this->repo->setSortBy('approved_date');
        // $this->repo->setSortOrder('desc');
        // $this->repo->setRelations([
        //     'details','details.expense','details.loanDetail','loan.department',
        //     'loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level',
        //     'loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan',
        //     'loan.courses','loan.courses.place','loan.courses.place.changwat'
        // ]);
    }

    public function find($id)
    {
        return $this->contractRepo->getPlace($id);
    }

    public function findAll($params = [])
    {
        return $this->contractRepo->getPlaces($params)->get();
    }

    // public function search($params = [])
    // {
    //     $limit = (array_key_exists('limit', $params) && $params['limit']) ? $params['limit'] : 10;

    //     return $this->contractRepo->getPlaces($params)->paginate(10);
    // }

    // public function findById($id)
    // {
    //     return $this->contractRepo->getPlaceById($id);
    // }

    public function findContractToNotify()
    {
        $contracts = LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                                ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                                ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan')
                                ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                                ->where(\DB::Raw('MONTH(refund_date)'), date('m'))
                                ->whereIn('refund_notify', [0,1])
                                ->whereIn('status', [1, 2, 3])
                                ->get();

        return $contracts;
    }

    public function sendNotify(INotify $notify)
    {
        $contracts = $this->findContractToNotify();

        foreach($contracts as $contract) {
            $remainDays = Carbon::parse(date('Y-m-d'))->diffInDays(Carbon::parse($contract->refund_date));

            if ($contract->refund_days == 15) { // กรณียืมไปราชการ
                if ($remainDays <= 5) {
                    /** อัพเดตฟิลด์ refund_notify เป็น 2 แจ้งเตือนครบแล้ว */
                    LoanContract::find($contract->id)->update(['refund_notify' => 2]);

                    /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
                    $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                    $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                    $msg .= ' จะครบกำหนดคืนเงินในอีก ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                    $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';

                    Line::send($msg);
                }
            } else { // กรณียืมโครงการ
                if ($contract->refund_notify == 0 && $remainDays <= 10) { // แจ้งเตือนครั้งที่ 1
                    /** อัพเดตฟิลด์ refund_notify เป็น 1 แจ้งเตือนยังไม่ครบ*/
                    LoanContract::find($contract->id)->update(['refund_notify' => 1]);

                    /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
                    $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                    $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                    $msg .= ' จะครบกำหนดคืนเงินในอีก ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                    $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';

                    Line::send($msg);
                } else if ($contract->refund_notify == 1 && $remainDays <= 5) { // แจ้งเตือนครั้งที่ 2
                    /** อัพเดตฟิลด์ refund_notify เป็น 1 แจ้งเตือนยังไม่ครบ*/
                    LoanContract::find($contract->id)->update(['refund_notify' => 2]);

                    /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
                    $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                    $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                    $msg .= ' จะครบกำหนดคืนเงินในอีก ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                    $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';

                    $notify->send($msg);
                }
            }
        }
    }
    // public function initForm()
    // {
    //     return [
    //         'tambons'       => Tambon::all(),
    //         'amphurs'       => Amphur::all(),
    //         'changwats'     => Changwat::all(),
    //     ];
    // }

    // public function store($req)
    // {
    //     $data = [
    //         'name'          => $req['name'],
    //         'place_type_id' => $req['place_type_id'],
    //         'address_no'    => $req['address_no'],
    //         'road'          => $req['road'],
    //         'moo'           => $req['moo'],
    //         'tambon_id'     => $req['tambon_id'],
    //         'amphur_id'     => $req['amphur_id'],
    //         'changwat_id'   => $req['changwat_id'],
    //         'zipcode'       => $req['zipcode'],
    //         'latitude'      => $req['latitude'],
    //         'longitude'     => $req['longitude'],
    //         'status'        => 1,
    //     ];

    //     return $this->contractRepo->store($data);
    // }

    // public function delete($id)
    // {
    //     return $this->contractRepo->delete($id);
    // }
}