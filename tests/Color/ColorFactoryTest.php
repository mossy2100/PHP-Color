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
 * Tests for Color factory methods.
 */
#[CoversClass(Color::class)]
final class ColorFactoryTest extends TestCase
{
    #region Method fromString() tests.

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
     * Data provider for fromString() with CSS and extra color names in different cases.
     *
     * @return array<string, array{string, string}>
     */
    public static function colorNameCaseProvider(): array
    {
        return [
            'CSS name, canonical' => ['LightGoldenrodYellow', '#fafad2ff'],
            'CSS name, lower case' => ['lightgoldenrodyellow', '#fafad2ff'],
            'CSS name, upper case' => ['LIGHTGOLDENRODYELLOW', '#fafad2ff'],
            'extra name, canonical' => ['AirForceBlue', '#5d8aa8ff'],
            'extra name, lower case' => ['airforceblue', '#5d8aa8ff'],
            'extra name, upper case' => ['AIRFORCEBLUE', '#5d8aa8ff'],
        ];
    }

    /**
     * Test fromString() accepts CSS and extra color names in any case.
     */
    #[DataProvider('colorNameCaseProvider')]
    public function testFromStringColorNameCase(string $name, string $hex): void
    {
        $this->assertSame($hex, Color::fromString($name)->toHex());
    }

    /**
     * Test fromString() accepts extra color names.
     */
    public function testExtraColorNames(): void
    {
        $this->assertSame('#9a63c6ff', Color::fromString('amethyst')->toHex());
    }

    /**
     * Test fromString() ignores spaces, hyphens and underscores in names, and accepts 'grey' for 'gray'.
     */
    public function testFromStringLenientName(): void
    {
        $this->assertSame('#5d8aa8ff', Color::fromString('air force blue')->toHex());
        $this->assertSame('#5d8aa8ff', Color::fromString('air-force_blue')->toHex());
        $this->assertSame('#6d6c6cff', Color::fromString('Dove Grey')->toHex());
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
     * Test 'transparent' is a known name.
     */
    public function testTransparentName(): void
    {
        $this->assertSame('#00000000', Color::fromString(' TRANSPARENT ')->toHex());
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

    #endregion

    #region Method fromRgba() tests.

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
            'red as byte' => [255, 0, 0, 100],
            'green negative' => [0, -1, 0, 100],
            'blue too high' => [0, 0, 100.1, 100],
            'alpha too high' => [0, 0, 0, 101],
            'red NAN' => [NAN, 0, 0, 100],
            'alpha INF' => [0, 0, 0, INF],
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

    #endregion

    #region Method fromHsla() tests.

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
        $this->assertSame($color2->toHex(), $color1->toHex());
        $this->assertSame($color3->toHex(), $color1->toHex());
    }

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
        $this->assertSame($color2->toHex(), $color1->toHex());
    }

    /**
     * Test negative hue wraps correctly.
     */
    public function testEdgeCaseNegativeHue(): void
    {
        $color1 = Color::fromHsla(120, 100, 50);
        $color2 = Color::fromHsla(-240, 100, 50);
        $this->assertSame($color2->toHex(), $color1->toHex());
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
            'saturation NAN' => [NAN, 50, 100],
            'lightness negative' => [50, -10, 100],
            'alpha too high' => [50, 50, 101],
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
     * Test fromHsla() throws DomainException for a non-finite hue.
     */
    public function testFromHslaNonFiniteHue(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Invalid hue: NAN. Must be finite.');
        Color::fromHsla(NAN, 100, 50);
    }

    /**
     * Test fromHsla() derives the HSL properties from the RGB bytes, so equal colors report equal HSL values.
     */
    public function testFromHslaPropertiesMatchEqualColor(): void
    {
        $black = Color::fromHsla(120, 100, 0);
        $this->assertSame(new Color()->toHex(), $black->toHex());
        $this->assertSame(['hue' => 0.0, 'saturation' => 0.0, 'lightness' => 0.0], $black->toHslArray());

        $color = Color::fromHsla(10.123, 50, 50);
        $this->assertSame(Color::fromString($color->toHex())->toHslArray(), $color->toHslArray());
    }

    #endregion

    #region Round-trip tests.

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
}
