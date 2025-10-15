<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class RadiusAuthenticator extends AbstractAuthenticator
{
    private RadiusUserProvider $radiusProvider;
    private UserProviderInterface $localProvider;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(RadiusUserProvider $radiusProvider, UserProviderInterface $localProvider, UrlGeneratorInterface $urlGenerator)
    {
        $this->radiusProvider = $radiusProvider;
        $this->localProvider = $localProvider;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        // 🟩 Tentative via RADIUS
        try {
            if ($this->radiusProvider->validateCredentials($username, $password)) {
                return new Passport(
                    new UserBadge($username, fn($id) => $this->radiusProvider->loadUserByIdentifier($id)),
                    new PasswordCredentials($password)
                );
            }
        } catch (\Throwable $e) {
            // on ignore l’erreur RADIUS pour le fallback
        }

        // 🟨 Fallback : compte local (admin/admin)
        try {
            $user = $this->localProvider->loadUserByIdentifier($username);
            if (password_verify($password, $user->getPassword())) {
                return new Passport(
                    new UserBadge($username, fn($id) => $user),
                    new PasswordCredentials($password)
                );
            }
        } catch (UserNotFoundException $e) {
            throw new AuthenticationException('Utilisateur inconnu.');
        }

        throw new AuthenticationException('Échec d’authentification.');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('error', $exception->getMessage());
        return new RedirectResponse($this->urlGenerator->generate('login'));
    }
}
