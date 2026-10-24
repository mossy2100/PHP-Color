<?php

declare(strict_types=1);

namespace Mossy2100\Color\Tests\Color;

use DomainException;
use Mossy2100\Color\Color;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Color constructor.
 */
#[CoversClass(Color::class)]
final class ColorConstructorTest extends TestCase
{
    #region Method __construct() tests.

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
}
