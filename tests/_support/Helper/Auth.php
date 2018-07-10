<?php

namespace Helper;

use App\Entity\User;
use App\Entity\UserAuthToken;
use App\Security\AuthTokenAuthenticator;
use Codeception\Module\Doctrine2;
use Codeception\Module\REST;

class Auth extends \Codeception\Module
{
    public function amTokenAuthenticated(string $address)
    {
        /** @var REST $rest */
        $rest = $this->getModule('REST');
        /** @var Doctrine2 $doctrine */
        $doctrine = $this->getModule('Doctrine2');

        $user = $doctrine->grabEntityFromRepository(User::class, ['ethAddress' => $address]);
        $token = $address;

        $doctrine->haveInRepository(
            UserAuthToken::class,
            [
                'user' => $user,
                'hash' => $token,
                'expiresAt' => new \DateTimeImmutable('2050-01-01 00:00:00'),
            ]
        );
        $rest->haveHttpHeader(AuthTokenAuthenticator::TOKEN_HEADER, $token);
    }

    public function amNotTokenAuthenticated()
    {
        /** @var REST $rest */
        $rest = $this->getModule('REST');
        $rest->deleteHeader(AuthTokenAuthenticator::TOKEN_HEADER);
    }
}
