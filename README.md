# Galaxon PHP Color

An immutable, memory-efficient Color class for PHP with support for RGB and HSL color spaces, CSS color names, and WCAG accessibility features.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Coverage Report](https://html-preview.github.io/?url=https://github.com/mossy2100/PHP-Color/blob/main/build/coverage/index.html)**

![PHP 8.4](docs/logo_php8_4.png)

## Description

This package provides a comprehensive Color class for working with colors in PHP. The class is designed to be:

- **Immutable** - Color values cannot be changed after creation, ensuring predictability and thread safety
- **Memory-efficient** - Colors are stored internally as a 4-byte binary string
- **Feature-rich** - Supports RGB, HSL, hex strings, CSS color names, and accessibility calculations
- **CSS-compatible** - Outputs to modern CSS color formats (hex, rgb, hsl)

## Development and Quality Assurance / AI Disclosure

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach resulted in a high-quality, thoroughly-tested, and well-documented package delivered in significantly less time than traditional development methods.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

## Requirements

- PHP ^8.4
- galaxon/core ^0.3

## Installation

```bash
composer require galaxon/color
```

## Features

### Creating Colors

```php
use Galaxon\Color\Color;

// From CSS color names (case-insensitive)
$red = new Color('red');
$blue = new Color('Blue');
$transparent = new Color('transparent');

// From hex strings (3, 4, 6, or 8 digits, with or without '#')
$orange = new Color('#ff8000');
$cyan = new Color('0ff');
$semiTransparent = new Color('#ff000080');

// From RGB values with optional alpha channel.
// All channels can be specified as integers in the range [0, 255] or floats in the range [0.0, 1.0].
$purple = Color::fromRGB(128, 0, 255);
$transparentRed = Color::fromRGB(255, 0, 0, 128);
$royalBlue = Color::fromRGB(0.255, 0.412, 0.882);
$transparentGreen = Color::fromRGB(0.133, 0.545, 0.133, 0.75);

// From HSLA values with optional alpha channel.
// Hue is specified in degrees.
// Saturation and lightness are specified as floats in the range [0.0, 1.0].
// Alpha can be specified as an integer in the range [0, 255] or a float in the range [0.0, 1.0].
$green = Color::fromHSL(120, 1.0, 0.5);
$pastel = Color::fromHSL(200, 0.5, 0.8, 200);
$transparentPink = Color::fromHSL(350, 1.0, 0.876, 0.66);
```

### Accessing Color Properties

Colors provide read-only access to both RGB and HSL representations:

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

// Luminance and perceived lightness (for accessibility)
$color->relativeLuminance;   // 0.0-1.0
$color->perceivedLightness;  // 0.0-1.0
```

### Immutable Modifications

Since Color is immutable, modification methods return new Color instances:

```php
$red = new Color('red');

// Modify RGB components
$darkRed = $red->withRed(128);
$redGreen = $red->withGreen(255);
$transparent = $red->withAlpha(128);

// Modify HSL components
$orange = $red->withHue(30);
$desaturated = $red->withSaturation(0.5);
$light = $red->withLightness(0.8);

// Chain modifications
$custom = $red->withHue(45)->withSaturation(0.8)->withAlpha(200);

// Original color is unchanged
$red->hue;  // Still 0
```

### String Output

```php
$color = Color::fromRGB(255, 128, 0, 200);

// Hex strings
(string)$color;                  // '#ff8000c8'
$color->toHex();                 // '#ff8000c8'
$color->toHex(false);            // '#ff8000' (no alpha)
$color->toHex(true, false);      // 'ff8000c8' (no hash)
$color->toHex(true, true, true); // '#FF8000C8' (upper-case)

// CSS functional notation (modern syntax)
$color->toRGBString();  // 'rgb(255 128 0 / 0.784314)'
$color->toHSLString();   // 'hsl(30.117647deg 100% 50% / 0.784314)'
```

### Color Operations

```php
// Mix two colors
$red = new Color('red');
$blue = new Color('blue');
$purple = $red->mix($blue);           // 50% each
$mostlyBlue = $red->mix($blue, 0.8);  // 20% red, 80% blue

// Get complementary color (hue + 180°)
$red = new Color('red');
$cyan = $red->complement();  // Hue shifts from 0° to 180°

// Average multiple colors
$avg = Color::average($color1, $color2, $color3);

// Compare colors
$color1->equals($color2);  // true if RGBA values match
```

### Accessibility (WCAG)

```php
$background = new Color('#336699');
$white = new Color('white');
$black = new Color('black');

// Calculate contrast ratio (1:1 to 21:1)
$ratio = $background->contrastRatio($white);  // e.g., 5.5

// Automatically pick best text color for readability
$textColor = $background->bestTextColor();  // Returns 'white' or 'black'
$text = new Color($textColor);
```

### Validation and Parsing

```php
// Validate color strings
Color::isValidHex('#ff0000');   // true
Color::isValidHex('ff0');       // true
Color::isValidHex('notahex');   // false

Color::isValidName('red');       // true
Color::isValidName('notacolor'); // false

// Normalize hex strings to 8-digit lowercase
Color::normalizeHex('#f00');      // 'ff0000ff'
Color::normalizeHex('#FF00');     // 'ffff0000'
Color::normalizeHex('abc');       // 'aabbccff'

// Parse to RGBA bytes
$bytes = Color::parseToBytes('#ff8000');
// [255, 128, 0, 255]
```

### Color Conversion

```php
// RGB to HSL (returns list: [hue, saturation, lightness])
$hsl = Color::RGBToHSL(255, 0, 0);  // [0.0, 1.0, 0.5]

// HSL to RGB (returns list: [red, green, blue])
$rgb = Color::HSLToRGB(120, 1.0, 0.5);  // [0, 255, 0]

// Gamma correction (sRGB transfer function)
$linear = Color::gamma(128);  // ~0.215
```

### CSS Color Names

The class supports all 147 standard CSS color names plus 'transparent'. Names are case-insensitive.

```php
new Color('AliceBlue');
new Color('coral');
new Color('darkslategray');
new Color('PapayaWhip');
new Color('transparent');  // RGBA: 0, 0, 0, 0
// ... and many more
```

## Efficiency

The Color class stores RGBA values as a 4-byte binary string internally, making it extremely memory-efficient:

- **4 bytes** for the color data (vs. 16-32 bytes for 4 integers)
- HSL values are computed on-demand and cached
- Immutability means no cache invalidation complexity

This makes the class suitable for applications that work with large numbers of colors, such as image processing or color palette generation.

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage (generates HTML report and clover.xml)
composer test
```

## License

MIT License - see [LICENSE](LICENSE) for details

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Color/issues).

## Support

- **Issues**: https://github.com/mossy2100/PHP-Color/issues
- **Examples**: See test files for comprehensive usage examples

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
