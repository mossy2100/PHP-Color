# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2026-06-18

### Changed

- **Renamed package** from `galaxon/color` to `oceanmoon/color` — update your `composer.json` require accordingly.
- **Renamed PHP namespaces** from `Galaxon\Color\*` to `OceanMoon\Color\*` throughout all source and test files.
- Updated runtime dependency `galaxon/core` → `oceanmoon/core: ^2.0`.
- Updated dev dependency `galaxon/coding-standard` → `oceanmoon/coding-standard: ^2.0`.
- `composer.json`: updated author email, homepage, and support URLs to Ocean Moon Software.

---

## [1.0.2] - 2026-04-09

### Changed

- **`Color::formatFloat()`** — Now uses `Floats::format()` from Core for explicit precision/trailing-zero handling. Behavior is unchanged for normal values; very small values that would previously have leaked scientific notation (e.g. `1.0E-7`) into CSS output now render cleanly as `0` after rounding.
- **`composer.json`** — Bumped `galaxon/core` constraint from `^1.0` to `^1.6`. This is a hard requirement: Core's traits were reorganised in v1.6.0 into `Traits/Asserts/` and `Traits/Comparison/` subnamespaces, and `Color` already imports `Galaxon\Core\Traits\Comparison\Equatable` (the new path). Older Core versions would fail to autoload. The bump also makes `Floats::format()` available.
- **Documentation** — Updated `Color.md` "See Also" link for `Equatable` to point at the new `Traits/Comparison/Equatable.md` path.

---

## [1.0.1] - 2026-04-02

### Changed

- **Exception types** — `normalizeHex()` and `hexToBytes()` now throw `FormatException` (was `DomainException`) for invalid hex strings, since these are string format validation errors.
- **Exception messages** — `validateByte()` and `validateFraction()` now include the offending value and constraint in the message.

### Documentation

- Fixed stale exception types in Color.md (`ValueError`/`RangeException` → `FormatException`/`DomainException`).
- Added missing `**Throws:**` for `withAlpha()`.
- Added `---` before H2 headings in CHANGELOG.md.
- Removed Contributing section from README (link moved to Support).

---

## [1.0.0] - 2026-01-05

### First Stable Release

This is the first stable release of Galaxon Color, ready for publication on Packagist.

### Breaking Changes

- **Exception types standardized** - All domain validation errors now throw `DomainException` consistently:
  - `__construct()` - Throws `DomainException` for invalid color strings (was `ValueError`)
  - `fromRgba()` - Throws `DomainException` for invalid values (was `RangeException`)
  - `fromHsla()` - Throws `DomainException` for invalid values (was `RangeException`)
  - `withRed()`, `withGreen()`, `withBlue()`, `withAlpha()` - Throw `DomainException` (was `RangeException`)
  - `withSaturation()`, `withLightness()` - Throw `DomainException` (was `RangeException`)
  - `normalizeHex()`, `hexToBytes()` - Throw `DomainException` for invalid hex (was `ValueError`)
  - `nameToHex()`, `nameToBytes()` - Throw `DomainException` for invalid name (was `ValueError`)

### Changed

- **composer.json** - Updated for Packagist publication:
  - Added keywords for discoverability
  - Added author information
  - Added homepage and support URLs
  - Updated dependencies to use Packagist versions (galaxon/core ^1.0)
  - Improved description

### Fixed

- Fixed GitHub URLs in README.md (`PHP-Color` → `Galaxon-PHP-Color`)

---

## [0.2.0] - 2025-12-10

### Changed

- **Renamed methods** to follow consistent lowerCamelCase naming:
  - `fromRGB()` → `fromRgba()`
  - `fromHSL()` → `fromHsla()`
  - `RGBToHSL()` → `rgbToHsl()`
  - `HSLToRGB()` → `hslToRgb()`
  - `toRGBString()` → `toRgbString()`
  - `toHSLString()` → `toHslString()`
  - `toRGBArray()` → `toRgbaArray()`
  - `isValidHex()` → `validHex()`
  - `isValidName()` → `validName()`
- **`withRed()`, `withGreen()`, `withBlue()`, `withAlpha()`** now accept `int|float` (float values in range [0.0, 1.0] are converted to bytes)
- **`bestTextColor()`** now accepts optional custom light/dark text colors (`string|Color`) and returns a `Color` object instead of a string

### Added

- Reorganised documentation with `docs/Color.md` for detailed class documentation
- New tests for float arguments to `with*()` methods
- New tests for custom text colors in `bestTextColor()`

---

## [0.1.0] - 2025-11-22

### Added

- Initial release of the Color class
- **Immutable design** - Color values cannot be modified after creation
- **Memory-efficient storage** - RGBA stored as 4-byte binary string
- **RGB color model support**
  - Constructor accepts CSS hex strings (3, 4, 6, or 8 digits) and color names
  - `fromRGB()` factory method for creating colors from byte values
  - Properties: `red`, `green`, `blue`, `alpha` (read-only)
  - Immutable setters: `withRed()`, `withGreen()`, `withBlue()`, `withAlpha()`
- **HSL color model support**
  - `fromHSL()` factory method for creating colors from HSL values
  - Properties: `hue`, `saturation`, `lightness` (read-only, lazily computed and cached)
  - Immutable setters: `withHue()`, `withSaturation()`, `withLightness()`
  - Static conversion methods: `RGBToHSL()`, `HSLToRGB()`
- **CSS output formats** (using modern CSS color syntax)
  - `toHex()` - Configurable hex output (alpha, hash prefix, case)
  - `toRGBString()` - Modern CSS `rgb(r g b / a)` format
  - `toHSLString()` - Modern CSS `hsl(Xdeg Y% Z% / a)` format
  - `__toString()` - Returns 8-digit hex with hash prefix
- **Color operations**
  - `mix()` - Blend two colors with configurable ratio
  - `complement()` - Get complementary color (hue + 180 degrees)
  - `average()` - Static method to average multiple colors
  - `equals()` - Compare colors for equality (implements Equatable interface)
- **Accessibility features (WCAG)**
  - `relativeLuminance` property - WCAG relative luminance calculation
  - `perceivedLightness` property - CIE L* perceived lightness
  - `contrastRatio()` - Calculate contrast ratio between two colors
  - `bestTextColor()` - Determine optimal text color (black or white) for a background
- **Validation and parsing**
  - `isValidHex()` - Validate CSS hex color strings
  - `isValidName()` - Validate CSS color names
  - `normalizeHex()` - Normalize hex strings to 8-digit lowercase
  - `parseToBytes()` - Parse any valid color string to RGBA bytes (returns indexed array)
  - `hexToBytes()` - Parse hex strings to RGBA bytes (returns indexed array)
  - `nameToHex()`, `nameToBytes()` - Convert named colors
- **CSS color names** - Support for all 147 standard CSS color names plus 'transparent'
- **Utility methods**
  - `gamma()` - sRGB transfer function for gamma correction
  - `toRGBArray()`, `toHSLArray()`, `toArray()` - Convert to array representations
- Comprehensive test suite with 100% code coverage
