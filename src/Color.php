<?php

declare(strict_types=1);

namespace Mossy2100\Color;

use DomainException;
use LengthException;
use Mossy2100\Core\Enums\FloatFormat;
use Mossy2100\Core\Exceptions\FormatException;
use Mossy2100\Core\Floats;
use Mossy2100\Core\Stringify;
use Mossy2100\Core\Traits\Comparison\Equatable;
use Override;
use Stringable;

/**
 * Color class.
 *
 * @author Shaun Moss
 * @version 2025-08-14
 */
final class Color implements Stringable
{
    use Equatable;

    #region Constants

    /**
     * Constants for computing perceived lightness.
     */
    private const float EPSILON = 216 / 24389; // 0.008856451679...

    private const float KAPPA = 24389 / 27;  // 903.296296...

    /**
     * The 'transparent' keyword, in the lookup format used by findNameHex(): a lower-case name mapped to 8-digit hex.
     *
     * CSS defines 'transparent' separately from the named colors, so it isn't in CSS_COLOR_NAMES.
     */
    private const array TRANSPARENT = [
        'transparent' => '00000000',
    ];

    #endregion

    #region Internal representation

    /**
     * Internal field to store color value.
     *
     * The 32-bit color value stored as a 4-byte binary string (RGBA order).
     *
     * This approach was adopted over 32-bit unsigned int, which would not have worked on 32-bit systems because of
     * the way PHP converts ints to floats when they're outside the int range.
     * This approach is also preferable to storing 4 individual integers for the color components, as that would
     * consume 16 bytes on a 32-bit system or 32 bytes on a 64-bit system.
     *
     * @var string
     */
    private string $rgba;

    #endregion

    #region Property hooks

    /**
     * Get the red component of the color.
     *
     * @var int
     */
    public int $red {
        get => $this->getByte(0);
    }

    /**
     * Get the green component of the color.
     *
     * @var int
     */
    public int $green {
        get => $this->getByte(1);
    }

    /**
     * Get the blue component of the color.
     *
     * @var int
     */
    public int $blue {
        get => $this->getByte(2);
    }

    /**
     * Get the alpha component of the color.
     *
     * @var int
     */
    public int $alpha {
        get => $this->getByte(3);
    }

    /**
     * Get the hue of the color, as an angle in degrees in the range [0, 360).
     *
     * Computed from the RGB bytes on first access, along with the other HSL properties, and stored.
     *
     * @var float
     */
    public private(set) float $hue {
        get {
            if (!isset($this->hue)) {
                $this->computeHsl();
            }
            return $this->hue;
        }
    }

    /**
     * Get the saturation of the color as a percentage in the range [0, 100].
     *
     * Computed from the RGB bytes on first access, along with the other HSL properties, and stored.
     *
     * @var float
     */
    public private(set) float $saturation {
        get {
            if (!isset($this->saturation)) {
                $this->computeHsl();
            }
            return $this->saturation;
        }
    }

    /**
     * Get the lightness of the color as a percentage in the range [0, 100].
     *
     * Computed from the RGB bytes on first access, along with the other HSL properties, and stored.
     *
     * @var float
     */
    public private(set) float $lightness {
        get {
            if (!isset($this->lightness)) {
                $this->computeHsl();
            }
            return $this->lightness;
        }
    }

    /**
     * Get the relative luminance of the color in the range [0.0, 1.0].
     *
     * @see https://www.w3.org/WAI/GL/wiki/Relative_luminance
     * @see https://en.wikipedia.org/wiki/Relative_luminance
     *
     * @var float
     */
    public float $relativeLuminance {
        get {
            // Calculate linear RGB components using the gamma transfer function.
            $rlin = self::gamma($this->red);
            $glin = self::gamma($this->green);
            $blin = self::gamma($this->blue);

            // Compute the relative luminance.
            return 0.2126 * $rlin + 0.7152 * $glin + 0.0722 * $blin;
        }
    }

    /**
     * Get the perceived lightness (CIE L*) of the color in the range [0, 100].
     *
     * @var float
     */
    public float $perceivedLightness {
        get {
            // Start with the relative luminance.
            $y = $this->relativeLuminance;

            // Compute CIE L* (perceived lightness) as a value in the range [0, 100].
            $lStar = ($y <= self::EPSILON) ? (self::KAPPA * $y) : (116.0 * ($y ** (1.0 / 3.0)) - 16.0);

            return self::clampPercent($lStar);
        }
    }

    #endregion

    #region Constructor

    /**
     * Construct a new color from RGBA bytes.
     *
     * Defaults to opaque black.
     *
     * @param int $red The red component as a byte in the range [0, 255].
     * @param int $green The green component as a byte in the range [0, 255].
     * @param int $blue The blue component as a byte in the range [0, 255].
     * @param int $alpha The alpha component as a byte in the range [0, 255]. Defaults to 255 (opaque).
     * @throws DomainException If any byte is out of range.
     */
    public function __construct(int $red = 0, int $green = 0, int $blue = 0, int $alpha = 255)
    {
        self::validateByte($red);
        self::validateByte($green);
        self::validateByte($blue);
        self::validateByte($alpha);
        $this->rgba = chr($red) . chr($green) . chr($blue) . chr($alpha);
    }

    #endregion

    #region Factory methods

    /**
     * Create a color from RGBA percentages, as in the CSS rgb() function.
     *
     * @static
     * @param float $red The red component as a percentage in the range [0, 100].
     * @param float $green The green component as a percentage in the range [0, 100].
     * @param float $blue The blue component as a percentage in the range [0, 100].
     * @param float $alpha The alpha component as a percentage in the range [0, 100]. Defaults to 100 (opaque).
     * @return self
     * @throws DomainException If any percentage is out of range.
     */
    public static function fromRgba(float $red, float $green, float $blue, float $alpha = 100): self
    {
        return new self(
            self::toByte($red, true),
            self::toByte($green, true),
            self::toByte($blue, true),
            self::toByte($alpha, true)
        );
    }

    /**
     * Create a color from HSLA values, as in the CSS hsl() function.
     *
     * @static
     * @param float $hue The hue in degrees. Any value is accepted, and wrapped to the range [0, 360).
     * @param float $saturation The saturation as a percentage in the range [0, 100].
     * @param float $lightness The lightness as a percentage in the range [0, 100].
     * @param float $alpha The alpha component as a percentage in the range [0, 100]. Defaults to 100 (opaque).
     * @return self
     * @throws DomainException If the hue is not finite, or any percentage is out of range.
     */
    public static function fromHsla(float $hue, float $saturation, float $lightness, float $alpha = 100): self
    {
        return self::createFromHsl($hue, $saturation, $lightness, self::toByte($alpha, true));
    }

