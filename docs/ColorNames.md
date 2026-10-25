# ColorNames

The color names that [`Color`](Color.md) recognises, and the case-insensitive lookup it uses.

---

## Overview

`ColorNames` holds two tables of color names mapped to 6-digit RGB hex values:

- `CSS` - the 148 CSS named colors.
- `EXTRA` - around 300 color names in common English use that aren't CSS named colors.

Every `Color` method that takes a color name accepts names from both tables, plus `'transparent'`. Names are matched
leniently:

- Case is ignored.
- Whitespace, hyphens and underscores are ignored, so `'air force blue'` and `'air-force-blue'` both match
  `'AirForceBlue'`.
- If a name containing `'grey'` isn't found, it's looked up again with `'gray'`, so `'DoveGrey'` matches `'DoveGray'`.
  The CSS `Grey` names match directly.

`ColorNames` is a static class, and can't be instantiated.

---

## Constants

### CSS

```php
public const array CSS
```

All 148 [CSS named colors](https://www.w3.org/TR/css-color-4/#named-colors), mapped to 6-digit RGB hex values. The names
are in UpperCamelCase, e.g. `'AliceBlue'`.

`'transparent'` isn't in the table, since CSS defines it separately from the named colors, but every method that takes
a color name accepts it.

**Example:**

```php
ColorNames::CSS['AliceBlue'];  // 'f0f8ff'

Color::fromString('coral');
Color::fromString('PapayaWhip');
Color::fromString('transparent');  // RGBA: 0, 0, 0, 0
```

### EXTRA

```php
public const array EXTRA
```

Around 300 color names in common English use that aren't CSS named colors, e.g. `'Amethyst'`, `'Aubergine'` and
`'AirForceBlue'`, mapped to 6-digit RGB hex values. Each value is a consensus of several sources, including the xkcd
color survey, Wikipedia, and paint and color references. The names are in UpperCamelCase.

No name is in both tables.

**Example:**

```php
ColorNames::EXTRA['Amethyst'];      // '9a63c6'

Color::fromString('airforceblue');  // #5d8aa8ff
```

---

## Inspection Methods

### lookupHex()

```php
public static function lookupHex(string $name): ?string
```

Look up a color name's hex value, matching names as described in the [Overview](#overview). CSS names take precedence
if a name is ever in both tables.

**Returns:** `?string` - The 8-digit RGBA hex value, with no leading '#', or `null` if the name isn't known.

**Examples:**

```php
ColorNames::lookupHex('AliceBlue');       // 'f0f8ffff'
ColorNames::lookupHex(' amethyst ');      // '9a63c6ff'
ColorNames::lookupHex('air force blue');  // '5d8aa8ff'
ColorNames::lookupHex('DoveGrey');        // '6d6c6cff'
ColorNames::lookupHex('transparent');     // '00000000'
ColorNames::lookupHex('notacolor');       // null
```

---

## Validation Methods

### validName()

```php
public static function validName(string $name): bool
```

Check if a string is a known color name: a CSS named color, an extra color name, or `'transparent'`. Names are
matched as described in the [Overview](#overview).

**Returns:** `bool` - `true` if the name is a known color name, `false` otherwise.

**Examples:**

```php
ColorNames::validName('red');         // true
ColorNames::validName(' Amethyst ');  // true
ColorNames::validName('Dove Grey');   // true
ColorNames::validName('notacolor');   // false
```

---

## See Also

- **[Color](Color.md)** - `fromString()` uses this lookup
