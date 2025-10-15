<?php

namespace App\Security;

use Dapphp\Radius\Radius;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class RadiusUserProvider implements UserProviderInterface
{
    private string $radiusServer;
    private string $radiusSecret;
    private int $radiusPort;
    private bool $useRadius;

    public function __construct(string $server, string $secret, int $port = 1812, bool $useRadius = false)
    {
        $this->radiusServer = $server;
        $this->radiusSecret = $secret;
        $this->radiusPort = $port;
        $this->useRadius = $useRadius;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new User($identifier, null, ['ROLE_USER']);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    public function validateCredentials(string $username, string $password): bool
    {
        // 🚧 Si le RADIUS est désactivé → toujours refuser (on passe sur le local)
        if (!$this->useRadius) {
            return false;
        }

        $radius = new Radius();
        $radius->setServer($this->radiusServer);
        $radius->setSecret($this->radiusSecret);
        $radius->setPort($this->radiusPort);
        $radius->setNasIpAddress('127.0.0.1');

        try {
            return $radius->accessRequest($username, $password) === true;
        } catch (\Throwable $e) {
            throw new AuthenticationException('Erreur RADIUS : ' . $e->getMessage());
        }
    }
}