    /**
     * Create a color from a color name or hex color string.
     *
     * A color name is a CSS named color or one of EXTRA_COLOR_NAMES.
     * A hex string has 3, 4, 6, or 8 hex digits, with or without a leading '#'. Names are case-insensitive.
     * Surrounding whitespace is ignored.
     *
     * @static
     * @param string $colorString A color name or hex color string.
     * @return self
     * @throws FormatException If the string is not a color name or hex color string.
     */
    public static function fromString(string $colorString): self
    {
        $trimmed = trim($colorString);

        if (self::validName($trimmed)) {
            $bytes = self::nameToBytes($trimmed);
        } else {
            try {
                $bytes = self::hexToBytes($trimmed);
            } catch (FormatException) {
                throw new FormatException(Stringify::prepEx(
                    'Invalid color string: ?. Must be a color name or hex color string.',
                    $colorString
                ));
            }
        }

        return new self($bytes[0], $bytes[1], $bytes[2], $bytes[3]);
    }

    #endregion

    #region Validation methods

    /**
     * Returns true if the string is a valid hex color string.
     * A leading '#' is optional, and it can have 3, 4, 6, or 8 hex digits.
     *
     * @static
     * @param string $hex A string that could be a CSS hex color.
     * @return bool If the provided string is a valid CSS hex color string.
     */
    public static function validHex(string $hex): bool
    {
        $hex = trim($hex);

        // Reject empty strings.
        if ($hex === '') {
            return false;
        }

        // Trim a leading '#' if present.
        if ($hex[0] === '#') {
            $hex = substr($hex, 1);
        }

        // Check the string is 3, 4, 6 or 8 hex digits.
        return in_array(strlen($hex), [3, 4, 6, 8], true) && ctype_xdigit($hex);
    }

    /**
     * Check if a given string is a known color name: a CSS named color, or one of EXTRA_COLOR_NAMES.
     *
     * Names are case-insensitive, and surrounding whitespace is ignored.
     *
     * @static
     * @param string $name A color name.
     * @return bool If the name is a known color name.
     */
    public static function validName(string $name): bool
    {
        return self::findNameHex($name) !== null;
    }

    #endregion

    #region Comparison methods

    /**
     * Checks if two colors are equal.
     *
     * @param mixed $other The other color to compare to.
     * @return bool True if the colors are equal, false otherwise.
     */
    #[Override]
    public function equal(mixed $other): bool
    {
        return $other instanceof self && $this->rgba === $other->rgba;
    }

    #endregion

    #region Transformation methods

    /**
     * Create a new Color with the specified red component.
     *
     * @param float $red The red component, as a byte in the range [0, 255] (rounded to the nearest integer), or as a
     * percentage in the range [0, 100] if $percent is true.
     * @param bool $percent If the value is a percentage rather than a byte.
     * @return self A new Color with the updated red value.
     * @throws DomainException If the value is out of range.
     */
    public function withRed(float $red, bool $percent = false): self
    {
        return new self(self::toByte($red, $percent), $this->green, $this->blue, $this->alpha);
    }

    /**
     * Create a new Color with the specified green component.
     *
     * @param float $green The green component, as a byte in the range [0, 255] (rounded to the nearest integer), or
     * as a percentage in the range [0, 100] if $percent is true.
     * @param bool $percent If the value is a percentage rather than a byte.
     * @return self A new Color with the updated green value.
     * @throws DomainException If the value is out of range.
     */
    public function withGreen(float $green, bool $percent = false): self
    {
        return new self($this->red, self::toByte($green, $percent), $this->blue, $this->alpha);
    }

    /**
     * Create a new Color with the specified blue component.
     *
     * @param float $blue The blue component, as a byte in the range [0, 255] (rounded to the nearest integer), or as
     * a percentage in the range [0, 100] if $percent is true.
     * @param bool $percent If the value is a percentage rather than a byte.
     * @return self A new Color with the updated blue value.
     * @throws DomainException If the value is out of range.
     */
    public function withBlue(float $blue, bool $percent = false): self
    {
        return new self($this->red, $this->green, self::toByte($blue, $percent), $this->alpha);
    }

    /**
     * Create a new Color with the specified alpha component.
     *
     * @param float $alpha The alpha component, as a byte in the range [0, 255] (rounded to the nearest integer), or
     * as a percentage in the range [0, 100] if $percent is true.
     * @param bool $percent If the value is a percentage rather than a byte.
     * @return self A new Color with the updated alpha value.
     * @throws DomainException If the value is out of range.
     */
    public function withAlpha(float $alpha, bool $percent = false): self
    {
        return new self($this->red, $this->green, $this->blue, self::toByte($alpha, $percent));
    }

    /**
     * Create a new Color with the specified hue.
     *
     * @param float $hue The hue in degrees. Any value is accepted, and wrapped to the range [0, 360).
     * @return self A new Color with the updated hue.
     * @throws DomainException If the hue is not finite.
     */
    public function withHue(float $hue): self
    {
        return self::createFromHsl($hue, $this->saturation, $this->lightness, $this->alpha);
    }

    /**
     * Create a new Color with the specified saturation.
     *
     * @param float $saturation The saturation as a percentage in the range [0, 100].
     * @return self A new Color with the updated saturation.
     * @throws DomainException If the value is out of range.
     */
    public function withSaturation(float $saturation): self
    {
        return self::createFromHsl($this->hue, $saturation, $this->lightness, $this->alpha);
    }

    /**
     * Create a new Color with the specified lightness.
     *
     * @param float $lightness The lightness as a percentage in the range [0, 100].
     * @return self A new Color with the updated lightness.
     * @throws DomainException If the value is out of range.
     */
    public function withLightness(float $lightness): self
    {
        return self::createFromHsl($this->hue, $this->saturation, $lightness, $this->alpha);
    }

    /**
     * Mix two colors.
     *
     * If the percentage is 0, then the result will equal "this" color.
     * If the percentage is 100, then the result will equal the "other" color.
     * If the percentage isn't specified, then the result will have 50% of each color.
     *
     * The colors are premultiplied by alpha, as in CSS color-mix(), so mixing with a transparent color fades a color
     * without darkening it.
     *
     * @param self $other The color to mix with.
     * @param float $percent The percentage of the "other" color, in the range [0, 100].
     * @return self
     * @throws DomainException If the percentage is out of range.
     */
    public function mix(self $other, float $percent = 50): self
    {
        // Validate the percentage.
        self::validatePercent($percent);

        // Check for 0% other color (100% this).
        if ($percent === 0.0) {
            return clone $this;
        }
        // Check for 100% other color.
        if ($percent === 100.0) {
            return clone $other;
        }

        return self::blend([$this, $other], [
            100 - $percent,
            $percent,
        ]);
    }

    /**
     * Get the complementary color.
     *
     * @return self The complementary color.
     */
    public function complement(): self
    {
        return $this->withHue($this->hue + 180);
    }

    #endregion

    #region Accessibility methods

