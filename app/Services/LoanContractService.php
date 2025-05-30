<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Services\BaseService;
use App\Repositories\LoanContractRepository;
use App\Common\Notifications\DiscordNotify;
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

    public function __construct(LoanContractRepository $repo)
    {
        $this->repo = $repo;

        $this->repo->setSortBy('approved_date');
        $this->repo->setSortOrder('desc');

        $this->repo->setRelations([
            'details','details.expense','details.loanDetail','loan.department',
            'loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level',
            'loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan',
            'loan.budgets.budget.type','loan.courses','loan.courses.place','loan.courses.place.changwat'
        ]);
    }

    /**
     * Get LoanContract data with conditions
     *
     * @param array $params
     * @param boolean $all
     * @param integer $perPage
     * @return LengthAwarePaginator | Collection
     */
    public function search(array $params, $all = false, $perPage = 10): LengthAwarePaginator | Collection
    {
        $collections = $this->repo->getModelWithRelations()
                            ->when((!auth()->user()->isAdmin() && !auth()->user()->isFinancial()), function($q) {
                                $q->where('employee_id', auth()->user()->employee_id);
                            })
                            ->when(!empty($params['employee']), function($q) use ($params) {
                                $q->where('employee_id', $params['employee']);
                            })
                            ->when(!empty($params['year']), function($q) use ($params) {
                                $q->where('year', $params['year']);
                            })
                            ->when(!empty($params['status']), function($q) use ($params) {
                                $q->where('status', $params['status']);
                            })
                            ->orderBy('approved_date', 'desc');

        return $all ?  $collections->get() : $collections->paginate($perPage);
    }

    /**
     * Get loan contract data for summary report
     *
     * @param integer $year
     * @return LengthAwarePaginator | Collection
     */
    public function getReport(int $year, int $perPage = 10): LengthAwarePaginator | Collection
    {
        return $this->repo->getModelWithRelations()
                    ->with('refund')
                    ->where('year', $year)
                    ->orderBy('approved_date', 'DESC')
                    ->paginate($perPage);
    }

    public function getContractToNotify(): Collection
    {
        return $this->repo->getModel()
                    ->where(\DB::Raw('MONTH(refund_date)'), date('m'))
                    ->whereIn('refund_notify', [0, 1, 2 ,3])
                    ->whereIn('status', [1, 2, 3])
                    ->get();
    }

    /**
     * แจ้งเตือนการคืนเงิน
     * @return void
     */
    public function notifyRefund(): void
    {
        $contracts = $this->getContractToNotify();

        foreach($contracts as $contract) {
            $msg = '';
            $refundNotify = 0;
            $remainDays = Carbon::parse(date('Y-m-d'))->diffInDays(Carbon::parse($contract->refund_date));

            if ($contract->refund_notify == 3 && $remainDays < 0) { // กรณีเลยกำหนดคืนเงิน
                /** เซตค่า refundNotify เป็น 2 = แจ้งเตือนครบแล้ว */
                $refundNotify = 4;

                /** ข้อความแจ้งเตือน */
                $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                $msg .= ' เลยกำหนดคืนเงินแล้ว ' .$remainDays .' วัน (ครบกำหนดวันที่ ' .convDbDateToThDate($contract->refund_date) . ')';
                $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';
            } else if ($contract->refund_notify == 2 && $remainDays == 0) { // กรณีครบกำหนดคืนเงิน
                /** เซตค่า refundNotify เป็น 2 = แจ้งเตือนครบแล้ว */
                $refundNotify = 3;

                /** ข้อความแจ้งเตือน */
                $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
                $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
                $msg .= ' ครบกำหนดคืนเงินแล้ววันนี้';
                $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';
            } else {
                if ($contract->refund_days == 15) { // กรณียืมไปราชการ
                    if ($contract->refund_notify == 0 && $remainDays <= 5) {
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
                $this->sendNotify(new DiscordNotify, $msg);

                /** อัพเดตฟิลด์ refund_notify */
                $this->update($contract->id, ['refund_notify' => $refundNotify]);
            }
        }
    }

    /**
     * แจ้งเตือนเงินเข้า
     * @param LoanContract $contract
     * @return void
     */
    public function notifyDeposit(LoanContract $contract): void
    {
        /** ข้อความแจ้งเตือน */
        $msg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
        $msg .= ' เลขที่สัญญา ' .$contract->contract_no;
        $msg .= ' จะเข้าบัญชีในวันที่ ' .convDbDateToThDate($contract->deposited_date);
        $msg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';

        /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
        $this->sendNotify(new DiscordNotify, $msg);
    }

    /**
     * @param INotify $notify
     * @param string $message
     * @return void
     */
    private function sendNotify(INotify $notify, string $message): void
    {
        /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
        $notify->send($message);
    }

    public function getFormData(): array
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

        $employees = Employee::with('prefix','position','level','memberOf')
                                ->with('memberOf.duty','memberOf.department','memberOf.division')
                                ->whereIn('status', [1,5,6])
                                ->get();

        return [
            'departments'   => Department::all(),
            'expenses'      => Expense::all(),
            'loanTypes'     => $loanTypes,
            'moneyTypes'    => $moneyTypes,
            'employees'     => $employees,
        ];
    }
}