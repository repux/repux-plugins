<?php

namespace Traits;

use App\Services\DateTimeFactoryService;
use Codeception\Util\Stub;

trait StubDateTimeFactoryServiceTrait
{
    public function stubDateTimeFactoryService(?\DateTime $now = null): DateTimeFactoryService
    {
        $properties = ['now' => $now ?: new \DateTime()];

        /** @var DateTimeFactoryService $stub */
        $stub = Stub::makeEmpty(DateTimeFactoryService::class, $properties, $this);

        return $stub;
    }
}
