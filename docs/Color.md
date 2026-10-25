# Color

Immutable class for working with colors in PHP.

---

## Overview

The `Color` class provides a comprehensive implementation of color handling with support for:
- RGB and HSL color spaces
- CSS color names (148 standard names plus 'transparent'), and around 300 extra color names (see [ColorNames](ColorNames.md))
- Hex string parsing (3, 4, 6, or 8 digits)
- WCAG accessibility calculations (contrast ratio, text color selection)
- Color mixing and complementary colors

All modification methods return new instances, maintaining immutability.

Storage:
- The RGBA components are stored as 4 read-only int properties.
- HSL values are computed on first use and stored, which is safe because colors are immutable.

### Units

| Value                                  | Unit                                         |
| -------------------------------------- | -------------------------------------------- |
| Red, green, blue and alpha properties  | Bytes in the range [0, 255]                  |
| Constructor arguments                  | Bytes in the range [0, 255]                  |
| `fromRgba()` and `fromHsla()` channels | Percentages in the range [0, 100]            |
| Hue                                    | Degrees, wrapped to the range [0, 360)       |
| Saturation, lightness                  | Percentages in the range [0, 100]            |
| Perceived lightness                    | CIE L*, in the range [0, 100]                |
| Relative luminance                     | A fraction in the range [0.0, 1.0], per WCAG |

The `withRed()`, `withGreen()`, `withBlue()` and `withAlpha()` methods take a byte by default, or a percentage when
`$percent` is `true`.

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

The alpha (opacity) component of the color as a byte value in the range [0, 255]. A value of 255 is fully opaque, 0 is
fully transparent.

### hue

```php
public float $hue { get; }
```

The hue of the color as an angle in degrees in the range [0, 360). Computed on first access and cached.

### saturation

```php
public float $saturation { get; }
```

The saturation of the color as a percentage in the range [0, 100]. Computed on first access and cached.

### lightness

```php
public float $lightness { get; }
```

The lightness of the color as a percentage in the range [0, 100]. Computed on first access and cached.

### relativeLuminance

```php
public float $relativeLuminance { get; }
```

The relative luminance of the color in the range [0.0, 1.0], calculated according to WCAG guidelines.

### perceivedLightness

```php
public float $perceivedLightness { get; }
```

The perceived lightness (CIE L*) of the color in the range [0, 100].

**Example:**

```php
$color = Color::fromString('#ff6600');

// RGBA components (0-255)
$color->red;    // 255
$color->green;  // 102
$color->blue;   // 0
$color->alpha;  // 255

// HSL components
$color->hue;         // 24.0 (degrees)
$color->saturation;  // 100.0 (percent)
$color->lightness;   // 50.0 (percent)

// Luminance (for accessibility)
$color->relativeLuminance;   // 0.0-1.0
$color->perceivedLightness;  // 0-100
```

---

## Constructor

```php
public function __construct(int $red = 0, int $green = 0, int $blue = 0, int $alpha = 255)
```

Create a new color from RGBA bytes. Defaults to opaque black.

**Parameters:**
- `$red` (int) - The red component, in the range [0, 255]
- `$green` (int) - The green component, in the range [0, 255]
- `$blue` (int) - The blue component, in the range [0, 255]
- `$alpha` (int) - The alpha component, in the range [0, 255] (default: 255 = fully opaque)

**Examples:**

```php
$black = new Color();
$purple = new Color(128, 0, 255);
$transparentRed = new Color(255, 0, 0, 128);
```

**Throws:** `DomainException` if any byte is out of range.

---

## Factory Methods

### fromString()

```php
public static function fromString(string $colorString): self
```

Create a color from a color name (e.g. 'Navy' or 'Amethyst') or hex string (3, 4, 6, or 8 hex digits, with or without
'#'). Names are case-insensitive, and surrounding whitespace is ignored. It's the inverse of `__toString()`.

The recognised names are listed in [`ColorNames`](ColorNames.md).

**Returns:** `Color` - The new color.

**Examples:**

```php
// From color names (case-insensitive)
$red = Color::fromString('red');
$blue = Color::fromString('Blue');
$transparent = Color::fromString('transparent');

// From hex strings
$orange = Color::fromString('#ff8000');
$cyan = Color::fromString('0ff');
$semiTransparent = Color::fromString('#ff000080');
```

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if
the string is not a color name or hex color string.

