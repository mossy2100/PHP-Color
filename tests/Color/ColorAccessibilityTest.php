<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use DomainException;
use Mossy2100\Color\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color accessibility methods.
 */
#[CoversClass(Color::class)]
final class ColorAccessibilityTest extends TestCase
{
    #region Method contrastRatio() tests.

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

    #region Method bestTextColor() tests.

    /**
     * Test bestTextColor() returns white for black background.
     */
    public function testBestTextColorOnBlack(): void
    {
        $color = Color::fromString('black');
        $result = $color->bestTextColor();
        $this->assertInstanceOf(Color::class, $result);
        $this->assertSame(Color::fromString('white')->toHex(), $result->toHex());
    }

    /**
     * Test bestTextColor() returns black for white background.
     */
    public function testBestTextColorOnWhite(): void
    {
        $color = Color::fromString('white');
        $result = $color->bestTextColor();
        $this->assertSame(Color::fromString('black')->toHex(), $result->toHex());
    }

    /**
     * Test bestTextColor() returns white for dark blue background.
     */
    public function testBestTextColorOnDarkBlue(): void
    {
        $color = Color::fromString('darkblue');
        $result = $color->bestTextColor();
        $this->assertSame(Color::fromString('white')->toHex(), $result->toHex());
    }

    /**
     * Test bestTextColor() returns black for yellow background.
     */
    public function testBestTextColorOnYellow(): void
    {
        $color = Color::fromString('yellow');
        $result = $color->bestTextColor();
        $this->assertSame(Color::fromString('black')->toHex(), $result->toHex());
    }

    /**
     * Test bestTextColor() with custom text colors as strings.
     */
    public function testBestTextColorWithCustomStrings(): void
    {
        $color = Color::fromString('black');
        $result = $color->bestTextColor('ivory', 'navy');
        $this->assertSame(Color::fromString('ivory')->toHex(), $result->toHex());
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
        $this->assertSame($navy->toHex(), $result->toHex());
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

    #region Method gamma() tests.

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
     * Test gamma() for a low input, on the linear branch.
     */
    public function testGammaLinearBranch(): void
    {
        $this->assertEqualsWithDelta(10 / 255 / 12.92, Color::gamma(10), 1e-12);
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

    #endregion
}
