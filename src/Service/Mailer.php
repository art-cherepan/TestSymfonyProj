<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class Mailer
{
    private $mailer;
    private $twig;

    private const FROM_ADDRESS = 'art.cherepan@gmail.com';

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }


    /**
     * @throws TransportExceptionInterface
     */
    public function sendConfirmationMessage(User $user): void
    {
        $messageBody = $this->twig->render('security/confirmation.html.twig', [
            'user' => $user,
        ]);

        //$transport = Transport::fromDsn('smtp://localhost');
        //$mailer = new \Symfony\Component\Mailer\Mailer($transport);

        $email = (new Email())
            ->from(self::FROM_ADDRESS)
            ->to('victoria.temlyantseva@gmail.com')
            ->subject('Вы успешно прошли регистрацию!')
            ->text('Тестовое письмо')
            ->html($messageBody);

       // dd($email);
        $this->mailer->send($email);

//        $email = (new TemplatedEmail())
//            ->from(self::FROM_ADDRESS)
//            ->to(new Address($user->getEmail()))
//            ->subject('Thanks for signing up!')
//
//            // path of the Twig template to render
//            ->htmlTemplate('security/confirmation.html.twig')
//
//            // pass variables (name => value) to the template
//            ->context([
//                'user' => $user,
//            ])
//        ;
//
//        $this->mailer->send($email);
    }
}
