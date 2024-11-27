<?php

namespace Modules\Payroll\Providers;

use App\Events\NewCompanyCreatedEvent;
use App\Events\NewUserEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Payroll\Entities\EmployeePayrollCycle;
use Modules\Payroll\Entities\PayrollSetting;
use Modules\Payroll\Entities\SalaryComponent;
use Modules\Payroll\Entities\SalaryGroup;
use Modules\Payroll\Entities\SalaryGroupComponent;
use Modules\Payroll\Entities\SalaryPaymentMethod;
use Modules\Payroll\Entities\SalarySlip;
use Modules\Payroll\Entities\SalaryTds;
use Modules\Payroll\Listeners\CompanyCreatedListener;
use Modules\Payroll\Listeners\NewUserListener;
use Modules\Payroll\Observers\EmployeePayrollCycleObserver;
use Modules\Payroll\Observers\PayrollSettingObserver;
use Modules\Payroll\Observers\SalaryComponentObserver;
use Modules\Payroll\Observers\SalaryGroupComponentObserver;
use Modules\Payroll\Observers\SalaryGroupObserver;
use Modules\Payroll\Observers\SalaryPaymentMethodObserver;
use Modules\Payroll\Observers\SalarySlipObserver;
use Modules\Payroll\Observers\SalaryTdsObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NewCompanyCreatedEvent::class => [CompanyCreatedListener::class],
        NewUserEvent::class => [NewUserListener::class],
    ];

    protected $observers = [
        EmployeePayrollCycle::class => [EmployeePayrollCycleObserver::class],
        PayrollSetting::class => [PayrollSettingObserver::class],
        SalaryComponent::class => [SalaryComponentObserver::class],
        SalaryGroupComponent::class => [SalaryGroupComponentObserver::class],
        SalaryGroup::class => [SalaryGroupObserver::class],
        SalarySlip::class => [SalarySlipObserver::class],
        SalaryPaymentMethod::class => [SalaryPaymentMethodObserver::class],
        SalaryTds::class => [SalaryTdsObserver::class],
    ];
}
