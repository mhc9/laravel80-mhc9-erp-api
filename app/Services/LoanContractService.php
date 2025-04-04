<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\Services\BaseService;
use App\Repositories\LoanContractRepository;
use App\Common\Notifications\DiscordNotify;
use App\Models\Loan;
use App\Models\LoanContract;
use App\Models\LoanContractDetail;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\Department;
use App\Interfaces\INotify;

class LoanContractService extends BaseService
{
    /**
     * @var $repo
     */
    protected $repo;

    /**
     * @var $destPath
     */
    protected $destPath = 'products';

    public function __construct(LoanContractRepository $repo)
    {
        $this->repo = $repo;

        $this->repo->setSortBy('approved_date');
        $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'details','details.expense','details.loanDetail','loan.department',
            'loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level',
            'loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan',
            'loan.courses','loan.courses.place','loan.courses.place.changwat'
        ]);
    }

    public function search(array $params, $all = false, $perPage = 10)
    {
        $collections = $this->repo->getModelWithRelations();

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    public function findContractToNotify()
    {
        return $this->repo->getModel()
                            ->where(\DB::Raw('MONTH(refund_date)'), date('m'))
                            ->whereIn('refund_notify', [0,1])
                            ->whereIn('status', [1, 2, 3])
                            ->get();
    }

    /**
     * แจ้งเตือนการคืนเงิน
     * @return void
     */
    public function notifyRefund(): void
    {
        $contracts = $this->findContractToNotify();

        foreach($contracts as $contract) {
            $msg = '';
            $refundNotify = 0;
            $remainDays = Carbon::parse(date('Y-m-d'))->diffInDays(Carbon::parse($contract->refund_date));

            if ($remainDays < 0) { // กรณีครบหรือเลยกำหนดคืนเงิน
                /** เซตค่า refundNotify เป็น 2 = แจ้งเตือนครบแล้ว */
                $refundNotify = 2;

                /** ข้อความแจ้งเตือน */
                $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                $msg .= ' เลยกำหนดคืนเงินแล้ว ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';
            } else {
                if ($contract->refund_days == 15) { // กรณียืมไปราชการ
                    if ($remainDays <= 5) {
                        /** เซตค่า refundNotify เป็น 2 = แจ้งเตือนครบแล้ว */
                        $refundNotify = 2;

                        /** ข้อความแจ้งเตือน */
                        $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                        $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                        $msg .= ' จะครบกำหนดคืนเงินในอีก ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                        $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';
                    }
                } else { // กรณียืมโครงการ
                    if ($contract->refund_notify == 0 && $remainDays <= 10) { // แจ้งเตือนครั้งที่ 1
                        /** เซตค่า refundNotify เป็น 1 = แจ้งเตือนยังไม่ครบ */
                        $refundNotify = 1;

                        /** ข้อความแจ้งเตือน */
                        $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                        $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                        $msg .= ' จะครบกำหนดคืนเงินในอีก ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                        $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';
                    } else if ($contract->refund_notify == 1 && $remainDays <= 5) { // แจ้งเตือนครั้งที่ 2
                        /** เซตค่า refundNotify เป็น 2 = แจ้งเตือนครบแล้ว */
                        $refundNotify = 2;

                        /** ข้อความแจ้งเตือน */
                        $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                        $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                        $msg .= ' จะครบกำหนดคืนเงินในอีก ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                        $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';
                    }
                }
            }

            if (!empty($msg)) {
                /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
                $notify->send(new DiscordNotify, $msg);

                /** อัพเดตฟิลด์ refund_notify */
                $this->update($contract->id, ['refund_notify' => $refundNotify]);
            }
        }
    }

    /**
     * แจ้งเตือนเงินเข้า
     * @param Collection $contract
     * 
     * @return void
     */
    public function notifyDeposit(Collection $contract): void
    {
        /** ข้อความแจ้งเตือน */
        $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
        $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
        $msg .= ' จะเข้าบัญชีในวันที่ ' .convDbDateToThDate($contract->deposited_date);
        $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';

        /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
        $notify->send(new DiscordNotify, $msg);
    }

    /**
     * @param INotify $notify
     * @param string $message
     * 
     * @return void
     */
    public function send(INotify $notify, string $message): void
    {
        /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
        $notify->send($message);
    }
}