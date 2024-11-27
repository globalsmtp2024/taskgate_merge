<?php

namespace App\Http\Controllers;

use Amp\Serialization\Serializer;
use App\Models\CompanySmtpSetting;
use App\Models\Module;
use App\Helper\Reply;
use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Google_Service_Gmail_MessagePart;
use Google_Service_Gmail_MessagePartHeader;

class GoogleAuthRedirectUrlController extends Controller
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    public function __construct()
    {
        $this->clientId = env('GOOGLE_CLIENT_ID');
        $this->clientSecret = env('GOOGLE_CLIENT_SECRET');
        $this->redirectUri = env('GOOGLE_REDIRECT_URI');
    }

    public function googleredirectleadsToProvider()
    {
        session()->put('previous_url', url()->previous());
        $client = $this->initializeGoogleClient();
        $authUrl = $client->createAuthUrl();
        return Redirect::to($authUrl);
    }

    public function googlehandleleadsProviderCallback(Request $request)
    {
        if ($request->has('code')) {
            try {
                $client = $this->authenticate($request->code);
                $userInfo = $this->getUserInfo($client);
                $company_id = user()->id;
                $module = Module::where('module_name', 'leads')->first();
                if (!$client->getRefreshToken()) {
                    return response()->json([
                        "message" => "Refresh Token is not Available",
                    ]);
                }
                $smtpSetting = CompanySmtpSetting::updateOrCreate(
                    ['module_id' => $module->id, 'company_id' => $company_id],
                    [
                        'sender_name' => $userInfo->name,
                        'sender_email' => $userInfo->email,
                        'smtp_username' => $userInfo->email,
                        'smtp_password' => $client->getAccessToken()['access_token'],
                        'refresh_token' => $client->getRefreshToken(),
                        'smtp_host' => "smtp.gmail.com",
                        'smtp_port' => 465,
                        'smtp_encryption' => "tls",
                        'imap_host' => "{imap.gmail.com:993/imap/ssl/novalidate-cert}",
                        'company_id' => $company_id,
                        "added_type" => "google",
                    ]
                );
                $company_id = user()->id;
                $module = Module::where('module_name', 'recruit')->first();
                $smtpSetting = CompanySmtpSetting::updateOrCreate(
                    ['module_id' => $module->id, 'company_id' => $company_id],
                    [
                        'sender_name' => $userInfo->name,
                        'sender_email' => $userInfo->email,
                        'smtp_username' => $userInfo->email,
                        'smtp_password' => $client->getAccessToken()['access_token'],
                        'refresh_token' => $client->getRefreshToken(),
                        'smtp_host' => "smtp.gmail.com",
                        'smtp_port' => 465,
                        'smtp_encryption' => "tls",
                        'imap_host' => "{imap.gmail.com:993/imap/ssl/novalidate-cert}",
                        'company_id' => $company_id,
                        "added_type" => "google",
                    ]
                );
                if ($smtpSetting) {
                    $previousURL = session()->get('previous_url');
                    if (str_contains($previousURL, "recruit-email-setting") || str_contains($previousURL, "job-applications")) {
                        session()->forget('previous_url');
                        return redirect()->route('recruit-settings.index', ['tab' => 'recruit-email-setting']);
                    } else {
                        session()->forget('previous_url');
                        return redirect()->route('lead-settings.index', ['tab' => 'email']);
                    }
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Error fetching token: ' . $e->getMessage()]);
            }
        }

        return response()->json(['error' => 'No authentication code received']);
    }

    private function initializeGoogleClient()
    {
        $client = new Google_Client();
        $client->setClientId($this->clientId);
        $client->setClientSecret($this->clientSecret);
        $client->setRedirectUri($this->redirectUri);
        $client->setScopes([
            \Google_Service_Oauth2::USERINFO_EMAIL,
            \Google_Service_Oauth2::USERINFO_PROFILE,
            'https://www.googleapis.com/auth/gmail.send',
            'https://www.googleapis.com/auth/gmail.readonly',
        ]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setApprovalPrompt('force');
        return $client;
    }
    private function authenticate($code)
    {
        $client = $this->initializeGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw new \Exception('Error fetching token');
        }
        $client->setAccessToken($token);
        if ($client->isAccessTokenExpired()) {
            $this->refreshToken($client);
        }
        return $client;
    }
    private function refreshToken($client)
    {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            throw new \Exception('No refresh token available');
        }
    }
    private function getUserInfo($client)
    {
        $oauthService = new Google_Service_Oauth2($client);
        return $oauthService->userinfo->get();
    }
    public function emailsSyncingFromGmail()
    {
        try {
            $module = Module::where('module_name', 'leads')->first();
            $usersSmtpSettings = CompanySmtpSetting::where('module_id', $module->id)->latest()->get();
            $usersSmtpSettingsData = [];
            foreach ($usersSmtpSettings as $settings) {
                $user = DB::table('users')->where('id', $settings->company_id)->where('is_active', 1)->first();
                if ($user) {
                    $usersSmtpSettingsData[] = [
                        'settings' => $settings,
                        'user' => $user
                    ];
                }
            }
            if (empty($usersSmtpSettingsData)) {
                return response()->json(['message' => 'No active users found'], 404);
            }
            $emailDataToInsert = [];
            foreach ($usersSmtpSettingsData as $smtpSettingData) {
                try {
                    if ($smtpSettingData['settings']['added_type'] == "google") {
                        $this->sentEmails = $this->getApplicantEmails($smtpSettingData['settings'], 'SENT');
                        $this->receivedEmails = $this->getApplicantEmails($smtpSettingData['settings'], 'INBOX');
                    } else {
                        $this->sentEmails = $this->getApplicantEmailsImap($smtpSettingData['settings'], '[Gmail]/Sent Mail');
                        $this->receivedEmails = $this->getApplicantEmailsImap($smtpSettingData['settings'], 'INBOX');
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Error fetching emails for user: ' . $smtpSettingData['user']->id . ' - ' . $e->getMessage());
                }
                if (isset($this->sentEmails['emails'])) {
                    foreach ($this->sentEmails['emails'] as $sentEmail) {
                        $isAttachment = 'False';
                        $sentAttachmentsNames = [];
                        $sentAttachmentsFiles = [];
                        if (!empty($sentEmail['attachments'])) {
                            foreach ($sentEmail['attachments'] as $sentAttachment) {
                                $isAttachment = 'True';
                                $sentAttachmentsNames[] = $sentAttachment['name'];
                                $sentAttachmentsFiles[] = $sentAttachment['data'];
                            }
                        }
                        $emailDataToInsert[] = [
                            "user_id" => $smtpSettingData['user']->id,
                            "subject" => $sentEmail['subject'],
                            "from_email" => $sentEmail['from'],
                            "to_email" => $sentEmail['to'],
                            "date" => $sentEmail['date'],
                            "body" => $sentEmail['body'],
                            "is_attachment" => $isAttachment,
                            "attachment_name" => !empty($sentAttachmentsNames) ? serialize($sentAttachmentsNames) : null,
                            "attachment_file" => !empty($sentAttachmentsFiles) ? serialize($sentAttachmentsFiles) : null,
                            "thread_id" => $sentEmail['thread_id'],
                            "ref_id" => $sentEmail['ref_id'],
                            "is_send" => 1,
                        ];
                    }
                }
                if (isset($this->receivedEmails['emails'])) {
                    foreach ($this->receivedEmails['emails'] as $receivedEmail) {
                        $isAttachment = 'False';
                        $receivedAttachmentsNames = [];
                        $receivedAttachmentsFiles = [];
                        if (!empty($receivedEmail['attachments'])) {
                            foreach ($receivedEmail['attachments'] as $receivedAttachment) {
                                $isAttachment = 'True';
                                $receivedAttachmentsNames[] = $receivedAttachment['name'];
                                $receivedAttachmentsFiles[] = $receivedAttachment['data'];
                            }
                        }
                        $emailDataToInsert[] = [
                            "user_id" => $smtpSettingData['user']->id,
                            "subject" => $receivedEmail['subject'],
                            "from_email" => $receivedEmail['from'],
                            "to_email" => $receivedEmail['to'],
                            "date" => $receivedEmail['date'],
                            "body" => $receivedEmail['body'],
                            "is_attachment" => $isAttachment,
                            "attachment_name" => !empty($receivedAttachmentsNames) ? serialize($receivedAttachmentsNames) : null,
                            "attachment_file" => !empty($receivedAttachmentsFiles) ? serialize($receivedAttachmentsFiles) : null,
                            "thread_id" => $receivedEmail['thread_id'],
                            "ref_id" => $receivedEmail['ref_id'],
                            "is_send" => 0,
                        ];
                    }
                }
            }
            if (!empty($emailDataToInsert)) {
                try {
                    foreach ($emailDataToInsert as $emailData) {
                        DB::table('retrieving_emails')->updateOrInsert(
                            [
                                'subject' => $emailData['subject'],
                                'user_id' => $emailData['user_id'],
                                'from_email' => $emailData['from_email'],
                                'to_email' => $emailData['to_email'],
                                'body' => $emailData['body'],
                                'is_send' => $emailData['is_send'],
                            ],
                            $emailData
                        );
                    }
                } catch (\Exception $e) {
                    throw new \Exception('Error inserting emails into retrieving_emails table: ' . $e->getMessage());
                    return response()->json(['message' => 'Failed to insert emails into the database'], 500);
                }
            }

            return response()->json([
                "message" => "Mails Added Successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during email syncing: ' . $e->getMessage()], 500);
        }
    }

    private function getApplicantEmails($companySmtp, $label)
    {
        try {
            return $this->getMailBoxEmails($companySmtp, $label);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching applicant emails: ' . $e->getMessage());
            throw new \Exception('Failed to fetch applicant emails');
        }
    }
    // =======================emails fetching for the imap api=====================================
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

    public function getMailBoxEmailsImap()
    {
        $perPage = request()->per_page ?? 50;
        $page = request()->page ?? 1;
        $rangeStart = ($page - 1) * $perPage;
        $allEmailNumbers = imap_sort($this->imap, SORTDATE, 1);
        $filteredEmailNumbers = $allEmailNumbers;
        $data['total_emails'] = count($filteredEmailNumbers);
        $emailsForPage = array_slice($filteredEmailNumbers, $rangeStart, $perPage);
        $allEmails = [];
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
                $allEmails[] = [
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
        $data['current_page'] = (int)$page;
        $data['emails'] = $allEmails;
        return $data;
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

    // =======================emails fetching for the imap api=====================================
    // =======================emails fetching for the gmail api=====================================
    private function getMailBoxEmails($companySmtp, $label)
    {
        try {
            $perPage = request()->per_page ?? 50;
            $page = request()->page ?? 1;
            $rangeStart = ($page - 1) * $perPage;
            $client = new Google_Client();
            $client->setClientId(env('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
            $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
            $accessToken = $companySmtp->smtp_password;
            $client->setAccessToken($accessToken);
            if ($client->isAccessTokenExpired()) {
                $refreshToken = $companySmtp->refresh_token;
                $newAccessToken = $client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (isset($newAccessToken['access_token'])) {
                    $companySmtp->smtp_password = $newAccessToken['access_token'];
                    $companySmtp->save();
                } else {
                    throw new \Exception('Unable to refresh the access token.');
                }
            }
            $gmailService = new Google_Service_Gmail($client);
            $messages = $this->fetchMessagesFromGmail($gmailService, $label, $perPage, $rangeStart);
            $allEmails = [];
            if ($messages->getMessages()) {
                foreach ($messages->getMessages() as $message) {
                    try {
                        $messageDetails = $gmailService->users_messages->get('me', $message->getId());
                        $headers = $messageDetails->getPayload()->getHeaders();
                        $subject = $this->getHeader($headers, 'Subject');
                        $from = $this->getHeader($headers, 'From');
                        $to = $this->getHeader($headers, 'To');
                        $date = $this->getHeader($headers, 'Date');
                        $body = $this->getEmailBody($messageDetails);
                        $attachments = $this->getAttachments($messageDetails, $gmailService, $message->getId());
                        $references = $this->getHeader($headers, 'References');
                        $refId = null;
                        if ($references) {
                            $referencesArray = explode(' ', $references);
                            $refId = $referencesArray[0];
                        }
                        $allEmails[] = [
                            'subject' => $subject,
                            'from' => $from,
                            'to' => $to,
                            'date' => $date,
                            'body' => $body,
                            'attachments' => $attachments,
                            'thread_id' => $messageDetails->getThreadId(),
                            'ref_id' => $refId
                        ];
                    } catch (\Exception $e) {
                        throw new \Exception('Error processing message ID ' . $message->getId() . ': ' . $e->getMessage());
                    }
                }
            }

            return [
                'current_page' => (int)$page,
                'emails' => $allEmails,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Error fetching emails for label ' . $label . ': ' . $e->getMessage());
        }
    }

    private function fetchMessagesFromGmail($gmailService, $label, $perPage, $rangeStart)
    {
        try {
            return $gmailService->users_messages->listUsersMessages('me', [
                'labelIds' => [$label],
                'maxResults' => $perPage,
                'pageToken' => $this->getPageToken($rangeStart)
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Error fetching messages from Gmail: ' . $e->getMessage());
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

    private function getPageToken($rangeStart)
    {
        return $rangeStart > 0 ? (string)$rangeStart : null;
    }
    // =======================emails fetching for the gmail api=====================================
    public function disconnectGoogleAuthentication()
    {
        $company_id = user()->id;
        $modules = ['leads', 'recruit'];

        foreach ($modules as $moduleName) {
            $module = Module::where('module_name', $moduleName)->first();
            CompanySmtpSetting::where('module_id', $module->id)->where('company_id', $company_id)->delete();
        }

        return redirect()->back();
    }
}
