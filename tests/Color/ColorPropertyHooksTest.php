<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use Mossy2100\Color\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color property hooks.
 */
#[CoversClass(Color::class)]
final class ColorPropertyHooksTest extends TestCase
{
    #region Property $relativeLuminance tests.

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

    #region Property $perceivedLightness tests.

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
}