### fromRgba()

```php
public static function fromRgba(float $red, float $green, float $blue, float $alpha = 100): self
```

Create a color from RGBA percentages, as in the CSS `rgb()` function. To create a color from bytes, use the
constructor.

**Parameters:**
- `$red` (float) - The red component, in the range [0, 100]
- `$green` (float) - The green component, in the range [0, 100]
- `$blue` (float) - The blue component, in the range [0, 100]
- `$alpha` (float) - The alpha component, in the range [0, 100] (default: 100 = fully opaque)

**Returns:** `Color` - The new color.

**Examples:**

```php
$orange = Color::fromRgba(100, 50, 0);              // #ff8000ff
$transparentGreen = Color::fromRgba(13.3, 54.5, 13.3, 75);
```

**Throws:** `DomainException` if any percentage is out of range.

### fromHsla()

```php
public static function fromHsla(float $hue, float $saturation, float $lightness, float $alpha = 100): self
```

Create a color from HSLA values, as in the CSS `hsl()` function.

The values are converted to RGB bytes, and the color's HSL properties are derived from those bytes, so they can differ
slightly from the values given. For example, `Color::fromHsla(120, 100, 0)` is black, with a hue of `0.0`.

**Parameters:**
- `$hue` (float) - The hue in degrees (wrapped to [0, 360))
- `$saturation` (float) - The saturation, in the range [0, 100]
- `$lightness` (float) - The lightness, in the range [0, 100]
- `$alpha` (float) - The alpha, in the range [0, 100] (default: 100 = fully opaque)

**Returns:** `Color` - The new color.

**Examples:**

```php
$green = Color::fromHsla(120, 100, 50);
$pastel = Color::fromHsla(200, 50, 80, 80);
$transparentPink = Color::fromHsla(350, 100, 87.6, 66);
```

**Throws:** `DomainException` if any percentage is out of range.

---

## Validation Methods

### validHex()

```php
public static function validHex(string $hex): bool
```

Check if a string is a valid hex color string.

**Returns:** `bool` - `true` if the string is a valid hex color string, `false` otherwise.

**Examples:**

```php
Color::validHex('#ff0000');   // true
Color::validHex('ff0');       // true
Color::validHex('notahex');   // false
```

---

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if two colors are equal (all RGBA values match).

**Returns:** `bool` - `true` if the value is a `Color` with the same RGBA components, `false` otherwise.

**Example:**

```php
$color1 = Color::fromString('#ff0000');
$color2 = Color::fromString('red');
$color1->equal($color2);  // true
```

---

## Transformation Methods

Since Color is immutable, transformation methods return new Color instances.

### withRed()

```php
public function withRed(float $red, bool $percent = false): self
```

Create a new Color with the specified red component.

By default, the value is a byte in the range [0, 255], rounded to the nearest integer. If `$percent` is `true`, it's a
percentage in the range [0, 100].

**Returns:** `Color` - A new color with the given red component.

**Throws:** `DomainException` if the value is out of range.

### withGreen()

```php
public function withGreen(float $green, bool $percent = false): self
```

Create a new Color with the specified green component. The value is interpreted as for `withRed()`.

**Returns:** `Color` - A new color with the given green component.

**Throws:** `DomainException` if the value is out of range.

### withBlue()

```php
public function withBlue(float $blue, bool $percent = false): self
```

Create a new Color with the specified blue component. The value is interpreted as for `withRed()`.

**Returns:** `Color` - A new color with the given blue component.

**Throws:** `DomainException` if the value is out of range.

### withAlpha()

```php
public function withAlpha(float $alpha, bool $percent = false): self
```

Create a new Color with the specified alpha component. The value is interpreted as for `withRed()`.

**Returns:** `Color` - A new color with the given alpha component.

**Throws:** `DomainException` if the value is out of range.

### withHue()

```php
public function withHue(float $hue): self
```

Create a new Color with the specified hue, provided in degrees (will be normalized to [0, 360)).

**Returns:** `Color` - A new color with the given hue.

### withSaturation()

```php
public function withSaturation(float $saturation): self
```

Create a new Color with the specified saturation, as a percentage.

**Returns:** `Color` - A new color with the given saturation.

**Throws:** `DomainException` if the value is not in [0, 100].

