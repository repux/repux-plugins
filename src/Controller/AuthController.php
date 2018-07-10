<?php

namespace App\Controller;

use App\Entity\User;
use App\Exception\SignInException;
use App\Security\AuthKeyToken;
use App\Service\InputValidationService;
use App\Service\AuthService;
use App\Service\UserService;
use App\Validator\Constraints\EthereumAddress;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Route("/api/auth")
 *
 * @SWG\Tag(name="Authentication")
 * @Security(name="")
 */
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
     * Regenerate auth message for given wallet address
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="address", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns generated message",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="message", type="string"),
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_BAD_REQUEST,
     *     description="Invalid input data",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="errors")
     *     )
     * )
     *
     * @Rest\Post("/generate-message", name="auth_generate_message")
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
     * Sign in by sending a signed auth message
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="address", type="string"),
     *         @SWG\Property(property="signed_message", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns auth token",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="token", type="string"),
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_BAD_REQUEST,
     *     description="Invalid input data",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="errors")
     *     )
     * )
     *
     * @Rest\Post("/sign-in", name="auth_sign_in")
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
     * Invalidate current auth token
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns nothing"
     * )
     *
     * @Rest\Get("/sign-out", name="auth_sign_out")
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
