<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use DomainException;
use Mossy2100\Color\Color;
use Mossy2100\Core\Exceptions\FormatException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color conversion methods.
 */
#[CoversClass(Color::class)]
final class ColorConversionTest extends TestCase
{
    #region Method toHex() tests.

    /**
     * Test toHex() with default options (includes alpha and hash).
     */
    public function testToHexDefault(): void
    {
        $color = new Color(255, 128, 0, 255);
        $this->assertSame('#ff8000ff', $color->toHex());
    }

    /**
     * Test toHex() without alpha.
     */
    public function testToHexNoAlpha(): void
    {
        $color = new Color(255, 128, 0);
        $this->assertSame('#ff8000', $color->toHex(false));
    }

    /**
     * Test toHex() without leading hash.
     */
    public function testToHexNoHash(): void
    {
        $color = new Color(255, 128, 0);
        $this->assertSame('ff8000ff', $color->toHex(true, false));
        $this->assertSame('ff8000', $color->toHex(false, false));
    }

    /**
     * Test toHex() with upper-case output.
     */
    public function testToHexUpperCase(): void
    {
        $color = new Color(255, 128, 0);
        $this->assertSame('#FF8000FF', $color->toHex(true, true, true));
    }

    #endregion

    #region Method __toString() tests.

    /**
     * Test __toString() returns hex string with alpha.
     */
    public function testToString(): void
    {
        $color = new Color(255, 0, 128, 200);
        $this->assertSame('#ff0080c8', (string) $color);
    }

    #endregion

    #region Method toRgbString() tests.

    /**
     * Test toRgbString() returns modern CSS rgb() format with alpha.
     */
    public function testToRgbString(): void
    {
        $color = new Color(120, 50, 50);
        $this->assertSame('rgb(120 50 50 / 100%)', $color->toRgbString());
    }

    /**
     * Test toRgbString() returns modern CSS rgb() format with partial alpha.
     */
    public function testToRgbStringWithAlpha(): void
    {
        $color = new Color(120, 50, 50, 128);
        $this->assertSame('rgb(120 50 50 / 50.196078%)', $color->toRgbString());
    }

    #endregion

    #region Method toHslString() tests.

    /**
     * Test toHslString() returns modern CSS hsl() format.
     */
    public function testToHslString(): void
    {
        $color = Color::fromString('#ff6600');
        $this->assertSame('hsl(24deg 100% 50% / 100%)', $color->toHslString());
    }

    /**
     * Test toHslString() returns modern CSS hsl() format with partial alpha and fractional values.
     */
    public function testToHslStringWithAlpha(): void
    {
        $color = new Color(255, 128, 0, 200);
        $this->assertSame('hsl(30.117647deg 100% 50% / 78.431373%)', $color->toHslString());
    }

    #endregion

    #region Method toRgbaArray() tests.

    /**
     * Test toRgbaArray() returns correct RGBA array.
     */
    public function testToRgbaArray(): void
    {
        $color = new Color(10, 20, 30, 40);
        $this->assertSame(['red' => 10, 'green' => 20, 'blue' => 30, 'alpha' => 40], $color->toRgbaArray());
    }

    #endregion

    #region Method toHslArray() tests.

    /**
     * Test toHslArray() returns correct HSL array.
     */
    public function testToHslArray(): void
    {
        $color = Color::fromString('#ff6600');
        $this->assertSame(['hue' => 24.0, 'saturation' => 100.0, 'lightness' => 50.0], $color->toHslArray());
    }

    #endregion

    #region Method toArray() tests.

    /**
     * Test toArray() returns array with all color properties.
     */
    public function testToArray(): void
    {
        $color = new Color(255, 102, 0, 250);
        $expected = [
            'red' => 255,
            'green' => 102,
            'blue' => 0,
            'alpha' => 250,
            'hue' => 24.0,
            'saturation' => 100.0,
            'lightness' => 50.0,
        ];
        $this->assertSame($expected, $color->toArray());
    }

    #endregion

    #region Method rgbToHsl() tests.

    /**
     * Data provider for RGB to HSL conversion tests.
     *
     * @return array<string, array{int, int, int, float, float, float}>
     */
    public static function rgbToHslProvider(): array
    {
        return [
            'red' => [255, 0, 0, 0.0, 100.0, 50.0],
            'green' => [0, 255, 0, 120.0, 100.0, 50.0],
            'blue' => [0, 0, 255, 240.0, 100.0, 50.0],
            'black' => [0, 0, 0, 0.0, 0.0, 0.0],
            'white' => [255, 255, 255, 0.0, 0.0, 100.0],
            'gray' => [128, 128, 128, 0.0, 0.0, 50.196],
            'orange' => [255, 102, 0, 24.0, 100.0, 50.0],
        ];
    }

