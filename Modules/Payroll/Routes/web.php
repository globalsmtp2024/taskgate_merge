<?php

use Illuminate\Support\Facades\Route;
use Modules\Payroll\Http\Controllers\EmployeeMonthlySalaryController;
use Modules\Payroll\Http\Controllers\PayrollController;
use Modules\Payroll\Http\Controllers\PayrollCurrencyController;
use Modules\Payroll\Http\Controllers\PayrollReportController;
use Modules\Payroll\Http\Controllers\PayrollSettingController;
use Modules\Payroll\Http\Controllers\SalaryComponentController;
use Modules\Payroll\Http\Controllers\SalaryGroupController;
use Modules\Payroll\Http\Controllers\SalaryPaymentMethodController;
use Modules\Payroll\Http\Controllers\SalarySettingController;
use Modules\Payroll\Http\Controllers\SalaryTdsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth', 'prefix' => 'account'], function () {

    Route::post('payroll/generate', [PayrollController::class, 'generatePaySlip'])->name('payroll.generate_pay_slip');
    Route::post('payroll/updateStatus', [PayrollController::class, 'updateStatus'])->name('payroll.update_status');
    Route::get('payroll/get-status', [PayrollController::class, 'getStatus'])->name('payroll.get_status');
    Route::get('payroll/download/{id}', [PayrollController::class, 'downloadPdf'])->name('payroll.download_pdf');
    Route::post('payroll/get-cycle-data', [PayrollController::class, 'getCycleData'])->name('payroll.get-cycle-data');
    Route::post('payroll/get_expense_title', [PayrollController::class, 'getExpenseTitle'])->name('payroll.get_expense_title');
    Route::get('payroll/get_employee/{payrollCycle?}/{departmentId?}', [PayrollController::class, 'byDepartment'])->name('payroll.get-employee');

    Route::resource('payroll', PayrollController::class);

    Route::get('employee-salary/data', [EmployeeMonthlySalaryController::class, 'data'])->name('employee-salary.data');
    Route::post('employee-salary/payroll-cycle', [EmployeeMonthlySalaryController::class, 'employeePayrollCycle'])->name('employee-salary.payroll-cycle');
    Route::post('employee-salary/payroll-status', [EmployeeMonthlySalaryController::class, 'employeePayrollStatus'])->name('employee-salary.payroll-status');
    Route::get('employee-salary/make-salary/{id}', [EmployeeMonthlySalaryController::class, 'makeSalary'])->name('employee-salary.make-salary');
    Route::get('employee-salary/edit-salary/{id?}', [EmployeeMonthlySalaryController::class, 'editSalary'])->name('employee-salary.edit-salary');
    Route::post('employee-salary/update-salary/{id?}', [EmployeeMonthlySalaryController::class, 'updateSalary'])->name('employee-salary.update-salary');
    Route::get('employee-salary/get-salary', [EmployeeMonthlySalaryController::class, 'getSalary'])->name('employee-salary.get-salary');
    Route::get('employee-salary/get-updated-salary', [EmployeeMonthlySalaryController::class, 'getUpdateSalary'])->name('employee-salary.get_update_salary');
    Route::get('employee-salary/increment/{id}', [EmployeeMonthlySalaryController::class, 'increment'])->name('employee-salary.increment');
    Route::post('employee-salary/increment-store/{id?}', [EmployeeMonthlySalaryController::class, 'incrementStore'])->name('employee-salary.increment-store');
    Route::get('employee-salary/increment-edit', [EmployeeMonthlySalaryController::class, 'incrementEdit'])->name('employee-salary.increment_edit');
    Route::post('employee-salary/increment-update', [EmployeeMonthlySalaryController::class, 'incrementUpdate'])->name('employee-salary.increment_update');
    Route::resource('employee-salary', EmployeeMonthlySalaryController::class);

    Route::get('payroll-reports/fetch-tds{id?}', [PayrollReportController::class, 'fetchTds'])->name('payroll-reports.fetch_tds');
    Route::resource('payroll-reports', PayrollReportController::class);
    Route::get('payroll-settings', [PayrollSettingController::class, 'index'])->name('payroll.payroll_settings');

    Route::group(
        ['prefix' => 'payroll-settings'],
        function () {
            Route::post('salary-groups/manage-employee', [SalaryGroupController::class, 'manageEmployee'])->name('salary_groups.manage_employee');
            Route::resource('salary-groups', SalaryGroupController::class);

            Route::get('salary-tds/get-status', [SalaryTdsController::class, 'getStatus'])->name('salary_tds.get_status');
            Route::post('salary-tds/status', [SalaryTdsController::class, 'status'])->name('salary_tds.status');
            Route::resource('salary-tds', SalaryTdsController::class);

            Route::resource('salary-components', SalaryComponentController::class);

            Route::resource('payment-methods', SalaryPaymentMethodController::class);
            Route::resource('salary-settings', SalarySettingController::class);
            Route::resource('payroll-currency-settings', PayrollCurrencyController::class);
        }
    );
});
