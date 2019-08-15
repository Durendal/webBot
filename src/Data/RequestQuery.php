<?php
// Wrappers for Data with more meaningful names
namespace WebBot\WebBot;

use WebBot\WebBot as webBot;

class RequestQuery extends Data
{
    /**
     *  __toString()
     *
     *      Returns a printable string representation of the RequestQuery object.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("<RequestQuery - {$this->encodedData} >");
    }
}
