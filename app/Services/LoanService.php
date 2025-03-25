<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\PlaceRepository;
use App\Common\Notifications\DiscordNotify;
use App\Models\Loan;
use App\Models\LoanDetail;
use App\Models\LoanBudget;
use App\Models\LoanContract;
use App\Models\ProjectCourse;
use App\Models\Expense;
use App\Models\Department;
use App\Models\Budget;
use Carbon\Carbon;
use App\Interfaces\INotify;

class LoanService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    /**
     * @var $notify
     */
    private INotify $notify;

    public function __construct(PlaceRepository $repo)
    {
        $this->repo = $repo;
        $this->notify = new DiscordNotify;
    }

    public function find($id)
    {
        return $this->repo->getPlace($id);
    }

    public function findAll($params = [])
    {
        return $this->repo->getPlaces($params)->get();
    }

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

    public function sendNotify()
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

                    $this->notify->send($msg);
                }
            }
        }
    }
}