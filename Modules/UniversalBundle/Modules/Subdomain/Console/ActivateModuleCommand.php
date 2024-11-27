<?php

namespace Modules\Subdomain\Console;

use App\DataTables\SuperAdmin\SuperAdminDataTable;
use App\Models\Company;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Modules\Subdomain\Entities\SubdomainSetting;
use Modules\Subdomain\Notifications\SuperAdminLoginUrlEmail;

class ActivateModuleCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'subdomain:activate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add all the module settings of asset module';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $companies = Company::withoutGlobalScope(ActiveScope::class)
            ->select(['id', 'app_name'])
            ->whereNull('sub_domain')
            ->get();

        foreach ($companies as $company) {
            SubdomainSetting::addDefaultSubdomain($company);
        }

        $this->sendSuperAdminLoginUrl();

    }

    public function sendSuperAdminLoginUrl()
    {
        $users = User::where('is_superadmin', 1)
            ->whereNull('users.company_id')
            ->get();

        try {
            Notification::send($users, new SuperAdminLoginUrlEmail());
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
