<?php

namespace App\Notifications;

use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\CompanyEmailTemplateSetting;
use App\Models\Module;

class AutoReplyNotification extends BaseNotification
{
    protected $recipientEmail;
    protected $companyId;

    /**
     * Create a new notification instance.
     *
     * @param  string  $recipientEmail
     * @param  int  $companyId
     * @return void
     */
    public function __construct($recipientEmail, $companyId)
    {
        $this->recipientEmail = $recipientEmail;
        $this->companyId = $companyId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $module = Module::where('module_name', 'leads')->first();
        $template = CompanyEmailTemplateSetting::where('company_id', $this->companyId)
            ->where('module_id', $module->id)
            ->where('autoreply_template', 1)
            ->first();

        if ($template) {
            $mail = (new MailMessage)
                ->subject($template->subject)
                ->line($template->content);

            // Attach file if available
            if (!empty($template->file)) {
                $mail->attach($template->file);
            }

            return $mail;
        } else {
            return;
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'recipient_email' => $this->recipientEmail,
            'company_id' => $this->companyId,
        ];
    }
}