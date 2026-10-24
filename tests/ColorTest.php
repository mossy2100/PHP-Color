<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests;

use DomainException;
use LengthException;
use Mossy2100\Color\Color;
use Mossy2100\Core\Exceptions\FormatException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Color.
 */
#[CoversClass(Color::class)]
final class ColorTest extends TestCase
{
    #region Constructor tests

    /**
     * Test default constructor creates black with full opacity.
     */
    public function testConstructDefault(): void
    {
        $color = new Color();
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test constructor with RGBA bytes.
     */
    public function testConstructBytes(): void
    {
        $color = new Color(10, 20, 30, 127);
        $this->assertSame(10, $color->red);
        $this->assertSame(20, $color->green);
        $this->assertSame(30, $color->blue);
        $this->assertSame(127, $color->alpha);
    }

    /**
     * Test constructor defaults alpha to 255 (fully opaque).
     */
    public function testConstructDefaultAlpha(): void
    {
        $color = new Color(100, 150, 200);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Data provider for invalid constructor bytes.
     *
     * @return array<string, array{int, int, int, int}>
     */
    public static function invalidByteProvider(): array
    {
        return [
            'red too high'   => [256, 0, 0, 255],
            'green negative' => [0, -1, 0, 255],
            'blue too high'  => [0, 0, 300, 255],
            'alpha too high' => [0, 0, 0, 256],
        ];
    }

    /**
     * Test constructor throws DomainException for an out-of-range byte.
     */
    #[DataProvider('invalidByteProvider')]
    public function testConstructInvalidByte(int $red, int $green, int $blue, int $alpha): void
    {
        $this->expectException(DomainException::class);
        new Color($red, $green, $blue, $alpha);
    }

    #endregion

    #region Factory method tests

    /**
     * Test fromString() with 3-digit hex string.
     */
    public function testFromStringHex3Digits(): void
    {
        $color = Color::fromString('#0f8');
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(136, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test fromString() with 4-digit hex string (includes alpha).
     */
    public function testFromStringHex4Digits(): void
    {
        $color = Color::fromString('#1234');
        $this->assertSame(0x11, $color->red);
        $this->assertSame(0x22, $color->green);
        $this->assertSame(0x33, $color->blue);
        $this->assertSame(0x44, $color->alpha);
    }

    /**
     * Test fromString() with 6-digit hex string.
     */
    public function testFromStringHex6Digits(): void
    {
        $color = Color::fromString('#ff8000');
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test fromString() with 8-digit hex string (includes alpha).
     */
    public function testFromStringHex8Digits(): void
    {
        $color = Color::fromString('#ff800080');
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(128, $color->alpha);
    }

    /**
     * Test fromString() accepts hex string without leading hash.
     */
    public function testFromStringHexWithoutHash(): void
    {
        $color = Color::fromString('ff0000');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromString() accepts upper-case hex digits.
     */
    public function testFromStringHexUpperCase(): void
    {
        $color = Color::fromString('#FF00FF');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test fromString() with lower-case color name.
     */
    public function testFromStringColorNameLowerCase(): void
    {
        $color = Color::fromString('red');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromString() with upper-case color name.
     */
    public function testFromStringColorNameUpperCase(): void
    {
        $color = Color::fromString('RED');
        $this->assertSame(255, $color->red);
    }

    /**
     * Test fromString() with mixed-case color name.
     */
    public function testFromStringColorNameMixedCase(): void
    {
        $color = Color::fromString('ReD');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromString() with 'transparent' color name.
     */
    public function testFromStringTransparent(): void
    {
        $color = Color::fromString('transparent');
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(0, $color->alpha);
    }

    /**
     * Test fromString() ignores surrounding whitespace.
     */
    public function testFromStringTrimsWhitespace(): void
    {
        $this->assertSame('#ff0000ff', Color::fromString(' Red ')->toHex());
        $this->assertSame('#ffffffff', Color::fromString(" #FFF\n")->toHex());
    }

    /**
     * Test fromString() round-trips with __toString().
     */
    public function testFromStringRoundTrip(): void
    {
        $color = new Color(18, 52, 86, 120);
        $this->assertSame((string) $color, (string) Color::fromString((string) $color));
    }

    /**
     * Test fromString() throws FormatException for invalid color name.
     */
    public function testFromStringInvalidColorName(): void
    {
        $this->expectException(FormatException::class);
        $this->expectExceptionMessage("Invalid color string: 'rde'. Must be a color name or hex color string.");
        Color::fromString('rde');
    }

    /**
     * Test fromString() throws FormatException for invalid hex string.
     */
    public function testFromStringInvalidHexString(): void
    {
        $this->expectException(FormatException::class);
        Color::fromString('#gg0000');
    }

    /**
     * Test fromRgba() creates color from RGBA percentages.
     */
    public function testFromRgba(): void
    {
        $color = Color::fromRgba(100, 50, 0, 50);
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(128, $color->alpha);
    }

    /**
     * Test fromRgba() defaults alpha to 100% (fully opaque).
     */
    public function testFromRgbaDefaultAlpha(): void
    {
        $color = Color::fromRgba(100, 50, 0);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test fromRgba() with 0% produces byte 0.
     */
    public function testFromRgbaZero(): void
    {
        $color = Color::fromRgba(0, 0, 0, 0);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(0, $color->alpha);
    }

    /**
     * Test fromRgba() with 100% produces byte 255.
     */
    public function testFromRgbaHundred(): void
    {
        $color = Color::fromRgba(100, 100, 100, 100);
        $this->assertSame(255, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(255, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Data provider for invalid fromRgba() percentages.
     *
     * @return array<string, array{float, float, float, float}>
     */
    public static function invalidRgbaPercentProvider(): array
    {
        return [
            'red as byte'    => [255, 0, 0, 100],
            'green negative' => [0, -1, 0, 100],
            'blue too high'  => [0, 0, 100.1, 100],
            'alpha too high' => [0, 0, 0, 101],
            'red NAN'        => [NAN, 0, 0, 100],
            'alpha INF'      => [0, 0, 0, INF],
        ];
    }

    /**
     * Test fromRgba() throws DomainException for an out-of-range percentage.
     */
    #[DataProvider('invalidRgbaPercentProvider')]
    public function testFromRgbaInvalid(float $red, float $green, float $blue, float $alpha): void
    {
        $this->expectException(DomainException::class);
        Color::fromRgba($red, $green, $blue, $alpha);
    }

    /**
     * Test fromRgba() reports the out-of-range percentage in the exception message.
     */
    public function testFromRgbaInvalidMessage(): void
    {
        $this->expectExceptionMessage('Invalid percentage: 150.0. Must be in the range [0, 100].');
        Color::fromRgba(150, 0, 0);
    }

    /**
     * Test fromHsla() creates pure red (hue=0).
     */
    public function testFromHslaPureRed(): void
    {
        $color = Color::fromHsla(0, 100, 50);
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromHsla() creates pure green (hue=120).
     */
    public function testFromHslaPureGreen(): void
    {
        $color = Color::fromHsla(120, 100, 50);
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromHsla() creates pure blue (hue=240).
     */
    public function testFromHslaPureBlue(): void
    {
        $color = Color::fromHsla(240, 100, 50);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test fromHsla() with alpha percentage.
     */
    public function testFromHslaWithAlpha(): void
    {
        $color = Color::fromHsla(120, 100, 50, 25);
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(64, $color->alpha);
    }

    /**
     * Test fromHsla() defaults alpha to 100% (fully opaque).
     */
    public function testFromHslaDefaultAlpha(): void
    {
        $this->assertSame(255, Color::fromHsla(0, 100, 50)->alpha);
    }

    /**
     * Test fromHsla() with alpha 0% produces byte 0.
     */
    public function testFromHslaAlphaZero(): void
    {
        $this->assertSame(0, Color::fromHsla(0, 100, 50, 0)->alpha);
    }

    /**
     * Test fromHsla() creates gray when saturation is zero.
     */
    public function testFromHslaGray(): void
    {
        $color = Color::fromHsla(0, 0, 50);
        $this->assertSame(128, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(128, $color->blue);
    }

    /**
     * Test fromHsla() creates black when lightness is zero.
     */
    public function testFromHslaBlack(): void
    {
        $color = Color::fromHsla(0, 0, 0);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromHsla() creates white when lightness is 100%.
     */
    public function testFromHslaWhite(): void
    {
        $color = Color::fromHsla(0, 0, 100);
        $this->assertSame(255, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test fromHsla() correctly wraps hue values outside [0, 360).
     */
    public function testFromHslaHueWrapping(): void
    {
        $color1 = Color::fromHsla(0, 100, 50);
        $color2 = Color::fromHsla(360, 100, 50);
        $color3 = Color::fromHsla(-360, 100, 50);
        $this->assertTrue($color1->equal($color2));
        $this->assertTrue($color1->equal($color3));
    }

    /**
     * Data provider for invalid fromHsla() percentages.
     *
     * @return array<string, array{float, float, float}>
     */
    public static function invalidHslaPercentProvider(): array
    {
        return [
            'saturation too high' => [150, 50, 100],
            'saturation NAN'      => [NAN, 50, 100],
            'lightness negative'  => [50, -10, 100],
            'alpha too high'      => [50, 50, 101],
        ];
    }

    /**
     * Test fromHsla() throws DomainException for an out-of-range percentage.
     */
    #[DataProvider('invalidHslaPercentProvider')]
    public function testFromHslaInvalid(float $saturation, float $lightness, float $alpha): void
    {
        $this->expectException(DomainException::class);
        Color::fromHsla(0, $saturation, $lightness, $alpha);
    }

    /**
     * Test fromHsla() derives the HSL properties from the RGB bytes, so equal colors report equal HSL values.
     */
    public function testFromHslaPropertiesMatchEqualColor(): void
    {
        $black = Color::fromHsla(120, 100, 0);
        $this->assertTrue($black->equal(new Color()));
        $this->assertSame([
            'hue'        => 0.0,
            'saturation' => 0.0,
            'lightness'  => 0.0,
        ], $black->toHslArray());

        $color = Color::fromHsla(10.123, 50, 50);
        $this->assertSame(Color::fromString($color->toHex())->toHslArray(), $color->toHslArray());
    }

    /**
     * Test fromHsla() throws DomainException for a non-finite hue.
     */
    public function testFromHslaNonFiniteHue(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid hue: NAN. Must be finite.');
        Color::fromHsla(NAN, 100, 50);
    }

    #endregion

    #region Immutable setter tests

    /**
     * Test withRed() returns new Color with updated red, original unchanged.
     */
    public function testWithRed(): void
    {
        $color = new Color();
        $newColor = $color->withRed(128);
        $this->assertSame(0, $color->red);
        $this->assertSame(128, $newColor->red);
    }

    /**
     * Test withGreen() returns new Color with updated green, original unchanged.
     */
    public function testWithGreen(): void
    {
        $color = new Color();
        $newColor = $color->withGreen(200);
        $this->assertSame(0, $color->green);
        $this->assertSame(200, $newColor->green);
    }

    /**
     * Test withBlue() returns new Color with updated blue, original unchanged.
     */
    public function testWithBlue(): void
    {
        $color = new Color();
        $newColor = $color->withBlue(40);
        $this->assertSame(0, $color->blue);
        $this->assertSame(40, $newColor->blue);
    }

    /**
     * Test withAlpha() returns new Color with updated alpha, original unchanged.
     */
    public function testWithAlpha(): void
    {
        $color = new Color();
        $newColor = $color->withAlpha(100);
        $this->assertSame(255, $color->alpha);
        $this->assertSame(100, $newColor->alpha);
    }

    /**
     * Test withRed() rounds a non-integer byte value.
     */
    public function testWithRedRoundsByte(): void
    {
        $color = new Color();
        $this->assertSame(128, $color->withRed(127.6)->red);
        $this->assertSame(128, $color->withRed(127.5)->red);
        $this->assertSame(127, $color->withRed(127.4)->red);
    }

    /**
     * Data provider for invalid byte values for the with*() methods.
     *
     * @return array<string, array{float}>
     */
    public static function invalidWithByteProvider(): array
    {
        return [
            'too high'  => [256],
            'just over' => [255.4],
            'negative'  => [-1],
            'NAN'       => [NAN],
            'INF'       => [INF],
        ];
    }

    /**
     * Test withRed() throws DomainException for an out-of-range byte value.
     */
    #[DataProvider('invalidWithByteProvider')]
    public function testWithRedInvalid(float $red): void
    {
        $this->expectException(DomainException::class);
        new Color()->withRed($red);
    }

    /**
     * Test withRed() with a percentage.
     */
    public function testWithRedPercent(): void
    {
        $color = new Color();
        $newColor = $color->withRed(50, true);
        $this->assertSame(0, $color->red);
        $this->assertSame(128, $newColor->red);
    }

    /**
     * Test withGreen() with a percentage.
     */
    public function testWithGreenPercent(): void
    {
        $color = new Color();
        $newColor = $color->withGreen(100, true);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $newColor->green);
    }

    /**
     * Test withBlue() with a percentage.
     */
    public function testWithBluePercent(): void
    {
        $color = new Color(0, 0, 255);
        $newColor = $color->withBlue(0, true);
        $this->assertSame(255, $color->blue);
        $this->assertSame(0, $newColor->blue);
    }

    /**
     * Test withAlpha() with a percentage.
     */
    public function testWithAlphaPercent(): void
    {
        $color = new Color();
        $newColor = $color->withAlpha(50, percent: true);
        $this->assertSame(255, $color->alpha);
        $this->assertSame(128, $newColor->alpha);
    }

    /**
     * Test withAlpha() throws DomainException for an out-of-range percentage.
     */
    public function testWithAlphaPercentInvalid(): void
    {
        $this->expectException(DomainException::class);
        new Color()->withAlpha(101, true);
    }

    /**
     * Test chaining with*() methods using percentages.
     */
    public function testWithMethodsChainingWithPercentages(): void
    {
        $color = new Color();
        $newColor = $color->withRed(100, true)->withGreen(50, true)->withBlue(0, true)->withAlpha(75, true);
        $this->assertSame(255, $newColor->red);
        $this->assertSame(128, $newColor->green);
        $this->assertSame(0, $newColor->blue);
        $this->assertSame(191, $newColor->alpha);
    }

    /**
     * Test withHue() returns new Color with updated hue, original unchanged.
     */
    public function testWithHue(): void
    {
        $color = Color::fromString('red');
        $newColor = $color->withHue(120);
        $this->assertEqualsWithDelta(0, $color->hue, 0.01);
        $this->assertEqualsWithDelta(120, $newColor->hue, 0.01);
        $this->assertSame(0, $newColor->red);
        $this->assertSame(255, $newColor->green);
        $this->assertSame(0, $newColor->blue);
    }

    /**
     * Test withHue() wraps hue values outside [0, 360).
     */
    public function testWithHueWraps(): void
    {
        $color = Color::fromString('red');
        $this->assertSame('#00ff00ff', $color->withHue(480)->toHex());
        $this->assertSame('#0000ffff', $color->withHue(-120)->toHex());
    }

    /**
     * Test withHue() throws DomainException for a non-finite hue.
     */
    public function testWithHueNonFinite(): void
    {
        $this->expectException(DomainException::class);
        Color::fromString('red')->withHue(INF);
    }

    /**
     * Test withHue() preserves alpha.
     */
    public function testWithHuePreservesAlpha(): void
    {
        $color = new Color(255, 0, 0, 77);
        $this->assertSame(77, $color->withHue(200)->alpha);
    }

    /**
     * Test withSaturation() returns new Color with updated saturation, original unchanged.
     */
    public function testWithSaturation(): void
    {
        $color = Color::fromHsla(0, 100, 50);
        $newColor = $color->withSaturation(50);
        $this->assertEqualsWithDelta(100, $color->saturation, 0.01);
        // The saturation is derived from the rounded bytes, so it's only accurate to about 0.4%.
        $this->assertEqualsWithDelta(50, $newColor->saturation, 0.5);
    }

    /**
     * Test withLightness() returns new Color with updated lightness, original unchanged.
     */
    public function testWithLightness(): void
    {
        $color = Color::fromHsla(0, 100, 50);
        $newColor = $color->withLightness(25);
        $this->assertEqualsWithDelta(50, $color->lightness, 0.01);
        // The lightness is derived from the rounded bytes, so it's only accurate to about 0.4%.
        $this->assertEqualsWithDelta(25, $newColor->lightness, 0.5);
        $this->assertSame('#800000ff', $newColor->toHex());
    }

    /**
     * Test withSaturation() throws DomainException for invalid value.
     */
    public function testWithSaturationInvalid(): void
    {
        $color = Color::fromString('red');
        $this->expectException(DomainException::class);
        $color->withSaturation(150);
    }

    /**
     * Test withLightness() throws DomainException for invalid value.
     */
    public function testWithLightnessInvalid(): void
    {
        $color = Color::fromString('red');
        $this->expectException(DomainException::class);
        $color->withLightness(-10);
    }

    /**
     * Test HSL transformations work for colors whose computed saturation rounds to just over 100%.
     */
    public function testHslTransformationsAtFullSaturation(): void
    {
        $color = Color::fromString('#7878ff');
        $this->assertSame(100.0, $color->saturation);
        $this->assertSame('#ffff78ff', $color->complement()->toHex());
        $this->assertSame('#0000ffff', $color->withLightness(50)->toHex());
        $this->assertSame('#010100ff', Color::fromString('#000001')->withHue(60)->toHex());
    }

    /**
     * Test chaining multiple with*() methods preserves immutability.
     */
    public function testImmutabilityChaining(): void
    {
        $color = Color::fromString('red');
        $newColor = $color->withRed(128)->withGreen(64)->withAlpha(200);
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $color->alpha);
        $this->assertSame(128, $newColor->red);
        $this->assertSame(64, $newColor->green);
        $this->assertSame(200, $newColor->alpha);
    }

    #endregion

    #region Conversion method tests

    /**
     * Test toRgbaArray() returns correct RGBA array.
     */
    public function testToRgbaArray(): void
    {
        $color = new Color(10, 20, 30, 40);
        $this->assertSame([
            'red'   => 10,
            'green' => 20,
            'blue'  => 30,
            'alpha' => 40,
        ], $color->toRgbaArray());
    }

    /**
     * Test toHslArray() returns correct HSL array.
     */
    public function testToHslArray(): void
    {
        $color = Color::fromString('#ff6600');
        $this->assertSame([
            'hue'        => 24.0,
            'saturation' => 100.0,
            'lightness'  => 50.0,
        ], $color->toHslArray());
    }

    /**
     * Test toArray() returns array with all color properties.
     */
    public function testToArray(): void
    {
        $color = new Color(255, 102, 0, 250);
        $expected = [
            'red'        => 255,
            'green'      => 102,
            'blue'       => 0,
            'alpha'      => 250,
            'hue'        => 24.0,
            'saturation' => 100.0,
            'lightness'  => 50.0,
        ];
        $this->assertSame($expected, $color->toArray());
    }

    #endregion

    #region RGB to HSL conversion tests

    /**
     * Data provider for RGB to HSL conversion tests.
     *
     * @return array<string, array{int, int, int, float, float, float}>
     */
    public static function rgbToHslProvider(): array
    {
        return [
            'red'    => [255, 0, 0, 0.0, 100.0, 50.0],
            'green'  => [0, 255, 0, 120.0, 100.0, 50.0],
            'blue'   => [0, 0, 255, 240.0, 100.0, 50.0],
            'black'  => [0, 0, 0, 0.0, 0.0, 0.0],
            'white'  => [255, 255, 255, 0.0, 0.0, 100.0],
            'gray'   => [128, 128, 128, 0.0, 0.0, 50.196],
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

    #region HSL to RGB conversion tests

    /**
     * Data provider for HSL to RGB conversion tests.
     *
     * @return array<string, array{float, float, float, int, int, int}>
     */
    public static function hslToRgbProvider(): array
    {
        return [
            'red'    => [0.0, 100.0, 50.0, 255, 0, 0],
            'green'  => [120.0, 100.0, 50.0, 0, 255, 0],
            'blue'   => [240.0, 100.0, 50.0, 0, 0, 255],
            'black'  => [0.0, 0.0, 0.0, 0, 0, 0],
            'white'  => [0.0, 0.0, 100.0, 255, 255, 255],
            'gray'   => [0.0, 0.0, 50.0, 128, 128, 128],
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

    #region Round-trip conversion tests

    /**
     * Test RGB -> HSL -> RGB round-trip preserves values.
     */
    public function testRgbToHslToRgbRoundTrip(): void
    {
        $original = new Color(123, 234, 45);
        $hsl = $original->toHslArray();
        $roundTrip = Color::fromHsla($hsl['hue'], $hsl['saturation'], $hsl['lightness']);
        $this->assertSame($original->toHex(), $roundTrip->toHex());
    }

    /**
     * Test HSL -> RGB -> HSL round-trip preserves values.
     */
    public function testHslToRgbToHslRoundTrip(): void
    {
        $original = Color::fromHsla(75, 60, 40);
        $rgb = $original->toRgbaArray();
        $roundTrip = new Color($rgb['red'], $rgb['green'], $rgb['blue']);
        $this->assertEqualsWithDelta($original->hue, $roundTrip->hue, 0.25);
        $this->assertEqualsWithDelta($original->saturation, $roundTrip->saturation, 1);
        $this->assertEqualsWithDelta($original->lightness, $roundTrip->lightness, 1);
    }

    /**
     * Test fromHsla() and fromRgba() reproduce every color from its own HSL and percentage values.
     */
    public function testPercentageRoundTripSweep(): void
    {
        mt_srand(7);
        for ($i = 0; $i < 2000; $i++) {
            $color = new Color(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            $alpha = $color->alpha * 100 / 255;
            $hsl = Color::fromHsla($color->hue, $color->saturation, $color->lightness, $alpha);
            $rgb = Color::fromRgba(
                $color->red * 100 / 255,
                $color->green * 100 / 255,
                $color->blue * 100 / 255,
                $alpha
            );
            $this->assertSame($color->toHex(), $hsl->toHex());
            $this->assertSame($color->toHex(), $rgb->toHex());
        }
    }

    #endregion

    #region String output tests

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

    /**
     * Test __toString() returns hex string with alpha.
     */
    public function testToString(): void
    {
        $color = new Color(255, 0, 128, 200);
        $this->assertSame('#ff0080c8', (string) $color);
    }

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

    #region Validation tests

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

    /**
     * Test fromString() and nameToHex() accept extra color names, case-insensitively.
     */
    public function testExtraColorNames(): void
    {
        $this->assertSame('#9a63c6ff', Color::fromString('amethyst')->toHex());
        $this->assertSame('5d8aa8ff', Color::nameToHex('AIRFORCEBLUE'));
    }

    /**
     * Test 'transparent' is a known name.
     */
    public function testTransparentName(): void
    {
        $this->assertSame('00000000', Color::nameToHex(' TRANSPARENT '));
    }

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

    /**
     * Test nameToHex() throws DomainException for invalid color name.
     */
    public function testNameToHexInvalid(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Invalid color name: ' NotAColor '.");
        Color::nameToHex(' NotAColor ');
    }

    #endregion

    #region Equals tests

    /**
     * Test equal() returns true for identical colors.
     */
    public function testEqual(): void
    {
        $color1 = new Color(100, 150, 200, 250);
        $color2 = new Color(100, 150, 200, 250);
        $this->assertTrue($color1->equal($color2));
    }

    /**
     * Test equal() returns false for different colors.
     */
    public function testNotEquals(): void
    {
        $color1 = new Color(100, 150, 200);
        $color2 = new Color(100, 150, 201);
        $this->assertFalse($color1->equal($color2));
    }

    /**
     * Test equal() returns false when only alpha differs.
     */
    public function testEqualWithDifferentAlpha(): void
    {
        $color1 = new Color(100, 150, 200, 255);
        $color2 = new Color(100, 150, 200, 128);
        $this->assertFalse($color1->equal($color2));
    }

    /**
     * Test equal() returns false when comparing with non-Color.
     */
    public function testEqualWithNonColor(): void
    {
        $color = Color::fromString('red');
        $this->assertFalse($color->equal('red'));
    }

    #endregion

    #region Relative luminance tests

    /**
     * Test relativeLuminance is 0 for black.
     */
    public function testRelativeLuminanceBlack(): void
    {
        $color = Color::fromString('black');
        $this->assertEqualsWithDelta(0.0, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance is 1 for white.
     */
    public function testRelativeLuminanceWhite(): void
    {
        $color = Color::fromString('white');
        $this->assertEqualsWithDelta(1.0, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance for pure red.
     */
    public function testRelativeLuminanceRed(): void
    {
        $color = Color::fromString('red');
        $this->assertEqualsWithDelta(0.2126, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance for CSS green (#008000).
     */
    public function testRelativeLuminanceGreen(): void
    {
        $color = Color::fromString('green');
        $this->assertEqualsWithDelta(0.1544, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance for pure blue.
     */
    public function testRelativeLuminanceBlue(): void
    {
        $color = Color::fromString('blue');
        $this->assertEqualsWithDelta(0.0722, $color->relativeLuminance, 0.001);
    }

    #endregion

    #region Perceived lightness tests

    /**
     * Test perceivedLightness is 0 for black.
     */
    public function testPerceivedLightnessBlack(): void
    {
        $color = Color::fromString('black');
        $this->assertEqualsWithDelta(0.0, $color->perceivedLightness, 0.001);
    }

    /**
     * Test perceivedLightness is 100 for white.
     */
    public function testPerceivedLightnessWhite(): void
    {
        $color = Color::fromString('white');
        $this->assertEqualsWithDelta(100.0, $color->perceivedLightness, 0.001);
    }

    /**
     * Test perceivedLightness for a mid gray, on the cube-root branch.
     */
    public function testPerceivedLightnessMidGray(): void
    {
        $color = new Color(119, 119, 119);
        $this->assertEqualsWithDelta(50.0344, $color->perceivedLightness, 0.001);
    }

    #endregion

    #region Contrast ratio tests

    /**
     * Test contrastRatio() returns 21:1 for black and white.
     */
    public function testContrastRatioBlackWhite(): void
    {
        $black = Color::fromString('black');
        $white = Color::fromString('white');
        $this->assertEqualsWithDelta(21.0, $black->contrastRatio($white), 0.1);
    }

    /**
     * Test contrastRatio() returns 1:1 for identical colors.
     */
    public function testContrastRatioIdentical(): void
    {
        $color = Color::fromString('red');
        $this->assertEqualsWithDelta(1.0, $color->contrastRatio($color), 0.01);
    }

    /**
     * Test contrastRatio() is symmetric.
     */
    public function testContrastRatioSymmetric(): void
    {
        $color1 = Color::fromString('red');
        $color2 = Color::fromString('blue');
        $this->assertEqualsWithDelta(
            $color1->contrastRatio($color2),
            $color2->contrastRatio($color1),
            0.001
        );
    }

    #endregion

    #region Best text color tests

    /**
     * Test bestTextColor() returns white for black background.
     */
    public function testBestTextColorOnBlack(): void
    {
        $color = Color::fromString('black');
        $result = $color->bestTextColor();
        $this->assertInstanceOf(Color::class, $result);
        $this->assertTrue($result->equal(Color::fromString('white')));
    }

    /**
     * Test bestTextColor() returns black for white background.
     */
    public function testBestTextColorOnWhite(): void
    {
        $color = Color::fromString('white');
        $result = $color->bestTextColor();
        $this->assertTrue($result->equal(Color::fromString('black')));
    }

    /**
     * Test bestTextColor() returns white for dark blue background.
     */
    public function testBestTextColorOnDarkBlue(): void
    {
        $color = Color::fromString('darkblue');
        $result = $color->bestTextColor();
        $this->assertTrue($result->equal(Color::fromString('white')));
    }

    /**
     * Test bestTextColor() returns black for yellow background.
     */
    public function testBestTextColorOnYellow(): void
    {
        $color = Color::fromString('yellow');
        $result = $color->bestTextColor();
        $this->assertTrue($result->equal(Color::fromString('black')));
    }

    /**
     * Test bestTextColor() with custom text colors as strings.
     */
    public function testBestTextColorWithCustomStrings(): void
    {
        $color = Color::fromString('black');
        $result = $color->bestTextColor('ivory', 'navy');
        $this->assertTrue($result->equal(Color::fromString('ivory')));
    }

    /**
     * Test bestTextColor() with custom text colors as Color objects.
     */
    public function testBestTextColorWithCustomColors(): void
    {
        $ivory = Color::fromString('ivory');
        $navy = Color::fromString('navy');
        $background = Color::fromString('white');
        $result = $background->bestTextColor($ivory, $navy);
        $this->assertTrue($result->equal($navy));
    }

    /**
     * Test bestTextColor() chooses dark when contrast ratios are equal.
     */
    public function testBestTextColorPrefersDarkWhenEqual(): void
    {
        // Contrast ignores alpha, so these two text colors have the same contrast with any background.
        $dark = new Color(0, 0, 0, 255);
        $light = new Color(0, 0, 0, 128);
        $result = Color::fromString('gray')->bestTextColor($light, $dark);
        $this->assertSame('#000000ff', $result->toHex());
    }

    #endregion

    #region Mix tests

    /**
     * Test mix() at 50% blends colors equally.
     */
    public function testMix50Percent(): void
    {
        $color1 = new Color(0, 0, 0);
        $color2 = new Color(100, 100, 100);
        $mixed = $color1->mix($color2);
        $this->assertSame(50, $mixed->red);
        $this->assertSame(50, $mixed->green);
        $this->assertSame(50, $mixed->blue);
    }

    /**
     * Test mix() with a given percentage of the other color.
     */
    public function testMix80Percent(): void
    {
        $red = Color::fromString('red');
        $blue = Color::fromString('blue');
        $this->assertSame('#3300ccff', $red->mix($blue, 80)->toHex());
    }

    /**
     * Test mix() at 100% returns the other color.
     */
    public function testMix100Percent(): void
    {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 255, 0);
        $mixed = $color1->mix($color2, 100);
        $this->assertTrue($color2->equal($mixed));
    }

    /**
     * Test mix() at 0% returns the original color.
     */
    public function testMix0Percent(): void
    {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 255, 0);
        $mixed = $color1->mix($color2, 0);
        $this->assertTrue($color1->equal($mixed));
    }

    /**
     * Test mix() also blends alpha channel.
     */
    public function testMixWithAlpha(): void
    {
        $color1 = new Color(0, 0, 0, 0);
        $color2 = new Color(100, 100, 100, 100);
        $mixed = $color1->mix($color2, 50);
        $this->assertSame(50, $mixed->alpha);
    }

    /**
     * Test mix() with a transparent color fades the color without darkening it, as CSS color-mix() does.
     */
    public function testMixWithTransparent(): void
    {
        $red = Color::fromString('red');
        $this->assertSame('#ff000080', $red->mix(Color::fromString('transparent'))->toHex());
    }

    /**
     * Test mix() weights each color's RGB by its alpha.
     */
    public function testMixPremultipliesAlpha(): void
    {
        $opaqueRed = new Color(255, 0, 0, 255);
        $translucentBlue = new Color(0, 0, 255, 64);
        $this->assertSame('#cc0033a0', $opaqueRed->mix($translucentBlue)->toHex());
    }

    /**
     * Test mix() of two fully transparent colors interpolates their RGB directly.
     */
    public function testMixFullyTransparent(): void
    {
        $color1 = new Color(0, 0, 0, 0);
        $color2 = new Color(200, 100, 0, 0);
        $this->assertSame('#64320000', $color1->mix($color2)->toHex());
    }

    /**
     * Test mix() throws DomainException for invalid percentage.
     */
    public function testMixInvalidPercent(): void
    {
        $color1 = Color::fromString('red');
        $color2 = Color::fromString('blue');
        $this->expectException(DomainException::class);
        $color1->mix($color2, 150);
    }

    #endregion

    #region Complement tests

    /**
     * Test complement() shifts hue by 180 degrees.
     */
    public function testComplementRed(): void
    {
        $color = Color::fromString('red');
        $complement = $color->complement();
        $this->assertEqualsWithDelta(180, $complement->hue, 0.1);
    }

    /**
     * Test complement() of green (hue=120) is magenta (hue=300).
     */
    public function testComplementGreen(): void
    {
        $color = Color::fromString('green');
        $complement = $color->complement();
        $hue = $complement->hue;
        $expected = 300; // 120 + 180
        $this->assertEqualsWithDelta($expected, $hue, 0.1);
    }

    /**
     * Test complement() preserves alpha.
     */
    public function testComplementPreservesAlpha(): void
    {
        $color = Color::fromHsla(60, 50, 50, 50);
        $complement = $color->complement();
        $this->assertSame(128, $complement->alpha);
    }

    /**
     * Test complement() preserves saturation.
     */
    public function testComplementPreservesSaturation(): void
    {
        $color = Color::fromHsla(60, 70, 50);
        $complement = $color->complement();
        $this->assertEqualsWithDelta(70, $complement->saturation, 1);
    }

    /**
     * Test complement() preserves lightness.
     */
    public function testComplementPreservesLightness(): void
    {
        $color = Color::fromHsla(60, 50, 30);
        $complement = $color->complement();
        $this->assertEqualsWithDelta(30, $complement->lightness, 1);
    }

    #endregion

    #region Average tests

    /**
     * Test average() of single color returns that color.
     */
    public function testAverageSingleColor(): void
    {
        $color = Color::fromString('red');
        $avg = Color::average($color);
        $this->assertTrue($color->equal($avg));
    }

    /**
     * Test average() of two colors.
     */
    public function testAverageTwoColors(): void
    {
        $color1 = new Color(0, 0, 0);
        $color2 = new Color(100, 100, 100);
        $avg = Color::average($color1, $color2);
        $this->assertSame(50, $avg->red);
        $this->assertSame(50, $avg->green);
        $this->assertSame(50, $avg->blue);
    }

    /**
     * Test average() of three colors.
     */
    public function testAverageThreeColors(): void
    {
        $color1 = new Color(0, 0, 0);
        $color2 = new Color(30, 60, 90);
        $color3 = new Color(60, 90, 120);
        $avg = Color::average($color1, $color2, $color3);
        $this->assertSame(30, $avg->red);
        $this->assertSame(50, $avg->green);
        $this->assertSame(70, $avg->blue);
    }

    /**
     * Test average() also averages alpha channel.
     */
    public function testAverageWithAlpha(): void
    {
        $color1 = new Color(0, 0, 0, 100);
        $color2 = new Color(0, 0, 0, 200);
        $avg = Color::average($color1, $color2);
        $this->assertSame(150, $avg->alpha);
    }

    /**
     * Test average() premultiplies alpha, agreeing with mix().
     */
    public function testAverageWithTransparent(): void
    {
        $red = Color::fromString('red');
        $transparent = Color::fromString('transparent');
        $this->assertSame('#ff000080', Color::average($red, $transparent)->toHex());
        $this->assertSame($red->mix($transparent)->toHex(), Color::average($red, $transparent)->toHex());
    }

    /**
     * Test average() throws LengthException with no arguments.
     */
    public function testAverageNoColors(): void
    {
        $this->expectException(LengthException::class);
        $this->expectExceptionMessage('Cannot average zero colors.');
        Color::average();
    }

    #endregion

    #region Gamma tests

    /**
     * Test gamma() returns 0 for byte value 0 (black).
     */
    public function testGammaBlack(): void
    {
        $this->assertEqualsWithDelta(0.0, Color::gamma(0), 0.001);
    }

    /**
     * Test gamma() returns 1 for byte value 255 (white).
     */
    public function testGammaWhite(): void
    {
        $this->assertEqualsWithDelta(1.0, Color::gamma(255), 0.001);
    }

    /**
     * Test gamma() for a mid-range input, on the power-curve branch.
     */
    public function testGammaMidRange(): void
    {
        $this->assertEqualsWithDelta(0.2158605, Color::gamma(128), 1e-7);
    }

    /**
     * Data provider for invalid gamma() bytes.
     *
     * @return array<string, array{int}>
     */
    public static function invalidGammaByteProvider(): array
    {
        return [
            'negative' => [-5],
            'too high' => [300],
        ];
    }

    /**
     * Test gamma() throws DomainException for an out-of-range byte.
     */
    #[DataProvider('invalidGammaByteProvider')]
    public function testGammaInvalid(int $byte): void
    {
        $this->expectException(DomainException::class);
        Color::gamma($byte);
    }

    /**
     * Test gamma() for a low input, on the linear branch.
     */
    public function testGammaLinearBranch(): void
    {
        $this->assertEqualsWithDelta(10 / 255 / 12.92, Color::gamma(10), 1e-12);
    }

    #endregion

    #region Hex string conversion tests

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

    /**
     * Test nameToHex() returns correct hex for color name.
     */
    public function testNameToHex(): void
    {
        $this->assertSame('ff0000ff', Color::nameToHex('red'));
        $this->assertSame('fafad2ff', Color::nameToHex('LIGHTGOLDENRODYELLOW'));
        $this->assertSame('fafad2ff', Color::nameToHex(' LightGoldenrodYellow '));
    }

    /**
     * Test nameToBytes() returns correct bytes for color name.
     */
    public function testNameToBytes(): void
    {
        $this->assertSame([255, 0, 0, 255], Color::nameToBytes('red'));
    }

    /**
     * Test nameToBytes() throws DomainException for invalid color name.
     */
    public function testNameToBytesInvalid(): void
    {
        $this->expectException(DomainException::class);
        Color::nameToBytes('notacolor');
    }

    #endregion

    #region Edge case tests

    /**
     * Test zero saturation produces gray regardless of hue.
     */
    public function testEdgeCaseZeroSaturation(): void
    {
        $color = Color::fromHsla(120, 0, 50);
        $this->assertSame(128, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(128, $color->blue);
    }

    /**
     * Test max saturation with 50% lightness produces pure color.
     */
    public function testEdgeCaseMaxSaturation(): void
    {
        $color = Color::fromHsla(120, 100, 50);
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test zero lightness always produces black.
     */
    public function testEdgeCaseZeroLightness(): void
    {
        $color = Color::fromHsla(120, 100, 0);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test max lightness always produces white.
     */
    public function testEdgeCaseMaxLightness(): void
    {
        $color = Color::fromHsla(120, 100, 100);
        $this->assertSame(255, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test hue 360 equals hue 0.
     */
    public function testEdgeCaseHue360(): void
    {
        $color1 = Color::fromHsla(0, 100, 50);
        $color2 = Color::fromHsla(360, 100, 50);
        $this->assertTrue($color1->equal($color2));
    }

    /**
     * Test negative hue wraps correctly.
     */
    public function testEdgeCaseNegativeHue(): void
    {
        $color1 = Color::fromHsla(120, 100, 50);
        $color2 = Color::fromHsla(-240, 100, 50);
        $this->assertTrue($color1->equal($color2));
    }

    #endregion
}
