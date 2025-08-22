<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetSetByAdmin extends Notification
{
    use Queueable;

    public function __construct(private readonly string $plainPassword)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre mot de passe a été réinitialisé')
            ->greeting('Bonjour '.$notifiable->name)
            ->line('Un administrateur a réinitialisé votre mot de passe.')
            ->line('Nouveau mot de passe: '.$this->plainPassword)
            ->line('Par sécurité, pensez à le modifier après connexion.')
            ->action('Se connecter', url(route('login')))
            ->line('Si vous n’êtes pas à l’origine de cette demande, contactez un administrateur.');
    }
}
