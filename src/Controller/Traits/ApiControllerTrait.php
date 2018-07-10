<?php

namespace App\Controller\Traits;

use Doctrine\Common\Util\Inflector;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Util\StringUtil;

trait ApiControllerTrait
{
    protected function createEntityCollectionView(
        string $className,
        array $collection,
        int $total,
        int $statusCode = null
    ): View {
        $collectionkey = Inflector::pluralize(StringUtil::fqcnToBlockPrefix($className));
        $data = [
            'meta' => ['total' => $total],
            $collectionkey => $collection,
        ];

        return View::create($data, $statusCode);
    }

    protected function createEntityView($entity, int $statusCode = null, array $additionalData = null): View
    {
        $entityKey = StringUtil::fqcnToBlockPrefix(get_class($entity));
        $data = [$entityKey => $entity];

        if (!empty($additionalData)) {
            $data = array_merge($data, $additionalData);
        }

        return View::create($data, $statusCode);
    }
}
