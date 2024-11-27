<?php

namespace Modules\Recruit\Http\Controllers;

use App\Models\Team;
use App\Helper\Files;
use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\CompanyAddress;
use Modules\Recruit\Entities\RecruitJob;
use Modules\Recruit\Entities\RecruitSkill;
use Modules\Recruit\Entities\RecruitSetting;
use App\Http\Controllers\AccountBaseController;
use Modules\Recruit\Imports\JobApplicationImport;
use App\Models\Currency;
use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Gmail_MessagePart;
use Google_Service_Gmail_MessagePartHeader;
use Symfony\Component\Mime\Email;
use App\Traits\ImportExcel;
use Carbon\Carbon;
use Modules\Recruit\Entities\ApplicationSource;
use Modules\Recruit\Entities\RecruitJobApplication;
use Modules\Recruit\Entities\RecruitApplicationFile;
use Modules\Recruit\Entities\RecruitApplicationSkill;
use Modules\Recruit\Entities\RecruitApplicationStatus;
use Modules\Recruit\DataTables\JobApplicationsDataTable;
use Modules\Recruit\Entities\RecruitCandidateFollowUp;
use Modules\Recruit\Entities\RecruitInterviewSchedule;
use Modules\Recruit\Entities\RecruitJobAddress;
use Modules\Recruit\Entities\RecruitJobHistory;
use Modules\Recruit\Entities\RecruitJobCustomAnswer;
use Modules\Recruit\Events\JobApplicationStatusChangeEvent;
use Modules\Recruit\Http\Requests\JobApplication\ImportProcessRequest;
use Modules\Recruit\Http\Requests\JobApplication\ImportRequest;
use Modules\Recruit\Http\Requests\JobApplication\StoreJobApplication;
use Modules\Recruit\Http\Requests\JobApplication\StoreQuickApplication;
use Modules\Recruit\Http\Requests\JobApplication\UpdateJobApplication;
use Modules\Recruit\Jobs\ImportJobApplicationJob;
use PhpParser\Node\Expr\Empty_;
use Modules\Recruit\Entities\RecruitApplicationStatusCategory;
use App\Models\CompanyEmailTemplateSetting;
use App\Models\CompanySmtpSetting;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Module;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobApplicationController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('recruit::app.menu.jobApplication');
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(RecruitSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });
    }

    public function index(JobApplicationsDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_job_application');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->applicationStatus = RecruitApplicationStatus::select('id', 'status', 'position', 'color')->orderBy('position')->get();
        $this->applicationSources = ApplicationSource::all();
        $this->jobs = RecruitJob::where('status', 'open')->get();
        $this->locations = CompanyAddress::all();
        $this->jobLocations = RecruitJobAddress::with('location')->where('recruit_job_id', request()->id)->get();
        $this->jobApp = RecruitJob::where('id', request()->id)->first();

        $this->locations = CompanyAddress::all();
        $this->currentLocations = RecruitJobApplication::select('current_location')->where('current_location', '!=', null)->distinct()->get();

        $settings = RecruitSetting::select('form_settings')->first();
        $this->formSettings = collect([]);

        if ($settings) {
            $formSettings = $settings->form_settings;

            foreach ($formSettings as $form) {
                if ($form['status'] == true) {
                    $this->formSettings->push($form);
                }
            }
        }

        $this->formFields = $this->formSettings->pluck('name')->toArray();

        return $dataTable->render('recruit::job-applications.table', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $addPermission = user()->permission('add_job_application');
        abort_403(!in_array($addPermission, ['all', 'added']));
        $this->jobId = request()->id ?? null;

        $this->pageTitle = __('recruit::modules.jobApplication.addJobApplications');

        $this->applicationStatus = RecruitApplicationStatus::select('id', 'status', 'position', 'color')->orderBy('position')->get();
        $this->applicationSources = ApplicationSource::where('company_id', company()->id)->get();
        $this->jobs = RecruitJob::where('status', 'open')->get();
        $this->locations = CompanyAddress::all();
        $this->jobLocations = RecruitJobAddress::with('location')->where('recruit_job_id', request()->id)->get();
        $this->jobApp = RecruitJob::where('id', request()->id)->first();
        $this->statusId = request()->column_id;

        if (request()->ajax()) {
            $html = view('recruit::job-applications.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recruit::job-applications.ajax.create';

        return view('recruit::job-applications.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreJobApplication $request)
    {
        $addPermission = user()->permission('add_job_application');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $jobApp = new RecruitJobApplication();
        $jobApp->recruit_job_id = $request->job_id;
        $jobApp->full_name = $request->full_name;
        $jobApp->email = $this->emailValidation($request);
        $jobApp->phone = $request->phone;

        if ($request->has('gender')) {
            $jobApp->gender = $request->gender;
        }

        if ($request->has('current_ctc')) {
            $jobApp->current_ctc = $request->current_ctc;
            $jobApp->currenct_ctc_rate = $request->currenct_ctc_rate;
        }

        if ($request->has('expected_ctc')) {
            $jobApp->expected_ctc = $request->expected_ctc;
            $jobApp->expected_ctc_rate = $request->expected_ctc_rate;
        }

        if ($request->date_of_birth != null && $request->has('date_of_birth')) {
            $date_of_birth = Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d');
            $jobApp->date_of_birth = $date_of_birth;
        }

        $jobApp->application_source_id = $request->source;
        $jobApp->cover_letter = $request->cover_letter;
        $jobApp->location_id = $request->location_id;
        $jobApp->total_experience = $request->total_experience;
        $jobApp->current_location = $request->current_location;
        $jobApp->notice_period = $request->notice_period;
        $jobApp->recruit_application_status_id = $request->status_id;
        $jobApp->application_sources = 'addedByUser';
        $jobApp->column_priority = 0;

        if ($request->hasFile('photo')) {
            Files::deleteFile($jobApp->photo, 'avatar');
            $jobApp->photo = Files::uploadLocalOrS3($request->photo, 'avatar', 300);
        }

        $jobApp->save();

        if (request()->hasFile('resume')) {
            $file = new RecruitApplicationFile();
            $file->recruit_job_application_id = $jobApp->id;
            Files::deleteFile($jobApp->resume, 'application-files/');
            $filename = Files::uploadLocalOrS3(request()->resume, 'application-files/' . $jobApp->id);
            $file->filename = request()->resume->getClientOriginalName();
            $file->hashname = $filename;
            $file->size = request()->resume->getSize();
            $file->save();
        }

        if (request()->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('recruit::messages.applicationAdded'), ['html' => $html, 'add_more' => true]);
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('job-applications.index');
        }

        return Reply::dataOnly(['redirectUrl' => $redirectUrl, 'application_id' => $jobApp->id]);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $interviewer = [];
        $this->application = RecruitJobApplication::with('job', 'applicationStatus', 'location', 'source', 'comments', 'comments.user', 'files')->find($id);
        $scheduleData = RecruitInterviewSchedule::with('employees')->where('recruit_job_application_id', $id)->first();

        $this->updateEmailStatus($this->application);
        $this->currencySymbol = Currency::where('id', '=', $this->application->job->currency_id)->first();

        if ($scheduleData) {
            $interviewer = $scheduleData->employees->pluck('id')->toArray();
        }

        $this->viewPermission = user()->permission('view_job_application');
        $this->interviewViewPermission = user()->permission('view_interview_schedule');
        abort_403(!($this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->application->added_by == user()->id)
            || ($this->viewPermission == 'owned' && user()->id == $this->application->job->recruiter_id)
            || ($this->viewPermission == 'owned' && in_array(user()->id, $interviewer))
            || ($this->viewPermission == 'both' && user()->id == $this->application->job->recruiter_id
                || $this->application->added_by == user()->id) || (in_array(user()->id, $interviewer))
            || ($this->interviewViewPermission == 'owned')));

        $this->departments = Team::all();
        $this->recruit_skills = RecruitApplicationSkill::where('recruit_job_application_id', $id)->get();
        $this->selected_skills = $this->recruit_skills->pluck('recruit_skill_id')->toArray();
        $this->skills = RecruitSkill::select('id', 'name')->get();
        $this->allAnswers = RecruitJobCustomAnswer::where('recruit_job_application_id', $this->application->id)->get();
        $this->followUps = RecruitCandidateFollowUp::where('recruit_job_application_id', $id)->get();
        $this->applicationStatus = RecruitApplicationStatus::select('id', 'status', 'position', 'color', 'slug')->orderBy('position')->get();
        $this->applicationStatusHistory = RecruitJobHistory::where('details', 'updateJobapplicationStatus')
            ->where('recruit_job_application_id', $id)
            ->get()->pluck('recruit_job_application_status_id')->toArray();

        $tab = request('view');

        switch ($tab) {
            case 'applicant_notes':
                $this->tab = 'recruit::job-applications.notes.notes';
                break;
            case 'skill':
                $this->tab = 'recruit::job-applications.ajax.skill';
                break;
            case 'custom':
                $this->tab = 'recruit::job-applications.ajax.custom-question';
                break;
            case 'follow-up':
                $this->tab = 'recruit::job-applications.ajax.follow-up';
                break;
            case 'resume':
                $this->tab = 'recruit::job-applications.ajax.resume';
                break;
            case 'emails-thread':
                $module = Module::where('module_name', 'recruit')->first();

                $companySmtpSetting = CompanySmtpSetting::where('company_id', user()->id)
                    ->where('module_id', $module->id)
                    ->first();
                $this->application->emailTemplates = CompanyEmailTemplateSetting::where('company_id', company()->id)
                    ->where('module_id', $module->id)
                    ->get();

                $filterEmail = $this->application->email ?? null;

                if ($companySmtpSetting) {
                    $this->application->smtpExist = true;

                    if (request('sent') == true) {
                        $sentEmailsQuery = DB::table('retrieving_emails')
                            ->where('user_id', user()->id)
                            ->where('is_send', 1)
                            ->orderBy('id', 'DESC');

                        if ($filterEmail) {
                            $sentEmailsQuery->where(function ($query) use ($filterEmail) {
                                $query->where('from_email', 'like', '%' . $filterEmail . '%')
                                    ->orWhere('to_email', 'like', '%' . $filterEmail . '%');
                            });
                        }

                        $sentEmails = $sentEmailsQuery->get();
                        $getApplicantsSentEmails = $this->processEmails($sentEmails);

                        $this->application->sentEmails = ['emails' => $getApplicantsSentEmails];
                        $this->application->receivedEmails = ["emails" => []];
                    } else {
                        $receivedEmailsQuery = DB::table('retrieving_emails')
                            ->where('user_id', user()->id)
                            ->where('is_send', 0)
                            ->orderBy('id', 'DESC');

                        if ($filterEmail) {
                            $receivedEmailsQuery->where(function ($query) use ($filterEmail) {
                                $query->where('from_email', 'like', '%' . $filterEmail . '%')
                                    ->orWhere('to_email', 'like', '%' . $filterEmail . '%');
                            });
                        }

                        $receivedEmails = $receivedEmailsQuery->get();
                        $getApplicantsReceivedEmails = $this->processEmails($receivedEmails);

                        $this->application->receivedEmails = ['emails' => $getApplicantsReceivedEmails];
                        $this->application->sentEmails = ["emails" => []];
                    }
                } else {
                    $this->application->smtpExist = false;
                    if (request('sent') == true) {
                        $sentEmailsQuery = DB::table('retrieving_emails')
                            ->where('user_id', user()->id)
                            ->where('is_send', 1)
                            ->orderBy('id', 'DESC');

                        if ($filterEmail) {
                            $sentEmailsQuery->where(function ($query) use ($filterEmail) {
                                $query->where('from_email', 'like', '%' . $filterEmail . '%')
                                    ->orWhere('to_email', 'like', '%' . $filterEmail . '%');
                            });
                        }

                        $sentEmails = $sentEmailsQuery->get();
                        $getApplicantsSentEmails = $this->processEmails($sentEmails);

                        $this->application->sentEmails = ['emails' => $getApplicantsSentEmails];
                        $this->application->receivedEmails = ["emails" => []];
                    } else {
                        $receivedEmailsQuery = DB::table('retrieving_emails')
                            ->where('user_id', user()->id)
                            ->where('is_send', 0)
                            ->orderBy('id', 'DESC');

                        if ($filterEmail) {
                            $receivedEmailsQuery->where(function ($query) use ($filterEmail) {
                                $query->where('from_email', 'like', '%' . $filterEmail . '%')
                                    ->orWhere('to_email', 'like', '%' . $filterEmail . '%');
                            });
                        }

                        $receivedEmails = $receivedEmailsQuery->get();
                        $getApplicantsReceivedEmails = $this->processEmails($receivedEmails);

                        $this->application->receivedEmails = ['emails' => $getApplicantsReceivedEmails];
                        $this->application->sentEmails = ["emails" => []];
                    }
                }

                $this->tab = 'recruit::job-applications.ajax.emails-thread';
                break;

            default:
                $this->editInterviewSchedulePermission = user()->permission('edit_interview_schedule');
                $this->deleteInterviewSchedulePermission = user()->permission('delete_interview_schedule');
                $this->viewInterviewSchedulePermission = user()->permission('view_interview_schedule');
                $this->reschedulePermission = user()->permission('reschedule_interview');
                $this->applicationStatuses = ['pending', 'hired', 'canceled', 'completed', 'rejected'];

                $this->interviewSchedule = RecruitInterviewSchedule::with(['employeesData', 'employeesData.user'])
                    ->select('recruit_interview_schedules.id', 'recruit_interview_schedules.recruit_job_application_id', 'recruit_interview_schedules.interview_type', 'recruit_interview_schedules.recruit_interview_stage_id', 'recruit_interview_employees.user_id as employee_id', 'recruit_interview_employees.user_accept_status', 'recruit_interview_employees.id as emp_id', 'recruit_job_applications.full_name', 'recruit_interview_schedules.status', 'recruit_interview_schedules.schedule_date', 'recruit_interview_stages.name')
                    ->where('recruit_job_application_id', $id)
                    ->leftjoin('recruit_job_applications', 'recruit_job_applications.id', 'recruit_interview_schedules.recruit_job_application_id')
                    ->leftjoin('recruit_interview_stages', 'recruit_interview_stages.id', 'recruit_interview_schedules.recruit_interview_stage_id')
                    ->leftjoin('recruit_interview_employees', 'recruit_interview_employees.recruit_interview_schedule_id', 'recruit_interview_schedules.id')
                    ->groupBy('recruit_interview_schedules.id')->get();
                $this->tab = 'recruit::job-applications.ajax.interview-schedule';
                break;
        }

        if (request()->ajax()) {
            if (request('json') == true) {
                $html = view($this->tab, $this->data)->render();
                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }

            $html = view('recruit::job-applications.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recruit::job-applications.ajax.show';
        return view('recruit::job-applications.show', $this->data);
    }
    protected function processEmails($emails)
    {
        $processedEmails = [];
        foreach ($emails as $email) {
            $attachments = [];
            if ($email->is_attachment == "True") {
                $unserializeNames = unserialize($email->attachment_name);
                $unserializeFiles = unserialize($email->attachment_file);
                foreach ($unserializeNames as $key => $fileName) {
                    $attachments[] = [
                        'is_attachment' => true,
                        'filename' => $fileName,
                        'name' => $fileName,
                        'attachment' => $unserializeFiles[$key]
                    ];
                }
            }
            $processedEmails[] = [
                'subject' => $email->subject,
                'from' => $email->from_email,
                'to' => $email->to_email,
                'date' => $email->date,
                'body' => $email->body,
                'attachments' => $attachments,
                'thread_id' => $email->thread_id,
                'ref_id' => $email->ref_id,
            ];
        }
        return $processedEmails;
    }

    public function updateEmailStatus($user)
    {
        $user->update(['email_notify' => 0]);
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $this->jobApplication = RecruitJobApplication::findOrFail($id);
        $this->jobId = null;
        $this->job = RecruitJob::where('id', $this->jobApplication->recruit_job_id)->get();
        $this->currency = $this->job ? Currency::where('id', '=', $this->jobApplication->job->currency_id)->first() : null;
        $this->editPermission = user()->permission('edit_job_application');
        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->jobApplication->added_by == user()->id)
            || ($this->editPermission == 'owned' && user()->id == $this->job->recruiter_id)
            || ($this->editPermission == 'both' && user()->id == $this->job->recruiter_id)
            || $this->jobApplication->added_by == user()->id));

        $this->jobApplictionFile = RecruitApplicationFile::where('recruit_job_application_id', $id)->first();
        $this->jobs = RecruitJob::all();
        $this->applicationSources = ApplicationSource::where('company_id', company()->id)->get();
        $this->locations = CompanyAddress::all();
        $this->applicationStatus = RecruitApplicationStatus::select('id', 'status', 'position', 'color')->orderBy('position')->get();

        if (request()->ajax()) {
            $html = view('recruit::job-applications.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'recruit::job-applications.ajax.edit';

        return view('recruit::job-applications.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(UpdateJobApplication $request, $id)
    {
        $this->editPermission = user()->permission('edit_job_application');
        $jobApp = RecruitJobApplication::with('job')->findOrFail($id);

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $jobApp->added_by == user()->id)
            || ($this->editPermission == 'owned' && user()->id == $jobApp->job->recruiter_id)
            || ($this->editPermission == 'both' && user()->id == $jobApp->job->recruiter_id)
            || $jobApp->added_by == user()->id));
        $status = $jobApp->status_id;
        $statusId = $request->status_id;
        $jobApp->recruit_job_id = $request->job_id;
        $jobApp->full_name = $request->full_name;
        $jobApp->email = $request->email;
        $jobApp->phone = $request->phone;
        $jobApp->location_id = $request->location_id;
        $jobApp->total_experience = $request->total_experience;
        $jobApp->current_location = $request->current_location;
        $jobApp->notice_period = $request->notice_period;

        if ($request->has('gender')) {
            $jobApp->gender = $request->gender;
        }

        if ($request->has('current_ctc')) {
            $jobApp->current_ctc = $request->current_ctc;
            $jobApp->currenct_ctc_rate = $request->currenct_ctc_rate;
        }

        if ($request->has('expected_ctc')) {
            $jobApp->expected_ctc = $request->expected_ctc;
            $jobApp->expected_ctc_rate = $request->expected_ctc_rate;
        }

        if ($request->date_of_birth != null) {
            if ($request->has('date_of_birth')) {
                $date_of_birth = Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d');
                $jobApp->date_of_birth = $date_of_birth;
            }
        }

        $jobApp->recruit_application_status_id = $request->status_id;
        $jobApp->application_source_id = $request->source;
        $jobApp->cover_letter = $request->cover_letter;

        if ($request->photo_delete == 'yes') {
            Files::deleteFile($jobApp->photo, 'avatar');
            $jobApp->photo = null;
        }

        if ($request->hasFile('photo')) {
            Files::deleteFile($jobApp->photo, 'avatar');
            $jobApp->photo = Files::uploadLocalOrS3($request->photo, 'avatar', 300);
        }

        $jobApp->save();

        if (request()->hasFile('resume')) {
            $file = RecruitApplicationFile::where('recruit_job_application_id', $jobApp->id)->first() ?? new RecruitApplicationFile;
            $file->recruit_job_application_id ? Files::deleteFile($file->recruit_job_application_id, 'application-files/' . $jobApp->id) : '';
            $file->recruit_job_application_id = $jobApp->id;
            $filename = Files::uploadLocalOrS3(request()->resume, 'application-files/' . $jobApp->id);
            $file->filename = request()->resume->getClientOriginalName();
            $file->hashname = $filename;
            $file->size = request()->resume->getSize();
            $file->save();
        }

        if ($status != $statusId) {
            $send = $this->statusForMailSend($statusId);

            if ($send == true) {
                event(new JobApplicationStatusChangeEvent($jobApp));
            }
        }

        return Reply::successWithData(__('recruit::modules.message.updateSuccess'), ['redirectUrl' => route('job-applications.index'), 'application_id' => $jobApp->id]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $jobApp = RecruitJobApplication::with('job')->findOrFail($id);

        $this->deletePermission = user()->permission('delete_job_application');
        abort_403(!($this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $jobApp->added_by == user()->id)
            || ($this->deletePermission == 'owned' && user()->id == $jobApp->job->recruiter_id)
            || ($this->deletePermission == 'both' && user()->id == $jobApp->job->recruiter_id)
            || $jobApp->added_by == user()->id));

        RecruitJobApplication::withTrashed()->find($id)->forceDelete();

        return Reply::successWithData(__('recruit::modules.message.deleteSuccess'), ['redirectUrl' => route('job-applications.index')]);
    }

    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);

                return Reply::success(__('messages.deleteSuccess'));
            case 'change-status':
                $this->changeStatus($request);

                return Reply::success(__('messages.updateSuccess'));
            default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    protected function deleteRecords($request)
    {
        abort_403(user()->permission('delete_job_application') != 'all');

        RecruitJobApplication::withTrashed()->whereIn('id', explode(',', $request->row_ids))->forceDelete();

        return true;
    }

    public function changeStatus(Request $request)
    {
        abort_403(user()->permission('edit_job_application') != 'all');
        $interviewPermission = user()->permission('add_interview_schedule');
        $offerLetterPermission = user()->permission('add_offer_letter');

        $item = explode(',', $request->row_ids);

        if (($key = array_search('on', $item)) !== false) {
            unset($item[$key]);
        }

        $statusId = $request->status;
        $status = RecruitApplicationStatus::with('category')->where('id', $statusId)->first();
        $send = $this->statusForMailSend($statusId);

        foreach ($item as $id) {
            $mail = RecruitJobApplication::findOrFail($id);

            if ($send == true) {
                event(new JobApplicationStatusChangeEvent($mail));
            }

            $mail->recruit_application_status_id = $request->status;
            $mail->save();

            if ($send == true) {
                event(new JobApplicationStatusChangeEvent($mail, $status));
            }
        }


        return Reply::dataOnly(['status' => 'success', 'status' => $status, 'interviewPermission' => $interviewPermission, 'offerLetterPermission' => $offerLetterPermission]);
    }

    public function statusForMailSend($id)
    {
        $settings = RecruitSetting::first();
        $mail = $settings->mail_setting;

        foreach ($mail as $mailDetails) {
            if ($mailDetails['id'] == $id && $mailDetails['status'] == true) {
                return true;
            }
        }
    }

    public function getApplicantEmails($companySmtp, $id)
    {
        $imap = (object)[];
        $imap->imap_host = $companySmtp->imap_host . $this->mailBox;
        $imap->imap_username = $companySmtp->smtp_username;
        $imap->imap_password = $companySmtp->smtp_password;

        $this->authenticateImap($imap);

        $emails = $this->getMailBoxEmails($id);

        return $emails;
    }


    public function sendComposeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required',
            'content' => 'required',
            'attachment' => 'sometimes|file|max:5120',
        ]);

        if ($validator->fails()) {
            return Reply::error($validator->messages()->first());
        }

        $requestData = $request->all();
        $subject = $requestData['subject'] ?? '';
        $content = urldecode($requestData['content'] ?? '');
        $toEmail = $requestData['to-email'] ?? $requestData['to_email'] ?? '';
        $attachment = null;

        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment');
        }

        $messageId = !empty($requestData['ref_id']) ? $requestData['ref_id'] : (Str::uuid()->toString() . '@taskgate.io');
        $inReplyTo = $requestData['in_reply_to'] ?? null;
        $references = $requestData['references'] ?? null;

        try {
            $module = Module::where('module_name', 'leads')->first();
            $companySmtpSetting = CompanySmtpSetting::where('company_id', '=', user()->id)
                ->where('module_id', $module->id)
                ->first();

            if (!$companySmtpSetting) {
                return Reply::error('SMTP settings are not configured for your company.');
            }

            if ($companySmtpSetting && $companySmtpSetting->added_type == "user") {
                $this->configureUserMailer($companySmtpSetting);

                $requestData = $request->all();
                $subject = $requestData['subject'] ?? '';
                $content = $requestData['content'] ? urldecode($requestData['content']) : '';
                $toEmail = $requestData['to-email'] ?? $requestData['to_email'] ?? '';
                $attachment = null;
                $messageId = '';

                if ($request->hasFile('attachment')) {
                    $attachment = $request->file('attachment');
                }

                if (!empty($requestData['ref_id'])) {
                    $messageId = $requestData['ref_id'];
                } else {
                    $uuid = Str::uuid()->toString();
                    $messageId = $uuid . '@taskgate.io';
                }

                $data = [
                    'subject' => $subject,
                    'content' => $content,
                ];

                $inReplyTo = null;
                $references = null;

                \Mail::send('emails.job-applicants.compose', [
                    'subject' => $subject,
                    'content' => $content,
                ], function ($message) use ($toEmail, $subject, $attachment, $messageId, $inReplyTo, $references) {
                    $message->to($toEmail);
                    $message->subject($subject);
                    $symfonyMessage = $message->getSymfonyMessage();
                    $symfonyMessage->getHeaders()->addIdHeader('Message-ID', $messageId);

                    if ($inReplyTo) {
                        $symfonyMessage->getHeaders()->addIdHeader('In-Reply-To', $inReplyTo);
                    }

                    if ($references) {
                        $symfonyMessage->getHeaders()->addIdHeader('References', $references);
                    }

                    if ($attachment !== null) {
                        $message->attach($attachment->getRealPath(), [
                            'as' => $attachment->getClientOriginalName(),
                            'mime' => $attachment->getMimeType(),
                        ]);
                    }
                });
                $latestEmailFetch = $this->getApplicantEmailsImap($companySmtpSetting, "[Gmail]/Sent Mail");
                $isAttachment = 'False';
                $receivedAttachmentsNames = [];
                $receivedAttachmentsFiles = [];
                if (!empty($latestEmailFetch['attachments'])) {
                    foreach ($latestEmailFetch['attachments'] as $receivedAttachment) {
                        $isAttachment = 'True';
                        $receivedAttachmentsNames[] = $receivedAttachment['name'];
                        $receivedAttachmentsFiles[] = $receivedAttachment['data'];
                    }
                }
                $emailDataToInsert = [
                    "user_id" => user()->id,
                    "subject" => $latestEmailFetch['subject'],
                    "from_email" => $latestEmailFetch['from'],
                    "to_email" => $latestEmailFetch['to'],
                    "date" => $latestEmailFetch['date'],
                    "body" => $latestEmailFetch['body'],
                    "is_attachment" => $isAttachment,
                    "attachment_name" => !empty($receivedAttachmentsNames) ? serialize($receivedAttachmentsNames) : null,
                    "attachment_file" => !empty($receivedAttachmentsFiles) ? serialize($receivedAttachmentsFiles) : null,
                    "thread_id" => $latestEmailFetch['thread_id'],
                    "ref_id" => $latestEmailFetch['ref_id'],
                    "is_send" => 1,
                ];
                DB::table('retrieving_emails')->updateOrInsert(
                    [
                        'subject' => $emailDataToInsert['subject'],
                        'user_id' => $emailDataToInsert['user_id'],
                        'from_email' => $emailDataToInsert['from_email'],
                        'to_email' => $emailDataToInsert['to_email'],
                        'body' => $emailDataToInsert['body'],
                        'is_send' => $emailDataToInsert['is_send'],
                    ],
                    $emailDataToInsert
                );
                return Reply::success('Email sent successfully');
            } else {
                $client = new Google_Client();
                $client->setClientId(env('GOOGLE_CLIENT_ID'));
                $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
                $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
                $client->setAccessToken($companySmtpSetting->smtp_password);

                if ($client->isAccessTokenExpired()) {
                    $refreshToken = $companySmtpSetting->refresh_token;
                    if (empty($refreshToken)) {
                        return Reply::error("Refresh token is missing or invalid.");
                    }

                    try {
                        $client->fetchAccessTokenWithRefreshToken($refreshToken);
                        if ($client->getAccessToken()) {
                            $newAccessToken = $client->getAccessToken()['access_token'];
                            $companySmtpSetting->smtp_password = $newAccessToken;
                            $companySmtpSetting->save();
                        } else {
                            return Reply::error("Failed to refresh the access token.");
                        }
                    } catch (\Exception $e) {
                        return Reply::error("Error refreshing token: " . $e->getMessage());
                    }
                }

                $gmailService = new Google_Service_Gmail($client);
                $email = new Email();
                $email->from($companySmtpSetting->smtp_username)
                    ->to($toEmail)
                    ->subject($subject)
                    ->html($content)
                    ->getHeaders()
                    ->addIdHeader('Message-ID', $messageId);

                if ($inReplyTo) {
                    $email->getHeaders()->addIdHeader('In-Reply-To', $inReplyTo);
                }
                if ($references) {
                    $email->getHeaders()->addIdHeader('References', $references);
                }
                if ($attachment !== null) {
                    $email->attachFromPath($attachment->getRealPath(), $attachment->getClientOriginalName(), $attachment->getMimeType());
                }

                $mimeMessage = $this->getMimeMessage($email);
                $gmailMessage = new Google_Service_Gmail_Message();
                $gmailMessage->setRaw($mimeMessage);
                $sentMessage = $gmailService->users_messages->send('me', $gmailMessage);

                $messageDetails = $gmailService->users_messages->get('me', $sentMessage->getId());
                $headers = $messageDetails->getPayload()->getHeaders();
                $subject = $this->getHeader($headers, 'Subject');
                $from = $this->getHeader($headers, 'From');
                $to = $this->getHeader($headers, 'To');
                $date = $this->getHeader($headers, 'Date');
                $body = $this->getEmailBody($messageDetails);
                $attachments = $this->getAttachments($messageDetails, $gmailService, $sentMessage->getId());
                $references = $this->getHeader($headers, 'References');
                $refId = null;
                if ($references) {
                    $referencesArray = explode(' ', $references);
                    $refId = $referencesArray[0];
                }
                $isAttachment = 'False';
                $receivedAttachmentsNames = [];
                $receivedAttachmentsFiles = [];
                if (!empty($attachments)) {
                    foreach ($attachments as $receivedAttachment) {
                        $isAttachment = 'True';
                        $receivedAttachmentsNames[] = $receivedAttachment['name'];
                        $receivedAttachmentsFiles[] = $receivedAttachment['data'];
                    }
                }
                $emailDataToInsert = [
                    "user_id" => user()->id,
                    "subject" => $subject,
                    "from_email" => $from,
                    "to_email" => $to,
                    "date" => $date,
                    "body" => $body,
                    "is_attachment" => $isAttachment,
                    "attachment_name" => !empty($receivedAttachmentsNames) ? serialize($receivedAttachmentsNames) : null,
                    "attachment_file" => !empty($receivedAttachmentsFiles) ? serialize($receivedAttachmentsFiles) : null,
                    "thread_id" => $messageDetails->getThreadId(),
                    "ref_id" => $refId,
                    "is_send" => 1,
                ];

                DB::table('retrieving_emails')->updateOrInsert(
                    [
                        'subject' => $emailDataToInsert['subject'],
                        'user_id' => $emailDataToInsert['user_id'],
                        'from_email' => $emailDataToInsert['from_email'],
                        'to_email' => $emailDataToInsert['to_email'],
                        'body' => $emailDataToInsert['body'],
                        'is_send' => $emailDataToInsert['is_send'],
                    ],
                    $emailDataToInsert
                );

                return Reply::success('Email sent successfully');
            }
        } catch (\Exception $ex) {
            return Reply::error('Error sending email: ' . $ex->getLine());
        }
    }

    public function getApplicantEmailsImap($companySmtp, $label)
    {
        $imap = (object)[];
        $imap->imap_host = $companySmtp->imap_host . $label;
        $imap->imap_username = $companySmtp->smtp_username;
        $imap->imap_password = $companySmtp->smtp_password;
        $this->authenticateImap($imap);
        $emails = $this->getMailBoxEmailsImap($companySmtp, $label);
        return $emails;
    }
    public function authenticateImap($imap)
    {
        $host = request()->mail_box ?? $imap->imap_host;
        $this->setImapHost($host);
        $this->setImapUsername($imap->imap_username);
        $this->setImapPassword($imap->imap_password);
        $imap = imap_open($this->host, $this->username, $this->password);
        if ($imap) {
            $this->setImapServer($imap);
        } else {
            throw new Exception("Invalid IMAP credentials. Connection failed.");
        }
    }

    public function setImapHost($value)
    {
        $this->host = $value;
    }

    public function setImapUsername($value)
    {
        $this->username = $value;
    }

    public function setImapPassword($value)
    {
        $this->password = $value;
    }

    public function setImapServer($imap)
    {
        $this->imap = $imap;
    }

    public function getMailBoxEmailsImap($companySmtp, $label)
    {
        $perPage = request()->per_page ?? 1;
        $page = request()->page ?? 1;
        $rangeStart = ($page - 1) * $perPage;
        $allEmailNumbers = imap_sort($this->imap, SORTDATE, 1);
        $filteredEmailNumbers = $allEmailNumbers;
        $emailsForPage = array_slice($filteredEmailNumbers, $rangeStart, $perPage);
        $sentEmail = [];
        if ($emailsForPage) {
            foreach ($emailsForPage as $email_number) {
                $structure = imap_fetchstructure($this->imap, $email_number);
                $header = imap_headerinfo($this->imap, $email_number);
                $subject = isset($header->subject) ? $this->decodeMimeSubjectImap($header->subject) : "(no-subject)";
                $body = $this->getEmailBodyImap($email_number, $structure);
                $refId = '';
                $attachments = $this->getAttachmentsImap($email_number, $structure);
                $references = $header->references ?? null;
                if ($references) {
                    $referencesArray = explode(' ', $references);
                    $refId = $referencesArray[0];
                }
                $threadId = $header->in_reply_to ?? $email_number;
                $sentEmail = [
                    'subject' => $subject,
                    'from' => $header->fromaddress ?? null,
                    'to' => $header->toaddress ?? null,
                    'date' => $header->date ?? null,
                    'body' => $body ?? null,
                    'attachments' => $attachments,
                    'thread_id' => $threadId,
                    'ref_id' => $refId
                ];
            }
        }
        return $sentEmail;
    }

    public function getMailBoxFolder()
    {
        $folder = substr($this->host, strrpos($this->host, '}') + 1);
        return $folder;
    }

    private function getAttachmentsImap($email_number, $structure)
    {
        $attachments = [];
        if (isset($structure->parts) && count($structure->parts)) {
            foreach ($structure->parts as $partNo => $part) {
                if ($part->ifdisposition && $part->disposition == 'ATTACHMENT') {
                    $attachment = [
                        'is_attachment' => true,
                        'filename' => $part->dparameters[0]->value,
                        'name' => $part->dparameters[0]->value,
                        'data' => imap_fetchbody($this->imap, $email_number, $partNo + 1)
                    ];
                    $attachments[] = $attachment;
                }
            }
        }
        return $attachments;
    }

    private function decodeMimeSubjectImap($subject)
    {
        return imap_utf8($subject);
    }

    private function getEmailBodyImap($email_number, $structure)
    {
        $body = "";

        try {
            if ($structure->type === 1 && property_exists($structure, 'parts')) {
                foreach ($structure->parts as $partNumber => $part) {
                    if ($part->subtype === 'HTML') {
                        $body = imap_fetchbody($this->imap, $email_number, $partNumber + 1);
                        break;
                    } elseif ($part->subtype === 'PLAIN') {
                        $body = imap_fetchbody($this->imap, $email_number, $partNumber + 1);
                        break;
                    }
                }
            } else {
                $body = imap_body($this->imap, $email_number);
            }

            $encoding = isset($structure->encoding) ? $structure->encoding : 0;
            switch ($encoding) {
                case 1:
                case 3:
                    $body = imap_8bit($body);
                    break;
                case 4:
                    $body = quoted_printable_decode($body);
                    break;
                case 5:
                    $body = base64_decode($body);
                    break;
            }

            $body = trim($body);
            $body = quoted_printable_decode($body);
            $body = preg_replace('/\n+/', "\n", $body);
            $body = preg_replace('/^\s*\n/', '', $body);
            $body = preg_replace('/\n\s*$/', '', $body);

            return nl2br($body);

        } catch (\Exception $e) {
            throw new \Exception('Error processing email body: ' . $e->getMessage());
        }
    }


    private function getHeader($headers, $name)
    {
        foreach ($headers as $header) {
            if ($header->getName() == $name) {
                return $header->getValue();
            }
        }
        return null;
    }

    private function getEmailBody($messageDetails)
    {
        try {
            $body = '';
            $payload = $messageDetails->getPayload();
            $bodyData = $payload->getBody()->getData();

            if ($bodyData) {
                $body = base64_decode(strtr($bodyData, '-_', '+/'));
            } else {
                $parts = $payload->getParts();
                foreach ($parts as $part) {
                    if ($part->getMimeType() == 'text/html' || $part->getMimeType() == 'text/plain') {
                        $body = base64_decode(strtr($part->getBody()->getData(), '-_', '+/'));
                        break;
                    }
                }
            }

            return nl2br($body);
        } catch (\Exception $e) {
            throw new \Exception('Error processing email body: ' . $e->getMessage());
        }
    }

    private function getAttachments($messageDetails, $gmailService, $messageId)
    {
        try {
            $attachments = [];
            $payload = $messageDetails->getPayload();

            if (isset($payload->parts)) {
                foreach ($payload->parts as $part) {
                    if ($part->getFilename()) {
                        $attachmentData = [
                            'is_attachment' => true,
                            'filename' => $part->getFilename(),
                            'name' => $part->getFilename(),
                        ];

                        if (isset($part->body->attachmentId)) {
                            $attachmentId = $part->body->attachmentId;
                            try {
                                $attachment = $gmailService->users_messages_attachments->get('me', $messageId, $attachmentId);
                                $attachmentData['data'] = strtr($attachment->data, '-_', '+/');
                            } catch (\Exception $e) {
                                $attachmentData['attachment'] = $e->getMessage();
                            }
                        }

                        $attachments[] = $attachmentData;
                    }
                }
            }

            return $attachments;
        } catch (\Exception $e) {
            throw new \Exception('Error processing attachments: ' . $e->getMessage());
        }
    }

    private function getMimeMessage(Email $email)
    {
        return $this->base64url_encode($email->toString());
    }

    private function base64url_encode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }


    public function configureUserMailer($smtp)
    {
        config([
            'mail.mailers.smtp.host' => $smtp->smtp_host,
            'mail.mailers.smtp.port' => $smtp->smtp_port,
            'mail.mailers.smtp.username' => $smtp->smtp_username,
            'mail.mailers.smtp.password' => $smtp->smtp_password,
            'mail.mailers.smtp.encryption' => $smtp->smtp_encryption,
            'mail.from.address' => $smtp->sender_email,
            'mail.from.name' => $smtp->sender_name,
        ]);
    }


    // public function authenticateImap($imap)
    // {
    //     $host = request()->mail_box ?? $imap->imap_host;

    //     $this->setImapHost($host);
    //     $this->setImapUsername($imap->imap_username);
    //     $this->setImapPassword($imap->imap_password);

    //     $imap = imap_open($this->host, $this->username, $this->password);

    //     // Check if the connection was successful or not
    //     if ($imap) {
    //         $this->setImapServer($imap);
    //     } else {
    //         throw new Exception("Invalid IMAP credentials. Connection failed.");
    //     }
    // }

    // public function setImapHost($value)
    // {
    //     $this->host = $value;
    // }

    // public function setImapUsername($value)
    // {
    //     $this->username = $value;
    // }

    // public function setImapPassword($value)
    // {
    //     $this->password = $value;
    // }

    // public function setImapServer($imap)
    // {
    //     $this->imap = $imap;
    // }

    // public function getMailBoxEmails($id)
    // {
    //     $perPage = request()->per_page ?? 50;
    //     $page = request()->page ?? 1;
    //     // Calculate the message range for the current page
    //     $rangeStart = ($page - 1) * $perPage;
    //     $rangeEnd = $page * $perPage;

    //     // Get email sort by date in descending order
    //     $allEmailNumbers = imap_sort($this->imap, SORTDATE, 1);

    //     $filteredEmailNumbers = [];

    //     $filterEmail = $this->application->email;
    //     // If a filter email is provided, filter the emails based on the 'from' or 'to' addresses
    //     if ($filterEmail) {
    //         foreach ($allEmailNumbers as $email_number) {
    //             $header = imap_headerinfo($this->imap, $email_number);
    //             $fromAddress = $header->fromaddress ?? null;
    //             $toAddress = $header->toaddress ?? null;

    //             if (($fromAddress && stripos($fromAddress, $filterEmail) !== false) ||
    //                 ($toAddress && stripos($toAddress, $filterEmail) !== false)
    //             ) {
    //                 $filteredEmailNumbers[] = $email_number;
    //             }
    //         }
    //     } else {
    //         $filteredEmailNumbers = $allEmailNumbers;
    //     }

    //     $data['total_emails'] = sizeof($filteredEmailNumbers);
    //     if ($this->mailBox == 'INBOX') {
    //         $userApplicant = RecruitJobApplication::find($id);
    //         $totalNewEmails = count($filteredEmailNumbers);
    //         $userApplicant->update([
    //             'email_count' => $totalNewEmails,
    //             'email_diff' => 0,
    //             'email_notify' => 0,
    //         ]);
    //     }

    //     // Extract the email numbers for the current page
    //     $emailsForPage = array_slice($filteredEmailNumbers, $rangeStart, $perPage);

    //     $allEmails = [];
    //     $threads = [];
    //     if ($emailsForPage) {
    //         foreach ($emailsForPage as $email_number) {
    //             // Fetch email structure
    //             $structure = imap_fetchstructure($this->imap, $email_number);
    //             // Fetch email headers
    //             $header = imap_headerinfo($this->imap, $email_number);

    //             // Decode the subject if it's encoded
    //             $subject = isset($header->subject) ? $this->decodeMimeSubject($header->subject) : "(no-subject)";

    //             // Fetch email body based on content type
    //             $body = $this->getEmailBody($email_number, $structure);
    //             $refId = '';

    //             // Fetch attachments
    //             $attachments = $this->getAttachments($email_number, $structure);
    //             $references = $header->references ?? null;
    //             if ($references) {
    //                 $referencesArray = explode(' ', $references);
    //                 $refId = $referencesArray[0];
    //             }
    //             // $messageId = $header->message_id ?? $email_number;
    //             // $inReplyTo = $header->in_reply_to ?? null;
    //             // $references = $header->references ?? null;

    //             //  $threadId = $inReplyTo ? $inReplyTo : $messageId;
    //             // $threadId = $subject;
    //             // Fetch In-Reply-To header for threading
    //             $threadId = $header->in_reply_to ?? $email_number;

    //             // Store email information in an array
    //             $emailInfo = [
    //                 'mailbox' => $this->getMailBoxFolder(),
    //                 'subject' => $subject,
    //                 'from' => $header->fromaddress ?? null,
    //                 'to' => $header->toaddress ?? null,
    //                 'date' => $header->date ?? null,
    //                 'body' => $body ?? null,
    //                 'attachments' => $attachments, // Include attachments in the response
    //                 'thread_id' => $threadId,
    //                 'ref_id' => $refId
    //                 // 'structure' => $structure // Include email structure if needed
    //             ];

    //             // Push the email information array into the main array

    //             $allEmails[] = $emailInfo;
    //         }
    //     }

    //     $data['current_page'] = (int)$page;
    //     $data['emails'] = $allEmails;
    //     return $data;
    // }

    // public function getMailBoxFolder()
    // {
    //     $folder = substr($this->host, strrpos($this->host, '}') + 1);
    //     return $folder;
    // }

    // private function getAttachments($email_number, $structure)
    // {
    //     $attachments = [];

    //     if (isset($structure->parts) && count($structure->parts)) {
    //         for ($i = 0; $i < count($structure->parts); $i++) {
    //             $attachments = array_merge($attachments, $this->getPartAttachments($email_number, $structure->parts[$i], $i + 1));
    //         }
    //     }

    //     return $attachments;
    // }

    // private function getPartAttachments($email_number, $part, $partNumber)
    // {
    //     $attachments = [];

    //     if (isset($part->ifdparameters) && $part->ifdparameters) {
    //         foreach ($part->dparameters as $object) {
    //             if (strtolower($object->attribute) == 'filename') {
    //                 $attachment = [
    //                     'is_attachment' => true,
    //                     'filename' => $object->value,
    //                     'name' => $object->value,
    //                     'attachment' => imap_fetchbody($this->imap, $email_number, $partNumber)
    //                 ];

    //                 // Handle different encodings
    //                 if ($part->encoding == 3) { // 3 = BASE64
    //                     $attachment['attachment'] = base64_encode(base64_decode($attachment['attachment']));
    //                 } elseif ($part->encoding == 4) { // 4 = QUOTED-PRINTABLE
    //                     $attachment['attachment'] = base64_encode(quoted_printable_decode($attachment['attachment']));
    //                 } else {
    //                     // Handle other encodings if necessary
    //                     $attachment['attachment'] = base64_encode(imap_qprint($attachment['attachment']));
    //                 }

    //                 $attachments[] = $attachment;
    //             }
    //         }
    //     }

    //     if (isset($part->parts) && count($part->parts)) {
    //         for ($j = 0; $j < count($part->parts); $j++) {
    //             $attachments = array_merge($attachments, $this->getPartAttachments($email_number, $part->parts[$j], $partNumber . '.' . ($j + 1)));
    //         }
    //     }

    //     return $attachments;
    // }

    // private function decodeMimeSubject($subject)
    // {
    //     $decodedSubject = null;

    //     // Try using iconv_mime_decode
    //     $decodedSubject = iconv_mime_decode($subject, 0, 'UTF-8');

    //     // If iconv_mime_decode fails, try mb_decode_mimeheader with UTF-8
    //     if ($decodedSubject === false) {
    //         $decodedSubject = mb_decode_mimeheader($subject, 0, 'UTF-8');
    //     }

    //     return $decodedSubject;
    // }

    // private function getEmailBody($email_number, $structure)
    // {
    //     try {
    //         $body = '';
    //         if ($structure->type === 1 && property_exists($structure, 'parts')) {
    //             // Extract multipart body
    //             foreach ($structure->parts as $partNumber => $part) {

    //                 if ($part->subtype === 'HTML') {
    //                     $body = imap_fetchbody($this->imap, $email_number, $partNumber + 1);
    //                     $body = $this->decodeBody($body, $part);
    //                     $body = $this->handleInlineImages($body, $structure->parts);
    //                     break;
    //                 }
    //             }
    //         }
    //         if (empty($body)) {
    //             // If no HTML part found, fallback to plain text part
    //             foreach ($structure->parts as $partNumber => $part) {
    //                 if ($part->subtype === 'PLAIN') {
    //                     $body = imap_fetchbody($this->imap, $email_number, $partNumber + 1);
    //                     $body = $this->decodeBody($body, $part);
    //                     $body = nl2br($body);  // Convert plain text to HTML line breaks
    //                     break;
    //                 }
    //             }
    //         }
    //         if (empty($body)) {
    //             // Fallback to the main body if no parts are found
    //             $body = imap_body($this->imap, $email_number);
    //             $body = $this->decodeBody($body, $structure);
    //             $body = nl2br($body);  // Convert plain text to HTML line breaks
    //         }

    //         return $body;
    //     } catch (\Exception $e) {
    //         // Handle any exceptions that occur during body retrieval
    //         Log::error('Error fetching email body: ' . $e->getMessage());
    //         return ''; // Return empty string or handle the error as per your application's logic
    //     }
    // }

    // private function decodeBody($body, $part)
    // {
    //     $encoding = isset($part->encoding) ? $part->encoding : 0;
    //     switch ($encoding) {
    //         case 1: // 8BIT
    //             return imap_8bit($body);
    //         case 3: // BASE64
    //             return base64_decode($body);
    //         case 4: // QUOTED-PRINTABLE
    //             return quoted_printable_decode($body);
    //         default:
    //             return $body;
    //     }
    // }

    // private function handleInlineImages($body, $parts)
    // {
    //     foreach ($parts as $partNumber => $part) {
    //         if ($part->ifdisposition && $part->disposition === 'INLINE' && $part->ifid) {
    //             $attachment = imap_fetchbody($this->imap, $partNumber, $partNumber + 1);
    //             $attachment = $this->decodeBody($attachment, $part);

    //             $filename = isset($part->dparameters[0]->value) ? $part->dparameters[0]->value : $part->parameters[0]->value;
    //             $filePath = storage_path('app/public/' . $filename);

    //             file_put_contents($filePath, $attachment);

    //             $body = str_replace('cid:' . trim($part->id, '<>'), asset('storage/' . $filename), $body);
    //         }
    //     }

    //     return $body;
    // }

    // private function getEmailBodyOld($email_number, $structure)
    // {
    //     $body = "";

    //     if ($structure->type === 1 && property_exists($structure, 'parts')) {
    //         // If multipart, find the HTML part
    //         foreach ($structure->parts as $partNumber => $part) {
    //             if ($part->subtype === 'HTML') {
    //                 $body = imap_fetchbody($this->imap, $email_number, $partNumber + 1);
    //                 break;
    //             }
    //         }
    //     } else {
    //         // If not multipart or HTML part not found, fetch the plain text part
    //         $body = imap_body($this->imap, $email_number);
    //     }

    //     // Decode the body if necessary
    //     $encoding = isset($structure->encoding) ? $structure->encoding : 0;
    //     switch ($encoding) {
    //         case 1: // 7bit
    //         case 3: // 8bit
    //             $body = imap_8bit($body);
    //             break;
    //         case 4: // QUOTED-PRINTABLE
    //             $body = quoted_printable_decode($body);
    //             break;
    //         case 5: // BASE64
    //             $body = base64_decode($body);
    //             break;
    //     }

    //     $body = quoted_printable_decode($body);
    //     $body = mb_convert_encoding($body, 'UTF-8', 'ISO-8859-1');
    //     return $body;
    // }

    public function getLocation(Request $request)
    {
        $this->data = RecruitJob::with('address')->findOrFail($request->job_id);
        $this->locations = RecruitJobAddress::with('location')->where('recruit_job_id', $request->job_id)->get();
        $view = view('recruit::job-applications.location', $this->data)->render();
        $job = RecruitJob::findOrFail($request->job_id);
        $currencySymbol = $job->currency_id ? Currency::where('id', '=', $job->currency_id)->first() : company()->currency;

        return Reply::dataOnly(['status' => 'success', 'locations' => $view, 'job' => $job, 'id' => $request->job_id, 'currencySymbol' => $currencySymbol]);
    }

    public function emailValidation($request)
    {
        $jobApps = RecruitJobApplication::where('recruit_job_id', $request->job_id)->get();

        if (count($jobApps) > 0) {
            foreach ($jobApps as $jobApp) {
                $mail = $jobApp->where('recruit_job_id', $request->job_id)->whereNotNull('email')->pluck('email')->toArray();
            }

            if (in_array($request->email, $mail)) {
                $this->validate($request, [
                    'email' => 'unique:recruit_job_applications|email'
                ]);
            } else {
                return $request->email;
            }
        } else {
            return $request->email;
        }
    }

    public function quickAddFormStore(StoreQuickApplication $request)
    {
        $addPermission = user()->permission('add_job_application');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $jobApp = new RecruitJobApplication();
        $jobApp->recruit_job_id = $request->job_id;
        $jobApp->full_name = $request->full_name;
        $jobApp->email = $this->emailValidation($request);
        $jobApp->phone = $request->phone;

        if ($request->has('gender')) {
            $jobApp->gender = $request->gender;
        }

        $jobApp->application_source_id = $request->source;
        $jobApp->cover_letter = $request->cover_letter;
        $jobApp->location_id = $request->location_id;
        $jobApp->total_experience = $request->total_experience;
        $jobApp->current_location = $request->current_location;
        $jobApp->current_ctc = $request->current_ctc;
        $jobApp->expected_ctc = $request->expected_ctc;
        $jobApp->notice_period = $request->notice_period;
        $jobApp->recruit_application_status_id = $request->status_id ?? 1;
        $jobApp->application_sources = 'addedByUser';
        $jobApp->column_priority = 0;

        $jobApp->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('job-applications.index');
        }

        return Reply::dataOnly(['redirectUrl' => $redirectUrl, 'application_id' => $jobApp->id]);
    }

    public function importJobApplication()
    {
        $this->pageTitle = __('recruit::modules.jobApplication.importJobCandidates');

        $this->addPermission = user()->permission('add_job_application');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'recruit::job-applications.ajax.import';


        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('recruit::job-applications.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $this->importFileProcess($request, JobApplicationImport::class);

        $this->jobs = RecruitJob::all();

        $view = view('recruit::job-applications.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, JobApplicationImport::class, ImportJobApplicationJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function downloadSampleCsv()
    {
        return response()->download(storage_path('csv/job-application-sample.xlsx'));
    }
}
