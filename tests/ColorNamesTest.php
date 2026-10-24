<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests;

use Mossy2100\Color\ColorNames;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for ColorNames.
 */
#[CoversClass(ColorNames::class)]
final class ColorNamesTest extends TestCase
{
    #region CSS table tests.

    /**
     * Test CSS has the 148 CSS named colors, in UpperCamelCase, with 6-digit hex values.
     */
    public function testCss(): void
    {
        $names = array_keys(ColorNames::CSS);
        $this->assertCount(148, $names);
        $this->assertCount(148, array_unique(array_map(strtolower(...), $names)));
        foreach (ColorNames::CSS as $name => $hex) {
            $this->assertMatchesRegularExpression('/^[A-Z][a-zA-Z]*$/', $name);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{6}$/', $hex);
        }
        $this->assertSame('663399', ColorNames::CSS['RebeccaPurple']);
        $this->assertArrayNotHasKey('Transparent', ColorNames::CSS);
    }

    #endregion

    #region EXTRA table tests.

    /**
     * Test EXTRA names are UpperCamelCase, with 6-digit hex values, and don't overlap the CSS names.
     */
    public function testExtra(): void
    {
        $names = array_keys(ColorNames::EXTRA);
        $lowerNames = array_map(strtolower(...), $names);
        $this->assertCount(296, $names);
        $this->assertCount(296, array_unique($lowerNames));
        foreach (ColorNames::EXTRA as $name => $hex) {
            $this->assertMatchesRegularExpression('/^[A-Z][a-zA-Z]*$/', $name);
            $this->assertMatchesRegularExpression('/^[0-9a-f]{6}$/', $hex);
        }
        $cssLowerNames = array_map(strtolower(...), array_keys(ColorNames::CSS));
        $this->assertSame([], array_values(array_intersect($lowerNames, $cssLowerNames)));
    }

    #endregion

    #region Method lookupHex() tests.

    /**
     * Data provider for lookupHex() tests with known names.
     *
     * @return array<string, array{string, string}>
     */
    public static function knownNameProvider(): array
    {
        return [
            'CSS name'           => ['AliceBlue', 'f0f8ffff'],
            'lower case'         => ['aliceblue', 'f0f8ffff'],
            'upper case'         => ['ALICEBLUE', 'f0f8ffff'],
            'surrounding spaces' => [' Red ', 'ff0000ff'],
            'extra name'         => ['Amethyst', '9a63c6ff'],
            'transparent'        => ['transparent', '00000000'],
            'transparent mixed'  => [' TransParent ', '00000000'],
        ];
    }

    /**
     * Test lookupHex() returns the 8-digit hex value of a known name, ignoring case and surrounding whitespace.
     */
    #[DataProvider('knownNameProvider')]
    public function testLookupHexKnownName(string $name, string $hex): void
    {
        $this->assertSame($hex, ColorNames::lookupHex($name));
    }

    /**
     * Test lookupHex() returns null for an unknown name.
     */
    public function testLookupHexUnknownName(): void
    {
        $this->assertNull(ColorNames::lookupHex('notacolor'));
        $this->assertNull(ColorNames::lookupHex(''));
        $this->assertNull(ColorNames::lookupHex('air force blue'));
    }

    /**
     * Test lookupHex() covers every name in both tables, with an opaque alpha appended.
     */
    public function testLookupHexEveryName(): void
    {
        foreach (ColorNames::CSS + ColorNames::EXTRA as $name => $hex) {
            $this->assertSame($hex . 'ff', ColorNames::lookupHex($name));
        }
    }

    #endregion

    #region Method validName() tests.

    /**
     * Data provider for valid color name tests.
     *
     * @return array<array{string}>
     */
    public static function validNameProvider(): array
    {
        return [
            ['red'],
            ['RED'],
            ['Blue'],
            ['transparent'],
            ['Transparent'],
            ['aliceblue'],
            ['rebeccapurple'],
            ['RebeccaPurple'],
            [' red '],
            ['Amethyst'],
            ['airforceblue'],
        ];
    }

    /**
     * Test validName() returns true for CSS and extra color names, ignoring case and surrounding whitespace.
     */
    #[DataProvider('validNameProvider')]
    public function testValidName(string $name): void
    {
        $this->assertTrue(ColorNames::validName($name));
    }

    /**
     * Test validName() returns false for unknown color names.
     */
    public function testValidNameInvalid(): void
    {
        $this->assertFalse(ColorNames::validName('notacolor'));
        $this->assertFalse(ColorNames::validName('air force blue'));
    }

    #endregion
}
