<?php

namespace Modules\Subdomain\Http\Middleware;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Closure;

class SubdomainCheck
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $host = str_replace('www.', '', request()->getHost());
        $subdomain = config('app.main_application_subdomain');

        $rootCrmSubDomain = preg_replace('#^https?://#', '', $subdomain); // Remove 'http://' or 'https://'

        // worksuite-saas.test
        $root = getDomain();

        $routeName = request()->route()->getName();

        // If the main application is installed on sub_domain
        // Example main application is installed on froiden.worksuite-saas.test
        if ($rootCrmSubDomain !== null && $rootCrmSubDomain == $host) {
            // Check login page
            if ($routeName === 'login') {
                return redirect('//' . $host . '/signin');
            }

            return $next($request);
        }

        $company = Company::where('sub_domain', $host)->first();

        $subdomain = array_first(explode('.', $host));
        // If subdomain exist is database and root is not to host
        if ($company) {
            // Check if the url is login then do not redirect
            // https://abc.worksuite-saas.test/login

            $ignore = ['login', 'password.request', 'password.reset', 'logout'];

            if (in_array($routeName, $ignore)) {
                return $next($request);
            }

            return redirect(route('login'));
        }

        // If Home is opened in root then continue else show not found
        if ($routeName === 'front.home') {

            if ($root == $host) {
                return $next($request);
            }

            // Show Company Not found Error Page
            $this->companyNotFound();
        }

        // Check login page
        if ($routeName === 'login') {

            // If opened login in main domain then redirect to workspace login page
            // https://worksuite-saas.test/login
            if ($root == $host) {
                return redirect('//' . $root . '/signin');
            }

            // Show Company Not found Error Page
            $this->companyNotFound();
        }


        if ($subdomain == array_first(explode('.', $root))) {
            return $next($request);
        }

        // Redirect to forgot-password when from 325 page
        if ($routeName == 'front.forgot-company') {
            return redirect('//' . $root . '/forgot-company');
        }

        // Redirect to signup when from 325 page
        if ($routeName == 'front.signup.index') {
            return redirect('//' . $root . '/signup');
        }

        // If sub-domain do not exist in database then redirect to works
        return redirect('//' . $root . '/signin');
    }

    public function companyNotFound(): void
    {
        // Get the main application subdomain
        $subdomain = config('app.main_application_subdomain');

        // Extract root CRM subdomain
        $rootCrmSubDomain = preg_replace('#^https?://#', '', $subdomain);

        // Get the current domain
        $domain = getDomain();

        // When root domain is a subdomain
        if ($rootCrmSubDomain !== null && $rootCrmSubDomain !== $domain) {
            $domain = $rootCrmSubDomain;
        }

        // Build signup and forgot-company links
        $signupLink = '//' . $domain . '/signup';
        $forgetLink = '//' . $domain . '/forgot-company';

        // Abort with custom HTTP status code and message
        abort(325, __('subdomain::app.companyNotFound'), ['signup' => $signupLink, 'forgot-company' => $forgetLink]);
    }

}


