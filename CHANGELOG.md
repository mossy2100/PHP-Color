# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

- **`Color::fromString()`** creates a color from a color name or hex string, and is the inverse of `__toString()`.
- **`ColorNames`,** holding the color name tables and the lookup `Color` uses. `ColorNames::EXTRA` adds 296 color
  names in common English use that aren't CSS colors, e.g. `'Amethyst'`. Every method that takes a color name accepts
  them.
- **`rebeccapurple`** (`#663399`), the CSS named color that was missing.

### Changed

- **Renamed the package from `oceanmoon/color` to `mossy2100/color`, and the namespace from `OceanMoon\Color` to
  `Mossy2100\Color` (breaking),** matching the author's GitHub and Packagist name. Update `composer.json` and any
  `use` statements. It depends on `mossy2100/core`.
- **Requires `mossy2100/core` `^4.0`.**
- **The constructor takes RGBA bytes (breaking):** `new Color(int $red = 0, int $green = 0, int $blue = 0, int $alpha =
  255)`. Replace `new Color('red')` with `Color::fromString('red')`.
- **Percentages replace fractions throughout (breaking),** as in CSS:
  - `fromRgba()` takes red, green, blue and alpha as percentages in [0, 100]. Use the constructor for bytes.
  - `fromHsla()` takes the saturation, lightness and alpha as percentages.
  - `saturation`, `lightness`, `perceivedLightness`, `toHslArray()`, `rgbToHsl()`, `hslToRgb()`, `withSaturation()`
    and `withLightness()` use percentages in [0, 100].
  - `mix()` takes the percentage of the other color, defaulting to 50. Its parameter is renamed from `$frac` to
    `$percent`.
  - `toRgbString()` and `toHslString()` give the alpha as a percentage, e.g. `rgb(255 0 0 / 50.196078%)`.
- **`withRed()`, `withGreen()`, `withBlue()` and `withAlpha()` take a byte, or a percentage with `$percent = true`
  (breaking).** A float is no longer read as a fraction, so `withRed(1.0)` is byte 1, not 255. A non-integer byte is
  rounded.
- **Removed `parseToBytes()` (breaking).** Use `fromString()`, or `hexToBytes()`.
- **Removed `nameToBytes()` and `nameToHex()` (breaking).** Use `fromString($name)` for a `Color`, or
  `ColorNames::lookupHex($name)` for the 8-digit hex value, which returns `null` for an unknown name instead of
  throwing.
- **`Color::CSS_COLOR_NAMES` moved to `ColorNames::CSS` (breaking for code that reads the constant directly),** with
  UpperCamelCase keys and 6-digit RGB values, e.g. `'AliceBlue' => 'f0f8ff'`, and without `transparent`. Name lookups
  are unchanged: they're case-insensitive, and accept `transparent`.
- **`Color::validName()` moved to `ColorNames::validName()` (breaking).**
- **`amethyst` moved from the CSS names to `ColorNames::EXTRA` (breaking),** since it isn't a CSS color. Its
  value changed from `#9966cc` to `#9a63c6`.
- **`Color` is now `final` (breaking).**
- **Renamed the `validHex()` parameter from `$str` to `$hex` (breaking for named arguments).**
- **`average()` throws `LengthException` instead of `ArgumentCountError` when given no colors (breaking).**
- **`mix()` and `average()` premultiply by alpha, as CSS `color-mix()` does (breaking).** Mixing with a transparent
  color now fades a color without darkening it: red mixed 50% with `transparent` gives `#ff000080`, not `#80000080`.
  Results only change when the colors' alphas differ.

### Fixed

- **A color made by `fromHsla()` reported the given HSL values,** not those of its RGB bytes, so equal colors could
  disagree. `Color::fromHsla(120, 100, 0)->hue` is now `0.0`, the same as black's.
- **`withHue()`, `withLightness()` and `complement()` threw `DomainException` for some colors,** e.g. `#7878ff` and
  `#000001`, whose computed saturation was just over the maximum due to rounding.
- **Name validation ignores surrounding whitespace,** as `fromString()` already did.
- **`gamma()` throws `DomainException` for a value outside [0, 255],** instead of returning a meaningless result.
- **`NAN` and `INF` are rejected** by the factories, `with*()` methods and `mix()`, instead of giving black, white or a
  warning.

---

## [2.0.0] - 2026-06-18

### Changed

- **Renamed package** from `galaxon/color` to `oceanmoon/color`. Update your `composer.json` require accordingly.
- **Renamed PHP namespaces** from `Galaxon\Color\*` to `OceanMoon\Color\*` throughout all source and test files.
- Updated runtime dependency `galaxon/core` → `oceanmoon/core: ^2.0`.
- Updated dev dependency `galaxon/coding-standard` → `oceanmoon/coding-standard: ^2.0`.
- `composer.json`: updated author email, homepage, and support URLs to Ocean Moon Software.

---

## [1.0.2] - 2026-04-09

### Changed

- **`Color::formatFloat()`**: now uses `Floats::format()` from Core for explicit precision/trailing-zero handling.
  Behavior is unchanged for normal values; very small values that would previously have leaked scientific notation (e.g.
  `1.0E-7`) into CSS output now render cleanly as `0` after rounding.
- **`composer.json`**: bumped `galaxon/core` constraint from `^1.0` to `^1.6`. This is a hard requirement: Core's traits
  were reorganised in v1.6.0 into `Traits/Asserts/` and `Traits/Comparison/` subnamespaces, and `Color` already imports
  `Galaxon\Core\Traits\Comparison\Equatable` (the new path). Older Core versions would fail to autoload. The bump also
  makes `Floats::format()` available.
- **Documentation**: updated `Color.md` "See Also" link for `Equatable` to point at the new
  `Traits/Comparison/Equatable.md` path.

---

## [1.0.1] - 2026-04-02

### Changed

- **Exception types**: `normalizeHex()` and `hexToBytes()` now throw `FormatException` (was `DomainException`) for
  invalid hex strings, since these are string format validation errors.
- **Exception messages**: `validateByte()` and `validateFraction()` now include the offending value and constraint in
  the message.

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
- **`withRed()`, `withGreen()`, `withBlue()`, `withAlpha()`** now accept `int|float` (float values in range [0.0, 1.0]
  are converted to bytes)
- **`bestTextColor()`** now accepts optional custom light/dark text colors (`string|Color`) and returns a `Color` object
  instead of a string

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
