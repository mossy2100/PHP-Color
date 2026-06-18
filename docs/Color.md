# Color

Immutable, memory-efficient class for working with colors in PHP.

---

## Overview

The `Color` class provides a comprehensive implementation of color handling with support for:
- RGB and HSL color spaces
- CSS color names (147 standard names plus 'transparent')
- Hex string parsing (3, 4, 6, or 8 digits)
- WCAG accessibility calculations (contrast ratio, text color selection)
- Color mixing and complementary colors

All modification methods return new instances, maintaining immutability.

Colors are stored internally in a memory-efficient way:
- Only 4 bytes for RGBA color data
- HSL values computed on-demand and cached
- Immutability eliminates cache invalidation complexity

This makes the class suitable for applications working with large numbers of colors, such as image processing or color palette generation.

---

## Properties

### red

```php
public int $red { get; }
```

The red component of the color as a byte value in the range [0, 255].

### green

```php
public int $green { get; }
```

The green component of the color as a byte value in the range [0, 255].

### blue

```php
public int $blue { get; }
```

The blue component of the color as a byte value in the range [0, 255].

### alpha

```php
public int $alpha { get; }
```

The alpha (opacity) component of the color as a byte value in the range [0, 255]. A value of 255 is fully opaque, 0 is fully transparent.

### hue

```php
public float $hue { get; }
```

The hue of the color as an angle in degrees in the range [0, 360). Computed on first access and cached.

### saturation

```php
public float $saturation { get; }
```

The saturation of the color as a fraction in the range [0.0, 1.0]. Computed on first access and cached.

### lightness

```php
public float $lightness { get; }
```

The lightness of the color as a fraction in the range [0.0, 1.0]. Computed on first access and cached.

### relativeLuminance

```php
public float $relativeLuminance { get; }
```

The relative luminance of the color in the range [0.0, 1.0], calculated according to WCAG guidelines.

### perceivedLightness

```php
public float $perceivedLightness { get; }
```

The perceived lightness (CIE L*) of the color in the range [0.0, 1.0].

**Example:**
```php
$color = new Color('#ff8040');

// RGBA components (0-255)
$color->red;    // 255
$color->green;  // 128
$color->blue;   // 64
$color->alpha;  // 255

// HSL components (floats)
$color->hue;         // 20.0 (degrees)
$color->saturation;  // 1.0 (fraction)
$color->lightness;   // 0.625 (fraction)

// Luminance (for accessibility)
$color->relativeLuminance;   // 0.0-1.0
$color->perceivedLightness;  // 0.0-1.0
```

---

## Constants

### CSS_COLOR_NAMES

```php
public const array CSS_COLOR_NAMES
```

Array of CSS color names (lowercase) mapped to 8-digit hex values. Includes all 147 standard CSS color names plus 'transparent'.

**Example:**
```php
new Color('AliceBlue');
new Color('coral');
new Color('darkslategray');
new Color('PapayaWhip');
new Color('transparent');  // RGBA: 0, 0, 0, 0
```

---

## Constructor

```php
public function __construct(string $color = 'black')
```

Create a new color from a string.

**Parameters:**
- `$color` (string) - A CSS color name (e.g. 'Navy') or hex string (3, 4, 6, or 8 hex digits, with or without '#')

**Examples:**
```php
// From CSS color names (case-insensitive)
$red = new Color('red');
$blue = new Color('Blue');
$transparent = new Color('transparent');

// From hex strings
$orange = new Color('#ff8000');
$cyan = new Color('0ff');
$semiTransparent = new Color('#ff000080');
```

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) or `DomainException` if the string is not a valid CSS color.

---

## Factory Methods

### fromRgba()

```php
public static function fromRgba(
    int|float $red,
    int|float $green,
    int|float $blue,
    int|float $alpha = 255
): self
```

Create a color from RGBA values.

Each channel can be provided as either an int in the range [0, 255] or a float in the range [0.0, 1.0].

**Parameters:**
- `$red` (int|float) - The red component
- `$green` (int|float) - The green component
- `$blue` (int|float) - The blue component
- `$alpha` (int|float) - The alpha component (default: 255 = fully opaque)

**Examples:**
```php
$purple = Color::fromRgba(128, 0, 255);
$transparentRed = Color::fromRgba(255, 0, 0, 128);
$royalBlue = Color::fromRgba(0.255, 0.412, 0.882);
$transparentGreen = Color::fromRgba(0.133, 0.545, 0.133, 0.75);
```

**Throws:** `DomainException` if any inputs are invalid.

### fromHsla()

```php
public static function fromHsla(
    float $hue,
    float $saturation,
    float $lightness,
    int|float $alpha = 255
): self
```

Create a color from HSLA values.

**Parameters:**
- `$hue` (float) - The hue in degrees
- `$saturation` (float) - The saturation as a fraction in [0.0, 1.0]
- `$lightness` (float) - The lightness as a fraction in [0.0, 1.0]
- `$alpha` (int|float) - The alpha as int [0, 255] or float [0.0, 1.0] (default: 255)

