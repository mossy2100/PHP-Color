<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use Mossy2100\Color\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color validation methods.
 */
#[CoversClass(Color::class)]
final class ColorValidationTest extends TestCase
{
    #region Method validHex() tests.

    /**
     * Data provider for valid hex string tests.
     *
     * @return array<array{string}>
     */
    public static function validHexProvider(): array
    {
        return [
            ['#abc'],
            ['#1234'],
            ['#abcdef'],
            ['#12345678'],
            ['abc'],
            ['ABCDEF'],
            ['#ABCDEF'],
        ];
    }

    /**
     * Test validHex() returns true for valid hex strings.
     */
    #[DataProvider('validHexProvider')]
    public function testValidHex(string $hex): void
    {
        $this->assertTrue(Color::validHex($hex));
    }

    /**
     * Data provider for invalid hex string tests.
     *
     * @return array<array{string}>
     */
    public static function invalidHexProvider(): array
    {
        return [
            [''],
            ['#'],
            ['#ab'],
            ['#abcde'],
            ['#abcdefgh'],
            ['#gggggg'],
            ['##abc'],
            ['notahex'],
        ];
    }

    /**
     * Test validHex() returns false for invalid hex strings.
     */
    #[DataProvider('invalidHexProvider')]
    public function testValidHexInvalid(string $hex): void
    {
        $this->assertFalse(Color::validHex($hex));
    }

    #endregion
}
