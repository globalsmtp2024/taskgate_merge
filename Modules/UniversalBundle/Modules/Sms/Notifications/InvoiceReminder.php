<?php

namespace Modules\Sms\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;
use Modules\Sms\Entities\SmsNotificationSetting;
use Modules\Sms\Http\Traits\WhatsappMessageTrait;
use NotificationChannels\Telegram\TelegramMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class InvoiceReminder extends Notification implements ShouldQueue
{
    use Queueable, WhatsappMessageTrait;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $smsSetting;

    private $company;

    private $invoice;

    private $message;

    private $dueDate;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;

        $this->company = $this->invoice->company;
        $this->smsSetting = SmsNotificationSetting::where('slug', 'invoice-reminder')->where('company_id', $this->company->id)->first();

        $this->dueDate = $invoice->due_date?->format($this->company->date_format);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($this->smsSetting && $this->smsSetting->send_sms != 'yes') {
            return [];
        }

        $this->message = __('email.invoiceReminder.subject').' - '.$this->invoice->invoice_number."\n".__('email.invoiceReminder.text').' '.$this->dueDate;

        $via = [];

        if (! is_null($notifiable->mobile) && ! is_null($notifiable->country_id)) {
            if (sms_setting()->status) {
                array_push($via, TwilioChannel::class);
            }

            if (sms_setting()->nexmo_status) {
                array_push($via, 'vonage');
            }

            if ($this->smsSetting->msg91_flow_id && sms_setting()->msg91_status) {
                array_push($via, 'msg91');
            }

        }

        if (sms_setting()->telegram_status && $notifiable->telegram_user_id) {
            array_push($via, 'telegram');
        }

        return $via;
    }

    public function toTwilio($notifiable)
    {
        $this->toWhatsapp($notifiable, __($this->smsSetting->slug->translationString(), ['invoiceNumber' => $this->invoice->invoice_number, 'dueDate' => $this->dueDate]));

        if ($this->smsSetting->status) {
            return (new TwilioSmsMessage)
                ->content($this->message);
        }
    }

    //phpcs:ignore
    public function toVonage($notifiable)
    {
        if (sms_setting()->nexmo_status) {
            return (new VonageMessage)
                ->content($this->message)->unicode();
        }
    }

    //phpcs:ignore
    public function toMsg91($notifiable)
    {
        if ($this->smsSetting->msg91_flow_id && sms_setting()->msg91_status) {
            return (new \Craftsys\Notifications\Messages\Msg91SMS)
                ->flow($this->smsSetting->msg91_flow_id)
                ->variable('invoice_number', $this->invoice->invoice_number)
                ->variable('due_date', $this->dueDate);
        }
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
            // Optional recipient user id.
            ->to($notifiable->telegram_user_id)
            // Markdown supported.
            ->content($this->message)
            ->button(__('email.invoiceReminder.action'), route('invoices.show', $this->invoice->id));
    }
}
