<?php

namespace App\Controller;

use App\Controller\Traits\ApiControllerTrait;
use App\Entity\AmazonChannelProcess;
use App\Form\AmazonChannelProcessType;
use App\Handler\AmazonChannelProcessHandler;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/amazon-process")
 *
 * @SWG\Tag(name="Amazon")
 */
class AmazonChannelProcessController extends FOSRestController
{
    use ApiControllerTrait;

    private $handler;

    public function __construct(
        AmazonChannelProcessHandler $handler
    ) {
        $this->handler = $handler;
    }

    /**
     * List AmazonChannelProcess objects
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns list of AmazonChannelProcesses",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="amazon_channel_processes",
     *             @SWG\Items(type="object", ref=@Model(type=AmazonChannelProcess::class))
     *         )
     *     )
     * )
     * @SWG\Parameter(
     *     name="channelAmazonId", in="path", type="string", description="object ID"
     * )
     *
     * @Rest\Get("/by-channel/{channelAmazonId}")
     */
    public function getListAction($channelAmazonId, Request $request)
    {
        $channelAmazonProcesses = $this->handler->getList($channelAmazonId);

        return $this->createEntityCollectionView(
            AmazonChannelProcess::class,
            $channelAmazonProcesses,
            count($channelAmazonProcesses)
        );
    }

    /**
     * Save AmazonChannelProcess object
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",
     *     @SWG\Schema(
     *         @SWG\Property(property="amazon_channel_process", ref=@Model(type=AmazonChannelProcessType::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns saved AmazonChannelProcess object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="amazon_channel_process", ref=@Model(type=AmazonChannelProcess::class)),
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
        $form = $this->createForm(AmazonChannelProcessType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var AmazonChannelProcess $channelAmazonProcess */
                $channelAmazonProcess = $form->getData();
                $this->handler->create($channelAmazonProcess);

                return $this->createEntityView($channelAmazonProcess);
            }

            return $this->view($form);
        }

        return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
    }
}
