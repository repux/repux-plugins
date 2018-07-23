<?php

namespace App\Controller;

use App\Controller\Traits\ApiControllerTrait;
use App\Entity\ShopifyStore;
use App\Form\ShopifyStoreType;
use App\Handler\ShopifyStoreHandler;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/shopify-store")
 *
 * @SWG\Tag(name="Shopify")
 */
class ShopifyStoreController extends FOSRestController
{
    use ApiControllerTrait;

    protected $handler;

    public function __construct(ShopifyStoreHandler $handler)
    {
        $this->handler = $handler;
    }

    /**
     * Get one ShopifyStore object
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a ShopifyStore object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="shopify_store", ref=@Model(type=ShopifyStore::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_NOT_FOUND,
     *     description="ShopifyStore object was not found"
     * )
     * @SWG\Parameter(
     *     name="id", in="path", type="integer", description="object ID"
     * )
     *
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     */
    public function getAction(string $id)
    {
        $entity = $this->handler->findOne($id);

        if (!$entity instanceof ShopifyStore) {
            throw new NotFoundHttpException();
        }

        return $this->createEntityView($entity);
    }

    /**
     * List ShopifyStore objects
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns list of ShopifyStore",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="shopify_stores",
     *             @SWG\Items(type="object", ref=@Model(type=ShopifyStore::class))
     *         )
     *     )
     * )
     *
     * @Rest\Get("")
     */
    public function getListAction()
    {
        $entities = $this->handler->getList();

        return $this->createEntityCollectionView(ShopifyStore::class, $entities, count($entities));
    }

    /**
     * Save a ShopifyStore entity object
     *
     * @SWG\Parameter(
     *     in="body",
     *     name="data",type="object",
     *     parameter="data",
     *     @SWG\Schema(
     *         @SWG\Property(property="shopify_store", ref=@Model(type=ShopifyStoreType::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns saved ShopifyStore object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="shopify_store", ref=@Model(type=ShopifyStore::class)),
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
        if ($this->handler->isAlreadyIntegrated()) {
            throw new BadRequestHttpException('Shopify Store already integrated.');
        }

        $shopifyStore = $this->handler->getCurrentUserShopifyStore();

        $form = $this->createForm(ShopifyStoreType::class, $shopifyStore);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            throw new BadRequestHttpException('No data submitted.');
        }

        if (!$form->isValid()) {
            return $this->view($form);
        }

        list($entity, $authorizeUrl) = $this->handler->create($form->getData());

        return $this->createEntityView($entity, Response::HTTP_CREATED, ['authorizeUrl' => $authorizeUrl]);
    }

    /**
     * Verify a ShopifyStore
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="TODO"
     * )
     * @SWG\Parameter(in="query", name="code", type="string")
     * @SWG\Parameter(in="query", name="shop", type="string")
     * @SWG\Parameter(in="query", name="state", type="string")
     * @SWG\Parameter(in="query", name="hmac", type="string")
     *
     * @Rest\Get("/store-verify", name="api_shopify_store_verify")
     */
    public function verifyAction(Request $request)
    {
        $shopifyStore = $this->handler->verify($request);

        return $this->createEntityView($shopifyStore);
    }
}