    /**
     * Determine the contrast ratio (as per WCAG 2.x).
     *
     * Alpha is ignored, since WCAG defines contrast for opaque colors. To check a translucent color, blend it with its
     * background first, e.g. using mix().
     *
     * @param Color $other The other color to compare to.
     * @return float The contrast ratio.
     */
    public function contrastRatio(self $other): float
    {
        $l1 = max($this->relativeLuminance, $other->relativeLuminance);
        $l2 = min($this->relativeLuminance, $other->relativeLuminance);
        return ($l1 + 0.05) / ($l2 + 0.05);
    }

    /**
     * Determine the best text color for accessibility/readability, suitable for this background color.
     *
     * Alpha is ignored, since WCAG defines contrast for opaque colors. To check a translucent color, blend it with its
     * background first, e.g. using mix().
     *
     * @param string|Color $lightTextColor The light text color (default white).
     * @param string|Color $darkTextColor The dark text color (default black).
     * @return self The most accessible text color.
     * @throws FormatException If either of the provided text colors are invalid strings.
     * @see https://www.w3.org/TR/WCAG20/#contrast-ratiodef
     * @see https://www.w3.org/TR/WCAG20/#visual-audio-contrast-contrast.
     */
    public function bestTextColor(string|self $lightTextColor = 'white', string|self $darkTextColor = 'black'): self
    {
        // Get the text colors as Color objects.
        if (is_string($lightTextColor)) {
            $lightTextColor = self::fromString($lightTextColor);
        }
        if (is_string($darkTextColor)) {
            $darkTextColor = self::fromString($darkTextColor);
        }

        // Calculate what text color will produce the most contrast. If equal, choose dark.
        return ($darkTextColor->contrastRatio($this) >= $lightTextColor->contrastRatio($this))
            ? $darkTextColor
            : $lightTextColor;
    }

    #endregion

    #region Conversion methods

    /**
     * Outputs the color as a 6-digit hexadecimal string, or 8-digit if alpha is included.
     *
     * @param bool $includeAlpha If the 2 characters for the alpha byte should be included.
     * @param bool $includeHash If the result should have a leading '#'.
     * @param bool $upperCase If letter digits should be upper-case.
     * @return string The color formatted as a CSS hexadecimal color string.
     */
    public function toHex(bool $includeAlpha = true, bool $includeHash = true, bool $upperCase = false): string
    {
        // Convert the 4-byte binary string to an 8-character hexadecimal string.
        $hex = bin2hex($this->rgba);

        // Remove the last 2 characters if alpha isn't required.
        if (!$includeAlpha) {
            $hex = substr($hex, 0, 6);
        }

        // Convert to upper-case if required.
        if ($upperCase) {
            $hex = strtoupper($hex);
        }

        // Add a leading '#' if required.
        return $includeHash ? "#$hex" : $hex;
    }

    /**
     * Outputs the color as an rgb() CSS string.
     *
     * The red, green and blue components are bytes, and the alpha is a percentage.
     *
     * @return string The Color as a CSS-compatible "rgb" color string, e.g. "rgb(120 50 50 / 50.196078%)".
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Values/color_value/rgb
     */
    public function toRgbString(): string
    {
        $a = self::formatPercent(self::byteToPercent($this->alpha));
        return "rgb($this->red $this->green $this->blue / $a)";
    }

    /**
     * Outputs the color as an HSL CSS string.
     *
     * @return string The Color as a CSS-compatible "hsl" color string, e.g. "hsl(120deg 50% 50% / 100%)".
     * @see https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Values/color_value/hsl
     */
    public function toHslString(): string
    {
        $h = self::formatAngle($this->hue);
        $s = self::formatPercent($this->saturation);
        $l = self::formatPercent($this->lightness);
        $a = self::formatPercent(self::byteToPercent($this->alpha));
        return "hsl($h $s $l / $a)";
    }

    /**
     * Stringable implementation.
     *
     * The default string representation of a Color is an 8-digit RGBA lower-case hex string with leading '#'.
     * Suitable for use in CSS.
     *
     * @return string The Color as a CSS hexadecimal color string (RGBA, 8 digits).
     */
    public function __toString(): string
    {
        return $this->toHex();
    }

    /**
     * Gets all RGBA values as an array.
     *
     * @return array{red:int, green:int, blue:int, alpha:int} Array of color components as bytes.
     */
    public function toRgbaArray(): array
    {
        return [
            'red'   => $this->red,
            'green' => $this->green,
            'blue'  => $this->blue,
            'alpha' => $this->alpha,

        ];
    }

    /**
     * Returns the Color as an array of numbers representing HSL values.
     *
     * Hue is represented as an angle in degrees [0, 360).
     * Saturation and lightness are represented as percentages [0, 100].
     *
     * @return array{hue:float, saturation:float, lightness:float} Array of color components as numbers.
     */
    public function toHslArray(): array
    {
        return [
            'hue'        => $this->hue,
            'saturation' => $this->saturation,
            'lightness'  => $this->lightness,

        ];
    }

    /**
     * Get the color as an array with RGBA and HSL properties.
     *
     * @return array<string, int|float> An array of color properties.
     */
    public function toArray(): array
    {
        return array_merge(
            $this->toRgbaArray(),
            $this->toHslArray()
        );
    }

    #endregion

    #region Static utility methods

    /**
     * Transfer function for gamma correction.
     *
     * This is needed by the relative luminance calculation, but let's make it public and static in case people need
     * it for other purposes.
     *
     * @see https://en.wikipedia.org/wiki/SRGB#Transfer_function_(%22gamma%22)
     *
     * @static
     * @param int $byte The RGB byte value, in the range [0, 255].
     * @return float The transfer function value.
     * @throws DomainException If the byte is out of range.
     */
    public static function gamma(int $byte): float
    {
        self::validateByte($byte);

        // Convert RGB byte to a float in the range [0.0, 1.0].
        $c = $byte / 255.0;

        // Compute transfer function.
        return ($c <= 0.04045) ? ($c / 12.92) : ((($c + 0.055) / 1.055) ** 2.4);
    }

    /**
     * Find the average of several colors.
     *
     * The colors are premultiplied by alpha, as in mix().
     *
     * @static
     * @param Color ...$colors The colors to average.
     * @return self The average color.
     * @throws LengthException If no colors are provided.
     */
    public static function average(self ...$colors): self
    {
        // Get the number of colors and make sure we have at least one.
        $n = count($colors);
        if ($n === 0) {
            throw new LengthException('Cannot average zero colors.');
        }

        // If there's only one, return it.
        if ($n === 1) {
            return $colors[0];
        }

        return self::blend(array_values($colors), array_fill(0, $n, 1.0));
    }

