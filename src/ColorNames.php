<?php

declare(strict_types=1);

namespace Mossy2100\Color;

/**
 * Color names, and the case-insensitive lookup used by Color.
 *
 * Holds the CSS named colors and the extra color names, each mapped to 6-digit RGB hex values.
 */
final class ColorNames
{
    #region Constants

    /**
     * The CSS named colors mapped to 6-digit RGB hex values.
     *
     * The names are in UpperCamelCase for readability. Lookups by name are case-insensitive.
     *
     * @see https://www.w3.org/TR/css-color-4/#named-colors
     * @var array<string,string>
     */
    public const array CSS = [
        'AliceBlue' => 'f0f8ff',
        'AntiqueWhite' => 'faebd7',
        'Aqua' => '00ffff',
        'Aquamarine' => '7fffd4',
        'Azure' => 'f0ffff',
        'Beige' => 'f5f5dc',
        'Bisque' => 'ffe4c4',
        'Black' => '000000',
        'BlanchedAlmond' => 'ffebcd',
        'Blue' => '0000ff',
        'BlueViolet' => '8a2be2',
        'Brown' => 'a52a2a',
        'BurlyWood' => 'deb887',
        'CadetBlue' => '5f9ea0',
        'Chartreuse' => '7fff00',
        'Chocolate' => 'd2691e',
        'Coral' => 'ff7f50',
        'CornflowerBlue' => '6495ed',
        'Cornsilk' => 'fff8dc',
        'Crimson' => 'dc143c',
        'Cyan' => '00ffff',
        'DarkBlue' => '00008b',
        'DarkCyan' => '008b8b',
        'DarkGoldenrod' => 'b8860b',
        'DarkGray' => 'a9a9a9',
        'DarkGreen' => '006400',
        'DarkGrey' => 'a9a9a9',
        'DarkKhaki' => 'bdb76b',
        'DarkMagenta' => '8b008b',
        'DarkOliveGreen' => '556b2f',
        'DarkOrange' => 'ff8c00',
        'DarkOrchid' => '9932cc',
        'DarkRed' => '8b0000',
        'DarkSalmon' => 'e9967a',
        'DarkSeaGreen' => '8fbc8f',
        'DarkSlateBlue' => '483d8b',
        'DarkSlateGray' => '2f4f4f',
        'DarkSlateGrey' => '2f4f4f',
        'DarkTurquoise' => '00ced1',
        'DarkViolet' => '9400d3',
        'DeepPink' => 'ff1493',
        'DeepSkyBlue' => '00bfff',
        'DimGray' => '696969',
        'DimGrey' => '696969',
        'DodgerBlue' => '1e90ff',
        'Firebrick' => 'b22222',
        'FloralWhite' => 'fffaf0',
        'ForestGreen' => '228b22',
        'Fuchsia' => 'ff00ff',
        'Gainsboro' => 'dcdcdc',
        'GhostWhite' => 'f8f8ff',
        'Gold' => 'ffd700',
        'Goldenrod' => 'daa520',
        'Gray' => '808080',
        'Green' => '008000',
        'GreenYellow' => 'adff2f',
        'Grey' => '808080',
        'Honeydew' => 'f0fff0',
        'HotPink' => 'ff69b4',
        'IndianRed' => 'cd5c5c',
        'Indigo' => '4b0082',
        'Ivory' => 'fffff0',
        'Khaki' => 'f0e68c',
        'Lavender' => 'e6e6fa',
        'LavenderBlush' => 'fff0f5',
        'LawnGreen' => '7cfc00',
        'LemonChiffon' => 'fffacd',
        'LightBlue' => 'add8e6',
        'LightCoral' => 'f08080',
        'LightCyan' => 'e0ffff',
        'LightGoldenrodYellow' => 'fafad2',
        'LightGray' => 'd3d3d3',
        'LightGreen' => '90ee90',
        'LightGrey' => 'd3d3d3',
        'LightPink' => 'ffb6c1',
        'LightSalmon' => 'ffa07a',
        'LightSeaGreen' => '20b2aa',
        'LightSkyBlue' => '87cefa',
        'LightSlateGray' => '778899',
        'LightSlateGrey' => '778899',
        'LightSteelBlue' => 'b0c4de',
        'LightYellow' => 'ffffe0',
        'Lime' => '00ff00',
        'LimeGreen' => '32cd32',
        'Linen' => 'faf0e6',
        'Magenta' => 'ff00ff',
        'Maroon' => '800000',
        'MediumAquamarine' => '66cdaa',
        'MediumBlue' => '0000cd',
        'MediumOrchid' => 'ba55d3',
        'MediumPurple' => '9370db',
        'MediumSeaGreen' => '3cb371',
        'MediumSlateBlue' => '7b68ee',
        'MediumSpringGreen' => '00fa9a',
        'MediumTurquoise' => '48d1cc',
        'MediumVioletRed' => 'c71585',
        'MidnightBlue' => '191970',
        'MintCream' => 'f5fffa',
        'MistyRose' => 'ffe4e1',
        'Moccasin' => 'ffe4b5',
        'NavajoWhite' => 'ffdead',
        'Navy' => '000080',
        'OldLace' => 'fdf5e6',
        'Olive' => '808000',
        'OliveDrab' => '6b8e23',
        'Orange' => 'ffa500',
        'OrangeRed' => 'ff4500',
        'Orchid' => 'da70d6',
        'PaleGoldenrod' => 'eee8aa',
        'PaleGreen' => '98fb98',
        'PaleTurquoise' => 'afeeee',
        'PaleVioletRed' => 'db7093',
        'PapayaWhip' => 'ffefd5',
        'PeachPuff' => 'ffdab9',
        'Peru' => 'cd853f',
        'Pink' => 'ffc0cb',
        'Plum' => 'dda0dd',
        'PowderBlue' => 'b0e0e6',
        'Purple' => '800080',
        'RebeccaPurple' => '663399',
        'Red' => 'ff0000',
        'RosyBrown' => 'bc8f8f',
        'RoyalBlue' => '4169e1',
        'SaddleBrown' => '8b4513',
        'Salmon' => 'fa8072',
        'SandyBrown' => 'f4a460',
        'SeaGreen' => '2e8b57',
        'SeaShell' => 'fff5ee',
        'Sienna' => 'a0522d',
        'Silver' => 'c0c0c0',
        'SkyBlue' => '87ceeb',
        'SlateBlue' => '6a5acd',
        'SlateGray' => '708090',
        'SlateGrey' => '708090',
        'Snow' => 'fffafa',
        'SpringGreen' => '00ff7f',
        'SteelBlue' => '4682b4',
        'Tan' => 'd2b48c',
        'Teal' => '008080',
        'Thistle' => 'd8bfd8',
        'Tomato' => 'ff6347',
        'Turquoise' => '40e0d0',
        'Violet' => 'ee82ee',
        'Wheat' => 'f5deb3',
        'White' => 'ffffff',
        'WhiteSmoke' => 'f5f5f5',
        'Yellow' => 'ffff00',
        'YellowGreen' => '9acd32',
    ];

