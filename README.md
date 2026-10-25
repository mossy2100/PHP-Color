# Mossy2100 PHP Color

An immutable Color class for PHP with support for RGB and HSL color spaces, CSS color names, and WCAG accessibility
features.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)**

![PHP 8.4](docs/logo_php8_4.png)

---

## Description

This package provides a comprehensive Color class for working with colors in PHP. The class is designed to be:

- **Immutable** - Color values cannot be changed after creation, so a color can be shared safely
- **Simple** - The RGBA components are read-only int properties, and HSL values are computed on first use
- **Feature-rich** - Supports RGB, HSL, hex strings, CSS and extra color names, and accessibility calculations
- **CSS-compatible** - Outputs to modern CSS color formats (hex, rgb, hsl)

---

## Development and Quality Assurance

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and
commented primarily by the author, with Claude providing substantial assistance with code review, suggesting
improvements, debugging, and generating tests and documentation.

All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/),
[PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing
with 100% code coverage.

This collaborative approach has produced a well-designed, production-ready package with thorough test coverage and
documentation.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

---

## Requirements

- PHP ^8.4
- ext-ctype
- ext-intl, through `mossy2100/core`, for `toRgbString()` and `toHslString()`
- mossy2100/core

---

## Installation

```bash
composer require mossy2100/color
```

---

## Quick Start

```php
use Mossy2100\Color\Color;

// Create colors from various formats
$red = Color::fromString('red');
$blue = Color::fromString('#0000ff');
$purple = new Color(128, 0, 255);           // Bytes [0, 255]
$orange = Color::fromRgba(100, 50, 0);      // Percentages [0, 100], as in CSS rgb()
$green = Color::fromHsla(120, 100, 50);     // Degrees and percentages, as in CSS hsl()

// Access color properties
$color = Color::fromString('#ff6600');
$color->red;         // 255
$color->hue;         // 24.0
$color->lightness;   // 50.0

// Modify colors (returns new instance)
$darkRed = $red->withLightness(30);
$transparent = $red->withAlpha(50, percent: true);

// Color operations
$mixed = $red->mix($blue, 50);
$complement = $red->complement();

// Accessibility
$textColor = $color->bestTextColor();       // Color for black or white
$ratio = $red->contrastRatio($blue);

// Output formats
echo $color->toHex();        // '#ff6600ff'
echo $color->toRgbString();  // 'rgb(255 102 0 / 100%)'
echo $color->toHslString();  // 'hsl(24deg 100% 50% / 100%)'
```

---

## Classes

### [Color](docs/Color.md)

Immutable class for color manipulation with support for:
- RGB and HSL color spaces with automatic conversion
- CSS color names (148 standard names plus 'transparent'), and around 300 extra color names
- Hex string parsing (3, 4, 6, or 8 digits)
- Immutable modification methods (withRed, withHue, etc.)
- Color mixing and complementary colors
- WCAG accessibility calculations (contrast ratio, text color selection)
- Multiple output formats (hex, rgb(), hsl())

### [ColorNames](docs/ColorNames.md)

The CSS named colors and around 300 extra color names, mapped to hex values, with the case-insensitive lookup `Color`
uses.

---

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run with coverage (generates HTML report and clover.xml)
composer test
```

---

## License

MIT License - see [LICENSE](LICENSE) for details

---

## Support

- **Issues**: https://github.com/mossy2100/PHP-Color/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See test files for comprehensive usage examples

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Color/issues).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
