<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use LengthException;
use Mossy2100\Color\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color aggregation methods.
 */
#[CoversClass(Color::class)]
final class ColorAggregationTest extends TestCase
{
    #region Method average() tests.

    /**
     * Test average() of single color returns that color.
     */
    public function testAverageSingleColor(): void
    {
        $color = Color::fromString('red');
        $avg = Color::average($color);
        $this->assertSame($avg->toHex(), $color->toHex());
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
     * Test average() rounds a half-way component away from zero.
     */
    public function testAverageRoundsHalfUp(): void
    {
        $avg = Color::average(new Color(0, 0, 0), new Color(1, 3, 5));
        $this->assertSame([1, 2, 3, 255], array_values($avg->toRgbaArray()));
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
}