**Examples:**
```php
$green = Color::fromHsla(120, 1.0, 0.5);
$pastel = Color::fromHsla(200, 0.5, 0.8, 200);
$transparentPink = Color::fromHsla(350, 1.0, 0.876, 0.66);
```

**Throws:** `DomainException` if any inputs are invalid.

---

## Validation Methods

### validHex()

```php
public static function validHex(string $str): bool
```

Check if a string is a valid hex color string.

**Examples:**
```php
Color::validHex('#ff0000');   // true
Color::validHex('ff0');       // true
Color::validHex('notahex');   // false
```

### validName()

```php
public static function validName(string $name): bool
```

Check if a string is a valid CSS color name.

**Examples:**
```php
Color::validName('red');       // true
Color::validName('notacolor'); // false
```

---

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if two colors are equal (all RGBA values match).

**Example:**
```php
$color1 = new Color('#ff0000');
$color2 = new Color('red');
$color1->equal($color2);  // true
```

---

## Transformation Methods

Since Color is immutable, transformation methods return new Color instances.

### withRed()

```php
public function withRed(int|float $red): self
```

Create a new Color with the specified red component.

The value can be provided as either an int in the range [0, 255] or a float in the range [0.0, 1.0].

**Throws:** `DomainException` if the value is invalid.

### withGreen()

```php
public function withGreen(int|float $green): self
```

Create a new Color with the specified green component.

The value can be provided as either an int in the range [0, 255] or a float in the range [0.0, 1.0].

**Throws:** `DomainException` if the value is invalid.

### withBlue()

```php
public function withBlue(int|float $blue): self
```

Create a new Color with the specified blue component.

The value can be provided as either an int in the range [0, 255] or a float in the range [0.0, 1.0].

**Throws:** `DomainException` if the value is invalid.

### withAlpha()

```php
public function withAlpha(int|float $alpha): self
```

Create a new Color with the specified alpha component.

The value can be provided as either an int in the range [0, 255] or a float in the range [0.0, 1.0].

**Throws:** `DomainException` if the value is invalid.

### withHue()

```php
public function withHue(float $hue): self
```

Create a new Color with the specified hue, provided in degrees (will be normalized to [0, 360)).

### withSaturation()

```php
public function withSaturation(float $saturation): self
```

Create a new Color with the specified saturation.

**Throws:** `DomainException` if the value is not in [0.0, 1.0].

### withLightness()

```php
public function withLightness(float $lightness): self
```

Create a new Color with the specified lightness.

**Throws:** `DomainException` if the value is not in [0.0, 1.0].

### Examples of `with` methods
```php
// Modify RGBA components (ints)
$red = new Color('red');
$darkRed = $red->withRed(128);  // 50% red
$transparentRed = $red->withAlpha(128);

// Modify RGBA components (floats)
$blue = new Color('blue');
$darkBlue = $blue->withBlue(0.5);  // 50% blue
$transparentBlue = $blue->withAlpha(0.5);

// Modify HSL components
$orange = $red->withHue(30);
$desaturated = $red->withSaturation(0.5);

// Chain modifications
$custom = $red->withHue(45)->withSaturation(0.8)->withAlpha(0.75);

// Original color is unchanged
$red->hue;  // Still 0
```

### mix()

```php
public function mix(self $other, float $frac = 0.5): self
```

Mix two colors.

**Parameters:**
- `$other` (Color) - The color to mix with
- `$frac` (float) - The fraction of the other color [0.0, 1.0] (default: 0.5)

If the fraction is 0, returns this color. If 1, returns the other color.

**Examples:**
```php
$red = new Color('red');
$blue = new Color('blue');
$purple = $red->mix($blue);           // 50% each
$mostlyBlue = $red->mix($blue, 0.8);  // 20% red, 80% blue
```

**Throws:** `DomainException` if the fraction is invalid.

### complement()

```php
public function complement(): self
```

Get the complementary color (hue + 180 degrees).

**Example:**
```php
$red = new Color('red');
$cyan = $red->complement();  // Hue shifts from 0 to 180
```

---

## Accessibility Methods

Methods for evaluating color accessibility according to WCAG (Web Content Accessibility Guidelines).

### contrastRatio()

```php
public function contrastRatio(self $other): float
```

Calculate the contrast ratio between two colors (1:1 to 21:1).

**Example:**
```php
$background = new Color('#336699');
$white = new Color('white');
$ratio = $background->contrastRatio($white);  // e.g., 5.5
```

### bestTextColor()

```php
public function bestTextColor(
    string|Color $lightTextColor = 'white',
    string|Color $darkTextColor = 'black'
): Color
```

Determine the best text color for readability on this background color.

By default, chooses between white and black, but custom light/dark text colors can be provided. When contrast ratios are equal, prefers the dark color.

**Parameters:**
- `$lightTextColor` (string|Color) - The light text color option (default: 'white')
- `$darkTextColor` (string|Color) - The dark text color option (default: 'black')

**Returns:** The text color with the best contrast ratio.

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) or `DomainException` if either text color string is invalid.

