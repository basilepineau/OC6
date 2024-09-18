<?php
// src/EventListener/TimezoneListener.php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class TimezoneListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        // Définir le fuseau horaire par défaut
        date_default_timezone_set('Europe/Paris');
    }
}
