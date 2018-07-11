<?php

namespace App\Controller;

use App\Controller\Traits\ApiControllerTrait;
use App\Entity\ChannelAmazon;
use App\Form\ChannelAmazonType;
use App\Handler\ChannelAmazonHandler;
use App\Service\AmazonMWS\CheckAmazonCredentialsService;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/amazon")
 *
 * @SWG\Tag(name="Amazon")
 */
class ChannelAmazonController extends Controller
{
    use ApiControllerTrait;

    private $handler;

    private $checkAmazonCredentialsService;

    public function __construct(
        ChannelAmazonHandler $handler,
        CheckAmazonCredentialsService $checkAmazonCredentialsService
    ) {
        $this->handler = $handler;
        $this->checkAmazonCredentialsService = $checkAmazonCredentialsService;
    }

    /**
     * Get one ChannelAmazon object
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a ChannelAmazon object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="channel_amazon", ref=@Model(type=ChannelAmazon::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_NOT_FOUND,
     *     description="ChannelAmazon object was not found"
     * )
     * @SWG\Parameter(
     *     name="id", in="path", type="string", description="object ID"
     * )
     *
     * @Rest\Get("")
     */
    public function getListAction()
    {
        $channels = $this->handler->getList();

        return $this->createEntityCollectionView(
            ChannelAmazon::class,
            $channels,
            count($channels)
        );
    }

    /**
     * Save a ChannelAmazon entity object
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",type="object",
     *     parameter="data",
     *     @SWG\Schema(
     *         @SWG\Property(property="channel_amazon", ref=@Model(type=ChannelAmazonType::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns saved ChannelAmazon object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="channel_amazon", ref=@Model(type=ChannelAmazon::class)),
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
        $form = $this->createForm(ChannelAmazonType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ChannelAmazon $channelAmazon */
            $channelAmazon = $form->getData();

            if (!$this->checkAmazonCredentialsService->isAuth($channelAmazon)) {
                return new JsonResponse('Bad amazon credential.', Response::HTTP_BAD_REQUEST);
            }

            $channelAmazon->setAuthenticated(true);
            $this->handler->create($channelAmazon);

            return $this->createEntityView($channelAmazon);
        }

        return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
    }
}
