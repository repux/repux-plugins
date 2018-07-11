<?php

namespace App\Controller;

use App\Controller\Traits\ApiControllerTrait;
use App\Entity\DataFile;
use App\Handler\DataFileHandler;
use App\Request\ParamsHandler\DataFileParamsHandler;
use App\Service\CurrentUserService;
use App\Service\FileStorageService;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/data-file")
 *
 * @SWG\Tag(name="Data files")
 */
class DataFileController extends FOSRestController
{
    use ApiControllerTrait;

    protected $handler;

    protected $currentUserService;

    protected $fileStorageService;

    public function __construct(
        DataFileHandler $handler,
        CurrentUserService $currentUserService,
        FileStorageService $fileStorageService
    ) {
        $this->handler = $handler;
        $this->currentUserService = $currentUserService;
        $this->fileStorageService = $fileStorageService;
    }

    /**
     * Get DataFile file content
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a DataFile file content"
     * )
     * @SWG\Response(
     *     response=Response::HTTP_NOT_FOUND,
     *     description="DataFile object was not found"
     * )
     * @SWG\Parameter(
     *     name="id", in="path", type="integer", description="object ID"
     * )
     *
     * @Rest\Get("/download/{id}", requirements={"id"="\d+"})
     */
    public function downloadAction(string $id)
    {
        $entity = $this->handler->findOne($id);

        if (!$entity instanceof DataFile) {
            throw new NotFoundHttpException();
        }

        $response = new BinaryFileResponse($this->fileStorageService->getPath($entity));
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $entity->getOriginalName());
        $response->headers->set('Content-type', $entity->getFileMimeType());

        return $response;
    }

    /**
     * Get one DataFile object
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns a DataFile object",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="data_file", ref=@Model(type=DataFile::class))
     *     )
     * )
     * @SWG\Response(
     *     response=Response::HTTP_NOT_FOUND,
     *     description="DataFile object was not found"
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

        if (!$entity instanceof DataFile) {
            throw new NotFoundHttpException();
        }

        return $this->createEntityView($entity);
    }

    /**
     * List stored data files objects
     *
     * @SWG\Response(
     *     response=Response::HTTP_OK,
     *     description="Returns list of DataFile objects",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(
     *             property="data_file",
     *             @SWG\Items(type="object", ref=@Model(type=DataFile::class))
     *         )
     *     )
     * )
     *
     * @Rest\Get("")
     */
    public function getListAction(Request $request)
    {
        $requestParamsHandler = new DataFileParamsHandler($request);

        return $this->createEntityCollectionView(
            DataFile::class,
            $this->handler->findAll($requestParamsHandler),
            $this->handler->countAll($requestParamsHandler)
        );
    }
}
