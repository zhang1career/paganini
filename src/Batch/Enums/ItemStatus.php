<?php

declare(strict_types=1);

namespace Paganini\Batch\Enums;

enum ItemStatus: int
{
    case Success = 1;
    case Failure = 2;
}
