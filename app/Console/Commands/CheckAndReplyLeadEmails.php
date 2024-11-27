<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AutoReplyNotification;
use App\Models\CompanySmtpSetting;
use App\Models\Module;
use App\Models\Company;
use Exception;
use Illuminate\Support\Facades\Log;

class CheckAndReplyLeadEmails extends Command
{
    protected $signature = 'lead-emails:check-replies';
    protected $description = 'Check for new emails and send auto-replies';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $module = Module::where('module_name', 'leads')->first();
            if (!$module) {
                throw new Exception('Module "leads" not found.');
            }

            $companyIds = Company::where('status', 'active')->get()->pluck('id');

            $companiesSMTP = CompanySmtpSetting::where('module_id', $module->id)
                ->whereIn('company_id', $companyIds)
                ->get();

            foreach ($companiesSMTP as $smtpSettings) {
                $imap = $this->setupImap($smtpSettings);
                
                if (!$imap) {
                    Log::error('Failed to set up IMAP for company ID: ' . $smtpSettings->company_id);
                    continue;
                }

                $emails = $this->getMailBoxEmails($imap);
                foreach ($emails as $email) {
                    try {
                      if($companiesSMTP->sender_email != $email){
                        $this->sendAutoReply($email, $smtpSettings->company_id);
                      }
                    } catch (Exception $e) {
                        Log::error('Failed to send auto-reply', [
                            'email' => $email,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                imap_close($imap);
            }
        } catch (Exception $e) {
            Log::error('Failed to process emails', [
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function setupImap($smtp)
    {
        try {
            $host = $smtp->imap_host;
            $username = $smtp->smtp_username;
            $password = $smtp->smtp_password;

            $imap = imap_open($host, $username, $password);
            if (!$imap) {
                throw new Exception('Unable to connect to IMAP server: ' . imap_last_error());
            }

            return $imap;
        } catch (Exception $e) {
            Log::error('IMAP setup failed', [
                'host' => $smtp->imap_host,
                'username' => $smtp->smtp_username,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function getMailBoxEmails($imap)
    {
        try {
            $emails = [];
            $mailbox = 'INBOX';

            // Get current date and time, and calculate the cutoff time
            $now = time();
            $cutoff = $now - 61; // 61 seconds ago
            $cutoffDate = date('d-M-Y H:i:s', $cutoff);

            // Convert cutoff date to the format used by IMAP
            $formattedCutoffDate = date('d-M-Y', $cutoff) . ' ' . date('H:i:s', $cutoff);

            // Search for unseen emails with date greater than cutoffDate
            $emailsToFetch = imap_search($imap, 'UNSEEN SINCE "' . $formattedCutoffDate . '"');

            if ($emailsToFetch) {
                foreach ($emailsToFetch as $emailNumber) {
                    $header = imap_headerinfo($imap, $emailNumber);
                    // Fetch the email's date
                    $emailDate = $header->date;
                    $emailTimestamp = strtotime($emailDate);

                    // Check if the email is within the last 61 seconds
                    if ($emailTimestamp >= $cutoff) {
                        $emails[] = [
                            'from' => $header->fromaddress,
                            'timestamp' => $emailTimestamp,
                            // 'subject' => $header->subject,
                            // 'body' => imap_fetchbody($imap, $emailNumber, 1),
                        ];
                    }
                }
            }

            return $emails;
        } catch (Exception $e) {
            Log::error('Failed to fetch emails from mailbox', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    protected function sendAutoReply($email, $companyId)
    {
        $recipientEmail = $email['from'];
        Log::info('Auto-reply would be sent to: ' . $recipientEmail);

        Notification::route('mail', $recipientEmail)
          ->notify(new AutoReplyNotification($recipientEmail, $companyId));
    }
}