<?php

namespace App\DataFixtures\test;

use App\DataFixtures\ReferenceHelperTrait;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    use ReferenceHelperTrait;

    const REFERENCE_PREFIX = 'user';

    const FIRST_USER_ADDRESS = '0x0000000000000000000000000000000000000001';
    const SECOND_USER_ADDRESS = '0x0000000000000000000000000000000000000002';

    /**
     * @var ObjectManager
     */
    protected $om;

    protected $entities = [
        [
            'address' => self::FIRST_USER_ADDRESS,
            'auth_message' => 'message-1',
        ],
        [
            'address' => self::SECOND_USER_ADDRESS,
            'auth_message' => 'message-2',
        ],
    ];

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager)
    {
        $this->om = $manager;

        foreach ($this->entities as $data) {
            $this->add($data['address'], $data['auth_message']);
        }

        $this->om->flush();
    }

    protected function add(string $address, string $authMessage)
    {
        $entity = new User();
        $entity->setEthAddress($address);
        $entity->setAuthMessage($authMessage);

        $this->addReference(self::getReferenceName($entity->getEthAddress()), $entity);

        $this->om->persist($entity);
    }
}
