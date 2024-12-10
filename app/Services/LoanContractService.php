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
                                ->whereIn('status', [1, 2])
                                ->get();

        return $contracts;
    }

    public function sendNotify($data)
    {
        $contract = $data->load('details','details.expense','details.loanDetail','loan.department',
                                'loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level',
                                'loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan',
                                'loan.courses','loan.courses.place','loan.courses.place.changwat');

        /** แจ้งเตือนไปในไลน์กลุ่ม "สัญญาเงินยืม09" */
        $lineMsg = 'เงินยืมราชการของคุณ' .$contract->loan->employee->firstname. ' ' .$contract->loan->employee->lastname;
        $lineMsg .= ' เลขที่สัญญา ' .$contract->contract_no;
        $lineMsg .= ' จะเข้าบัญชีในวันที่ ' .convDbDateToThDate($contract->deposited_date);
        $lineMsg .= ' แจ้งเตือน ณ วันที่ ' .convDbDateToThDate(date('Y-m-d')). ' เวลา ' .date('H:i'). 'น.';

        Line::send($lineMsg);
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