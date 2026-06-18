# OceanMoon PHP Color

An immutable, memory-efficient Color class for PHP with support for RGB and HSL color spaces, CSS color names, and WCAG accessibility features.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)**

![PHP 8.4](docs/logo_php8_4.png)

---

## Description

This package provides a comprehensive Color class for working with colors in PHP. The class is designed to be:

- **Immutable** - Color values cannot be changed after creation, ensuring predictability and thread safety
- **Memory-efficient** - Colors are stored internally as a 4-byte binary string
- **Feature-rich** - Supports RGB, HSL, hex strings, CSS color names, and accessibility calculations
- **CSS-compatible** - Outputs to modern CSS color formats (hex, rgb, hsl)

---

## Development and Quality Assurance

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach has produced a well-designed, production-ready package with thorough test coverage and documentation.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

---

## Requirements

- PHP ^8.4
- ext-ctype
- oceanmoon/core

---

## Installation

```bash
composer require oceanmoon/color
```

---

## Quick Start

```php
use OceanMoon\Color\Color;

// Create colors from various formats
$red = new Color('red');
$orange = new Color('#ff8000');
$purple = Color::fromRgba(128, 0, 255);
$green = Color::fromHsla(120, 1.0, 0.5);

// Access color properties
$color = new Color('#ff8040');
$color->red;         // 255
$color->hue;         // 20.0
$color->lightness;   // 0.625

// Modify colors (returns new instance)
$darkRed = $red->withLightness(0.3);
$transparent = $red->withAlpha(128);

// Color operations
$mixed = $red->mix($blue, 0.5);
$complement = $red->complement();

// Accessibility
$textColor = $background->bestTextColor();  // 'black' or 'white'
$ratio = $color1->contrastRatio($color2);

// Output formats
echo $color->toHex();        // '#ff8040ff'
echo $color->toRgbString();  // 'rgb(255 128 64 / 1)'
echo $color->toHslString();  // 'hsl(20deg 100% 62.5% / 1)'
```

---

## Classes

### [Color](docs/Color.md)

Immutable class for color manipulation with support for:
- RGB and HSL color spaces with automatic conversion
- CSS color names (147 standard names plus 'transparent')
- Hex string parsing (3, 4, 6, or 8 digits)
- Immutable modification methods (withRed, withHue, etc.)
- Color mixing and complementary colors
- WCAG accessibility calculations (contrast ratio, text color selection)
- Multiple output formats (hex, rgb(), hsl())

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

- **Issues**: https://github.com/mossy2100/OceanMoon-PHP-Color/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See test files for comprehensive usage examples

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Color/issues).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
