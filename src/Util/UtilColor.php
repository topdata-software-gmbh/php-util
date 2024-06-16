<?php


namespace TopdataSoftwareGmbH\Util;


class UtilColor
{
    public static function lighten($hex, $percent)
    {
        return self::mix($hex, '#fff', $percent);
    }

    public static function darken($hex, $percent)
    {
        return self::mix($hex, '#000', $percent);
    }

    public static function mix($col1, $col2, $mix)
    {
        $rgb1 = self::hex2rgb($col1);
        $rgb2 = self::hex2rgb($col2);
        $ret = '#';
        for ($i = 0; $i < 3; $i++) {
            $ret .= sprintf('%02x', round($rgb1[$i] * (1.0 - $mix) + $rgb2[$i] * $mix));
        }
        return $ret;
    }

    /**
     * @param string $col
     * @return array
     * @throws \Exception
     */
    public static function hex2rgb(string $col)
    {
        if (preg_match('~^#([0-f]{1})([0-f]{1})([0-f]{1})$~i', trim($col), $gr)) {
            return [hexdec($gr[1] . $gr[1]), hexdec($gr[2] . $gr[2]), hexdec($gr[3] . $gr[3])];
        } elseif (preg_match('~^#([0-f]{2})([0-f]{2})([0-f]{2})$~i', trim($col), $gr)) {
            return [hexdec($gr[1]), hexdec($gr[2]), hexdec($gr[3])];
        } else {
            throw new \Exception('invalid color ' . $col);
        }
    }

    /**
     * 07/2017 moved from UtilImage to here
     *
     * @param $strHex
     * @return int
     */
    public static function hex2int(string $strHex)
    {
        $strHex = str_replace('#', '', $strHex);
        [$r, $g, $b] = sscanf($strHex, "%02x%02x%02x");

        return $r * 0x10000 + $g * 0x100 + $b;
    }


    public static function rgb2hex($r, $g, $b)
    {
        return sprintf('#%02x%02x%02x', intval($r), intval($g), intval($b));
    }


    /**
     * from marketer v1
     *
     * @param $idx
     * @return mixed|string
     */
    static function getLegendColor($idx)
    {
        $grundfarben = [
            '#fc0',
            '#F88C1F',
            '#83AEE3',
            '#FE2A29',
            '#8DCC35',
            '#CC2DD2',
        ];

        $variations = [
            ['#fff', 0.5],
            ['#000', 0.5],
            ['#fff', 0.25],
            ['#000', 0.25],
            ['#fff', 0.75],
            ['#000', 0.75],
            // noch mehr variationen
            ['#f0f', 0.5],
            ['#0f0', 0.5],
            ['#f0f', 0.25],
            ['#0f0', 0.25],
            ['#f0f', 0.75],
            ['#0f0', 0.75],
        ];
        $grundfarbe = $grundfarben[$idx % count($grundfarben)];
        $variation_idx = $idx / count($grundfarben) - 1;
        $variation_idx = $variation_idx % count($variations); # so it never gets out of range
        $color = ($variation_idx >= 0) ? self::mix($grundfarbe, $variations[$variation_idx][0], $variations[$variation_idx][1]) : $grundfarbe;

        return $color;
    }


    /**
     * source: https://stackoverflow.com/a/42921358/2848530
     *
     * I think the best way is the Luminosity Contrast algorithm:
     * ADVISE: The next functions works well most of the time, But sometimes there are colors in which it does not work properly.
     * @param $hexColor
     * @return string
     * @throws \Exception
     */
    public static function getContrastColor($hexColor)
    {
        //////////// hexColor RGB
        [$R1, $G1, $B1] = self::hex2rgb($hexColor);

        //////////// Black RGB
        $blackColor = "#000000";
        [$R2BlackColor, $G2BlackColor, $B2BlackColor] = self::hex2rgb($blackColor);


        //////////// Calc contrast ratio
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
            0.7152 * pow($G1 / 255, 2.2) +
            0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
            0.7152 * pow($G2BlackColor / 255, 2.2) +
            0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        //////////// If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else { //////////// if not, return white color.
            return '#ffffff';
        }
    }


    /**
     * for coloring avatars with name initials
     * HSL stands for hue, saturation, and lightness
     *
     * 08/2021 created, used for MB
     *
     *
     * @param string $str some string, eg. a name
     * @param int $s saturation in % 0..100
     * @param int $l lightness in % 0..100
     * @return string
     */
    public static function stringToHslColor(string $str, int $s, int $l): string
    {
        $hash = 0;
        foreach (mb_str_split($str) as $ch) {
            $hash = mb_ord($ch) + (($hash << 5) - $hash);
        }
        $h = abs($hash % 360);

        return "hsl({$h}, {$s}%, {$l}%)";
    }


}

