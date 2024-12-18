<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\LoanContract;

class LoanContractExport implements FromView
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function view(): View
    {
        $contracts = LoanContract::with('details','details.expense','details.loanDetail','loan.department')
                                ->with('loan.employee','loan.employee.prefix','loan.employee.position','loan.employee.level')
                                ->with('loan.budgets','loan.budgets.budget','loan.budgets.budget.activity.project','loan.budgets.budget.activity.project.plan')
                                ->with('loan.courses','loan.courses.place','loan.courses.place.changwat')
                                ->where('year', $this->year)
                                ->orderBy('approved_date')
                                ->orderBy('contract_no')
                                ->get();

        return view('exports.loans.contract-report', [
            'contracts' => $contracts
        ]);
    }
}