    /**
     * Convert RGB values to HSL.
     *
     * The returned list contains the following values, in the ranges below:
     *    hue        => [0, 360)
     *    saturation => [0, 100]
     *    lightness  => [0, 100]
     *
     * Algorithm:
     * @see https://en.wikipedia.org/wiki/HSL_and_HSV#From_RGB
     *
     * @static
     * @param int $red The red byte value.
     * @param int $green The green byte value.
     * @param int $blue The blue byte value.
     * @return list<float> The hue in degrees, and the saturation and lightness as percentages.
     * @throws DomainException If the provided values are not in the valid range for RGB bytes.
     */
    public static function rgbToHsl(int $red, int $green, int $blue): array
    {
        // Check the red, green and blue bytes are in range:
        self::validateByte($red);
        self::validateByte($green);
        self::validateByte($blue);

        // Convert to fractions.
        $r = $red / 255;
        $g = $green / 255;
        $b = $blue / 255;

        // Get the min and max values.
        $min = min($r, $g, $b);
        $max = max($r, $g, $b);
        $c = $max - $min;
        $l = ($min + $max) / 2;

        // Check for gray.
        if (Floats::approxEqual($c, 0)) {
            $h = 0.0;
            $s = 0.0;
        } else {
            // Calculate hue and wrap to [0, 360).
            if ($max === $r) {
                $h = fmod(($g - $b) / $c, 6);
            } elseif ($max === $g) {
                $h = ($b - $r) / $c + 2;
            } else { // $max === $b
                $h = ($r - $g) / $c + 4;
            }
            $h = self::wrapHue($h * 60);

            // Calculate saturation. Rounding error can push it just past 1, hence the clamp.
            // Note: $l cannot be 0 or 1 here because that would require $c == 0,
            // which is handled by the `if` branch above.
            $s = min($c / (1 - abs(2 * $l - 1)), 1.0);
        }

        return [
            $h,
            $s * 100,
            $l * 100,
        ];
    }

    /**
     * Convert HSL values to RGB.
     *
     * @see https://en.wikipedia.org/wiki/HSL_and_HSV#HSL_to_RGB_alternative
     *
     * @static
     * @param float $hue The hue as an angle in degrees.
     * @param float $saturation The saturation as a percentage in the range [0, 100].
     * @param float $lightness The lightness as a percentage in the range [0, 100].
     * @return list<int> An array of red, green, and blue bytes.
     * @throws DomainException If the hue is not finite, or the saturation or lightness is out of range.
     */
    public static function hslToRgb(float $hue, float $saturation, float $lightness): array
    {
        // Normalize the hue angle.
        $hue = self::wrapHue($hue);

        // Validate saturation and lightness, and convert them to fractions.
        self::validatePercent($saturation);
        self::validatePercent($lightness);
        $s = $saturation / 100;
        $l = $lightness / 100;

        // Conversion function.
        $f = static function ($n) use ($hue, $s, $l): int {
            $k = fmod($n + $hue / 30, 12);
            $a = $s * min($l, 1 - $l);
            $c = $l - $a * max(-1, min($k - 3, 9 - $k, 1));
            return self::percentToByte(self::clampPercent($c * 100));
        };

        return [
            $f(0),
            $f(8),
            $f(4),
        ];
    }

