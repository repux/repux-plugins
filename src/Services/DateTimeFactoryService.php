<?php

namespace App\Services;

class DateTimeFactoryService
{
    public function now(): \DateTime
    {
        return new \DateTime();
    }
}
