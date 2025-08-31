<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\TravelRequest;

class TravelRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TravelRequest $travelRequest, public string $status) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Status do pedido de viagem alterado')
            ->greeting('Olá, ' . $notifiable->name)
            ->line("Seu pedido de viagem para {$this->travelRequest->destination} foi {$this->status}.")
            ->action('Ver pedido', url('/viagens/' . $this->travelRequest->id))
            ->line('Obrigado por usar o sistema de viagens!');
    }
}
