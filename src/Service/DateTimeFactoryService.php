<?php

namespace App\Service;

class DateTimeFactoryService
{
    public function now(): \DateTime
    {
        return new \DateTime();
    }
}
