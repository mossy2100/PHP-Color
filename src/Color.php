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
        self::validatePercent($saturation);
        self::validatePercent($lightness);
        return self::createFromHsl($hue, $saturation, $lightness, self::toByte($alpha, true));
    }

    /**
     * Create a color from a CSS color name or hex color string.
     *
     * A hex string has 3, 4, 6, or 8 hex digits, with or without a leading '#'. Names are case-insensitive.
     * Surrounding whitespace is ignored.
     *
     * @static
     * @param string $colorString A CSS color name or hex color string.
     * @return self
     * @throws FormatException If the string is not a CSS color name or hex color string.
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
                    'Invalid color string: ?. Must be a CSS color name or hex color string.',
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
     * Check if a given string is a valid CSS color name.
     *
     * @static
     * @param string $name A color name.
     * @return bool If the name is a valid CSS color name.
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
        self::validatePercent($saturation);
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
        self::validatePercent($lightness);
        return self::createFromHsl($this->hue, $this->saturation, $lightness, $this->alpha);
    }

    /**
     * Mix two colors.
     *
     * If the percentage is 0, then the result will equal "this" color.
     * If the percentage is 100, then the result will equal the "other" color.
     * If the percentage isn't specified, then the result will have 50% of each color.
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

        // Compute the components of the new color.
        $otherFraction = $percent / 100;
        $thisFraction = 1.0 - $otherFraction;
        $r = (int) round(($this->red * $thisFraction) + ($other->red * $otherFraction));
        $g = (int) round(($this->green * $thisFraction) + ($other->green * $otherFraction));
        $b = (int) round(($this->blue * $thisFraction) + ($other->blue * $otherFraction));
        $a = (int) round(($this->alpha * $thisFraction) + ($other->alpha * $otherFraction));

        // Create and return the mixed color.
        return new self($r, $g, $b, $a);
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
     * @param int $byte The RGB byte value.
     * @return float The transfer function value.
     */
    public static function gamma(int $byte): float
    {
        // Convert RGB byte to a float in the range [0.0, 1.0].
        $c = $byte / 255.0;

        // Compute transfer function.
        return ($c <= 0.04045) ? ($c / 12.92) : ((($c + 0.055) / 1.055) ** 2.4);
    }

    /**
     * Find the average of several colors.
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

        // Sum the color components.
        $sumR = 0;
        $sumG = 0;
        $sumB = 0;
        $sumA = 0;
        foreach ($colors as $color) {
            $sumR += $color->red;
            $sumG += $color->green;
            $sumB += $color->blue;
            $sumA += $color->alpha;
        }

        // Calculate the averages.
        $avg = static fn ($sum) => (int) round($sum / $n);
        $r = $avg($sumR);
        $g = $avg($sumG);
        $b = $avg($sumB);
        $a = $avg($sumA);

        // Create and return the average color.
        return new self($r, $g, $b, $a);
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
     * @param string $name A CSS color name.
     * @return string The hex value for this color.
     * @throws DomainException If the provided string is not a valid color name.
     */
    public static function nameToHex(string $name): string
    {
        $hex = self::findNameHex(trim($name));

        // Check the provided color name is valid.
        if ($hex === null) {
            throw new DomainException(Stringify::prepEx('Invalid color name: ?.', $name));
        }

        return $hex;
    }

    /**
     * Convert a color name to an 8-digit hex value (no leading '#').
     *
     * @static
     * @param string $name A CSS color name.
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
     * Look up the hex value for a CSS color name, ignoring case.
     *
     * @param string $name The color name.
     * @return ?string The 8-digit hex value, or null if the name isn't a CSS color name.
     */
    private static function findNameHex(string $name): ?string
    {
        static $hexByLowerName = array_change_key_case(self::CSS_COLOR_NAMES);
        return $hexByLowerName[strtolower($name)] ?? null;
    }

    /**
     * Create a color from HSL values and an alpha byte.
     *
     * The HSL values aren't cached, so the color's HSL properties are always derived from its RGB bytes, and equal
     * colors report equal HSL values.
     *
     * @param float $hue The hue in degrees.
     * @param float $saturation The saturation as a percentage.
     * @param float $lightness The lightness as a percentage.
     * @param int $alpha The alpha component as a byte.
     * @return self The new color.
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

    #region CSS color names (public)

    /**
     * The CSS named colors, plus 'transparent', mapped to 8-digit RGBA hex values.
     *
     * The names are in UpperCamelCase for readability. Lookups by name are case-insensitive.
     *
     * @see https://www.w3.org/TR/css-color-4/#named-colors
     * @var array<string,string>
     */
    public const array CSS_COLOR_NAMES = [
        'Transparent'          => '00000000',
        'AliceBlue'            => 'f0f8ffff',
        'AntiqueWhite'         => 'faebd7ff',
        'Aqua'                 => '00ffffff',
        'Aquamarine'           => '7fffd4ff',
        'Azure'                => 'f0ffffff',
        'Beige'                => 'f5f5dcff',
        'Bisque'               => 'ffe4c4ff',
        'Black'                => '000000ff',
        'BlanchedAlmond'       => 'ffebcdff',
        'Blue'                 => '0000ffff',
        'BlueViolet'           => '8a2be2ff',
        'Brown'                => 'a52a2aff',
        'BurlyWood'            => 'deb887ff',
        'CadetBlue'            => '5f9ea0ff',
        'Chartreuse'           => '7fff00ff',
        'Chocolate'            => 'd2691eff',
        'Coral'                => 'ff7f50ff',
        'CornflowerBlue'       => '6495edff',
        'Cornsilk'             => 'fff8dcff',
        'Crimson'              => 'dc143cff',
        'Cyan'                 => '00ffffff',
        'DarkBlue'             => '00008bff',
        'DarkCyan'             => '008b8bff',
        'DarkGoldenrod'        => 'b8860bff',
        'DarkGray'             => 'a9a9a9ff',
        'DarkGreen'            => '006400ff',
        'DarkGrey'             => 'a9a9a9ff',
        'DarkKhaki'            => 'bdb76bff',
        'DarkMagenta'          => '8b008bff',
        'DarkOliveGreen'       => '556b2fff',
        'DarkOrange'           => 'ff8c00ff',
        'DarkOrchid'           => '9932ccff',
        'DarkRed'              => '8b0000ff',
        'DarkSalmon'           => 'e9967aff',
        'DarkSeaGreen'         => '8fbc8fff',
        'DarkSlateBlue'        => '483d8bff',
        'DarkSlateGray'        => '2f4f4fff',
        'DarkSlateGrey'        => '2f4f4fff',
        'DarkTurquoise'        => '00ced1ff',
        'DarkViolet'           => '9400d3ff',
        'DeepPink'             => 'ff1493ff',
        'DeepSkyBlue'          => '00bfffff',
        'DimGray'              => '696969ff',
        'DimGrey'              => '696969ff',
        'DodgerBlue'           => '1e90ffff',
        'Firebrick'            => 'b22222ff',
        'FloralWhite'          => 'fffaf0ff',
        'ForestGreen'          => '228b22ff',
        'Fuchsia'              => 'ff00ffff',
        'Gainsboro'            => 'dcdcdcff',
        'GhostWhite'           => 'f8f8ffff',
        'Gold'                 => 'ffd700ff',
        'Goldenrod'            => 'daa520ff',
        'Gray'                 => '808080ff',
        'Green'                => '008000ff',
        'GreenYellow'          => 'adff2fff',
        'Grey'                 => '808080ff',
        'Honeydew'             => 'f0fff0ff',
        'HotPink'              => 'ff69b4ff',
        'IndianRed'            => 'cd5c5cff',
        'Indigo'               => '4b0082ff',
        'Ivory'                => 'fffff0ff',
        'Khaki'                => 'f0e68cff',
        'Lavender'             => 'e6e6faff',
        'LavenderBlush'        => 'fff0f5ff',
        'LawnGreen'            => '7cfc00ff',
        'LemonChiffon'         => 'fffacdff',
        'LightBlue'            => 'add8e6ff',
        'LightCoral'           => 'f08080ff',
        'LightCyan'            => 'e0ffffff',
        'LightGoldenrodYellow' => 'fafad2ff',
        'LightGray'            => 'd3d3d3ff',
        'LightGreen'           => '90ee90ff',
        'LightGrey'            => 'd3d3d3ff',
        'LightPink'            => 'ffb6c1ff',
        'LightSalmon'          => 'ffa07aff',
        'LightSeaGreen'        => '20b2aaff',
        'LightSkyBlue'         => '87cefaff',
        'LightSlateGray'       => '778899ff',
        'LightSlateGrey'       => '778899ff',
        'LightSteelBlue'       => 'b0c4deff',
        'LightYellow'          => 'ffffe0ff',
        'Lime'                 => '00ff00ff',
        'LimeGreen'            => '32cd32ff',
        'Linen'                => 'faf0e6ff',
        'Magenta'              => 'ff00ffff',
        'Maroon'               => '800000ff',
        'MediumAquamarine'     => '66cdaaff',
        'MediumBlue'           => '0000cdff',
        'MediumOrchid'         => 'ba55d3ff',
        'MediumPurple'         => '9370dbff',
        'MediumSeaGreen'       => '3cb371ff',
        'MediumSlateBlue'      => '7b68eeff',
        'MediumSpringGreen'    => '00fa9aff',
        'MediumTurquoise'      => '48d1ccff',
        'MediumVioletRed'      => 'c71585ff',
        'MidnightBlue'         => '191970ff',
        'MintCream'            => 'f5fffaff',
        'MistyRose'            => 'ffe4e1ff',
        'Moccasin'             => 'ffe4b5ff',
        'NavajoWhite'          => 'ffdeadff',
        'Navy'                 => '000080ff',
        'OldLace'              => 'fdf5e6ff',
        'Olive'                => '808000ff',
        'OliveDrab'            => '6b8e23ff',
        'Orange'               => 'ffa500ff',
        'OrangeRed'            => 'ff4500ff',
        'Orchid'               => 'da70d6ff',
        'PaleGoldenrod'        => 'eee8aaff',
        'PaleGreen'            => '98fb98ff',
        'PaleTurquoise'        => 'afeeeeff',
        'PaleVioletRed'        => 'db7093ff',
        'PapayaWhip'           => 'ffefd5ff',
        'PeachPuff'            => 'ffdab9ff',
        'Peru'                 => 'cd853fff',
        'Pink'                 => 'ffc0cbff',
        'Plum'                 => 'dda0ddff',
        'PowderBlue'           => 'b0e0e6ff',
        'Purple'               => '800080ff',
        'RebeccaPurple'        => '663399ff',
        'Red'                  => 'ff0000ff',
        'RosyBrown'            => 'bc8f8fff',
        'RoyalBlue'            => '4169e1ff',
        'SaddleBrown'          => '8b4513ff',
        'Salmon'               => 'fa8072ff',
        'SandyBrown'           => 'f4a460ff',
        'SeaGreen'             => '2e8b57ff',
        'SeaShell'             => 'fff5eeff',
        'Sienna'               => 'a0522dff',
        'Silver'               => 'c0c0c0ff',
        'SkyBlue'              => '87ceebff',
        'SlateBlue'            => '6a5acdff',
        'SlateGray'            => '708090ff',
        'SlateGrey'            => '708090ff',
        'Snow'                 => 'fffafaff',
        'SpringGreen'          => '00ff7fff',
        'SteelBlue'            => '4682b4ff',
        'Tan'                  => 'd2b48cff',
        'Teal'                 => '008080ff',
        'Thistle'              => 'd8bfd8ff',
        'Tomato'               => 'ff6347ff',
        'Turquoise'            => '40e0d0ff',
        'Violet'               => 'ee82eeff',
        'Wheat'                => 'f5deb3ff',
        'White'                => 'ffffffff',
        'WhiteSmoke'           => 'f5f5f5ff',
        'Yellow'               => 'ffff00ff',
        'YellowGreen'          => '9acd32ff',
    ];

    #endregion
}
