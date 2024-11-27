<?php

namespace Modules\Subdomain\Http\Controllers;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use Modules\Subdomain\Entities\SubdomainSetting;
use Modules\Subdomain\Http\Requests\Auth\BannedSubdomainRequest;

class BannedSubdomainController extends AccountBaseController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Subdomain Settings';
        $this->pageIcon = 'icon-settings';
        $this->activeSettingMenu = 'subdomain_setting';
        $this->middleware(function ($request, $next) {
            abort_403(!user()->is_superadmin);

            return $next($request);
        });

    }

    public function bannedDomain()
    {
        $this->bannedSubDomains = SubdomainSetting::first()->banned_subdomain;

        return view('subdomain::super-admin.setting.edit', $this->data);
    }

    public function bannedDomainSubmit(BannedSubdomainRequest $request)
    {
        $settings = SubdomainSetting::first();
        $bannedList = $settings->banned_subdomain ?? [];
        $settings->banned_subdomain = array_unique(array_merge([$request->banned_subdomain], $bannedList));
        $settings->save();

        return Reply::redirect(route('super-admin.get.banned-subdomains'), 'Updated successfully');
    }

    public function deleteBannedDomain()
    {
        $settings = SubdomainSetting::first();
        $array = $settings->banned_subdomain;

        // Remove from array
        unset($array[request()->keyIndex]);
        $settings->banned_subdomain = $array;
        $settings->save();

        return Reply::redirect(route('super-admin.get.banned-subdomains'));
    }

}
