<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseResetPassword
{
    public function toMail($notifiable)
    {
        // PROD TURN THIS TO frontend url
        $front_end_url = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $front_end_url . '/auth/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
        
        return (new MailMessage)
            ->subject('Reset Password Notification')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, no further action is required.');
    }
}
