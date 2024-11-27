<?php

namespace Modules\Recruit\Observers;

use Modules\Recruit\Entities\RecruitJobApplication;
use Modules\Recruit\Entities\RecruitJobHistory;
use Modules\Recruit\Events\NewJobApplicationEvent;
use Modules\Recruit\Events\UpdateJobApplicationEvent;

class JobApplicationsObserver
{
    public function saving(RecruitJobApplication $event)
    {
        if (! isRunningInConsoleOrSeeding() && user()) {
            $event->last_updated_by = user()->id;
        }
    }

    public function creating(RecruitJobApplication $event)
    {
        if (! isRunningInConsoleOrSeeding() && user()) {
            $event->added_by = user()->id;
        }

        if (company()) {
            $event->company_id = company()->id;
        }
    }

    public function created(RecruitJobApplication $event)
    {
        if (! isRunningInConsoleOrSeeding()) {
            if (\user()) {
                $this->logRecruitJobsActivity($event->recruit_job_id, user()->id, 'createJobapplication', $event->id, null);
            }

            event(new NewJobApplicationEvent($event));
        }
    }

    public function updating(RecruitJobApplication $event)
    {
        if (! isRunningInConsoleOrSeeding()) {
            if (\user()) {
                $this->logRecruitJobsActivity($event->recruit_job_id, user()->id, 'updateJobapplication', $event->id, null);
            }
        }
    }

    public function updated(RecruitJobApplication $event)
    {
        if (! isRunningInConsoleOrSeeding()) {
            event(new UpdateJobApplicationEvent($event));
        }
    }

    public function logRecruitJobsActivity($jobID, $userID, $text, $jobapplicationID, $interviewID)
    {
        $activity = new RecruitJobHistory;

        if (! is_null($jobID)) {
            $activity->recruit_job_id = $jobID;
        }

        if (! is_null($jobapplicationID)) {
            $activity->recruit_job_application_id = $jobapplicationID;
        }

        if (! is_null($interviewID)) {
            $activity->recruit_interview_schedule_id = $interviewID;
        }

        $activity->user_id = $userID;
        $activity->details = $text;
        $activity->save();
    }
}
