<?php

declare(strict_types=1);

namespace Galaxon\Color\Tests;

use ArgumentCountError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Galaxon\Color\Color;
use RangeException;
use ValueError;

/**
 * Test class for Color.
 */
#[CoversClass(Color::class)]
final class ColorTest extends TestCase
{
    // region Constructor tests

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
     * Test constructor with 3-digit hex string.
     */
    public function testConstructHex3Digits(): void
    {
        $color = new Color('#0f8');
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(136, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test constructor with 4-digit hex string (includes alpha).
     */
    public function testConstructHex4Digits(): void
    {
        $color = new Color('#1234');
        $this->assertSame(0x11, $color->red);
        $this->assertSame(0x22, $color->green);
        $this->assertSame(0x33, $color->blue);
        $this->assertSame(0x44, $color->alpha);
    }

    /**
     * Test constructor with 6-digit hex string.
     */
    public function testConstructHex6Digits(): void
    {
        $color = new Color('#ff8000');
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test constructor with 8-digit hex string (includes alpha).
     */
    public function testConstructHex8Digits(): void
    {
        $color = new Color('#ff800080');
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(128, $color->alpha);
    }

    /**
     * Test constructor accepts hex string without leading hash.
     */
    public function testConstructHexWithoutHash(): void
    {
        $color = new Color('ff0000');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test constructor accepts upper-case hex digits.
     */
    public function testConstructHexUpperCase(): void
    {
        $color = new Color('#FF00FF');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test constructor with lower-case color name.
     */
    public function testConstructColorNameLowerCase(): void
    {
        $color = new Color('red');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test constructor with upper-case color name.
     */
    public function testConstructColorNameUpperCase(): void
    {
        $color = new Color('RED');
        $this->assertSame(255, $color->red);
    }

    /**
     * Test constructor with mixed-case color name.
     */
    public function testConstructColorNameMixedCase(): void
    {
        $color = new Color('ReD');
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test constructor with 'transparent' color name.
     */
    public function testConstructTransparent(): void
    {
        $color = new Color('transparent');
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(0, $color->alpha);
    }

    /**
     * Test constructor throws ValueError for invalid color name.
     */
    public function testConstructInvalidColorName(): void
    {
        $this->expectException(ValueError::class);
        new Color('notacolor');
    }

    /**
     * Test constructor throws ValueError for invalid hex string.
     */
    public function testConstructInvalidHexString(): void
    {
        $this->expectException(ValueError::class);
        new Color('#gg0000');
    }

    // endregion

    // region Factory method tests

    /**
     * Test fromRGB() creates color with specified RGBA values.
     */
    public function testFromRGB(): void
    {
        $color = Color::fromRGB(10, 20, 30, 127);
        $this->assertSame(10, $color->red);
        $this->assertSame(20, $color->green);
        $this->assertSame(30, $color->blue);
        $this->assertSame(127, $color->alpha);
    }

    /**
     * Test fromRGB() defaults alpha to 255 (fully opaque).
     */
    public function testFromRGBDefaultAlpha(): void
    {
        $color = Color::fromRGB(100, 150, 200);
        $this->assertSame(100, $color->red);
        $this->assertSame(150, $color->green);
        $this->assertSame(200, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test fromRGB() throws RangeException for invalid red value.
     */
    public function testFromRGBInvalidRed(): void
    {
        $this->expectException(RangeException::class);
        Color::fromRGB(256, 0, 0);
    }

    /**
     * Test fromRGB() throws RangeException for invalid green value.
     */
    public function testFromRGBInvalidGreen(): void
    {
        $this->expectException(RangeException::class);
        Color::fromRGB(0, -1, 0);
    }

    /**
     * Test fromRGB() throws RangeException for invalid blue value.
     */
    public function testFromRGBInvalidBlue(): void
    {
        $this->expectException(RangeException::class);
        Color::fromRGB(0, 0, 300);
    }

    /**
     * Test fromRGB() throws RangeException for invalid alpha value.
     */
    public function testFromRGBInvalidAlpha(): void
    {
        $this->expectException(RangeException::class);
        Color::fromRGB(0, 0, 0, 256);
    }

    /**
     * Test fromRGB() with float arguments (0.0 to 1.0 range).
     */
    public function testFromRGBWithFloats(): void
    {
        $color = Color::fromRGB(1.0, 0.5, 0.0);
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test fromRGB() with float alpha.
     */
    public function testFromRGBWithFloatAlpha(): void
    {
        $color = Color::fromRGB(255, 128, 0, 0.5);
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(128, $color->alpha);
    }

    /**
     * Test fromRGB() with mixed int and float arguments.
     */
    public function testFromRGBMixedIntAndFloat(): void
    {
        $color = Color::fromRGB(255, 0.5, 0, 1.0);
        $this->assertSame(255, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test fromRGB() with float 0.0 produces byte 0.
     */
    public function testFromRGBFloatZero(): void
    {
        $color = Color::fromRGB(0.0, 0.0, 0.0, 0.0);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(0, $color->alpha);
    }

    /**
     * Test fromRGB() with float 1.0 produces byte 255.
     */
    public function testFromRGBFloatOne(): void
    {
        $color = Color::fromRGB(1.0, 1.0, 1.0, 1.0);
        $this->assertSame(255, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(255, $color->blue);
        $this->assertSame(255, $color->alpha);
    }

    /**
     * Test fromHSLa() creates pure red (hue=0).
     */
    public function testFromHSLPureRed(): void
    {
        $color = Color::fromHSL(0, 1, 0.5);
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromHSL() creates pure green (hue=120).
     */
    public function testFromHSLPureGreen(): void
    {
        $color = Color::fromHSL(120, 1, 0.5);
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromHSL() creates pure blue (hue=240).
     */
    public function testFromHSLPureBlue(): void
    {
        $color = Color::fromHSL(240, 1, 0.5);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test fromHSL() with alpha value.
     */
    public function testFromHSLWithAlpha(): void
    {
        $color = Color::fromHSL(120, 1, 0.5, 64);
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(64, $color->alpha);
    }

    /**
     * Test fromHSL() creates gray when saturation is zero.
     */
    public function testFromHSLGray(): void
    {
        $color = Color::fromHSL(0, 0, 0.5);
        $this->assertSame(128, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(128, $color->blue);
    }

    /**
     * Test fromHSL() creates black when lightness is zero.
     */
    public function testFromHSLBlack(): void
    {
        $color = Color::fromHSL(0, 0, 0);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test fromHSL() creates white when lightness is one.
     */
    public function testFromHSLWhite(): void
    {
        $color = Color::fromHSL(0, 0, 1);
        $this->assertSame(255, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test fromHSL() correctly wraps hue values outside [0, 360).
     */
    public function testFromHSLHueWrapping(): void
    {
        $color1 = Color::fromHSL(0, 1, 0.5);
        $color2 = Color::fromHSL(360, 1, 0.5);
        $color3 = Color::fromHSL(-360, 1, 0.5);
        $this->assertTrue($color1->equals($color2));
        $this->assertTrue($color1->equals($color3));
    }

    /**
     * Test fromHSL() throws RangeException for invalid saturation.
     */
    public function testFromHSLInvalidSaturation(): void
    {
        $this->expectException(RangeException::class);
        Color::fromHSL(0, 1.5, 0.5);
    }

    /**
     * Test fromHSL() throws RangeException for invalid lightness.
     */
    public function testFromHSLInvalidLightness(): void
    {
        $this->expectException(RangeException::class);
        Color::fromHSL(0, 0.5, -0.1);
    }

    /**
     * Test fromHSL() with float alpha (0.0 to 1.0 range).
     */
    public function testFromHSLWithFloatAlpha(): void
    {
        $color = Color::fromHSL(120, 1.0, 0.5, 0.5);
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(0, $color->blue);
        $this->assertSame(128, $color->alpha);
    }

    /**
     * Test fromHSL() with float alpha 0.0 produces byte 0.
     */
    public function testFromHSLFloatAlphaZero(): void
    {
        $color = Color::fromHSL(0, 1.0, 0.5, 0.0);
        $this->assertSame(0, $color->alpha);
    }

    /**
     * Test fromHSL() with float alpha 1.0 produces byte 255.
     */
    public function testFromHSLFloatAlphaOne(): void
    {
        $color = Color::fromHSL(0, 1.0, 0.5, 1.0);
        $this->assertSame(255, $color->alpha);
    }

    // endregion

    // region Immutable setter tests

    /**
     * Test withRed() returns new Color with updated red, original unchanged.
     */
    public function testWithRed(): void
    {
        $color = new Color('black');
        $newColor = $color->withRed(128);
        $this->assertSame(0, $color->red);
        $this->assertSame(128, $newColor->red);
    }

    /**
     * Test withGreen() returns new Color with updated green, original unchanged.
     */
    public function testWithGreen(): void
    {
        $color = new Color('black');
        $newColor = $color->withGreen(200);
        $this->assertSame(0, $color->green);
        $this->assertSame(200, $newColor->green);
    }

    /**
     * Test withBlue() returns new Color with updated blue, original unchanged.
     */
    public function testWithBlue(): void
    {
        $color = new Color('black');
        $newColor = $color->withBlue(40);
        $this->assertSame(0, $color->blue);
        $this->assertSame(40, $newColor->blue);
    }

    /**
     * Test withAlpha() returns new Color with updated alpha, original unchanged.
     */
    public function testWithAlpha(): void
    {
        $color = new Color('black');
        $newColor = $color->withAlpha(100);
        $this->assertSame(255, $color->alpha);
        $this->assertSame(100, $newColor->alpha);
    }

    /**
     * Test withRed() throws RangeException for invalid value.
     */
    public function testWithRedInvalid(): void
    {
        $color = new Color('black');
        $this->expectException(RangeException::class);
        $color->withRed(256);
    }

    /**
     * Test withHue() returns new Color with updated hue, original unchanged.
     */
    public function testWithHue(): void
    {
        $color = new Color('red');
        $newColor = $color->withHue(120);
        $this->assertEqualsWithDelta(0, $color->hue, 0.01);
        $this->assertEqualsWithDelta(120, $newColor->hue, 0.01);
        $this->assertSame(0, $newColor->red);
        $this->assertSame(255, $newColor->green);
        $this->assertSame(0, $newColor->blue);
    }

    /**
     * Test withSaturation() returns new Color with updated saturation, original unchanged.
     */
    public function testWithSaturation(): void
    {
        $color = Color::fromHSL(0, 1, 0.5);
        $newColor = $color->withSaturation(0.5);
        $this->assertEqualsWithDelta(1.0, $color->saturation, 0.01);
        $this->assertEqualsWithDelta(0.5, $newColor->saturation, 0.01);
    }

    /**
     * Test withLightness() returns new Color with updated lightness, original unchanged.
     */
    public function testWithLightness(): void
    {
        $color = Color::fromHSL(0, 1, 0.5);
        $newColor = $color->withLightness(0.25);
        $this->assertEqualsWithDelta(0.5, $color->lightness, 0.01);
        $this->assertEqualsWithDelta(0.25, $newColor->lightness, 0.01);
    }

    /**
     * Test withSaturation() throws RangeException for invalid value.
     */
    public function testWithSaturationInvalid(): void
    {
        $color = new Color('red');
        $this->expectException(RangeException::class);
        $color->withSaturation(1.5);
    }

    /**
     * Test withLightness() throws RangeException for invalid value.
     */
    public function testWithLightnessInvalid(): void
    {
        $color = new Color('red');
        $this->expectException(RangeException::class);
        $color->withLightness(-0.1);
    }

    /**
     * Test chaining multiple with*() methods preserves immutability.
     */
    public function testImmutabilityChaining(): void
    {
        $color = new Color('red');
        $newColor = $color->withRed(128)->withGreen(64)->withAlpha(200);
        $this->assertSame(255, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $color->alpha);
        $this->assertSame(128, $newColor->red);
        $this->assertSame(64, $newColor->green);
        $this->assertSame(200, $newColor->alpha);
    }

    // endregion

    // region Conversion method tests

    /**
     * Test toRGB() returns correct RGBA array.
     */
    public function testToRGB(): void
    {
        $color = Color::fromRGB(10, 20, 30, 40);
        $rgba = $color->toRGBArray();
        $this->assertSame(10, $rgba['red']);
        $this->assertSame(20, $rgba['green']);
        $this->assertSame(30, $rgba['blue']);
        $this->assertSame(40, $rgba['alpha']);
    }

    /**
     * Test toHSL() returns correct HSLA array.
     */
    public function testToHSL(): void
    {
        $color = Color::fromHSL(120, 0.5, 0.75, 128);
        $hsla = $color->toHSLArray();
        $this->assertEqualsWithDelta(120, $hsla['hue'], 0.1);
        $this->assertEqualsWithDelta(0.5, $hsla['saturation'], 0.01);
        $this->assertEqualsWithDelta(0.75, $hsla['lightness'], 0.01);
        $this->assertSame(128, $color->alpha);
    }

    /**
     * Test toArray() returns array with all color properties.
     */
    public function testToArray(): void
    {
        $color = Color::fromRGB(100, 150, 200, 250);
        $array = $color->toArray();
        $this->assertArrayHasKey('red', $array);
        $this->assertArrayHasKey('green', $array);
        $this->assertArrayHasKey('blue', $array);
        $this->assertArrayHasKey('alpha', $array);
        $this->assertArrayHasKey('hue', $array);
        $this->assertArrayHasKey('saturation', $array);
        $this->assertArrayHasKey('lightness', $array);
    }

    // endregion

    // region RGB to HSL conversion tests

    /**
     * Data provider for RGB to HSL conversion tests.
     *
     * @return array<string, array{int, int, int, float, float, float}>
     */
    public static function RGBToHSLProvider(): array
    {
        return [
            'red' => [255, 0, 0, 0.0, 1.0, 0.5],
            'green' => [0, 255, 0, 120.0, 1.0, 0.5],
            'blue' => [0, 0, 255, 240.0, 1.0, 0.5],
            'black' => [0, 0, 0, 0.0, 0.0, 0.0],
            'white' => [255, 255, 255, 0.0, 0.0, 1.0],
            'gray' => [128, 128, 128, 0.0, 0.0, 0.502],
        ];
    }

    /**
     * Test RGBToHSL() converts RGB values to HSL correctly.
     */
    #[DataProvider('RGBToHSLProvider')]
    public function testRGBToHSL(int $r, int $g, int $b, float $h, float $s, float $l): void
    {
        $hsl = Color::RGBToHSL($r, $g, $b);
        $this->assertEqualsWithDelta($h, $hsl[0], 0.1);
        $this->assertEqualsWithDelta($s, $hsl[1], 0.01);
        $this->assertEqualsWithDelta($l, $hsl[2], 0.01);
    }

    // endregion

    // region HSL to RGB conversion tests

    /**
     * Data provider for HSL to RGB conversion tests.
     *
     * @return array<string, array{float, float, float, int, int, int}>
     */
    public static function HSLToRGBProvider(): array
    {
        return [
            'red' => [0.0, 1.0, 0.5, 255, 0, 0],
            'green' => [120.0, 1.0, 0.5, 0, 255, 0],
            'blue' => [240.0, 1.0, 0.5, 0, 0, 255],
            'black' => [0.0, 0.0, 0.0, 0, 0, 0],
            'white' => [0.0, 0.0, 1.0, 255, 255, 255],
            'gray' => [0.0, 0.0, 0.5, 128, 128, 128],
        ];
    }

    /**
     * Test HSLToRGB() converts HSL values to RGB correctly.
     */
    #[DataProvider('HSLToRGBProvider')]
    public function testHSLToRGB(float $h, float $s, float $l, int $r, int $g, int $b): void
    {
        $rgb = Color::HSLToRGB($h, $s, $l);
        $this->assertSame($r, $rgb[0]);
        $this->assertSame($g, $rgb[1]);
        $this->assertSame($b, $rgb[2]);
    }

    // endregion

    // region Round-trip conversion tests

    /**
     * Test RGB -> HSL -> RGB round-trip preserves values.
     */
    public function testRGBToHSLToRGBRoundTrip(): void
    {
        $original = Color::fromRGB(123, 234, 45);
        $hsl = $original->toHSLArray();
        $roundTrip = Color::fromHSL($hsl['hue'], $hsl['saturation'], $hsl['lightness']);
        $this->assertSame($original->red, $roundTrip->red);
        $this->assertSame($original->green, $roundTrip->green);
        $this->assertSame($original->blue, $roundTrip->blue);
    }

    /**
     * Test HSL -> RGB -> HSL round-trip preserves values.
     */
    public function testHSLToRGBToHSLRoundTrip(): void
    {
        $original = Color::fromHSL(75, 0.6, 0.4);
        $rgb = $original->toRGBArray();
        $roundTrip = Color::fromRGB($rgb['red'], $rgb['green'], $rgb['blue']);
        $this->assertEqualsWithDelta($original->hue, $roundTrip->hue, 0.25);
        $this->assertEqualsWithDelta($original->saturation, $roundTrip->saturation, 0.01);
        $this->assertEqualsWithDelta($original->lightness, $roundTrip->lightness, 0.01);
    }

    // endregion

    // region String output tests

    /**
     * Test toHexString() with default options (includes alpha and hash).
     */
    public function testToHexStringDefault(): void
    {
        $color = Color::fromRGB(255, 128, 0, 255);
        $this->assertSame('#ff8000ff', $color->toHex());
    }

    /**
     * Test toHexString() without alpha.
     */
    public function testToHexStringNoAlpha(): void
    {
        $color = Color::fromRGB(255, 128, 0);
        $this->assertSame('#ff8000', $color->toHex(false));
    }

    /**
     * Test toHexString() without leading hash.
     */
    public function testToHexStringNoHash(): void
    {
        $color = Color::fromRGB(255, 128, 0);
        $this->assertSame('ff8000ff', $color->toHex(true, false));
    }

    /**
     * Test toHexString() with upper-case output.
     */
    public function testToHexStringUpperCase(): void
    {
        $color = Color::fromRGB(255, 128, 0);
        $this->assertSame('#FF8000FF', $color->toHex(true, true, true));
    }

    /**
     * Test __toString() returns hex string with alpha.
     */
    public function testToString(): void
    {
        $color = Color::fromRGB(255, 0, 128, 200);
        $this->assertSame('#ff0080c8', (string)$color);
    }

    /**
     * Test toRGBString() returns modern CSS rgb() format with alpha.
     */
    public function testToRGBString(): void
    {
        $color = Color::fromRGB(120, 50, 50);
        $this->assertSame('rgb(120 50 50 / 1)', $color->toRGBString());
    }

    /**
     * Test toRGBString() returns modern CSS rgb() format with partial alpha.
     */
    public function testToRGBStringWithAlpha(): void
    {
        $color = Color::fromRGB(120, 50, 50, 128);
        $this->assertSame('rgb(120 50 50 / 0.501961)', $color->toRGBString());
    }

    /**
     * Test toHSLString() returns modern CSS hsl() format.
     */
    public function testToHSLString(): void
    {
        $color = Color::fromHSL(120, 0.5, 0.5);
        $result = $color->toHSLString();
        $this->assertStringContainsString('hsl(', $result);
        $this->assertStringContainsString('deg', $result);
        $this->assertStringContainsString('%', $result);
        $this->assertStringContainsString('/', $result);
    }

    /**
     * Test toHSLString() returns modern CSS hsl() format with partial alpha.
     */
    public function testToHSLStringWithAlpha(): void
    {
        $color = Color::fromHSL(120, 0.5, 0.5, 128);
        $result = $color->toHSLString();
        $this->assertStringContainsString('hsl(', $result);
        $this->assertStringContainsString('/ 0.501961', $result);
    }

    // endregion

    // region Validation tests

    /**
     * Data provider for valid hex string tests.
     *
     * @return array<array{string}>
     */
    public static function validHexStringProvider(): array
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
     * Test isValidHexString() returns true for valid hex strings.
     */
    #[DataProvider('validHexStringProvider')]
    public function testIsValidHexString(string $hex): void
    {
        $this->assertTrue(Color::isValidHex($hex));
    }

    /**
     * Data provider for invalid hex string tests.
     *
     * @return array<array{string}>
     */
    public static function invalidHexStringProvider(): array
    {
        return [
            [''],
            ['#'],
            ['#ab'],
            ['#abcde'],
            ['#abcdefgh'],
            ['#gggggg'],
            ['notahex'],
        ];
    }

    /**
     * Test isValidHexString() returns false for invalid hex strings.
     */
    #[DataProvider('invalidHexStringProvider')]
    public function testIsValidHexStringInvalid(string $hex): void
    {
        $this->assertFalse(Color::isValidHex($hex));
    }

    /**
     * Data provider for valid color name tests.
     *
     * @return array<array{string}>
     */
    public static function validColorNameProvider(): array
    {
        return [
            ['red'],
            ['RED'],
            ['Blue'],
            ['transparent'],
            ['aliceblue'],
        ];
    }

    /**
     * Test isValidColorName() returns true for valid CSS color names.
     */
    #[DataProvider('validColorNameProvider')]
    public function testIsValidColorName(string $name): void
    {
        $this->assertTrue(Color::isValidName($name));
    }

    /**
     * Test isValidColorName() returns false for invalid color names.
     */
    public function testIsValidColorNameInvalid(): void
    {
        $this->assertFalse(Color::isValidName('notacolor'));
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
     * Test normalizeHex() throws ValueError for invalid hex string.
     */
    public function testNormalizeHexInvalid(): void
    {
        $this->expectException(ValueError::class);
        Color::normalizeHex('notahex');
    }

    /**
     * Test colorNameToHex() throws ValueError for invalid color name.
     */
    public function testColorNameToHexInvalid(): void
    {
        $this->expectException(ValueError::class);
        Color::nameToHex('notacolor');
    }

    // endregion

    // region Equals tests

    /**
     * Test equals() returns true for identical colors.
     */
    public function testEquals(): void
    {
        $color1 = Color::fromRGB(100, 150, 200, 250);
        $color2 = Color::fromRGB(100, 150, 200, 250);
        $this->assertTrue($color1->equals($color2));
    }

    /**
     * Test equals() returns false for different colors.
     */
    public function testNotEquals(): void
    {
        $color1 = Color::fromRGB(100, 150, 200);
        $color2 = Color::fromRGB(100, 150, 201);
        $this->assertFalse($color1->equals($color2));
    }

    /**
     * Test equals() returns false when only alpha differs.
     */
    public function testEqualsWithDifferentAlpha(): void
    {
        $color1 = Color::fromRGB(100, 150, 200, 255);
        $color2 = Color::fromRGB(100, 150, 200, 128);
        $this->assertFalse($color1->equals($color2));
    }

    /**
     * Test equals() returns false when comparing with non-Color.
     */
    public function testEqualsWithNonColor(): void
    {
        $color = new Color('red');
        $this->assertFalse($color->equals('red'));
    }

    // endregion

    // region Relative luminance tests

    /**
     * Test relativeLuminance is 0 for black.
     */
    public function testRelativeLuminanceBlack(): void
    {
        $color = new Color('black');
        $this->assertEqualsWithDelta(0.0, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance is 1 for white.
     */
    public function testRelativeLuminanceWhite(): void
    {
        $color = new Color('white');
        $this->assertEqualsWithDelta(1.0, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance for pure red.
     */
    public function testRelativeLuminanceRed(): void
    {
        $color = new Color('red');
        $this->assertEqualsWithDelta(0.2126, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance for CSS green (#008000).
     */
    public function testRelativeLuminanceGreen(): void
    {
        $color = new Color('green');
        $this->assertEqualsWithDelta(0.1544, $color->relativeLuminance, 0.001);
    }

    /**
     * Test relativeLuminance for pure blue.
     */
    public function testRelativeLuminanceBlue(): void
    {
        $color = new Color('blue');
        $this->assertEqualsWithDelta(0.0722, $color->relativeLuminance, 0.001);
    }

    // endregion

    // region Perceived lightness tests

    /**
     * Test perceivedLightness is 0 for black.
     */
    public function testPerceivedLightnessBlack(): void
    {
        $color = new Color('black');
        $this->assertEqualsWithDelta(0.0, $color->perceivedLightness, 0.001);
    }

    /**
     * Test perceivedLightness is 1 for white.
     */
    public function testPerceivedLightnessWhite(): void
    {
        $color = new Color('white');
        $this->assertEqualsWithDelta(1.0, $color->perceivedLightness, 0.001);
    }

    // endregion

    // region Contrast ratio tests

    /**
     * Test contrastRatio() returns 21:1 for black and white.
     */
    public function testContrastRatioBlackWhite(): void
    {
        $black = new Color('black');
        $white = new Color('white');
        $this->assertEqualsWithDelta(21.0, $black->contrastRatio($white), 0.1);
    }

    /**
     * Test contrastRatio() returns 1:1 for identical colors.
     */
    public function testContrastRatioIdentical(): void
    {
        $color = new Color('red');
        $this->assertEqualsWithDelta(1.0, $color->contrastRatio($color), 0.01);
    }

    /**
     * Test contrastRatio() is symmetric.
     */
    public function testContrastRatioSymmetric(): void
    {
        $color1 = new Color('red');
        $color2 = new Color('blue');
        $this->assertEqualsWithDelta(
            $color1->contrastRatio($color2),
            $color2->contrastRatio($color1),
            0.001
        );
    }

    // endregion

    // region Best text color tests

    /**
     * Test bestTextColor() returns 'white' for black background.
     */
    public function testBestTextColorOnBlack(): void
    {
        $color = new Color('black');
        $this->assertSame('white', $color->bestTextColor());
    }

    /**
     * Test bestTextColor() returns 'black' for white background.
     */
    public function testBestTextColorOnWhite(): void
    {
        $color = new Color('white');
        $this->assertSame('black', $color->bestTextColor());
    }

    /**
     * Test bestTextColor() returns 'white' for dark blue background.
     */
    public function testBestTextColorOnDarkBlue(): void
    {
        $color = new Color('darkblue');
        $this->assertSame('white', $color->bestTextColor());
    }

    /**
     * Test bestTextColor() returns 'black' for yellow background.
     */
    public function testBestTextColorOnYellow(): void
    {
        $color = new Color('yellow');
        $this->assertSame('black', $color->bestTextColor());
    }

    // endregion

    // region Mix tests

    /**
     * Test mix() at 50% blends colors equally.
     */
    public function testMix50Percent(): void
    {
        $color1 = Color::fromRGB(0, 0, 0);
        $color2 = Color::fromRGB(100, 100, 100);
        $mixed = $color1->mix($color2, 0.5);
        $this->assertSame(50, $mixed->red);
        $this->assertSame(50, $mixed->green);
        $this->assertSame(50, $mixed->blue);
    }

    /**
     * Test mix() at 100% returns the other color.
     */
    public function testMix100Percent(): void
    {
        $color1 = Color::fromRGB(255, 0, 0);
        $color2 = Color::fromRGB(0, 255, 0);
        $mixed = $color1->mix($color2, 1.0);
        $this->assertTrue($color2->equals($mixed));
    }

    /**
     * Test mix() at 0% returns the original color.
     */
    public function testMix0Percent(): void
    {
        $color1 = Color::fromRGB(255, 0, 0);
        $color2 = Color::fromRGB(0, 255, 0);
        $mixed = $color1->mix($color2, 0.0);
        $this->assertTrue($color1->equals($mixed));
    }

    /**
     * Test mix() also blends alpha channel.
     */
    public function testMixWithAlpha(): void
    {
        $color1 = Color::fromRGB(0, 0, 0, 0);
        $color2 = Color::fromRGB(100, 100, 100, 100);
        $mixed = $color1->mix($color2, 0.5);
        $this->assertSame(50, $mixed->alpha);
    }

    /**
     * Test mix() throws RangeException for invalid fraction.
     */
    public function testMixInvalidFraction(): void
    {
        $color1 = new Color('red');
        $color2 = new Color('blue');
        $this->expectException(RangeException::class);
        $color1->mix($color2, 1.5);
    }

    // endregion

    // region Complement tests

    /**
     * Test complement() shifts hue by 180 degrees.
     */
    public function testComplementRed(): void
    {
        $color = new Color('red');
        $complement = $color->complement();
        $this->assertEqualsWithDelta(180, $complement->hue, 0.1);
    }

    /**
     * Test complement() of green (hue=120) is magenta (hue=300).
     */
    public function testComplementGreen(): void
    {
        $color = new Color('green');
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
        $color = Color::fromHSL(60, 0.5, 0.5, 128);
        $complement = $color->complement();
        $this->assertSame(128, $complement->alpha);
    }

    /**
     * Test complement() preserves saturation.
     */
    public function testComplementPreservesSaturation(): void
    {
        $color = Color::fromHSL(60, 0.7, 0.5);
        $complement = $color->complement();
        $this->assertEqualsWithDelta(0.7, $complement->saturation, 0.01);
    }

    /**
     * Test complement() preserves lightness.
     */
    public function testComplementPreservesLightness(): void
    {
        $color = Color::fromHSL(60, 0.5, 0.3);
        $complement = $color->complement();
        $this->assertEqualsWithDelta(0.3, $complement->lightness, 0.01);
    }

    // endregion

    // region Average tests

    /**
     * Test average() of single color returns that color.
     */
    public function testAverageSingleColor(): void
    {
        $color = new Color('red');
        $avg = Color::average($color);
        $this->assertTrue($color->equals($avg));
    }

    /**
     * Test average() of two colors.
     */
    public function testAverageTwoColors(): void
    {
        $color1 = Color::fromRGB(0, 0, 0);
        $color2 = Color::fromRGB(100, 100, 100);
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
        $color1 = Color::fromRGB(0, 0, 0);
        $color2 = Color::fromRGB(30, 60, 90);
        $color3 = Color::fromRGB(60, 90, 120);
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
        $color1 = Color::fromRGB(0, 0, 0, 100);
        $color2 = Color::fromRGB(0, 0, 0, 200);
        $avg = Color::average($color1, $color2);
        $this->assertSame(150, $avg->alpha);
    }

    /**
     * Test average() throws ArgumentCountError with no arguments.
     */
    public function testAverageNoColors(): void
    {
        $this->expectException(ArgumentCountError::class);
        Color::average();
    }

    // endregion

    // region Gamma tests

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
     * Test gamma() returns value between 0 and 1 for mid-range input.
     */
    public function testGammaMidRange(): void
    {
        $result = Color::gamma(128);
        $this->assertGreaterThan(0.0, $result);
        $this->assertLessThan(1.0, $result);
    }

    // endregion

    // region Hex string conversion tests

    /**
     * Test hexStringToBytes() parses 6-digit hex string.
     */
    public function testHexStringToBytes(): void
    {
        $bytes = Color::hexToBytes('#ff8000');
        $this->assertSame(255, $bytes[0]);
        $this->assertSame(128, $bytes[1]);
        $this->assertSame(0, $bytes[2]);
        $this->assertSame(255, $bytes[3]);
    }

    /**
     * Test hexStringToBytes() parses 8-digit hex string with alpha.
     */
    public function testHexStringToBytesWithAlpha(): void
    {
        $bytes = Color::hexToBytes('#ff800080');
        $this->assertSame(255, $bytes[0]);
        $this->assertSame(128, $bytes[1]);
        $this->assertSame(0, $bytes[2]);
        $this->assertSame(128, $bytes[3]);
    }

    /**
     * Test colorNameToHex() returns correct hex for color name.
     */
    public function testColorNameToHex(): void
    {
        $hex = Color::nameToHex('red');
        $this->assertSame('ff0000ff', $hex);
    }

    /**
     * Test nameToBytes() returns correct bytes for color name.
     */
    public function testColorNameToBytes(): void
    {
        $bytes = Color::nameToBytes('red');
        $this->assertSame(255, $bytes[0]);
        $this->assertSame(0, $bytes[1]);
        $this->assertSame(0, $bytes[2]);
        $this->assertSame(255, $bytes[3]);
    }

    /**
     * Test parseToBytes() parses hex string.
     */
    public function testColorStringToBytesHex(): void
    {
        $bytes = Color::parseToBytes('#00ff00');
        $this->assertSame(0, $bytes[0]);
        $this->assertSame(255, $bytes[1]);
        $this->assertSame(0, $bytes[2]);
        $this->assertSame(255, $bytes[3]);
    }

    /**
     * Test parseToBytes() parses color name.
     */
    public function testColorStringToBytesName(): void
    {
        $bytes = Color::parseToBytes('blue');
        $this->assertSame(0, $bytes[0]);
        $this->assertSame(0, $bytes[1]);
        $this->assertSame(255, $bytes[2]);
        $this->assertSame(255, $bytes[3]);
    }

    // endregion

    // region Edge case tests

    /**
     * Test zero saturation produces gray regardless of hue.
     */
    public function testEdgeCaseZeroSaturation(): void
    {
        $color = Color::fromHSL(120, 0, 0.5);
        $this->assertSame(128, $color->red);
        $this->assertSame(128, $color->green);
        $this->assertSame(128, $color->blue);
    }

    /**
     * Test max saturation with 50% lightness produces pure color.
     */
    public function testEdgeCaseMaxSaturation(): void
    {
        $color = Color::fromHSL(120, 1, 0.5);
        $this->assertSame(0, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test zero lightness always produces black.
     */
    public function testEdgeCaseZeroLightness(): void
    {
        $color = Color::fromHSL(120, 1, 0);
        $this->assertSame(0, $color->red);
        $this->assertSame(0, $color->green);
        $this->assertSame(0, $color->blue);
    }

    /**
     * Test max lightness always produces white.
     */
    public function testEdgeCaseMaxLightness(): void
    {
        $color = Color::fromHSL(120, 1, 1);
        $this->assertSame(255, $color->red);
        $this->assertSame(255, $color->green);
        $this->assertSame(255, $color->blue);
    }

    /**
     * Test hue 360 equals hue 0.
     */
    public function testEdgeCaseHue360(): void
    {
        $color1 = Color::fromHSL(0, 1, 0.5);
        $color2 = Color::fromHSL(360, 1, 0.5);
        $this->assertTrue($color1->equals($color2));
    }

    /**
     * Test negative hue wraps correctly.
     */
    public function testEdgeCaseNegativeHue(): void
    {
        $color1 = Color::fromHSL(120, 1, 0.5);
        $color2 = Color::fromHSL(-240, 1, 0.5);
        $this->assertTrue($color1->equals($color2));
    }

    // endregion
}
