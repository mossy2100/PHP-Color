# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-11-22

### Added

- Initial release of the Color class
- **Immutable design** - Color values cannot be modified after creation
- **Memory-efficient storage** - RGBA stored as 4-byte binary string
- **RGB color model support**
  - Constructor accepts CSS hex strings (3, 4, 6, or 8 digits) and color names
  - `fromRgb()` factory method for creating colors from byte values
  - Properties: `red`, `green`, `blue`, `alpha` (read-only)
  - Immutable setters: `withRed()`, `withGreen()`, `withBlue()`, `withAlpha()`
- **HSL color model support**
  - `fromHsl()` factory method for creating colors from HSL values
  - Properties: `hue`, `saturation`, `lightness` (read-only, lazily computed and cached)
  - Immutable setters: `withHue()`, `withSaturation()`, `withLightness()`
  - Static conversion methods: `RGBToHSL()`, `HSLToRGB()`
- **CSS output formats** (using modern CSS color syntax)
  - `toHex()` - Configurable hex output (alpha, hash prefix, case)
  - `toRgbString()` - Modern CSS `rgb(r g b / a)` format
  - `toHslString()` - Modern CSS `hsl(Xdeg Y% Z% / a)` format
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
