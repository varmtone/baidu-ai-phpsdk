<?php


namespace Bdai\http;


use Throwable;

class Exception extends \Exception
{
    private $_data= [];

    public function __construct($message = "", $code = 0, $data=[], Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->_data = $data;
    }

    /**
     * 异常附带的信息
     *
     * @return array
     */
    public function getData(){
        return $this->_data;
    }
}