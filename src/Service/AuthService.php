<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserAuthToken;
use App\Exception\SignInException;
use Ethereum\EcRecover;

class AuthService
{
    private $userService;

    private $userAuthTokenService;

    public function __construct(UserService $userService, UserAuthTokenService $userAuthTokenService)
    {
        $this->userService = $userService;
        $this->userAuthTokenService = $userAuthTokenService;
    }

    public function generateMessageFor(User $user): string
    {
        $randomBytes = openssl_random_pseudo_bytes(32);
        $message = sprintf('%s.%s', bin2hex($randomBytes), $user->getEthAddress());

        return hash('sha512', $message);
    }

    /**
     * @param string $address
     * @param string $signedMessage
     * @return UserAuthToken
     * @throws SignInException
     */
    public function signIn(string $address, string $signedMessage): UserAuthToken
    {
        $user = $this->userService->getUserByAddress($address);

        if (!$user) {
            throw new SignInException('Address not found.');
        }

        try {
            $valid = EcRecover::personalVerifyEcRecover($user->getAuthMessage(), $signedMessage, $address);
        } catch (\Exception $error) {
            throw new SignInException($error->getMessage(), 0, $error);
        }

        if (!$valid) {
            throw new SignInException('Invalid signature.');
        }

        return $this->userAuthTokenService->generateTokenFor($user);
    }

    /**
     * @param string $hash
     */
    public function signOut(string $hash)
    {
        $this->userAuthTokenService->invalidateToken($hash);
    }
}
