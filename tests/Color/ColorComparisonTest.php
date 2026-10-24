<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use Mossy2100\Color\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color comparison methods.
 */
#[CoversClass(Color::class)]
final class ColorComparisonTest extends TestCase
{
    #region Method equal() tests.

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
}