    /**
     * Test rgbToHsl() converts RGB values to HSL correctly.
     */
    #[DataProvider('rgbToHslProvider')]
    public function testRgbToHsl(int $r, int $g, int $b, float $h, float $s, float $l): void
    {
        $hsl = Color::rgbToHsl($r, $g, $b);
        $this->assertEqualsWithDelta($h, $hsl[0], 0.001);
        $this->assertEqualsWithDelta($s, $hsl[1], 0.001);
        $this->assertEqualsWithDelta($l, $hsl[2], 0.001);
    }

    /**
     * Test rgbToHsl() never returns a saturation over 100%.
     */
    public function testRgbToHslSaturationClamped(): void
    {
        $this->assertSame(100.0, Color::rgbToHsl(120, 120, 255)[1]);
        $this->assertSame(100.0, Color::rgbToHsl(0, 0, 1)[1]);
    }

    /**
     * Test rgbToHsl() throws DomainException for an invalid byte.
     */
    public function testRgbToHslInvalid(): void
    {
        $this->expectException(DomainException::class);
        Color::rgbToHsl(256, 0, 0);
    }

    #endregion

    #region Method hslToRgb() tests.

    /**
     * Data provider for HSL to RGB conversion tests.
     *
     * @return array<string, array{float, float, float, int, int, int}>
     */
    public static function hslToRgbProvider(): array
    {
        return [
            'red' => [0.0, 100.0, 50.0, 255, 0, 0],
            'green' => [120.0, 100.0, 50.0, 0, 255, 0],
            'blue' => [240.0, 100.0, 50.0, 0, 0, 255],
            'black' => [0.0, 0.0, 0.0, 0, 0, 0],
            'white' => [0.0, 0.0, 100.0, 255, 255, 255],
            'gray' => [0.0, 0.0, 50.0, 128, 128, 128],
            'orange' => [24.0, 100.0, 50.0, 255, 102, 0],
        ];
    }

    /**
     * Test hslToRgb() converts HSL values to RGB correctly.
     */
    #[DataProvider('hslToRgbProvider')]
    public function testHslToRgb(float $h, float $s, float $l, int $r, int $g, int $b): void
    {
        $this->assertSame([$r, $g, $b], Color::hslToRgb($h, $s, $l));
    }

    /**
     * Test hslToRgb() throws DomainException for an invalid percentage.
     */
    public function testHslToRgbInvalid(): void
    {
        $this->expectException(DomainException::class);
        Color::hslToRgb(0, 50, 101);
    }

    #endregion

    #region Method normalizeHex() tests.

    /**
     * Test normalizeHex() converts various formats to 8-digit lowercase.
     */
    public function testNormalizeHex(): void
    {
        $this->assertSame('ff0000ff', Color::normalizeHex('#f00'));
        $this->assertSame('112233ff', Color::normalizeHex('123'));
        $this->assertSame('11223344', Color::normalizeHex('#1234'));
        $this->assertSame('ff0000ff', Color::normalizeHex('#FF0000'));
        $this->assertSame('ff000080', Color::normalizeHex('#ff000080'));
    }

    /**
     * Test normalizeHex() throws FormatException for invalid hex string.
     */
    public function testNormalizeHexInvalid(): void
    {
        $this->expectException(FormatException::class);
        Color::normalizeHex('notahex');
    }

    #endregion

    #region Method hexToBytes() tests.

    /**
     * Test hexToBytes() parses 6-digit hex string.
     */
    public function testHexToBytes(): void
    {
        $this->assertSame([255, 128, 0, 255], Color::hexToBytes('#ff8000'));
    }

    /**
     * Test hexToBytes() parses 8-digit hex string with alpha.
     */
    public function testHexToBytesWithAlpha(): void
    {
        $this->assertSame([255, 128, 0, 128], Color::hexToBytes('#ff800080'));
    }

    /**
     * Test hexToBytes() throws FormatException for invalid hex string.
     */
    public function testHexToBytesInvalid(): void
    {
        $this->expectException(FormatException::class);
        Color::hexToBytes('#ff800');
    }

    #endregion
}
