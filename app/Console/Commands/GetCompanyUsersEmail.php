<?php

namespace App\Console\Commands;
use App\Models\Company;
use App\Models\Module;
use App\Models\CompanySmtpSetting;
use Illuminate\Support\Facades\Log;

use Illuminate\Console\Command;

class GetCompanyUsersEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:update-user-email-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update User Emails';

    /**
     * Execute the console command.
     */
   public function handle()
{
    $this->mailBox = 'INBOX';
    $companies = Company::with(['companyLeads', 'recruitJobApplications'])
                        ->where('status', 'active')
                        ->get();

    // Process leads
    foreach ($companies as $company) {
        try {
            $module = Module::where('module_name', 'leads')->first();
            $companySmtpSetting = CompanySmtpSetting::where('company_id', $company->id)
                                                    ->where('module_id', $module->id)
                                                    ->first();
            if ($companySmtpSetting) {
                foreach ($company->companyLeads as $lead) {
                    if ($lead->email_notify != 1) {
                        $this->getNewInboxEmailCount($lead, $companySmtpSetting);
                        sleep(3);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error processing leads for company ID ' . $company->id . ': ' . $e->getMessage());
        }
    }

    // Process job applications
    foreach ($companies as $company) {
        try {
            $module = Module::where('module_name', 'recruit')->first();
            $companySmtpSetting = CompanySmtpSetting::where('company_id', $company->id)
                                                    ->where('module_id', $module->id)
                                                    ->first();
            if ($companySmtpSetting) {
                foreach ($company->recruitJobApplications as $jobApplicant) {
                    if ($jobApplicant->email_notify != 1) {
                        $this->getNewInboxEmailCount($jobApplicant, $companySmtpSetting);
                        sleep(3);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error processing job applications for company ID ' . $company->id . ': ' . $e->getMessage());
        }
    }
}

public function getNewInboxEmailCount($leadContact, $companySmtp)
{
    try {
        $imap = (object)[];
        $imap->imap_host = $companySmtp->imap_host . $this->mailBox;
        $imap->imap_username = $companySmtp->smtp_username;
        $imap->imap_password = $companySmtp->smtp_password;

        $this->authenticateImap($imap);
        $this->getEmailsCountUpdateUser($leadContact);
    } catch (\Exception $e) {
        \Log::error('Error in getNewInboxEmailCount for leadContact ID ' . $leadContact->id . ': ' . $e->getMessage());
    }
}

public function getEmailsCountUpdateUser($leadContact)
{
    try {
        // Get all email numbers sorted by date in descending order
        $allEmailNumbers = imap_sort($this->imap, SORTDATE, 1);

        $filteredEmailNumbers = [];
        $filterEmail = $leadContact->client_email ? $leadContact->client_email : $leadContact->email;

        // If a filter email is provided, filter the emails based on the 'from' or 'to' addresses
        if ($filterEmail) {
            foreach ($allEmailNumbers as $email_number) {
                $header = imap_headerinfo($this->imap, $email_number);
                $fromAddress = $header->fromaddress ?? null;
                $toAddress = $header->toaddress ?? null;
                if (($fromAddress && stripos($fromAddress, $filterEmail) !== false) ||
                    ($toAddress && stripos($toAddress, $filterEmail) !== false)) {
                    $filteredEmailNumbers[] = $email_number;
                }
            }
        } else {
            $filteredEmailNumbers = $allEmailNumbers;
        }

        // Get the total number of filtered emails
        $totalEmails = count($filteredEmailNumbers);

        // Update the user's email count in the database
        if ($leadContact->email_count < $totalEmails) {
            $email_difference =   $totalEmails - $leadContact->email_count;
            $leadContact->email_count = $totalEmails;
            $leadContact->email_diff = $email_difference;
            $leadContact->email_notify = 1;
            $leadContact->save();
        }
    } catch (\Exception $e) {
        \Log::error('Error in getEmailsCountUpdateUser for leadContact ID ' . $leadContact->id . ': ' . $e->getMessage());
    }
}

private function authenticateImap($imap)
{
    try {
        $host = request()->mail_box ?? $imap->imap_host;

        $this->setImapHost($host);
        $this->setImapUsername($imap->imap_username);
        $this->setImapPassword($imap->imap_password);

        $imapConnection = imap_open($this->host, $this->username, $this->password);

        // Check if the connection was successful or not
        if ($imapConnection) {
            $this->setImapServer($imapConnection);
        } else {
            throw new \Exception("Invalid IMAP credentials. Connection failed.");
        }
    } catch (\Exception $e) {
        \Log::error('Error in authenticateImap: ' . $e->getMessage());
        throw $e; // Re-throw the exception to ensure it's caught by the calling method
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



}