    /**
     * Color names in common English use that are not CSS named colors, mapped to 6-digit RGB hex values.
     *
     * Each value is a consensus of several sources (the xkcd color survey, Wikipedia, paint and color references),
     * averaged in CIELAB. The names are in UpperCamelCase for readability.
     *
     * @var array<string,string>
     */
    public const array EXTRA = [
        'AcidGreen' => '8ffe09',
        'AirForceBlue' => '5d8aa8',
        'Alabaster' => 'f3efe9',
        'Almond' => 'eedcc9',
        'Amber' => 'ffb904',
        'Amethyst' => '9a63c6',
        'AppleGreen' => '82c319',
        'Apricot' => 'fbb88a',
        'ArmyGreen' => '4a561b',
        'AshGray' => 'bdbfb2',
        'Asparagus' => '7da85e',
        'Aubergine' => '3d0d28',
        'Auburn' => '9f2e15',
        'Avocado' => '769c21',
        'BabyBlue' => 'a6d8f9',
        'BabyPink' => 'f7bac5',
        'Banana' => 'fdf141',
        'BarbiePink' => 'eb3093',
        'BattleshipGray' => '78817f',
        'Berry' => '990f4b',
        'Biscuit' => 'deccaf',
        'Blond' => 'faf0be',
        'BloodOrange' => 'f33f0d',
        'BloodRed' => '8e0002',
        'BlueGreen' => '138f99',
        'BluePurple' => '591edd',
        'Bluebell' => '9e9ece',
        'Blueberry' => '464196',
        'Blush' => 'd05479',
        'BondiBlue' => '0095b6',
        'Bone' => 'e3d7c6',
        'Bordeaux' => '710028',
        'BottleGreen' => '0b5029',
        'Brass' => 'c2ae4f',
        'BrickRed' => '993320',
        'BrightBlue' => '0165fc',
        'BrightGreen' => '3aff0b',
        'BrightPurple' => 'ba10fe',
        'BrightTeal' => '01f9c6',
        'BritishRacingGreen' => '044519',
        'Bronze' => 'ba7c1e',
        'BubblegumPink' => 'fd78b5',
        'Buff' => 'fae08c',
        'Burgundy' => '770021',
        'BurntOrange' => 'c95709',
        'BurntRed' => '9f2305',
        'BurntSienna' => 'cc5f32',
        'BurntUmber' => '8e3c1b',
        'ButterYellow' => 'fffd74',
        'Buttercup' => 'eba51e',
        'Butterscotch' => 'f7aa45',
        'CambridgeBlue' => '96baa5',
        'Camel' => 'c49d62',
        'CamoGreen' => '7f8f4e',
        'CanaryYellow' => 'fff72b',
        'CandyAppleRed' => 'fd1703',
        'CandyPink' => 'ff63e9',
        'Cappuccino' => '704a3a',
        'Caramel' => 'af6f09',
        'Cardinal' => 'c41e3a',
        'Carmine' => 'a6011e',
        'Carnation' => 'fd778a',
        'CarolinaBlue' => '76ade3',
        'Celadon' => 'b5efb3',
        'Celery' => 'bee37a',
        'Cerise' => 'd92468',
        'Cerulean' => '168ec7',
        'Chalk' => 'ebeae8',
        'Champagne' => 'f8dfba',
        'Charcoal' => '353e42',
        'CherryRed' => 'd61d47',
        'Chestnut' => '9d4231',
        'Cinnamon' => 'b1560d',
        'Claret' => '700821',
        'Clay' => 'b66a50',
        'Cobalt' => '16489c',
        'Cocoa' => '875f42',
        'Coffee' => '856746',
        'Cognac' => '9f381d',
        'CoolGray' => '9199ad',
        'Copper' => 'bb6e33',
        'CottonCandy' => 'ffbad7',
        'Cranberry' => 'b2234d',
        'Cream' => 'fffec9',
        'Custard' => 'fffd78',
        'Daffodil' => 'ffff31',
        'Damson' => '854c65',
        'Dandelion' => 'f4e434',
        'DarkBeige' => 'ac9362',
        'DarkBrown' => '482c12',
        'DarkChocolate' => '3f170a',
        'DarkLavender' => '7e5d97',
        'DarkLilac' => '9c6da5',
        'DarkMaroon' => '3c0008',
        'DarkNavy' => '000435',
        'DarkPeach' => 'de7e5d',
        'DarkPurple' => '36033f',
        'DarkRose' => 'b5485d',
        'DarkTan' => 'a2854d',
        'DarkTeal' => '014d4e',
        'DarkYellow' => 'bca20b',
        'DeepBlue' => '110475',
        'DeepGreen' => '02590f',
        'Denim' => '315ea5',
        'Desert' => 'c7a465',
        'DoveGray' => '6d6c6c',
        'DuckEggBlue' => 'c6f4f1',
        'DustyPink' => 'd28b97',
        'DustyRose' => 'c37c7e',
        'Ebony' => '4e544b',
        'Ecru' => 'e1daa6',
        'Eggplant' => '4b2544',
        'Eggshell' => 'f8f4d1',
        'ElectricBlue' => '0652ff',
        'Emerald' => '30b059',
        'Espresso' => '5c2a1e',
        'Evergreen' => '084b30',
        'Fawn' => 'daad76',
        'FernGreen' => '4e8645',
        'FireEngineRed' => 'fe0002',
        'Flamingo' => 'ed5a37',
        'FrenchBlue' => '2f6fb4',
        'Garnet' => '873130',
        'Ginger' => 'b06500',
        'GoldenBrown' => 'a6700d',
        'GoldenYellow' => 'ffd30d',
        'Grape' => '5c2b5c',
        'Graphite' => '2b2014',
        'GrassGreen' => '3f9b0b',
        'GrayBlue' => '6d8fa3',
        'GrayBrown' => '7f7053',
        'GrayGreen' => '7f9e78',
        'Greige' => 'b4b0a4',
        'Gunmetal' => '4b565b',
        'Hazel' => '8e7618',
        'Heather' => 'a484ac',
        'Honey' => 'dfb265',
        'HunterGreen' => '224e22',
        'IceBlue' => 'd7fffd',
        'Iris' => '5f51cb',
        'IrishGreen' => '069738',
        'Jade' => '1ca671',
        'JetBlack' => '222324',
        'JungleGreen' => '1b9664',
        'KellyGreen' => '33b325',
        'KeyLime' => 'aeff6e',
        'KhakiGreen' => '778640',
        'Kiwi' => '9cef43',
        'KleinBlue' => '0c219d',
        'Latte' => 'aa8767',
        'LavenderBlue' => 'ada9fc',
        'LeafGreen' => '5ca904',
        'Leather' => 'a37243',
        'Lemon' => 'fef832',
        'LightLavender' => 'e2c4fe',
        'LightLilac' => 'eac8f6',
        'LightMaroon' => 'a24857',
        'LightMauve' => 'c292a1',
        'LightNavy' => '155084',
        'LightOlive' => 'acbf69',
        'LightPeach' => 'ffd8b1',
        'LightPurple' => 'bf77f6',
        'LightRed' => 'ff5456',
        'LightTan' => 'fbeeac',
        'LightTeal' => '98dfc2',
        'Lilac' => 'c7a1da',
        'LipstickRed' => 'c0022f',
        'Magnolia' => 'f7f0ec',
        'Mahogany' => '7f1f0f',
        'Maize' => 'f9d749',
        'Malachite' => '0bda51',
        'Mango' => 'fdb228',
        'Marigold' => 'e7ab1c',
        'MarineBlue' => '01386a',
        'Mauve' => 'c890be',
        'MediumBrown' => '7f5112',
        'Melon' => 'fcbbb0',
        'Merlot' => '791433',
        'MidGray' => '828287',
        'MidGreen' => '50a747',
        'MilitaryGreen' => '667c3e',
        'MilkChocolate' => '804c20',
        'MillennialPink' => 'f1c9c4',
        'Mink' => '87796e',
        'MintGreen' => '94fe9b',
        'Mocha' => '9b7757',
        'MossGreen' => '759958',
        'Mud' => '735a1d',
        'Mulberry' => 'a82a67',
        'Mushroom' => 'bba18d',
        'Mustard' => 'e5c534',
        'NeonBlue' => '04d9ff',
        'NeonPink' => 'fe208e',
        'NeonYellow' => 'cfff04',
        'Oatmeal' => 'd4cbb3',
        'OceanBlue' => '057baf',
        'Ochre' => 'c78818',
        'OffBlack' => '3c3e3f',
        'OffWhite' => 'fefde9',
        'OldGold' => 'cfb53b',
        'OldRose' => 'c48085',
        'Onyx' => '2b2c2c',
        'OrangeBrown' => 'ad5a00',
        'Oxblood' => '640314',
        'OxfordBlue' => '192c4c',
        'Oyster' => 'd5cbbe',
        'PaleLavender' => 'e6cffe',
        'PaleOrange' => 'ffa551',
        'PalePink' => 'fbd3dc',
        'Parchment' => 'f9f4be',
        'PastelBlue' => 'a2bffe',
        'PastelRed' => 'ec605b',
        'PeaGreen' => '8eab12',
        'Peach' => 'ffcb9e',
        'PeacockBlue' => '016795',
        'Pear' => 'cee848',
        'Pearl' => 'f0e4d6',
        'Periwinkle' => 'b1aafc',
        'Persimmon' => 'f35e23',
        'Petrol' => '005f6a',
        'Pewter' => '91a09b',
        'PillarBoxRed' => 'cb1d27',
        'PineGreen' => '296f52',
        'PinkPurple' => 'e43be0',
        'Pistachio' => 'a9df7e',
        'Platinum' => 'e5e4e2',
        'Poppy' => 'd74b3c',
        'PowderPink' => 'ffb9d4',
        'Primrose' => 'edea99',
        'PrussianBlue' => '003a65',
        'Puce' => 'b77f73',
        'Pumpkin' => 'f0760e',
        'Putty' => 'ccb787',
        'Raspberry' => 'c80950',
        'RawSienna' => 'bb7533',
        'RawUmber' => '8b5c26',
        'RedBrown' => '992c22',
        'RedPink' => 'fa2a55',
        'RobinEggBlue' => '5ddfe6',
        'Rose' => 'e84678',
        'RoseGold' => 'ebc8be',
        'RoseQuartz' => 'f7cac9',
        'Rosewood' => '65000b',
        'Rouge' => 'ab1f41',
        'RoyalPurple' => '643290',
        'Ruby' => 'd40852',
        'Russet' => '914111',
        'Rust' => 'af3e0b',
        'SafetyOrange' => 'f57500',
        'Saffron' => 'fab625',
        'Sage' => '9eaf7f',
        'Sand' => 'd3be7b',
        'Sandstone' => 'c9ae74',
        'Sangria' => '92000a',
        'SapGreen' => '578520',
        'Sapphire' => '1d419e',
        'Scarlet' => 'e11718',
        'SchoolBusYellow' => 'fbc100',
        'SeaBlue' => '036f95',
        'SeafoamGreen' => '8af3b2',
        'Sepia' => '875325',
        'ShamrockGreen' => '1ea066',
        'ShockingPink' => 'fe21b9',
        'SpaceGray' => '3b3c3d',
        'Spruce' => '0a5f38',
        'SteelGray' => '69767d',
        'Stone' => 'ada587',
        'StoneGray' => '958f85',
        'Straw' => 'f0e774',
        'Strawberry' => 'fb2943',
        'Sunflower' => 'fbdb0f',
        'Tangerine' => 'f98d04',
        'Taupe' => 'b9a281',
        'Tawny' => 'cd691f',
        'TeaGreen' => 'c7f4b2',
        'Terracotta' => 'd66c4d',
        'TiffanyBlue' => '56d4c8',
        'Toffee' => '76553d',
        'TyrianPurple' => '650239',
        'Ultramarine' => '1906a6',
        'Umber' => '8f5b2c',
        'Vanilla' => 'e6d6aa',
        'Verdigris' => '42b4ae',
        'Vermilion' => 'f73e0f',
        'Viridian' => '328a6a',
        'Walnut' => '773f1a',
        'WarmGray' => '978a84',
        'Watermelon' => 'fd4e61',
        'Wine' => '7a1e3b',
        'Wisteria' => 'b188ca',
        'YellowBrown' => 'b59203',
        'YellowOchre' => 'ca9b1a',
        'YellowOrange' => 'feaf2c',
    ];

