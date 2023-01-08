<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\CodeGenerator;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/register', name: 'register')]
    public function register(
        UserPasswordHasherInterface $passwordHasher,
        Request $request,
        CodeGenerator $codeGenerator,
        Mailer $mailer
    ) {
        $user = new User();
        $form = $this->createForm(
            UserType::class,
            $user
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $password = $passwordHasher->hashPassword(
                $user,
                $user->getPlainPassword()
            );
            $user->setPassword($password);
            $user->setConfirmationCode($codeGenerator->getConfirmationCode());

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $mailer->sendConfirmationMessage($user);
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/confirm/{code}', name: 'email_confirmation')]
    public function confirmEmail(UserRepository $userRepository, string $code)
    {
        /** @var User $user */
        $user = $userRepository->findOneBy(['confirmationCode' => $code]);

        if ($user === null) {
            return new Response('404');
        }

        $user->setEnable(true);
        $user->setConfirmationCode('');

        $this->entityManager->flush();

        return $this->render('security/account_confirm.html.twig', [
            'user' => $user,
        ]);
    }

}
