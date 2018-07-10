<?php

namespace Tests\Unit\Security;

use App\Entity\User;
use App\Security\AuthKeyToken;
use App\Security\AuthTokenAuthenticator;
use Codeception\Stub;
use Codeception\TestCase\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AuthTokenAuthenticatorTest extends Test
{
    public function testSupports()
    {
        $service = new AuthTokenAuthenticator();
        $request = new Request();

        $this->assertFalse($service->supports($request));
        $request->headers->set(AuthTokenAuthenticator::TOKEN_HEADER, 'token');
        $this->assertTrue($service->supports($request));
    }

    public function testGetCredentials()
    {
        $service = new AuthTokenAuthenticator();
        $request = new Request();

        $request->headers->set(AuthTokenAuthenticator::TOKEN_HEADER, 'token');
        $this->assertEquals(['token' => 'token'], $service->getCredentials($request));
    }

    public function testGetUser()
    {
        $loadUserByUsername = Stub\Expected::once(function ($username) {
            $this->assertEquals('some-token', $username);

            return new User();
        });

        $service = new AuthTokenAuthenticator();
        /** @var UserProviderInterface $userProvider */
        $userProvider = Stub::makeEmpty(
            UserProviderInterface::class,
            ['loadUserByUsername' => $loadUserByUsername],
            $this
        );

        $this->assertEmpty($service->getUser([], $userProvider));

        $credentials = ['token' => 'some-token'];
        $this->assertInstanceOf(User::class, $service->getUser($credentials, $userProvider));
    }

    public function testOnAuthenticationSuccess()
    {
        /** @var UserInterface $user */
        $user = Stub::makeEmpty(UserInterface::class);
        $providerKey = 'provider';
        $roles = [];

        $request = new Request();
        $token = new AuthKeyToken($user, $providerKey, $roles);

        $service = new AuthTokenAuthenticator();

        $service->onAuthenticationSuccess($request, $token, $providerKey);
        $this->assertEmpty($token->getHash());

        $request->headers->set(AuthTokenAuthenticator::TOKEN_HEADER, 'token');
        $service->onAuthenticationSuccess($request, $token, $providerKey);
        $this->assertEquals('token', $token->getHash());
    }
}
