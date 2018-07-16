<?php

namespace App\Controller;

use App\Controller\Traits\ApiControllerTrait;
use App\Entity\AmazonChannel;
use App\Form\AmazonChannelType;
use App\Handler\AmazonChannelHandler;
use App\Service\AmazonMWS\CheckAmazonCredentialsService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/amazon")
 *
 * @SWG\Tag(name="Amazon")
 */
class AmazonChannelController extends FOSRestController
{
    use ApiControllerTrait;

    private $handler;

    private $checkAmazonCredentialsService;

    public function __construct(
        AmazonChannelHandler $handler,
        CheckAmazonCredentialsService $checkAmazonCredentialsService
    ) {
        $this->handler = $handler;
        $this->checkAmazonCredentialsService = $checkAmazonCredentialsService;
    }

    /**
     * Get all AmazonChannel objects
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns list of AmazonChannel objects",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="amazon_channel",
     *             @SWG\Items(type="object", ref=@Model(type=AmazonChannel::class))
     *         )
     *     )
     * )
     *
     * @Rest\Get("")
     */
    public function getListAction()
    {
        $channels = $this->handler->getList();

        return $this->createEntityCollectionView(
            AmazonChannel::class,
            $channels,
            count($channels)
        );
    }

    /**
     * Save a AmazonChannel entity object
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",type="object",
     *     parameter="data",
     *     @SWG\Schema(
     *         @SWG\Property(property="amazon_channel", ref=@Model(type=AmazonChannelType::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns saved AmazonChannel object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="amazon_channel", ref=@Model(type=AmazonChannel::class)),
     *         @SWG\Property(property="authorizeUrl", type="string")
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_BAD_REQUEST,
     *     description="Invalid input data"
     * )
     *
     * @Rest\Post("")
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(AmazonChannelType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var AmazonChannel $channelAmazon */
            $channelAmazon = $form->getData();

            if (!$this->checkAmazonCredentialsService->isAuth($channelAmazon)) {
                return new JsonResponse('Bad amazon credential.', Response::HTTP_BAD_REQUEST);
            }

            $channelAmazon->setAuthenticated(true);
            $this->handler->create($channelAmazon);

            return $this->createEntityView($channelAmazon);
        }

        return $this->view($form);
    }
}
