<?php
// Wrappers for Data with more meaningful names
namespace WebBot\WebBot;

use WebBot\WebBot as webBot;

class RequestData extends Data
{
    /**
     *  __toString()
     *
     *      Returns a printable string representation of the RequestData object.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("<RequestData - {$this->encodedData} >");
    }
}
