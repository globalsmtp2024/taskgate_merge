<?php

use App\Models\Company;

if (!function_exists('getCompanyBySubDomain')) {

    function getCompanyBySubDomain()
    {
        $company = Company::where('sub_domain', request()->getHost())->first();

        return $company;

    }
}
