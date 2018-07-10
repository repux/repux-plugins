<?php

namespace App\Entity\Model;

use App\Entity\User;

interface UserRelatedInterface
{
    /**
     * @return User|null
     */
    public function getUser();
}
