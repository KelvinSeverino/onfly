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

    public function __construct(public TravelRequest $travelRequest, public string $code) {}

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = $this->getSubject();
        $message = $this->getMessage();

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Olá, ' . $notifiable->name)
            ->line($message)
            ->line('Destino: ' . $this->travelRequest->destination)
            ->line('Data de partida: ' . date('d/m/Y H:i', strtotime($this->travelRequest->departure_date)))
            ->line('Data de retorno: ' . date('d/m/Y H:i', strtotime($this->travelRequest->return_date)))
            ->action('Ver pedido', url('/api/viagens/' . $this->travelRequest->id))
            ->line('Obrigado por usar o sistema de viagens!');
    }

    private function getSubject(): string
    {
        return match ($this->code) {
            'A'             => 'Sua solicitação de viagem foi aprovada!',
            'C'             => 'Sua solicitação de viagem foi cancelada',
            default         => 'Status do pedido de viagem alterado',
        };
    }

    private function getMessage(): string
    {
        return match ($this->code) {
            'A'             => 'Parabéns! Sua viagem foi aprovada.',
            'C'             => 'Infelizmente, sua viagem foi cancelada.',
            default         => "Seu pedido de viagem foi alterado.",
        };
    }
}
