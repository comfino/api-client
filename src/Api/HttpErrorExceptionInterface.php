<?php

namespace Comfino\Api;

interface HttpErrorExceptionInterface
{
    public function getStatusCode(): int;
}
