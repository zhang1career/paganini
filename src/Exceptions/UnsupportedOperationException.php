<?php

namespace Paganini\Exceptions;

use Paganini\Constants\ResponseConstant;

class UnsupportedOperationException extends BaseException
{
    protected static int $respCode = ResponseConstant::RET_ERR_UNSUPPORTED_OPERATION;
}

