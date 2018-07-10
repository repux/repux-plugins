<?php

namespace App\Shopify\Api;

class GenericResource implements \ArrayAccess
{
    private $data = [];

    public function hydrate(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->data[$offset]);
        }
    }

    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param string $param
     * @param mixed $default
     * @return mixed
     */
    public function get(string $param, $default = null)
    {
        return $this->offsetExists($param) ? $this->offsetGet($param) : $default;
    }

    public static function create(array $data = []): GenericResource
    {
        $entity = new static();
        $entity->hydrate($data);
        return $entity;
    }
}
