<?php

namespace Modules\Recruit\Listeners;

use App\Models\User;
use Modules\Recruit\Events\UpdateJobEvent;
use Modules\Recruit\Notifications\JobRecruiter;
use Modules\Recruit\Notifications\RemoveJobRecruiter;
use Modules\Recruit\Notifications\UpdateJob;
use Notification;

class UpdateJobListener
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
    public function handle(UpdateJobEvent $event)
    {
        $job = $event->job;

        if ($job->isDirty('recruiter_id')) {
            $oldRecruiterId = $job->getOriginal('recruiter_id');
            Notification::send(User::findOrFail($oldRecruiterId), new RemoveJobRecruiter($job));
            Notification::send($job->recruiter, new JobRecruiter($job));
        } else {
            Notification::send($job->recruiter, new UpdateJob($job));
        }
    }


}
