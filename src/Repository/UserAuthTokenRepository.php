<?php

namespace App\Repository;

use App\Repository\Traits\RepositoryAliasTrait;
use Doctrine\ORM\EntityRepository;

class UserAuthTokenRepository extends EntityRepository
{
    use RepositoryAliasTrait;

    const ALIAS = 'userAuthToken';
}
