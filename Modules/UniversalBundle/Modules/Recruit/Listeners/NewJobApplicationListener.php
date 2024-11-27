<?php

namespace Modules\Recruit\Listeners;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Modules\Recruit\Events\NewJobApplicationEvent;
use Modules\Recruit\Notifications\NewJobApplication;
use Modules\Recruit\Notifications\AdminNewJobApplication;
use Modules\Recruit\Notifications\SendJobApplication;

class NewJobApplicationListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(NewJobApplicationEvent $event)
    {
        $jobApplication = $event->jobApplication;
        $companyId = $jobApplication->company->id;

        // Get company admins
        $companyAdmins = User::allAdmins($companyId);

        // Send admin notification
        $adminNotification = new AdminNewJobApplication($jobApplication);
        Notification::send($companyAdmins, $adminNotification);

        // Send notification to the recruiter
        $recruiter = $jobApplication->job->recruiter;

        if (isset($recruiter->email) && $recruiter->email) {
            Notification::send($recruiter, new NewJobApplication($jobApplication));
        }

        // Send notification to the candidate if email exists

            Notification::send($jobApplication, new SendJobApplication($jobApplication));
    }

}
