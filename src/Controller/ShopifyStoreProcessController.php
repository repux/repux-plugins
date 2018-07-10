<?php

namespace App\Controller;

use App\Controller\Traits\ApiControllerTrait;
use App\Entity\ShopifyStoreProcess;
use App\Form\ShopifyStoreProcessType;
use App\Handler\ShopifyStoreProcessHandler;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/shopify-store-process")
 * @SWG\Tag(name="Shopify")
 */
class ShopifyStoreProcessController extends FOSRestController
{
    use ApiControllerTrait;

    private $handler;

    public function __construct(ShopifyStoreProcessHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * List ShopifyStoreProcesse objects
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns list of ShopifyStoreProcesses",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="shopify_store_processes",
     *             @SWG\Items(type="object", ref=@Model(type=ShopifyStoreProcess::class))
     *         )
     *     )
     * )
     * @SWG\Parameter(
     *     name="shopifyStoreId", in="path", type="string", description="object ID"
     * )
     *
     * @Rest\Get("/by-store/{shopifyStoreId}")
     */
    public function getListAction(string $shopifyStoreId)
    {
        $shopifyStoreProcesses = $this->handler->getList($shopifyStoreId);

        return $this->createEntityCollectionView(
            ShopifyStoreProcess::class,
            $shopifyStoreProcesses,
            count($shopifyStoreProcesses)
        );
    }

    /**
     * Save ShopifyStoreProcess object
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",
     *     @SWG\Schema(
     *         @SWG\Property(property="shopify_store_process", ref=@Model(type=ShopifyStoreProcessType::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns saved ShopifyStoreProcess object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="shopify_store_process", ref=@Model(type=ShopifyStoreProcess::class)),
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
        $form = $this->createForm(ShopifyStoreProcessType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var ShopifyStoreProcess $shopifyStoreProcess */
                $shopifyStoreProcess = $form->getData();
                $this->handler->create($shopifyStoreProcess);

                return $this->createEntityView($shopifyStoreProcess);
            }

            return $this->view($form);
        }

        return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
    }
}
