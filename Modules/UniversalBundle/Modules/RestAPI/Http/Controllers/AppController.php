<?php

namespace Modules\RestAPI\Http\Controllers;

use App\Models\Company;
use App\Models\GlobalSetting;
use Froiden\RestAPI\ApiResponse;
use Froiden\RestAPI\Exceptions\ApiException;
use Illuminate\Routing\Controller;

class AppController extends Controller
{

    /**
     * @throws ApiException
     */
    public function app()
    {
        $setting = GlobalSetting::select(['global_app_name', 'logo'])->first();

        if (!module_enabled('Subdomain')) {
            $setting->company_name = $setting->global_app_name;

            return ApiResponse::make('Application data fetched successfully', $setting->toArray());
        }

        $company = Company::where('sub_domain', request()->getHost())
            ->select([
                'id',
                'sub_domain',
                'company_name',
                'app_name',
                'logo',
                'light_logo',
                'currency_id',
                'timezone',
                'date_format',
                'date_picker_format',
                'moment_format',
                'time_format',
                'locale',
                'status',
                'hash'
            ])->first();

        if (!$company) {
            $exception = new ApiException('Please enter correct subdomain url your company', null, 403, 403, 2026);

            return ApiResponse::exception($exception);
        }

        if ($company->status == 'inactive') {
            return ApiResponse::exception(new ApiException('The company is currently inactive.', null, 403, 403, 2015));
        }

        if ($company->status == 'license_expired') {
            return ApiResponse::exception(new ApiException('The Company license is expired', null, 403, 403, 2015));
        }

        return ApiResponse::make('Company data fetched successfully', $company->toArray());

    }

}
