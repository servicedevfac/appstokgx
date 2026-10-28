<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockAlerte extends Notification implements ShouldQueue
{
    use Queueable;

    protected $produits; // tableau de produits

    public function __construct(array $produits)
    {
        $this->produits = $produits;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('⚠️ Alerte Stock Faible')
            ->view('emails.stock-alert', ['produits' => $this->produits]);
    }
}
