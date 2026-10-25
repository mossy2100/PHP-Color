<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use DomainException;
use Mossy2100\Color\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color transformation methods.
 */
#[CoversClass(Color::class)]
final class ColorTransformationTest extends TestCase
{
    #region Method withRed() tests.

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

    #endregion

    #region Method withGreen() tests.

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
     * Test withGreen() with a percentage.
     */
    public function testWithGreenPercent(): void
    {
        $color = new Color();
        $newColor = $color->withGreen(100, true);
        $this->assertSame(0, $color->green);
        $this->assertSame(255, $newColor->green);
    }

    #endregion

    #region Method withBlue() tests.

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
     * Test withBlue() with a percentage.
     */
    public function testWithBluePercent(): void
    {
        $color = new Color(0, 0, 255);
        $newColor = $color->withBlue(0, true);
        $this->assertSame(255, $color->blue);
        $this->assertSame(0, $newColor->blue);
    }

    #endregion

    #region Method withAlpha() tests.

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
     * Data provider for invalid percentages.
     *
     * @return array<string, array{float}>
     */
    public static function invalidPercentProvider(): array
    {
        return [
            'too high' => [101],
            'negative' => [-1],
            'NAN'      => [NAN],
            'INF'      => [INF],
        ];
    }

    /**
     * Test withAlpha() throws DomainException for an out-of-range or non-finite percentage.
     */
    #[DataProvider('invalidPercentProvider')]
    public function testWithAlphaPercentInvalid(float $percent): void
    {
        $this->expectException(DomainException::class);
        new Color()->withAlpha($percent, true);
    }

    #endregion

    #region Method withHue() tests.

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
     * Test withHue() preserves alpha.
     */
    public function testWithHuePreservesAlpha(): void
    {
        $color = new Color(255, 0, 0, 77);
        $this->assertSame(77, $color->withHue(200)->alpha);
    }

    /**
     * Test withHue() throws DomainException for a non-finite hue.
     */
    public function testWithHueNonFinite(): void
    {
        $this->expectException(DomainException::class);
        Color::fromString('red')->withHue(INF);
    }

    #endregion

    #region Method withSaturation() tests.

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
     * Test withSaturation() throws DomainException for an out-of-range or non-finite percentage.
     */
    #[DataProvider('invalidPercentProvider')]
    public function testWithSaturationInvalidPercent(float $percent): void
    {
        $this->expectException(DomainException::class);
        Color::fromString('red')->withSaturation($percent);
    }

    #endregion

    #region Method withLightness() tests.

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
     * Test withLightness() throws DomainException for an out-of-range or non-finite percentage.
     */
    #[DataProvider('invalidPercentProvider')]
    public function testWithLightnessInvalidPercent(float $percent): void
    {
        $this->expectException(DomainException::class);
        Color::fromString('red')->withLightness($percent);
    }

    #endregion

    #region Full saturation tests.

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

    #endregion

    #region Chaining tests.

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

    #region Method mix() tests.

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
        $this->assertSame($color2, $color1->mix($color2, 100));
    }

    /**
     * Test mix() at 0% returns the original color.
     */
    public function testMix0Percent(): void
    {
        $color1 = new Color(255, 0, 0);
        $color2 = new Color(0, 255, 0);
        $this->assertSame($color1, $color1->mix($color2, 0));
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
     * Test mix() rounds a half-way component away from zero.
     */
    public function testMixRoundsHalfUp(): void
    {
        $mixed = new Color(0, 0, 0)->mix(new Color(1, 3, 5));
        $this->assertSame([1, 2, 3, 255], array_values($mixed->toRgbaArray()));
    }

    /**
     * Test mix() throws DomainException for an out-of-range or non-finite percentage.
     */
    #[DataProvider('invalidPercentProvider')]
    public function testMixInvalidPercent(float $percent): void
    {
        $color1 = Color::fromString('red');
        $color2 = Color::fromString('blue');
        $this->expectException(DomainException::class);
        $color1->mix($color2, $percent);
    }

    #endregion

    #region Method complement() tests.

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
}
