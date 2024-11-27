<?php

namespace Modules\Subdomain\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Modules\Subdomain\Console\ActivateModuleCommand;
use Modules\Subdomain\Entities\SubdomainSetting;
use Modules\Subdomain\Events\CompanyUrlEvent;
use Modules\Subdomain\Http\Middleware\CompanyNotFound;
use Modules\Subdomain\Http\Middleware\SubdomainCheck;
use Modules\Subdomain\Listeners\ForgotCompanyListener;

class SubdomainServiceProvider extends ServiceProvider
{

    /**
     * Boot the application events.
     *
     * @param Router $router
     * @return void
     */
    public function boot(Router $router)
    {

        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path('Subdomain', 'Database/Migrations'));
        $this->registerCommands();

        // Middleware
        $router->aliasMiddleware('sub-domain-check', SubdomainCheck::class);
        $router->aliasMiddleware('company-not-found', CompanyNotFound::class);

        // Events
        Event::listen(CompanyUrlEvent::class, ForgotCompanyListener::class);

        // Banned_sub_domain
        Validator::extend('banned_sub_domain', function ($attribute, $value, $parameters, $validator) {

            $setting = SubdomainSetting::first();
            $value = explode('.' . getDomain(), $value)[0];

            if (is_null($setting->banned_subdomain)) {
                return true;
            }

            $value = strtolower($value);

            return $this->isSubdomainAllowed($value, $setting);


        }, __('subdomain::app.messages.notAllowedToUseThisSubdomain'));

    }

    public function isSubdomainAllowed($subdomain, $setting): bool
    {

        $bannedSubdomains = $setting->banned_subdomain;

        if (in_array($subdomain, $bannedSubdomains)) {
            return false;
        }

        // Check each pattern
        foreach ($bannedSubdomains as $pattern) {
            // Convert SQL wildcards to regex
            $regexPattern = '/^' . str_replace(['%', '_'], ['.*', '.'], preg_quote($pattern, '/')) . '$/';


            if (preg_match($regexPattern, $subdomain)) {
                return false; // Return false if any pattern matches
            }
        }

        return true; // Return true if no pattern matches
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            module_path('Subdomain', 'Config/config.php') => config_path('subdomain.php'),
        ], 'config');
        $this->mergeConfigFrom(
            module_path('Subdomain', 'Config/config.php'), 'subdomain'
        );


        $this->mergeConfigFrom(
            module_path('subdomain', 'Config/xss_ignore.php'),
            'subdomain::xss_ignore'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/subdomain');

        $sourcePath = module_path('Subdomain', 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], 'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/subdomain';
        }, \Config::get('view.paths')), [$sourcePath]), 'subdomain');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/subdomain');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'subdomain');
        }
        else {
            $this->loadTranslationsFrom(module_path('Subdomain', 'Resources/lang'), 'subdomain');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    private function registerCommands()
    {
        $this->commands(
            [
                ActivateModuleCommand::class,
            ]
        );
    }

}
