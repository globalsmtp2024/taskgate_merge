<?php

namespace Modules\Sms\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Modules\Sms\Entities\SmsNotificationSetting;
use Modules\Sms\Http\Traits\WhatsappMessageTrait;
use NotificationChannels\Telegram\TelegramMessage;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class AutoTaskReminder extends Notification implements ShouldQueue
{
    use Queueable, WhatsappMessageTrait;

    private $task;
    private $dueDate;

    private $message;

    private $user;

    private $smsSetting;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
        $company = $this->task->company;
        $this->dueDate = $task?->due_date?->format($company->date_format);
        $this->smsSetting = SmsNotificationSetting::where('slug', 'auto-task-reminder')->where('company_id', $company->id)->first();

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

        $this->message = __('email.reminder.subject')."\n".($this->task->heading).' #'.$this->task->task_short_code."\n".__('email.dueOn').': '.$this->dueDate;

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

        $this->toWhatsapp($notifiable, __($this->smsSetting->slug->translationString(), ['taskId' => $this->task->id, 'heading' => $this->task->heading, 'dueDate' => $this->dueDate]));

        if (sms_setting()->status) {
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
                ->variable('task_id', $this->task->id)
                ->variable('due_date', $this->dueDate)
                ->variable('heading', Str::limit($this->task->heading, 27, '...'));
        }
    }

    public function toTelegram($notifiable)
    {

        return TelegramMessage::create()
            // Optional recipient user id.
            ->to($notifiable->telegram_user_id)
            // Markdown supported.
            ->content($this->message)
            ->button(__('app.view').' '.__('app.task'), route('tasks.show', $this->task->id));
    }
}
