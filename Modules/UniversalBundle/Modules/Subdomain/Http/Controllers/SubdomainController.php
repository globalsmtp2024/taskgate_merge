<?php

namespace Modules\Subdomain\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\SuperAdmin\FrontBaseController;
use App\Models\Company;
use App\Models\ThemeSetting;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Http\Request;
use Modules\Subdomain\Events\CompanyUrlEvent;
use Modules\Subdomain\Http\Requests\Auth\CheckSubdomainRequest;
use Modules\Subdomain\Http\Requests\Auth\ForgotCompanyRequest;
use Modules\Subdomain\Notifications\ForgotCompany;

class SubdomainController extends FrontBaseController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param null $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function workspace()
    {

        $this->pageTitle = __('subdomain::app.core.workspaceTitle');
        $this->themeSetting = ThemeSetting::withoutGlobalScopes([CompanyScope::class])->first();

        if ((isset(global_setting()->frontend_disable) && $this->global->frontend_disable) || $this->global->setup_homepage == "custom") {
            return view('subdomain::login-subdomain', $this->data);
        }

        $view = (global_setting()->front_design == 1) ? 'subdomain::saas.workspace' : 'subdomain::workspace';

        return view($view, $this->data);
    }

    public function forgotCompany()
    {
        $this->pageTitle = __('subdomain::app.core.forgotCompanyTitle');
        $this->themeSetting = ThemeSetting::withoutGlobalScopes([CompanyScope::class])->first();

        if ((isset($this->global->frontend_disable) && $this->global->frontend_disable) || $this->global->setup_homepage == "custom") {
            return view('subdomain::forgot-subdomain', $this->data);
        }

        $view = ($this->setting->front_design) == 1 ? 'subdomain::saas.forgot-company' : 'subdomain::forgot-company';

        return view($view, $this->data);
    }

    public function submitForgotCompany(ForgotCompanyRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return Reply::error(__('subdomain::app.messages.forgetMailFail'));
        }

        if (!$user->company) {
            return Reply::error(__('subdomain::app.messages.noCompanyLined'));
        }

        $user->notify(new ForgotCompany($user->company));

        return Reply::success(__('subdomain::app.messages.forgetMailSuccess'));

    }

    public function checkDomain(CheckSubdomainRequest $request)
    {
        return Reply::redirect(str_replace(request()->getHost(), $request->sub_domain . '.' . getDomain(), route('login')));
    }

    public function notifyDomain(Request $request)
    {
        $company = Company::findOrFail($request->company_id);
        event(new CompanyUrlEvent($company));

        return Reply::success('Successfully notified to all admins');
    }

}
