<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\SignInException;
use App\Security\AuthKeyToken;
use App\Services\InputValidationService;
use App\Services\AuthService;
use App\Services\UserService;
use App\Validator\Constraints\EthereumAddress;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AuthController extends Controller
{
    private $inputValidationService;

    private $userService;

    private $authService;

    private $tokenStorage;

    public function __construct(
        InputValidationService $inputValidationService,
        UserService $userService,
        AuthService $authService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->inputValidationService = $inputValidationService;
        $this->userService = $userService;
        $this->authService = $authService;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @Route("/auth/generate-message", name="auth_generate_message", methods={"POST"})
     */
    public function generateMessage(Request $request)
    {
        $address = strtolower($request->request->get('address'));

        $data = ['address' => trim($address)];
        $fields = [
            'address' => [new Assert\NotBlank(), new EthereumAddress()]
        ];

        $errors = $this->inputValidationService->validateData($data, $fields);
        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->regenerateMessageForAddress($data['address']);

        return $this->json(['message' => $user->getAuthMessage()]);
    }

    /**
     * @Route("/auth/sign-in", name="auth_sign_in", methods={"POST"})
     */
    public function signIn(Request $request)
    {
        $constraints = [
            'address' => [new Assert\NotBlank(), new EthereumAddress()],
            'signed_message' => new Assert\NotBlank()
        ];

        $errors = $this->inputValidationService->validateData($request->request->all(), $constraints);

        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {
            $token = $this->authService->signIn(
                strtolower($request->request->get('address')),
                $request->request->get('signed_message')
            );
        } catch (SignInException $error) {
            return $this->json(['errors' => [$error->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['token' => $token->getHash()]);
    }

    /**
     * @Route("/auth/sign-out", name="auth_sign_out", methods={"GET"})
     */
    public function signOut()
    {
        $token = $this->tokenStorage->getToken();

        if ($token instanceof AuthKeyToken) {
            $this->authService->signOut($token->getHash());
        }

        return new Response();
    }

    private function regenerateMessageForAddress(string $address): User
    {
        $user = $this->userService->getUserByAddress($address);
        if (!$user) {
            $user = $this->userService->createUserByAddress($address);
        }

        $message = $this->authService->generateMessageFor($user);
        $user->setAuthMessage($message);
        $this->userService->saveUser($user);

        return $user;
    }
}