**Examples:**
```php
$background = new Color('#336699');

// Default: choose between white and black
$textColor = $background->bestTextColor();  // Returns white Color

// Custom text colors as strings
$textColor = $background->bestTextColor('ivory', 'navy');

// Custom text colors as Color objects
$cream = new Color('cornsilk');
$dark = new Color('#1a1a1a');
$textColor = $background->bestTextColor($cream, $dark);
```

---

## Conversion Methods

### toHex()

```php
public function toHex(
    bool $includeAlpha = true,
    bool $includeHash = true,
    bool $upperCase = false
): string
```

Output the color as a hexadecimal string.

**Examples:**
```php
$color = Color::fromRgba(255, 128, 0, 200);

(string)$color;                  // '#ff8000c8'
$color->toHex();                 // '#ff8000c8'
$color->toHex(false);            // '#ff8000' (no alpha)
$color->toHex(true, false);      // 'ff8000c8' (no hash)
$color->toHex(true, true, true); // '#FF8000C8' (upper-case)
```

### toRgbString()

```php
public function toRgbString(): string
```

Output the color as a CSS rgb() string (modern syntax).

**Example:**
```php
$color = Color::fromRgba(255, 128, 0, 200);
$color->toRgbString();  // 'rgb(255 128 0 / 0.784314)'
```

### toHslString()

```php
public function toHslString(): string
```

Output the color as a CSS hsl() string (modern syntax).

**Example:**
```php
$color = Color::fromRgba(255, 128, 0, 200);
$color->toHslString();  // 'hsl(30.117647deg 100% 50% / 0.784314)'
```

### \_\_toString()

```php
public function __toString(): string
```

Convert to string (8-digit RGBA hex with leading '#').

Called implicitly when casting to string or interpolating in a string context.

**Example:**
```php
$color = new Color('red');
echo "background-color: $color;";  // "background-color: #ff0000ff;"
```

### toRgbaArray()

```php
public function toRgbaArray(): array
```

Get RGBA values as an associative array of bytes.

**Returns:** `array{red: int, green: int, blue: int, alpha: int}`

### toHslArray()

```php
public function toHslArray(): array
```

Get HSL values as an associative array of floats.

**Returns:** `array{hue: float, saturation: float, lightness: float}`

### toArray()

```php
public function toArray(): array
```

Get all color properties (RGBA and HSL) as an associative array.

---

## Static Utility Methods

### gamma()

```php
public static function gamma(int $byte): float
```

Apply the sRGB gamma correction transfer function.

Used by the relative luminance calculation, but made public for other purposes.

**Example:**
```php
$linear = Color::gamma(128);  // ~0.215
```

### average()

```php
public static function average(self ...$colors): self
```

Find the average of several colors.

**Example:**
```php
$avg = Color::average($color1, $color2, $color3);
```

**Throws:** `ArgumentCountError` if no colors are provided.

### rgbToHsl()

```php
public static function rgbToHsl(int $red, int $green, int $blue): array
```

Convert RGB values to HSL.

**Returns:** `float[]` - Array of [hue, saturation, lightness] where:
- hue is in degrees [0, 360)
- saturation is a fraction [0.0, 1.0]
- lightness is a fraction [0.0, 1.0]

**Example:**
```php
$hsl = Color::rgbToHsl(255, 0, 0);  // [0.0, 1.0, 0.5]
```

**Throws:** `DomainException` if values are not valid bytes.

### hslToRgb()

```php
public static function hslToRgb(float $hue, float $saturation, float $lightness): array
```

Convert HSL values to RGB.

**Returns:** `int[]` - Array of [red, green, blue] as bytes.

**Example:**
```php
$rgb = Color::hslToRgb(120, 1.0, 0.5);  // [0, 255, 0]
```

**Throws:** `DomainException` if values are not valid.

### normalizeHex()

```php
public static function normalizeHex(string $hex): string
```

Convert a CSS hex color string to an 8-digit lowercase hex string (no leading '#').

**Examples:**
```php
Color::normalizeHex('#f00');      // 'ff0000ff'
Color::normalizeHex('#FF00');     // 'ffff0000'
Color::normalizeHex('abc');       // 'aabbccff'
```

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the string is not a valid hex color.

### hexToBytes()

```php
public static function hexToBytes(string $hex): array
```

Convert a hex string to RGBA bytes.

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if the string is not a valid hex color.

### nameToHex()

```php
public static function nameToHex(string $name): string
```

Convert a color name to an 8-digit hex value (no leading '#').

**Throws:** `DomainException` if the name is not valid.

### nameToBytes()

```php
public static function nameToBytes(string $name): array
```

Convert a color name to RGBA bytes.

**Throws:** `DomainException` if the name is not valid.

### parseToBytes()

```php
public static function parseToBytes(string $str): array
```

Parse a CSS hex or named color to RGBA bytes.

**Returns:** `int[]` - Array of [red, green, blue, alpha] as bytes.

**Example:**
```php
$bytes = Color::parseToBytes('#ff8000');
// [255, 128, 0, 255]
```

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) or `DomainException` if the string is not a valid color.

---

## See Also

- **[Equatable](https://github.com/mossy2100/PHP-Core/blob/main/docs/Traits/Comparison/Equatable.md)** - Trait for implementing `equal()`
