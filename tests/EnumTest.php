<?php

namespace Comfino;

use PHPUnit\Framework\TestCase;

readonly class TestedEnum extends Enum
{
    public const ONE = '1';
    public const TWO = '2';
    public const THREE = '3';

    public static function from(string $value): TestedEnum
    {
        return new self($value);
    }
}

class EnumTest extends TestCase
{
    public function testConstruct(): void
    {
        $enum1 = new TestedEnum(TestedEnum::ONE);
        $enum2 = new TestedEnum(TestedEnum::TWO);
        $enum3 = new TestedEnum(TestedEnum::THREE);

        $this->assertEquals(TestedEnum::ONE, (string) $enum1);
        $this->assertEquals(TestedEnum::TWO, (string) $enum2);
        $this->assertEquals(TestedEnum::THREE, (string) $enum3);

        $enum1 = TestedEnum::from(TestedEnum::ONE);
        $enum2 = TestedEnum::from(TestedEnum::TWO);
        $enum3 = TestedEnum::from(TestedEnum::THREE);

        $this->assertEquals(TestedEnum::ONE, (string) $enum1);
        $this->assertEquals(TestedEnum::TWO, (string) $enum2);
        $this->assertEquals(TestedEnum::THREE, (string) $enum3);
    }

    /**
     * @throws \JsonException
     */
    public function testEnumMethods(): void
    {
        $this->assertEquals([TestedEnum::ONE, TestedEnum::TWO, TestedEnum::THREE], TestedEnum::values());
        $this->assertEquals(['ONE', 'TWO', 'THREE'], TestedEnum::names());
        $this->assertJsonStringEqualsJsonString(
            '["1","2"]',
            json_encode([new TestedEnum(TestedEnum::ONE), new TestedEnum(TestedEnum::TWO)], JSON_THROW_ON_ERROR)
        );
    }

    public function testError(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TestedEnum::from('InvalidEnum');
    }
}
