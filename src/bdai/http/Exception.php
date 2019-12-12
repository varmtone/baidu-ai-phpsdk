<?php
/**
 * 异常处理
 * Copyright (c) 2017 varmtone.com, Inc. All Rights Reserved
 *
 * @author river_he@varmtone.com
 */
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
