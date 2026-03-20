<?php

namespace Modules\Partner\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ContentStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $status;      // 'approved' | 'rejected'
    public string $contentName;
    public string $contentType;
    public ?string $reason;

    public function __construct(string $status, string $contentName, string $contentType, ?string $reason = null)
    {
        $this->status      = $status;
        $this->contentName = $contentName;
        $this->contentType = $contentType;
        $this->reason      = $reason;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $isApproved = $this->status === 'approved';
        $subject    = $isApproved
            ? __('partner::partner.notif_approved_subject', ['name' => $this->contentName])
            : __('partner::partner.notif_rejected_subject', ['name' => $this->contentName]);

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting(__('partner::partner.notif_hello', ['name' => $notifiable->name ?? $notifiable->email]))
            ->line($isApproved
                ? __('partner::partner.notif_approved_line', ['name' => $this->contentName, 'type' => $this->contentType])
                : __('partner::partner.notif_rejected_line', ['name' => $this->contentName, 'type' => $this->contentType])
            );

        if (!$isApproved && $this->reason) {
            $mail->line(__('partner::partner.notif_rejection_reason') . ' : ' . $this->reason);
        }

        $mail->action(
            __('partner::partner.notif_view_dashboard'),
            url('/app/partner-dashboard')
        );

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'status'       => $this->status,
            'content_name' => $this->contentName,
            'content_type' => $this->contentType,
            'reason'       => $this->reason,
            'message'      => $this->status === 'approved'
                ? __('partner::partner.notif_approved_line', ['name' => $this->contentName, 'type' => $this->contentType])
                : __('partner::partner.notif_rejected_line', ['name' => $this->contentName, 'type' => $this->contentType]),
        ];
    }
}
