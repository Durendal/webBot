<?php

namespace WebBot\WebBot\Headers;

class Header
{
    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    public function __toString()
    {
        return "{$this->key}: {$this->value}";
    }

    public function string()
    {
        return $this->__toString();
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getValue()
    {
        return $this->value;
    }
}
