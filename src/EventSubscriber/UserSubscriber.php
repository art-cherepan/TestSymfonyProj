<?php

namespace App\EventSubscriber;

use App\Event\RegisteredUserEvent;
use App\Service\Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RegisteredUserEvent::NAME => 'onUserRegister'
        ];
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function onUserRegister(RegisteredUserEvent $registeredUserEvent)
    {
        $this->mailer->sendConfirmationMessage($registeredUserEvent->getRegisteredUser());
    }
}