### withLightness()

```php
public function withLightness(float $lightness): self
```

Create a new Color with the specified lightness, as a percentage.

**Returns:** `Color` - A new color with the given lightness.

**Throws:** `DomainException` if the value is not in [0, 100].

### Examples of `with` methods

```php
// Modify RGBA components (bytes)
$red = Color::fromString('red');
$darkRed = $red->withRed(128);
$transparentRed = $red->withAlpha(128);

// Modify RGBA components (percentages)
$blue = Color::fromString('blue');
$darkBlue = $blue->withBlue(50, true);
$transparentBlue = $blue->withAlpha(50, percent: true);

// Modify HSL components
$orange = $red->withHue(30);
$desaturated = $red->withSaturation(50);

// Chain modifications
$custom = $red->withHue(45)->withSaturation(80)->withAlpha(75, true);

// Original color is unchanged
$red->hue;  // Still 0
```

### mix()

```php
public function mix(self $other, float $percent = 50): self
```

Mix two colors.

**Parameters:**
- `$other` (Color) - The color to mix with
- `$percent` (float) - The percentage of the other color, in the range [0, 100] (default: 50)

If the percentage is 0, returns this color. If 100, returns the other color.

The colors are premultiplied by alpha, as in CSS `color-mix()`. So a more transparent color contributes less to the
result's RGB, and mixing with `'transparent'` fades a color without darkening it.

**Returns:** `Color` - The mixed color.

**Examples:**

```php
$red = Color::fromString('red');
$blue = Color::fromString('blue');
$purple = $red->mix($blue);         // 50% each
$mostlyBlue = $red->mix($blue, 80); // 20% red, 80% blue
$fadedRed = $red->mix(Color::fromString('transparent')); // #ff000080
```

**Throws:** `DomainException` if the percentage is out of range.

### complement()

```php
public function complement(): self
```

Get the complementary color (hue + 180 degrees).

**Returns:** `Color` - The complementary color.

**Example:**

```php
$red = Color::fromString('red');
$cyan = $red->complement();  // Hue shifts from 0 to 180
```

---

## Aggregation Methods

### average()

```php
public static function average(self ...$colors): self
```

Find the average of several colors. Like `mix()`, it premultiplies by alpha.

**Returns:** `Color` - The average color.

**Example:**

```php
$avg = Color::average($color1, $color2, $color3);
```

**Throws:** `LengthException` if no colors are provided.

---

## Accessibility Methods

Methods for evaluating color accessibility according to WCAG (Web Content Accessibility Guidelines).

### contrastRatio()

```php
public function contrastRatio(self $other): float
```

Calculate the contrast ratio between two colors (1:1 to 21:1).

Alpha is ignored, since WCAG defines contrast for opaque colors. To check a translucent color, blend it with its
background first, e.g. using `mix()`.

**Returns:** `float` - The contrast ratio, from 1 (no contrast) to 21 (black on white).

**Example:**

```php
$background = Color::fromString('#336699');
$white = Color::fromString('white');
$ratio = $background->contrastRatio($white);  // about 6.0
```

### bestTextColor()

```php
public function bestTextColor(
    string|Color $lightTextColor = 'white',
    string|Color $darkTextColor = 'black'
): Color
```

Determine the best text color for readability on this background color.

By default, chooses between white and black, but custom light/dark text colors can be provided. When contrast ratios
are equal, prefers the dark color.

Alpha is ignored, since WCAG defines contrast for opaque colors. To check a translucent color, blend it with its
background first, e.g. using `mix()`.

**Parameters:**
- `$lightTextColor` (string|Color) - The light text color option (default: 'white')
- `$darkTextColor` (string|Color) - The dark text color option (default: 'black')

**Returns:** `Color` - The text color with the best contrast ratio.

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if
either text color string is invalid.

**Examples:**

```php
$background = Color::fromString('#336699');

// Default: choose between white and black
$textColor = $background->bestTextColor();  // Returns white Color

// Custom text colors as strings
$textColor = $background->bestTextColor('ivory', 'navy');

// Custom text colors as Color objects
$cream = Color::fromString('cornsilk');
$dark = Color::fromString('#1a1a1a');
$textColor = $background->bestTextColor($cream, $dark);
```

### gamma()

```php
public static function gamma(int $byte): float
```

