<?php

namespace Traits;

use Codeception\Util\Stub;
use Doctrine\ORM\EntityManager;

trait StubEntityManagerTrait
{
    public function stubEntityManager(array $repositories = [], array $properties = []): EntityManager
    {
        $getRepository = function ($entityName) use ($repositories) {
            return $repositories[$entityName];
        };

        $properties = array_merge($properties, ['getRepository' => $getRepository]);

        /** @var EntityManager $stub */
        $stub = Stub::makeEmpty(EntityManager::class, $properties, $this);

        return $stub;
    }
}
