<?php

namespace Tests\Functional\Controller;

use App\Entity\User;
use App\Entity\UserAuthToken;
use App\Security\AuthTokenAuthenticator;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerCest
{
    const AUTH_GENERATE_MESSAGE_PATH = '/api/auth/generate-message';
    const AUTH_SIGN_IN_PATH = '/api/auth/sign-in';
    const AUTH_SIGN_OUT_PATH = '/api/auth/sign-out';

    const ADDRESS = '0x107a1dc2a74adb3c0fdddb20614b1bdabf35a8a8';
    const MESSAGE = '99896b43b25ff9691d0d3cfa7dc79ae0e2a8a12a3deeb741093c342602d339d2';
    const SIGNED_MESSAGE = '0xde274f9f5df24b22c86e65bec41830cba3b1f66d2a90b570e67d189d8c4f51ea55f32049f628f6eb6431408123d3568d4e8011899f30fbd2f34dae8bb7d684231b';

    public function generateMessage(\FunctionalTester $I)
    {
        $I->sendPOST(self::AUTH_GENERATE_MESSAGE_PATH, ['address' => self::ADDRESS]);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->canSeeResponseMatchesJsonType(['message' => 'string']);
        $response = json_decode($I->grabResponse(), true);
        $I->seeInRepository(User::class, ['ethAddress' => self::ADDRESS, 'authMessage' => $response['message']]);
    }

    public function generateMessageWithInvalidAddress(\FunctionalTester $I)
    {
        $address = '0xinvalidAddress';

        $I->sendPOST(self::AUTH_GENERATE_MESSAGE_PATH, ['address' => $address]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->cantSeeInRepository(User::class, ['ethAddress' => $address]);
    }

    public function generateMessageWithEmptyAddress(\FunctionalTester $I)
    {
        $I->sendPOST(self::AUTH_GENERATE_MESSAGE_PATH);
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
    }

    public function signIn(\FunctionalTester $I)
    {
        $I->haveInRepository(User::class, [
            'ethAddress' => self::ADDRESS,
            'authMessage' => self::MESSAGE,
        ]);

        $I->sendPOST(self::AUTH_SIGN_IN_PATH, ['address' => self::ADDRESS, 'signed_message' => self::SIGNED_MESSAGE]);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseMatchesJsonType(['token' => 'string']);
        $response = json_decode($I->grabResponse(), true);
        $I->seeInRepository(UserAuthToken::class, ['hash' => $response['token']]);
    }

    public function signInWithInvalidAddress(\FunctionalTester $I)
    {
        $I->haveInRepository(User::class, [
            'ethAddress' => self::ADDRESS,
            'authMessage' => self::MESSAGE,
        ]);

        $I->sendPOST(self::AUTH_SIGN_IN_PATH, ['address' => '0x123456789012345678901234567890', 'signed_message' => self::SIGNED_MESSAGE]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseMatchesJsonType(['errors' => []]);
    }

    public function signInWithNonExistingAddress(\FunctionalTester $I)
    {
        $I->haveInRepository(User::class, [
            'ethAddress' => self::ADDRESS,
            'authMessage' => self::MESSAGE,
        ]);

        $I->sendPOST(self::AUTH_SIGN_IN_PATH, ['address' => '0x1234567890123456789012345678901234567890', 'signed_message' => self::SIGNED_MESSAGE]);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseMatchesJsonType(['errors' => []]);
    }

    public function signInWithInvalidSignedMessage(\FunctionalTester $I)
    {
        $I->haveInRepository(User::class, [
            'ethAddress' => self::ADDRESS,
            'authMessage' => self::MESSAGE,
        ]);

        $I->sendPOST(self::AUTH_SIGN_IN_PATH, ['address' => self::ADDRESS, 'signed_message' => '0x1234567890']);

        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseMatchesJsonType(['errors' => []]);
    }

    public function signOut(\FunctionalTester $I)
    {
        $userId = $I->haveInRepository(User::class, [
            'ethAddress' => self::ADDRESS,
            'authMessage' => self::MESSAGE,
        ]);

        $user = $I->grabEntityFromRepository(User::class, ['id' => $userId]);

        $I->haveInRepository(UserAuthToken::class, [
            'hash' => 'token-hash',
            'expiresAt' => (new \DateTimeImmutable())->modify('+5 day'),
            'user' => $user
        ]);

        $I->seeInRepository(UserAuthToken::class, ['hash' => 'token-hash']);

        $I->haveHttpHeader(AuthTokenAuthenticator::TOKEN_HEADER, 'token-hash');
        $I->sendGET(self::AUTH_SIGN_OUT_PATH);

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->dontSeeInRepository(UserAuthToken::class, ['hash' => 'token-hash']);
    }
}
