<?php

namespace Paganini\Exceptions;

use Paganini\Constants\ResponseConstant;

class ResourceNotFoundException extends BaseException
{
    protected static int $respCode = ResponseConstant::RET_RESOURCE_NOT_FOUND;
}