    /**
     * Convert a CSS color hex string, being 3, 4, 6, or 8 hexadecimal digits, lower- or upper-case, with or without
     * leading '#', into 8 lower-case hexadecimal digits.
     *
     * @param string $hex A CSS hex color string.
     * @return string The 8-digit hex string.
     * @throws FormatException If the provided string is not a valid CSS hex color string.
     */
    public static function normalizeHex(string $hex): string
    {
        // Check the input string is a valid format.
        if (!self::validHex($hex)) {
            throw new FormatException(Stringify::prepEx('Invalid CSS hex color: ?.', $hex));
        }

        // Remove a leading # character if present and lower-case letter digits.
        $hex = strtolower(ltrim(trim($hex), '#'));

        // Check length.
        $len = strlen($hex);
        if ($len === 8) {
            return $hex;
        }

        // Expand string to 8 characters as needed.
        $rgb = $len === 6 ? $hex : ($hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2]);
        $a = $len === 4 ? ($hex[3] . $hex[3]) : 'ff';
        return $rgb . $a;
    }

    /**
     * Convert a hex string in CSS form to 4 RGBA bytes
     *
     * The allowed values can have an optional leading '#' followed by 3, 4, 6, or 8 lower- or upper-case hex digits.
     * If there are only 3 or 4 digits, the value is expanded to 6 or 8 digits respectively by repeating each digit.
     *
     * @param string $hex A CSS hex color string.
     * @return list<int<0, 255>> Array of color components as bytes.
     * @throws FormatException If the provided string is not a valid CSS hex color string.
     */
    public static function hexToBytes(string $hex): array
    {
        // Normalize to 8 hex digits. This will throw if the hex string is invalid.
        $hex = self::normalizeHex($hex);

        // Convert string into bytes.
        /** @var int $red */
        $red = hexdec(substr($hex, 0, 2));
        /** @var int $green */
        $green = hexdec(substr($hex, 2, 2));
        /** @var int $blue */
        $blue = hexdec(substr($hex, 4, 2));
        /** @var int $alpha */
        $alpha = hexdec(substr($hex, 6, 2));

        // Assertions to satisfy phpstan.
        assert($red >= 0 && $red <= 255);
        assert($green >= 0 && $green <= 255);
        assert($blue >= 0 && $blue <= 255);
        assert($alpha >= 0 && $alpha <= 255);

        return [$red, $green, $blue, $alpha];
    }

    /**
     * Convert a color name to an 8-digit hex value (no leading '#').
     *
     * @static
     * @param string $name A CSS or extra color name.
     * @return string The hex value for this color.
     * @throws DomainException If the provided string is not a valid color name.
     */
    public static function nameToHex(string $name): string
    {
        $hex = self::findNameHex($name);

        // Check the provided color name is valid.
        if ($hex === null) {
            throw new DomainException(Stringify::prepEx('Invalid color name: ?.', $name));
        }

        return $hex;
    }

    /**
     * Convert a color name to RGBA bytes.
     *
     * @static
     * @param string $name A CSS or extra color name.
     * @return list<int<0, 255>> RGBA color components as array of bytes.
     * @throws DomainException if the provided string is not a valid color name.
     */
    public static function nameToBytes(string $name): array
    {
        $hex = self::nameToHex($name);
        return self::hexToBytes($hex);
    }

    #endregion

    #region Instance helper methods

    /**
     * Get a byte value from the internal RGBA string.
     *
     * NB: This is an internal function, and the offset is assumed to be in range [0, 3].
     *
     * @param int $offset The offset, which will be 0 for red, 1 for green, 2 for blue, and 3 for alpha.
     * @return int The byte value at the given position.
     */
    private function getByte(int $offset): int
    {
        return ord($this->rgba[$offset]);
    }

    /**
     * Compute the hue, saturation and lightness from the RGB bytes, and store them.
     *
     * @return void
     */
    private function computeHsl(): void
    {
        [
            $this->hue,
            $this->saturation,
            $this->lightness,
        ] = self::rgbToHsl($this->red, $this->green, $this->blue);
    }

    #endregion

    #region Static helper methods

    /**
     * Blend colors by weighted average, premultiplying the RGB components by alpha, as CSS color-mix() does.
     *
     * Premultiplying means a more transparent color contributes less to the RGB of the result. If every color with a
     * non-zero weight is fully transparent, there's no visible color to weight by, so the RGB components are
     * averaged directly.
     *
     * @param list<self> $colors The colors to blend.
     * @param list<float> $weights The weight of each color. Must be non-negative, with a positive sum.
     * @return self The blended color.
     */
    private static function blend(array $colors, array $weights): self
    {
        $totalWeight = array_sum($weights);

        // Sum the alpha-weighted components, and the plain weighted components for the fully transparent case.
        $alphaSum = 0.0;
        $premultiplied = [0.0, 0.0, 0.0];
        $plain = [0.0, 0.0, 0.0];
        foreach ($colors as $i => $color) {
            $weight = $weights[$i];
            $alphaWeight = $color->alpha * $weight;
            $alphaSum += $alphaWeight;
            for ($offset = 0; $offset < 3; $offset++) {
                $component = $color->getByte($offset);
                $premultiplied[$offset] += $component * $alphaWeight;
                $plain[$offset] += $component * $weight;
            }
        }

        // Divide out the weights to get the blended components.
        $rgb = $alphaSum > 0
            ? array_map(static fn ($sum) => (int) round($sum / $alphaSum), $premultiplied)
            : array_map(static fn ($sum) => (int) round($sum / $totalWeight), $plain);
        $alpha = (int) round($alphaSum / $totalWeight);

        return new self($rgb[0], $rgb[1], $rgb[2], $alpha);
    }

    /**
     * Look up the hex value for a CSS or extra color name, ignoring case and surrounding whitespace.
     *
     * The map is built once. Since `+` keeps the left operand's entry for a duplicate key, CSS names take precedence if
     * a name is ever in both tables.
     *
     * @param string $name The color name.
     * @return ?string The 8-digit hex value, or null if the name isn't a known color name.
     */
    private static function findNameHex(string $name): ?string
    {
        static $hexByLowerName = self::TRANSPARENT
            + self::buildNameMap(self::CSS_COLOR_NAMES)
            + self::buildNameMap(self::EXTRA_COLOR_NAMES);
        return $hexByLowerName[strtolower(trim($name))] ?? null;
    }

    /**
     * Convert a color name table to a map from lower-case names to 8-digit hex values, appending an opaque alpha.
     *
     * @param array<string, string> $colors A color name table, mapping names to 6-digit hex values.
     * @return array<string, string> The color name map.
     */
    private static function buildNameMap(array $colors): array
    {
        $map = [];
        foreach ($colors as $name => $hex) {
            $map[strtolower($name)] = $hex . 'ff';
        }
        return $map;
    }

    /**
     * Create a color from HSL values and an alpha byte.
     *
     * The hue, saturation and lightness are validated by hslToRgb().
     *
     * The HSL values aren't cached, so the color's HSL properties are always derived from its RGB bytes, and equal
     * colors report equal HSL values.
     *
     * @param float $hue The hue in degrees.
     * @param float $saturation The saturation as a percentage.
     * @param float $lightness The lightness as a percentage.
     * @param int $alpha The alpha component as a byte.
     * @return self The new color.
     * @throws DomainException If the hue is not finite, or the saturation or lightness is out of range.
     */
    private static function createFromHsl(float $hue, float $saturation, float $lightness, int $alpha): self
    {
        [$red, $green, $blue] = self::hslToRgb($hue, $saturation, $lightness);
        return new self($red, $green, $blue, $alpha);
    }

    /**
     * Convert a float to a value within the range [0, 100].
     *
     * @param float $value The value to clamp.
     * @return float The clamped value.
     */
    private static function clampPercent(float $value): float
    {
        return max(0.0, min($value, 100.0));
    }

    /**
     * Check if a byte value is valid.
     *
     * @param int $byte The value to check.
     * @return void
     * @throws DomainException If the byte is out of range.
     * @phpstan-assert int<0, 255> $byte
     */
    private static function validateByte(int $byte): void
    {
        if ($byte < 0 || $byte > 255) {
            throw new DomainException("Invalid byte value: $byte. Must be in the range [0, 255].");
        }
    }

    /**
     * Check if a percentage value is valid.
     *
     * @param float $percent The percentage to check.
     * @return void
     * @throws DomainException If the percentage is out of range.
     */
    private static function validatePercent(float $percent): void
    {
        if (is_nan($percent) || $percent < 0 || $percent > 100) {
            throw new DomainException(
                Stringify::prepEx('Invalid percentage: ?. Must be in the range [0, 100].', $percent)
            );
        }
    }

    /**
     * Convert a color component, given as a byte or a percentage, to a byte.
     *
     * @param float $value The value, as a byte in the range [0, 255] (rounded to the nearest integer), or as a
     * percentage in the range [0, 100] if $percent is true.
     * @param bool $percent If the value is a percentage rather than a byte.
     * @return int The byte value.
     * @throws DomainException If the value is out of range.
     */
    private static function toByte(float $value, bool $percent): int
    {
        if ($percent) {
            self::validatePercent($value);
            return self::percentToByte($value);
        }

        if (is_nan($value) || $value < 0 || $value > 255) {
            throw new DomainException(
                Stringify::prepEx('Invalid byte value: ?. Must be in the range [0, 255].', $value)
            );
        }
        return (int) round($value);
    }

    /**
     * Convert a byte to a percentage.
     *
     * @static
     * @param int $byte The byte value.
     * @return float The percentage.
     */
    private static function byteToPercent(int $byte): float
    {
        return $byte * 100 / 255;
    }

    /**
     * Convert a percentage to a byte.
     *
     * @static
     * @param float $percent The percentage to convert.
     * @return int The byte value.
     */
    private static function percentToByte(float $percent): int
    {
        return (int) round($percent * 255 / 100);
    }

    /**
     * Wrap the hue angle to the range [0, 360).
     *
     * @param float $hue The hue angle to wrap.
     * @return float The wrapped hue angle.
     * @throws DomainException If the hue is not finite.
     */
    private static function wrapHue(float $hue): float
    {
        if (!is_finite($hue)) {
            throw new DomainException(Stringify::prepEx('Invalid hue: ?. Must be finite.', $hue));
        }
        return Floats::wrap($hue, 360, false);
    }

    /**
     * Format a number with up to 6 decimal places, trailing zeros stripped.
     *
     * @param float $value The float to format.
     * @return string The formatted float.
     */
    private static function formatFloat(float $value): string
    {
        return Floats::format($value, 6, true, FloatFormat::FixedPoint);
    }

    /**
     * Format an angle in canonical CSS style, e.g. '123deg' or '12.345deg'
     *
     * @param float $angle The angle to format.
     * @return string The formatted angle string.
     */
    private static function formatAngle(float $angle): string
    {
        return self::formatFloat($angle) . 'deg';
    }

    /**
     * Format a percentage, e.g. '50%' or '50.196078%'.
     *
     * @param float $percent The percentage to format.
     * @return string The formatted percentage string.
     */
    private static function formatPercent(float $percent): string
    {
        return self::formatFloat($percent) . '%';
    }

    #endregion

    #region Color names (public)

    /**
     * The CSS named colors mapped to 6-digit RGB hex values.
     *
     * The names are in UpperCamelCase for readability. Lookups by name are case-insensitive.
     *
     * @see https://www.w3.org/TR/css-color-4/#named-colors
     * @var array<string,string>
     */
    public const array CSS_COLOR_NAMES = [
        'AliceBlue'            => 'f0f8ff',
        'AntiqueWhite'         => 'faebd7',
        'Aqua'                 => '00ffff',
        'Aquamarine'           => '7fffd4',
        'Azure'                => 'f0ffff',
        'Beige'                => 'f5f5dc',
        'Bisque'               => 'ffe4c4',
        'Black'                => '000000',
        'BlanchedAlmond'       => 'ffebcd',
        'Blue'                 => '0000ff',
        'BlueViolet'           => '8a2be2',
        'Brown'                => 'a52a2a',
        'BurlyWood'            => 'deb887',
        'CadetBlue'            => '5f9ea0',
        'Chartreuse'           => '7fff00',
        'Chocolate'            => 'd2691e',
        'Coral'                => 'ff7f50',
        'CornflowerBlue'       => '6495ed',
        'Cornsilk'             => 'fff8dc',
        'Crimson'              => 'dc143c',
        'Cyan'                 => '00ffff',
        'DarkBlue'             => '00008b',
        'DarkCyan'             => '008b8b',
        'DarkGoldenrod'        => 'b8860b',
        'DarkGray'             => 'a9a9a9',
        'DarkGreen'            => '006400',
        'DarkGrey'             => 'a9a9a9',
        'DarkKhaki'            => 'bdb76b',
        'DarkMagenta'          => '8b008b',
        'DarkOliveGreen'       => '556b2f',
        'DarkOrange'           => 'ff8c00',
        'DarkOrchid'           => '9932cc',
        'DarkRed'              => '8b0000',
        'DarkSalmon'           => 'e9967a',
        'DarkSeaGreen'         => '8fbc8f',
        'DarkSlateBlue'        => '483d8b',
        'DarkSlateGray'        => '2f4f4f',
        'DarkSlateGrey'        => '2f4f4f',
        'DarkTurquoise'        => '00ced1',
        'DarkViolet'           => '9400d3',
        'DeepPink'             => 'ff1493',
        'DeepSkyBlue'          => '00bfff',
        'DimGray'              => '696969',
        'DimGrey'              => '696969',
        'DodgerBlue'           => '1e90ff',
        'Firebrick'            => 'b22222',
        'FloralWhite'          => 'fffaf0',
        'ForestGreen'          => '228b22',
        'Fuchsia'              => 'ff00ff',
        'Gainsboro'            => 'dcdcdc',
        'GhostWhite'           => 'f8f8ff',
        'Gold'                 => 'ffd700',
        'Goldenrod'            => 'daa520',
        'Gray'                 => '808080',
        'Green'                => '008000',
        'GreenYellow'          => 'adff2f',
        'Grey'                 => '808080',
        'Honeydew'             => 'f0fff0',
        'HotPink'              => 'ff69b4',
        'IndianRed'            => 'cd5c5c',
        'Indigo'               => '4b0082',
        'Ivory'                => 'fffff0',
        'Khaki'                => 'f0e68c',
        'Lavender'             => 'e6e6fa',
        'LavenderBlush'        => 'fff0f5',
        'LawnGreen'            => '7cfc00',
        'LemonChiffon'         => 'fffacd',
        'LightBlue'            => 'add8e6',
        'LightCoral'           => 'f08080',
        'LightCyan'            => 'e0ffff',
        'LightGoldenrodYellow' => 'fafad2',
        'LightGray'            => 'd3d3d3',
        'LightGreen'           => '90ee90',
        'LightGrey'            => 'd3d3d3',
        'LightPink'            => 'ffb6c1',
        'LightSalmon'          => 'ffa07a',
        'LightSeaGreen'        => '20b2aa',
        'LightSkyBlue'         => '87cefa',
        'LightSlateGray'       => '778899',
        'LightSlateGrey'       => '778899',
        'LightSteelBlue'       => 'b0c4de',
        'LightYellow'          => 'ffffe0',
        'Lime'                 => '00ff00',
        'LimeGreen'            => '32cd32',
        'Linen'                => 'faf0e6',
        'Magenta'              => 'ff00ff',
        'Maroon'               => '800000',
        'MediumAquamarine'     => '66cdaa',
        'MediumBlue'           => '0000cd',
        'MediumOrchid'         => 'ba55d3',
        'MediumPurple'         => '9370db',
        'MediumSeaGreen'       => '3cb371',
        'MediumSlateBlue'      => '7b68ee',
        'MediumSpringGreen'    => '00fa9a',
        'MediumTurquoise'      => '48d1cc',
        'MediumVioletRed'      => 'c71585',
        'MidnightBlue'         => '191970',
        'MintCream'            => 'f5fffa',
        'MistyRose'            => 'ffe4e1',
        'Moccasin'             => 'ffe4b5',
        'NavajoWhite'          => 'ffdead',
        'Navy'                 => '000080',
        'OldLace'              => 'fdf5e6',
        'Olive'                => '808000',
        'OliveDrab'            => '6b8e23',
        'Orange'               => 'ffa500',
        'OrangeRed'            => 'ff4500',
        'Orchid'               => 'da70d6',
        'PaleGoldenrod'        => 'eee8aa',
        'PaleGreen'            => '98fb98',
        'PaleTurquoise'        => 'afeeee',
        'PaleVioletRed'        => 'db7093',
        'PapayaWhip'           => 'ffefd5',
        'PeachPuff'            => 'ffdab9',
        'Peru'                 => 'cd853f',
        'Pink'                 => 'ffc0cb',
        'Plum'                 => 'dda0dd',
        'PowderBlue'           => 'b0e0e6',
        'Purple'               => '800080',
        'RebeccaPurple'        => '663399',
        'Red'                  => 'ff0000',
        'RosyBrown'            => 'bc8f8f',
        'RoyalBlue'            => '4169e1',
        'SaddleBrown'          => '8b4513',
        'Salmon'               => 'fa8072',
        'SandyBrown'           => 'f4a460',
        'SeaGreen'             => '2e8b57',
        'SeaShell'             => 'fff5ee',
        'Sienna'               => 'a0522d',
        'Silver'               => 'c0c0c0',
        'SkyBlue'              => '87ceeb',
        'SlateBlue'            => '6a5acd',
        'SlateGray'            => '708090',
        'SlateGrey'            => '708090',
        'Snow'                 => 'fffafa',
        'SpringGreen'          => '00ff7f',
        'SteelBlue'            => '4682b4',
        'Tan'                  => 'd2b48c',
        'Teal'                 => '008080',
        'Thistle'              => 'd8bfd8',
        'Tomato'               => 'ff6347',
        'Turquoise'            => '40e0d0',
        'Violet'               => 'ee82ee',
        'Wheat'                => 'f5deb3',
        'White'                => 'ffffff',
        'WhiteSmoke'           => 'f5f5f5',
        'Yellow'               => 'ffff00',
        'YellowGreen'          => '9acd32',
    ];

    /**
     * Color names in common English use that are not CSS named colors, mapped to 6-digit RGB hex values.
     *
     * Each value is a consensus of several sources (the xkcd color survey, Wikipedia, paint and color references),
     * averaged in CIELAB. The names are in UpperCamelCase for readability.
     *
     * @var array<string,string>
     */
    public const array EXTRA_COLOR_NAMES = [
        'AirForceBlue'       => '5d8aa8',
        'Alabaster'          => 'f3efe9',
        'Amber'              => 'ffb904',
        'Amethyst'           => '9a63c6',
        'AppleGreen'         => '82c319',
        'Apricot'            => 'fbb88a',
        'ArmyGreen'          => '4a561b',
        'AshGrey'            => 'bdbfb2',
        'Asparagus'          => '7da85e',
        'Aubergine'          => '3d0d28',
        'Auburn'             => '9f2e15',
        'Avocado'            => '769c21',
        'BabyBlue'           => 'a6d8f9',
        'BabyPink'           => 'fabcc9',
        'Banana'             => 'fdf141',
        'BarbiePink'         => 'eb3093',
        'BattleshipGrey'     => '78817f',
        'Berry'              => '990f4b',
        'BloodOrange'        => 'f33f0d',
        'BloodRed'           => '8e0002',
        'BlueGreen'          => '138f99',
        'BlueGrey'           => '8aa2bc',
        'BluePurple'         => '591edd',
        'Bluebell'           => '9e9ece',
        'Blueberry'          => '464196',
        'Blush'              => 'd05479',
        'Bone'               => 'e3d7c6',
        'Bordeaux'           => '710028',
        'BottleGreen'        => '0b5029',
        'Brass'              => 'c2ae4f',
        'BrickRed'           => '993320',
        'BrightBlue'         => '0165fc',
        'BrightGreen'        => '47ff04',
        'BrightOrange'       => 'ff5f10',
        'BrightPurple'       => 'be03fd',
        'BrightRed'          => 'ff000d',
        'BrightTeal'         => '01f9c6',
        'BrightYellow'       => 'fffd1a',
        'BritishRacingGreen' => '044519',
        'Bronze'             => 'ba7c1e',
        'BubblegumPink'      => 'fd78b5',
        'Buff'               => 'fae08c',
        'Burgundy'           => '770021',
        'BurntOrange'        => 'c95709',
        'BurntRed'           => '9f2305',
        'BurntSienna'        => 'cc5f32',
        'BurntUmber'         => '8e3c1b',
        'ButterYellow'       => 'fffd74',
        'Buttercup'          => 'eba51e',
        'Butterscotch'       => 'f7aa45',
        'CambridgeBlue'      => '96baa5',
        'Camel'              => 'c49d62',
        'CamoGreen'          => '7f8f4e',
        'CanaryYellow'       => 'fff72b',
        'CandyPink'          => 'ff63e9',
        'Cappuccino'         => '704a3a',
        'Caramel'            => 'af6f09',
        'Carmine'            => 'a6011e',
        'Carnation'          => 'fd778a',
        'CarolinaBlue'       => '76ade3',
        'Celadon'            => 'b5efb3',
        'Celery'             => 'bee37a',
        'Cerise'             => 'd92468',
        'Cerulean'           => '168ec7',
        'Champagne'          => 'f8dfba',
        'Charcoal'           => '353e42',
        'CherryRed'          => 'd61d47',
        'Chestnut'           => '9d4231',
        'Cinnamon'           => 'b1560d',
        'Claret'             => '700821',
        'Clay'               => 'b66a50',
        'Cobalt'             => '16489c',
        'Cocoa'              => '875f42',
        'Coffee'             => '856746',
        'Cognac'             => '9f381d',
        'CoolGrey'           => '9199ad',
        'Copper'             => 'bb6e33',
        'CottonCandy'        => 'ffbad7',
        'Cranberry'          => 'b2234d',
        'Cream'              => 'fffec9',
        'Custard'            => 'fffd78',
        'Daffodil'           => 'ffff31',
        'Damson'             => '854c65',
        'Dandelion'          => 'f4e434',
        'DarkBeige'          => 'ac9362',
        'DarkBrown'          => '482c12',
        'DarkLavender'       => '7e5d97',
        'DarkLilac'          => '9c6da5',
        'DarkMaroon'         => '3c0008',
        'DarkMauve'          => '874c62',
        'DarkNavy'           => '000435',
        'DarkPeach'          => 'de7e5d',
        'DarkPink'           => 'd74974',
        'DarkPurple'         => '35063e',
        'DarkRose'           => 'b5485d',
        'DarkTan'            => 'a2854d',
        'DarkTeal'           => '014d4e',
        'DarkYellow'         => 'bca20b',
        'DeepBlue'           => '110475',
        'DeepGreen'          => '02590f',
        'DeepPurple'         => '36013f',
        'DeepRed'            => '9a0200',
        'Denim'              => '315ea5',
        'Desert'             => 'c7a465',
        'DoveGrey'           => '6d6c6c',
        'DuckEggBlue'        => 'c6f4f1',
        'DustyPink'          => 'd28b97',
        'DustyRose'          => 'c37c7e',
        'Ebony'              => '4e544b',
        'Ecru'               => 'e1daa6',
        'Eggplant'           => '4b2544',
        'Eggshell'           => 'f8f4d1',
        'ElectricBlue'       => '0652ff',
        'ElectricPurple'     => 'b517ff',
        'Emerald'            => '30b059',
        'Espresso'           => '5c2a1e',
        'Evergreen'          => '084b30',
        'Fawn'               => 'daad76',
        'FernGreen'          => '4e8645',
        'FireEngineRed'      => 'fe0002',
        'Flamingo'           => 'ed5a37',
        'FrenchBlue'         => '2f6fb4',
        'Garnet'             => '873130',
        'Ginger'             => 'b06500',
        'GoldenBrown'        => 'a6700d',
        'GoldenYellow'       => 'ffd30d',
        'Grape'              => '5c2b5c',
        'Graphite'           => '2b2014',
        'GrassGreen'         => '3f9b0b',
        'Greige'             => 'b4b0a4',
        'GreyBrown'          => '7f7053',
        'GreyGreen'          => '7f9e78',
        'Gunmetal'           => '4b565b',
        'Hazel'              => '8e7618',
        'Heather'            => 'a484ac',
        'Honey'              => 'dfb265',
        'HunterGreen'        => '224e22',
        'IceBlue'            => 'd7fffd',
        'Iris'               => '5f51cb',
        'IrishGreen'         => '069738',
        'Jade'               => '1ca671',
        'JetBlack'           => '222324',
        'JungleGreen'        => '1b9664',
        'KellyGreen'         => '33b325',
        'KeyLime'            => 'aeff6e',
        'KhakiGreen'         => '778640',
        'Kiwi'               => '9cef43',
        'KleinBlue'          => '0c219d',
        'Latte'              => 'aa8767',
        'LavenderBlue'       => 'ada9fc',
        'LeafGreen'          => '5ca904',
        'Leather'            => 'a37243',
        'Lemon'              => 'fef832',
        'LightBrown'         => 'b1753a',
        'LightLavender'      => 'e2c4fe',
        'LightLilac'         => 'eac8f6',
        'LightMaroon'        => 'a24857',
        'LightMauve'         => 'c292a1',
        'LightNavy'          => '155084',
        'LightOlive'         => 'acbf69',
        'LightOrange'        => 'fdaa48',
        'LightPeach'         => 'ffd8b1',
        'LightPurple'        => 'bf77f6',
        'LightRed'           => 'ff5456',
        'LightTan'           => 'fbeeac',
        'LightTeal'          => '98dfc2',
        'Lilac'              => 'c7a1da',
        'LipstickRed'        => 'c0022f',
        'Magnolia'           => 'f7f0ec',
        'Mahogany'           => '7f1f0f',
        'Maize'              => 'f9d749',
        'Malachite'          => '0bda51',
        'Mango'              => 'fdb228',
        'Marigold'           => 'e7ab1c',
        'MarineBlue'         => '01386a',
        'Mauve'              => 'c890be',
        'MediumBrown'        => '7f5112',
        'MediumGreen'        => '3ca64b',
        'Melon'              => 'fcbbb0',
        'Merlot'             => '791433',
        'MidBlue'            => '276ab3',
        'MidGreen'           => '50a747',
        'MidGrey'            => '828287',
        'MilitaryGreen'      => '667c3e',
        'MillennialPink'     => 'f1c9c4',
        'MintGreen'          => '94fe9b',
        'Mocha'              => '9b7757',
        'MossGreen'          => '759958',
        'Mud'                => '735a1d',
        'Mulberry'           => 'a82a67',
        'Mushroom'           => 'bba18d',
        'Mustard'            => 'e5c534',
        'NeonBlue'           => '04d9ff',
        'NeonGreen'          => '29ff10',
        'NeonOrange'         => 'ff5f1f',
        'NeonPink'           => 'fe208e',
        'NeonPurple'         => 'bc13fe',
        'NeonRed'            => 'ff1438',
        'NeonYellow'         => 'cfff04',
        'Nude'               => 'eacbb9',
        'Oatmeal'            => 'd4cbb3',
        'OceanBlue'          => '057baf',
        'Ochre'              => 'c78818',
        'OffBlack'           => '3c3e3f',
        'OffWhite'           => 'fefde9',
        'OldRose'            => 'c48085',
        'Onyx'               => '2b2c2c',
        'OrangeBrown'        => 'ad5a00',
        'Oxblood'            => '640314',
        'OxfordBlue'         => '192c4c',
        'PaleBlue'           => 'c2f7f7',
        'PaleLavender'       => 'e6cffe',
        'PaleOrange'         => 'ffa756',
        'PalePink'           => 'fbd3dc',
        'PalePurple'         => 'b790d4',
        'PaleYellow'         => 'ffff84',
        'Parchment'          => 'f9f4be',
        'PastelBlue'         => 'a2bffe',
        'PastelGreen'        => '95ef8b',
        'PastelOrange'       => 'ffa44c',
        'PastelPink'         => 'f4b8c2',
        'PastelPurple'       => 'c1a2de',
        'PastelRed'          => 'ec605b',
        'PastelYellow'       => 'fffd87',
        'PeaGreen'           => '8eab12',
        'Peach'              => 'ffcb9e',
        'PeacockBlue'        => '016795',
        'Pear'               => 'cee848',
        'Pearl'              => 'f0e4d6',
        'Periwinkle'         => 'b1aafc',
        'Petrol'             => '005f6a',
        'Pewter'             => '91a09b',
        'PillarBoxRed'       => 'cb1d27',
        'PineGreen'          => '296f52',
        'PinkPurple'         => 'e43be0',
        'Pistachio'          => 'a9df7e',
        'Platinum'           => 'e5e4e2',
        'PowderPink'         => 'ffb9d4',
        'Primrose'           => 'edea99',
        'PrussianBlue'       => '003a65',
        'Puce'               => 'b77f73',
        'Pumpkin'            => 'f0760e',
        'Putty'              => 'ccb787',
        'Raspberry'          => 'c80950',
        'RawSienna'          => 'bb7533',
        'RawUmber'           => '8b5c26',
        'RedBrown'           => '992c22',
        'RedPink'            => 'fa2a55',
        'RobinEggBlue'       => '5ddfe6',
        'Rose'               => 'e84678',
        'RoseGold'           => 'b76e79',
        'Rouge'              => 'ab1f41',
        'RoyalPurple'        => '643290',
        'Ruby'               => 'd40852',
        'Russet'             => '914111',
        'Rust'               => 'af3e0b',
        'Saffron'            => 'fab625',
        'Sage'               => '9eaf7f',
        'Sand'               => 'd3be7b',
        'Sandstone'          => 'c9ae74',
        'SapGreen'           => '578520',
        'Sapphire'           => '1d419e',
        'Scarlet'            => 'e11718',
        'SeaBlue'            => '036f95',
        'SeafoamGreen'       => '8af3b2',
        'Sepia'              => '875325',
        'ShamrockGreen'      => '1ea066',
        'ShockingPink'       => 'fe21b9',
        'Spruce'             => '0a5f38',
        'SteelGrey'          => '69767d',
        'Stone'              => 'ada587',
        'StoneGrey'          => '958f85',
        'Straw'              => 'f0e774',
        'Strawberry'         => 'fb2943',
        'Sunflower'          => 'fbdb0f',
        'Tangerine'          => 'f98d04',
        'Taupe'              => 'b9a281',
        'Tawny'              => 'cd691f',
        'TeaGreen'           => 'c7f4b2',
        'Terracotta'         => 'd66c4d',
        'TiffanyBlue'        => '56d4c8',
        'Toffee'             => '76553d',
        'TyrianPurple'       => '650239',
        'Ultramarine'        => '1906a6',
        'Umber'              => '8f5b2c',
        'Vanilla'            => 'e6d6aa',
        'Vermilion'          => 'f73e0f',
        'Viridian'           => '328a6a',
        'Walnut'             => '773f1a',
        'WarmGrey'           => '978a84',
        'Watermelon'         => 'fd4e61',
        'Wine'               => '7a1e3b',
        'Wisteria'           => 'b188ca',
        'YellowBrown'        => 'b59203',
        'YellowOchre'        => 'ca9b1a',
        'YellowOrange'       => 'feaf2c',
    ];

    #endregion
}