Apply the sRGB gamma correction transfer function.

Used by the relative luminance calculation, but made public for other purposes.

**Returns:** `float` - The linear component value, in the range [0.0, 1.0].

**Example:**

```php
$linear = Color::gamma(128);  // ~0.216
```

**Throws:** `DomainException` if the byte is out of range.

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

**Returns:** `string` - The hex color string.

**Examples:**

```php
$color = new Color(255, 128, 0, 200);

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

Output the color as a CSS rgb() string (modern syntax). The red, green and blue components are bytes, and the alpha is
a percentage.

**Returns:** `string` - The CSS `rgb()` string.

**Example:**

```php
$color = new Color(255, 128, 0, 200);
$color->toRgbString();  // 'rgb(255 128 0 / 78.431373%)'
```

### toHslString()

```php
public function toHslString(): string
```

Output the color as a CSS hsl() string (modern syntax).

**Returns:** `string` - The CSS `hsl()` string.

**Example:**

```php
$color = new Color(255, 128, 0, 200);
$color->toHslString();  // 'hsl(30.117647deg 100% 50% / 78.431373%)'
```

### \_\_toString()

```php
public function __toString(): string
```

Convert to string (8-digit RGBA hex with leading '#'). `fromString()` is the inverse.

Called implicitly when casting to string or interpolating in a string context.

**Returns:** `string` - The 8-digit RGBA hex string, with a leading '#'.

**Example:**

```php
$color = Color::fromString('red');
echo "background-color: $color;";  // "background-color: #ff0000ff;"
```

### toRgbaArray()

```php
public function toRgbaArray(): array
```

Get RGBA values as an associative array of bytes.

**Returns:** `array{red: int, green: int, blue: int, alpha: int}` - The RGBA components as bytes.

### toHslArray()

```php
public function toHslArray(): array
```

Get HSL values as an associative array of floats: the hue in degrees, and the saturation and lightness as percentages.

**Returns:** `array{hue: float, saturation: float, lightness: float}` - The HSL components.

### toArray()

```php
public function toArray(): array
```

Get all color properties (RGBA and HSL) as an associative array.

**Returns:** `array<string, int|float>` - The RGBA bytes and HSL values, keyed by name.

### rgbToHsl()

```php
public static function rgbToHsl(int $red, int $green, int $blue): array
```

Convert RGB values to HSL.

**Returns:** `list<float>` - The [hue, saturation, lightness], where:
- hue is in degrees [0, 360)
- saturation is a percentage [0, 100]
- lightness is a percentage [0, 100]

**Example:**

```php
$hsl = Color::rgbToHsl(255, 0, 0);  // [0.0, 100.0, 50.0]
```

**Throws:** `DomainException` if values are not valid bytes.

### hslToRgb()

```php
public static function hslToRgb(float $hue, float $saturation, float $lightness): array
```

Convert HSL values to RGB. The saturation and lightness are percentages.

**Returns:** `list<int>` - The [red, green, blue] bytes.

**Example:**

```php
$rgb = Color::hslToRgb(120, 100, 50);  // [0, 255, 0]
```

**Throws:** `DomainException` if values are not valid.

### normalizeHex()

```php
public static function normalizeHex(string $hex): string
```

Convert a CSS hex color string to an 8-digit lowercase hex string (no leading '#').

**Returns:** `string` - The 8-digit lower-case hex string, with no leading '#'.

**Examples:**

```php
Color::normalizeHex('#f00');      // 'ff0000ff'
Color::normalizeHex('#FF00');     // 'ffff0000'
Color::normalizeHex('abc');       // 'aabbccff'
```

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if
the string is not a valid hex color.

### hexToBytes()

```php
public static function hexToBytes(string $hex): array
```

Convert a hex string to RGBA bytes.

**Returns:** `list<int<0, 255>>` - The [red, green, blue, alpha] bytes.

**Throws:** [`FormatException`](https://github.com/mossy2100/PHP-Core/blob/main/docs/Exceptions/FormatException.md) if
the string is not a valid hex color.

---

## See Also

- **[ColorNames](ColorNames.md)** - The color names that `fromString()` and the name methods recognise
- **[Equatable](https://github.com/mossy2100/PHP-Core/blob/main/docs/Traits/Comparison/Equatable.md)** - Trait for
  implementing `equal()`
