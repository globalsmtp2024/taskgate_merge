<?php

namespace Modules\Subdomain\Http\Middleware;

use App\Models\Company;
use Closure;

class CompanyNotFound
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $company = Company::where('sub_domain', request()->getHost())->first();

        if ($company) {
            return $next($request);
        }

        abort(325);
    }

}
