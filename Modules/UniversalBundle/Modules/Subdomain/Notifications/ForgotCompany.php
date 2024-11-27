<?php

namespace Modules\Subdomain\Notifications;

use App\Models\Company;
use App\Models\GlobalSetting;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ForgotCompany extends BaseNotification
{

    protected $company;

    /**
     * Create a new notification instance.
     *
     * @param Company $company
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
        $this->settings = GlobalSetting::first();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($notifiable->email != '') {
            $via = ['mail'];
        }

        return $via;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $url = route('login');
        $url = getDomainSpecificUrl($url, $this->company);


        return parent::build()
            ->subject(__('subdomain::app.email.subject'))
            ->line(__('subdomain::app.email.line2') . $this->company->company_name)
            ->line(__('subdomain::app.email.noteLoginUrl') . ": [**$url**]($url) ")
            ->action(__('app.login'), $url)
            ->line(__('subdomain::app.email.thankYou'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }

}
