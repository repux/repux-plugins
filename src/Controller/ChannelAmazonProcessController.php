<?php

namespace App\Controller;

use App\Controller\Traits\ApiControllerTrait;
use App\Entity\ChannelAmazonProcess;
use App\Form\ChannelAmazonProcessType;
use App\Handler\ChannelAmazonProcessHandler;
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
class ChannelAmazonProcessController extends FOSRestController
{
    use ApiControllerTrait;

    private $handler;

    public function __construct(
        ChannelAmazonProcessHandler $handler
    ) {
        $this->handler = $handler;
    }

    /**
     * List ChannelAmazonProcess objects
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns list of ChannelAmazonProcesses",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="channel_amazon_processes",
     *             @SWG\Items(type="object", ref=@Model(type=ChannelAmazonProcess::class))
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
            ChannelAmazonProcess::class,
            $channelAmazonProcesses,
            count($channelAmazonProcesses)
        );
    }

    /**
     * Save ChannelAmazonProcess object
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",
     *     @SWG\Schema(
     *         @SWG\Property(property="channel_amazon_process", ref=@Model(type=ChannelAmazonProcessType::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns saved ChannelAmazonProcess object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="channel_amazon_process", ref=@Model(type=ChannelAmazonProcess::class)),
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
        $form = $this->createForm(ChannelAmazonProcessType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var ChannelAmazonProcess $channelAmazonProcess */
                $channelAmazonProcess = $form->getData();
                $this->handler->create($channelAmazonProcess);

                return $this->createEntityView($channelAmazonProcess);
            }

            return $this->view($form);
        }

        return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
    }
}
