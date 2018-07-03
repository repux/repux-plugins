<?php

namespace App\Security;

use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class AuthKeyToken extends PostAuthenticationGuardToken
{
    protected $hash;

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    public function setHash(string $hash = null)
    {
        $this->hash = $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->hash, parent::serialize()));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->hash, $parentStr) = unserialize($serialized);
        parent::unserialize($parentStr);
    }
}