    /**
     * The 'transparent' keyword, in the lookup format used by lookupHex(): a lower-case name mapped to 8-digit hex.
     *
     * CSS defines 'transparent' separately from the named colors, so it isn't in CSS.
     */
    private const array TRANSPARENT = ['transparent' => '00000000'];

    #endregion

    #region Constructor

    /**
     * Private constructor to prevent instantiation.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    #endregion

    #region Inspection methods

    /**
     * Convert a CSS or extra color name to an 8-digit hex value.
     *
     * Case, whitespace, hyphens and underscores are ignored, so 'air force blue' and 'air-force-blue' both match
     * 'AirForceBlue'. If a name containing 'grey' isn't found, it's looked up again with 'gray', so 'DoveGrey' matches
     * 'DoveGray'.
     *
     * The map is built once. Since `+` keeps the left operand's entry for a duplicate key, CSS names take precedence if
     * a name is ever in both tables.
     *
     * @param string $name The color name.
     * @return ?string The 8-digit hex value, or null if the name isn't a known color name.
     */
    public static function lookupHex(string $name): ?string
    {
        static $hexByLowerName = self::TRANSPARENT
            + self::buildNameMap(self::CSS)
            + self::buildNameMap(self::EXTRA);

        // Normalize the name to match the map's keys.
        $key = preg_replace('/[\s_-]+/', '', strtolower($name)) ?? '';

        return $hexByLowerName[$key] ?? $hexByLowerName[str_replace('grey', 'gray', $key)] ?? null;
    }

    #endregion

    #region Validation methods

    /**
     * Check if a given string is a known color name: a CSS named color, an extra color name, or 'transparent'.
     *
     * Names are matched as in lookupHex(), ignoring case, whitespace, hyphens and underscores, and accepting 'grey' for
     * 'gray'.
     *
     * @param string $name A color name.
     * @return bool If the name is a known color name.
     */
    public static function validName(string $name): bool
    {
        return self::lookupHex($name) !== null;
    }

    #endregion

    #region Helper methods

    /**
     * Convert a color name table to a map from lower-case names to 8-digit hex values, appending an opaque alpha.
     *
     * @param array<string, string> $colors A color name table, mapping names to 6-digit hex values.
     * @return array<string, string> The color name map.
     */
    private static function buildNameMap(array $colors): array
    {
        $map = [];
        foreach ($colors as $name => $hex) {
            $map[strtolower($name)] = $hex . 'ff';
        }
        return $map;
    }

    #endregion
}
