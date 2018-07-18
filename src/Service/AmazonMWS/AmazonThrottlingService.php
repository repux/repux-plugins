<?php

namespace App\Service\AmazonMWS;

use App\Exception\AmazonThrottleException;

class AmazonThrottlingService
{
    protected $throttlingManager;

    public function __construct(AmazonThrottlingManager $throttlingManager)
    {
        $this->throttlingManager = $throttlingManager;
    }

    /**
     * @throws AmazonThrottleException
     */
    public function processThrottling(
        string $requestId,
        int $requestedItemId,
        int $restoreRate,
        int $quota,
        int $hourlyQuota = 0
    ) {
        $currentHourlyQuota = $this->throttlingManager->getHourlyQuota($requestId);
        if ($hourlyQuota && $currentHourlyQuota >= $hourlyQuota) {
            throw new AmazonThrottleException("{$requestId} hourly quota of {$hourlyQuota} exceeded");
        }

        $currentQuota = $this->throttlingManager->getThrottlingQueueSize($requestId, $quota);
        if ($currentQuota >= $quota) {
            throw new AmazonThrottleException("{$requestId} throttled (Quota = {$quota} Restore rate = {$restoreRate})");
        }

        $this->throttlingManager->pushRequestToThrottlingQueue($requestId, $requestedItemId, $restoreRate * $quota,
            $hourlyQuota);
    }
}
