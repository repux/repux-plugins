<?php

namespace App\Service;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    /**
     * @param string|array $commandline
     *
     * @return Process
     */
    public function create($commandline): Process
    {
        return new Process($commandline);
    }
}
