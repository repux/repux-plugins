<?php

namespace App\Service\AmazonMWS;

use Predis\Client as RedisClient;
use Redis;

class AmazonThrottlingManager
{
    const THROTTLING_KEY_PREFIX = 'amazon_throttling';
    const HOURLY_QUOTA_KEY = 'hourly_quota';
    const KEY_SEPARATOR = ':';

    /** @var Redis */
    protected $redis;

    /**
     * @param RedisClient $redis
     */
    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @param string $requestId
     * @param string $requestedItemId
     * @param int $ttl //seconds
     * @param int $hourlyQuota
     */
    public function pushRequestToThrottlingQueue($requestId, $requestedItemId, $ttl, $hourlyQuota = 0)
    {
        $key = $this->formKey([$requestId, $requestedItemId]);
        $this->redis->setex($key, $ttl, '');

        if ($hourlyQuota) {
            $hourlyQuotaKey = $this->formKey([$requestId, self::HOURLY_QUOTA_KEY]);
            if ($this->redis->exists($hourlyQuotaKey)) {
                $this->redis->incr($hourlyQuotaKey);
            } else {
                $this->redis->setex($hourlyQuotaKey, 3600, '1');
            }
        }
    }

    /**
     * @param $requestId
     * @param int $quota
     *
     * @return int
     */
    public function getThrottlingQueueSize($requestId, $quota = 100)
    {
        $key = $this->formKey([$requestId, '*']);

// Commented out because of error: "snc_redis.ERROR: Command "SCAN  amazon_throttling:ListOrderItems[...] 2" failed (ERR syntax error)"
//
//        $iterator = null;
//        return count($this->redis->scan($iterator, $key, $quota));

        return count($this->redis->keys($key)); // TODO: Don't use redis `keys` command
    }

    /**
     * @param $requestId
     *
     * @return bool|string
     */
    public function getHourlyQuota($requestId)
    {
        $key = $this->formKey([$requestId, self::HOURLY_QUOTA_KEY]);

        return $this->redis->get($key);
    }

    /**
     * @param array $keyParts
     *
     * @return string
     */
    private function formKey(array $keyParts)
    {
        array_unshift($keyParts, self::THROTTLING_KEY_PREFIX);

        return implode(self::KEY_SEPARATOR, $keyParts);
    }
}
