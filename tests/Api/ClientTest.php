<?php

declare(strict_types=1);

namespace Comfino\Tests\Api;

use Comfino\Tests\ClientTestTrait;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ClientTest extends TestCase
{
    use ProphecyTrait;
    use ClientTestTrait;
}
