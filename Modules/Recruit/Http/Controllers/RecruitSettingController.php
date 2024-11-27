<?php

namespace Modules\Recruit\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Modules\Recruit\Entities\RecruitSetting;
use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\User;
use App\Models\Module;
use App\Models\CompanySmtpSetting;
use App\Models\CompanyEmailTemplateSetting;
use Modules\Recruit\Entities\ApplicationSource;
use Modules\Recruit\Entities\RecruitCustomQuestion;
use Modules\Recruit\Entities\RecruitEmailNotificationSetting;
use Modules\Recruit\Entities\Recruiter;
use Modules\Recruit\Entities\RecruitFooterLink;
use Modules\Recruit\Entities\RecruitJobCustomQuestion;
use Modules\Recruit\Entities\RecruitApplicationStatus;
use Modules\Recruit\Http\Requests\RecruitSetting\StoreSettingRequest;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;

class RecruitSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'recruit::app.menu.recruitSetting';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array(RecruitSetting::MODULE_NAME, $this->user->modules));

            return $next($request);
        });

    }

    public function index()
    {
        $module = Module::where('module_name', 'recruit')->first();

        $this->mail = RecruitSetting::where('company_id', company()->id)->first();
        $this->recruiters = Recruiter::with('user')->get();
        $this->employees = User::allEmployees()->all();
        $this->selectedRecruiter = Recruiter::get()->pluck('user_id')->toArray();
        $this->activeSettingMenu = 'recruit_settings';
        $this->emailSettings = RecruitEmailNotificationSetting::all();
        $this->footerLinks = RecruitFooterLink::where('company_id', company()->id)->get();
        $this->jobQuestions = RecruitCustomQuestion::where('company_id', company()->id)->get();
        $this->statuses = RecruitApplicationStatus::with('category')->where('company_id', company()->id)->get();
        $this->sources = ApplicationSource::where('company_id', company()->id)->get();
        $this->mail->companySmtpSetting = CompanySmtpSetting::where('company_id', '=', user()->id)->where('module_id', $module->id)->first() ?? [];
        $this->mail->companyEmailTemplates = CompanyEmailTemplateSetting::where('company_id', '=', company()->id)->where('module_id', $module->id)->get();


        $tab = request('tab');

        switch ($tab) {
        case 'recruit-setting':
            $this->view = 'recruit::recruit-setting.ajax.recruit-setting';
            break;
        case 'footer-settings':
            $this->view = 'recruit::recruit-setting.ajax.footer-settings';
            break;
        case 'recruit-email-notification-setting':
            $this->view = 'recruit::recruit-setting.ajax.recruit-email-notification-setting';
            break;
        case 'job-application-status-settings':
            $this->view = 'recruit::recruit-setting.ajax.job-application-status-settings';
            break;
        case 'recruit-custom-question-setting':
            $this->view = 'recruit::recruit-setting.ajax.custom-question-settings';
            break;
        case 'recruit-source-setting':
            $this->view = 'recruit::recruit-setting.ajax.source-setting';
            break;
        case 'recruit-email-setting':
            $this->view = 'recruit::recruit-setting.ajax.email-setting';
            break;
        case 'recruit-email-template-setting':
            $this->view = 'recruit::recruit-setting.ajax.email-template-setting';
            break;
        default:
            $this->general = RecruitSetting::where('company_id', '=', company()->id)->select('about')->first();
            $this->view = 'recruit::recruit-setting.ajax.general-setting';
            break;
        }

        $this->activeTab = $tab ?: 'general-setting';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('recruit::recruit-setting.index', $this->data);
    }

    public function update(StoreSettingRequest $request)
    {
        $settings = RecruitSetting::where('company_id', company()->id)->first();

        $formSetting = [];
        $ar = $request->checkColumns;

        foreach ($settings->form_settings as $id => $from) {
            $from['status'] = false;

            if ($request->has('checkColumns') && in_array($id, $ar)) {
                $from['status'] = true;
            }

            $formSetting = Arr::add($formSetting, $id, $from);
        }

        // Background image

        if ($request->image_delete == 'yes') {
            Files::deleteFile($settings->background_image, 'background');
            $settings->background_image = null;
        }
        elseif ($request->type == 'bg-image') {
            $oldImage = $settings->background_image;

            if ($request->hasFile('image')) {
                $settings->background_image = Files::uploadLocalOrS3($request->image, 'background');

                $path = Files::UPLOAD_FOLDER . '/background' . '/' . $oldImage;

                if (\File::exists($path)) {
                    Files::deleteFile($oldImage, 'background');
                }
            }
        }
        elseif ($request->type == 'bg-color') {
            $settings->background_color = $request->logo_background_color;
        }

        // front page logo

        if ($request->logo_delete == 'yes') {
            Files::deleteFile($settings->logo, 'company-logo');
            $settings->logo = null;
        }

        if ($request->hasFile('logo')) {
            Files::deleteFile($settings->logo, 'company-logo');
            $settings->logo = Files::uploadLocalOrS3($request->logo, 'company-logo');
        }

        if ($request->favicon_delete == 'yes') {
            Files::deleteFile($settings->favicon, 'company-favicon');
            $settings->favicon = null;
        }

        if ($request->hasFile('favicon')) {
            $settings->favicon = Files::uploadLocalOrS3($request->favicon, 'company-favicon', null, null, false);
        }

        $settings->career_site = $request->career_site;
        $settings->job_alert_status = $request->job_alert_status ?? 'no';
        $settings->google_recaptcha_status = $request->google_recaptcha_status ?? 'deactive';
        session()->forget('messageforAdmin');
        $settings->company_name = $request->company_name;
        $settings->application_restriction = $request->application_restriction;
        $settings->offer_letter_reminder = $request->offer_letter_reminder;
        $settings->company_website = $request->company_website;
        $settings->about = $request->about;
        $settings->type = $request->type;
        $settings->form_settings = $formSetting;
        $settings->legal_term = ($request->description == '<p><br></p>') ? null : $request->description;
        $settings->save();

        return Reply::successWithData(__('recruit::messages.settingupdated'), ['redirectUrl' => route('recruit-settings.index')]);
    }

     public function addCompanyEmailTemplate()
    {
        return view('recruit::recruit-setting.create-email-template-modal', $this->data);
    }

    public function deleteCompanyEmailTemplate(Request $request, $id) {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:company_email_template_settings,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->messages()->first()]);
        }

        try {
            // Find the template by id and delete it
            $template = CompanyEmailTemplateSetting::findOrFail($id);
            $template->delete();

            return response()->json(['status' => 'success', 'message' => 'Email template deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => 'An error occurred while deleting the template.']);
        }
    }

    public function updateCompanyEmailTemplate(Request $request, $id)
    {
        // Validation rules
        $rules = [
            'name' => 'required|unique:company_email_template_settings,name,' . $id,
            'subject' => 'required',
            'content' => 'required',
            'autoreply_template' => 'required|boolean',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['status' => 'fail', 'message' => $validator->messages()->first()]);
        }

        $module = Module::where('module_name', 'recruit')->first();

        try {
            $content_updated = $request->input('content_updated') ? urldecode($request->input('content_updated')) : null;
            $content = $request->input('content');
            $finalContent = $content_updated ?? $content;
            $template = CompanyEmailTemplateSetting::findOrFail($id);
            $template->update([
                'name' => $request->name,
                'subject' => $request->subject,
                'content' => $finalContent,
                'autoreply_template' => $request->autoreply_template,
            ]);

            // If autoreply_template is set to 1, set all other templates' autoreply_template to 0
            if ($request->autoreply_template) {
                CompanyEmailTemplateSetting::where('company_id', $template->company_id)->where('module_id', $module->id)
                    ->where('id', '<>', $template->id)
                    ->update(['autoreply_template' => 0]);
            }

            return response()->json(['status' => 'success', 'message' => 'Email template updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => 'An error occurred while updating the template.']);
        }
    }

     public function editCompanyEmailTemplate($id)
    {
        try {
            $template = CompanyEmailTemplateSetting::findOrFail($id);
            return response()->json(['status' => 'success', 'data' => $template]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'fail', 'message' => 'Template not found.']);
        }
    }

     public function saveCompanyEmailTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'sometimes|nullable|exists:company_email_template_settings,id',
            'name' => 'required|unique:company_email_template_settings,name,' . $request->id,
            'subject' => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return Reply::error($validator->messages()->first());
        }

        // Assuming you have a method to get the company id
        $company_id = company()->id;
        $content = urldecode($request->input('content'));
        $module = Module::where('module_name', 'recruit')->first();

        // Update or create the email template settings for the company
        $template = CompanyEmailTemplateSetting::updateOrCreate(
            ['id' => $request->id], // Condition to check if the setting exists
            [
                'name' => $request->name,
                'subject' => $request->subject,
                'content' => $content,
                'company_id' => $company_id,
                'module_id' => $module->id,
            ]
        );

        if($request->autoreply_template) {
            // Set all existing templates' autoreply_template field to 0
            CompanyEmailTemplateSetting::where('company_id', $company_id)->where('module_id', $module->id)
            ->update(['autoreply_template' => 0]);

            // Set the newly entered template's autoreply_template field to 1
            $template->autoreply_template = 1;
            $template->save();
        }

        // Return a success response
        return Reply::success('Email Template saved successfully');
    }

    public function addCompanySmtpSetting(Request $request)
    {
        $company_id = user()->id;
        $module = Module::where('module_name', 'leads')->first();
        $is_verified = $this->verifySmtp($request);
        if ($is_verified !== true) {
            return Reply::error($is_verified);
        }
        $smtpSetting = CompanySmtpSetting::updateOrCreate(
            ['company_id' => $company_id, 'module_id' => $module->id],
            [
                'sender_name' => $request->mail_from_name,
                'sender_email' => $request->mail_from_email,
                'smtp_username' => $request->mail_username,
                'smtp_password' => $request->mail_password,
                'smtp_host' => $request->mail_host,
                'smtp_port' => $request->mail_port,
                'smtp_encryption' => $request->mail_encryption,
                'imap_host' => $request->imap_host,
                'company_id' => $company_id,
                'added_type' => "user",
            ]
        );
        $module = Module::where('module_name', 'recruit')->first();
        $smtpSetting = CompanySmtpSetting::updateOrCreate(
            ['company_id' => $company_id, 'module_id' => $module->id],
            [
                'sender_name' => $request->mail_from_name,
                'sender_email' => $request->mail_from_email,
                'smtp_username' => $request->mail_username,
                'smtp_password' => $request->mail_password,
                'smtp_host' => $request->mail_host,
                'smtp_port' => $request->mail_port,
                'smtp_encryption' => $request->mail_encryption,
                'imap_host' => $request->imap_host,
                'company_id' => $company_id,
                'added_type' => "user",
            ]
        );
        return Reply::success('SMTP connected successfully');
    }

    private function verifySmtp($request)
    {
        try {
            $encryption = $request->mail_encryption;
            //            dd($this->mail_password,$this->mail_username,$this->mail_host,$this->mail_port,$tls);
            $transport = new EsmtpTransport($request->mail_host, $request->mail_port, $encryption);
            $transport->setUsername($request->mail_username);
            $transport->setPassword($request->mail_password);
            $transport->start();

            return true;

        } catch (TransportException | \Exception $e) {

            return $e->getMessage();
        }
    }
}
