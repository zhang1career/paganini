<?php

namespace Paganini\Exceptions;

use Paganini\Constants\ResponseConstant;

class IllegalArgumentException extends BaseException
{
    protected static int $respCode = ResponseConstant::RET_ERR_PARAM;
}

