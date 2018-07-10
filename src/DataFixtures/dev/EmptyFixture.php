<?php

namespace App\DataFixtures\dev;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class EmptyFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
    }
}
