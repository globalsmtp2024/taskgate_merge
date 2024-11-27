<?php

namespace Modules\Payroll\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\Payroll\Entities\PayrollSetting;
use Modules\Payroll\Entities\SalarySlip;
use DB;

class PayrollReportController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('payroll::app.menu.payroll');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(PayrollSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $this->pageTitle = 'app.menu.reports';
        $this->employees = User::allEmployees(null, true);
        $this->payrollSetting = PayrollSetting::first();
        $this->currency = PayrollSetting::with('currency')->first();

        $totals = SalarySlip::select('month',
            DB::raw('SUM(tds) as total_tds')
        )->groupBy('month')->get();

        $this->totalArr = $totals->mapWithKeys(function ($item) {
            return [$item['month'] => $item['total_tds']];
        })->toArray();

        $this->totalTdsPaid = $totals->sum('total_tds');

        $tab = request('tab');

        switch ($tab) {
        case 'employee-tds':
            $this->view = 'payroll::payroll-report.ajax.employee-tds';
            break;
        default:
            $this->view = 'payroll::payroll-report.ajax.company-tds';
            break;
        }

        $this->activeTab = $tab ?: 'company-tds';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('payroll::payroll-report.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function fetchTds($id)
    {
        $this->currency = PayrollSetting::with('currency')->first();
        $this->salarySlip = SalarySlip::where('user_id', $id)->select('tds', 'month', 'year')->get();
        $monthArr = SalarySlip::where('user_id', $id)->pluck('month')->toArray();
        $tdsArr = SalarySlip::where('user_id', $id)->pluck('tds')->toArray();
        $this->main = array_combine($monthArr, $tdsArr);
 
        $this->monthArr = $monthArr;
        $this->payrollSetting = PayrollSetting::first();

        $this->tdsAlreadyPaid = SalarySlip::where('user_id', $id)->sum('tds');

        $view = view('payroll::payroll-report.ajax.fetch-tds', $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $this->data, 'html' => $view]);
    }

}
