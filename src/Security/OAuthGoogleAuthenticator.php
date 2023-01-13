<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthGoogleAuthenticator extends SocialAuthenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $em;
    private UserRepository $userRepository;

    /**
     * @param ClientRegistry $clientRegistry
     * @param EntityManagerInterface $em
     * @param UserRepository $userRepository
     */
    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        UserRepository $userRepository
    )
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    /**
     * @param Request $request
     * @param AuthenticationException|null $authenticationException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authenticationException = null): RedirectResponse
    {
        return new RedirectResponse(
            '/connect',
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->attributes->get('_route') === 'google_auth';
    }

    /**
     * @param Request $request
     * @return AccessToken/mixed
     */
    public function getCredentials(Request $request): AccessToken
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    /**
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     * @return User/null/UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider): User
    {
        /**
         * @var GoogleUser $googleUser
         */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        /**
         * @var User $existingUser
         */
        $existingUser = $this->userRepository
            ->findOneBy(['clientId' => $googleUser->getId()]);

        if ($existingUser) {
            return $existingUser;
        }

        $email = $googleUser->getEmail();

        /**
         * @var User $user
         */
        $user = $this->userRepository
            ->findOneBy(['email' => $email]);

        if (!$user) {
            $user = User::fromGoogleRequest(
                $googleUser->getId(),
                $email,
                $googleUser->getName()
            );

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    /**
     * @return OAuth2ClientInterface
     */
    public function getGoogleClient(): OAuth2ClientInterface
    {
        return $this->clientRegistry->getClient('google');
    }
}
