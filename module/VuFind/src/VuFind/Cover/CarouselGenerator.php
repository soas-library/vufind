<?php
/**
 * Dynamic Book Cover Generator
 *
 * PHP version 5
 *
 * Copyright (C) Villanova University 2014.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuFind
 * @package  Cover_Generator
 * @author   Chris Hallberg <crhallberg@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/configuration:external_content Wiki
 */
namespace VuFind\Cover;

/**
 * Dynamic Book Cover Generator
 *
 * @category VuFind
 * @package  Cover_Generator
 * @author   Chris Hallberg <crhallberg@gmail.com>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/configuration:external_content Wiki
 */
class CarouselGenerator
{
    /**
     * Style configuration
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Default images
     *
     * @var array
     */
    protected $leaves = [];

    /**
     * Base color used to fill initially created image.
     *
     * @var int
     */
    protected $baseColor;

    /**
     * Title's fill color
     *
     * @var int
     */
    protected $titleFillColor;

    /**
     * Title's border color
     *
     * @var int
     */
    protected $titleBorderColor;
    /**
     * Base for image
     *
     * @var resource
     */
    protected $im;

    /**
     * Width of image (pixels)
     *
     * @var int
     */
    protected $width;

    /**
     * Height of image (pixels)
     *
     * @var int
     */
    protected $height;

    /**
     * Constructor
     *
     * @param \VuFindTheme\ThemeInfo $themeTools For font loading
     * @param array                  $settings   Overwrite styles
     */
    public function __construct($themeTools, $settings = [], $leaves = [])
    {
        $this->themeTools = $themeTools;
        $default = [
            'backgroundMode' => 'solid',
            'textMode' => 'default',
            'titleFontSize' => 8,
            'lightness'    => 220,
            'maxTitleLines' => 3,
            'saturation'   => 50,
            'size'         => 84,
            'textAlign'    => 'center',
            'titleFont'    => 'OpenSans-Bold.ttf',
            'topPadding'   => 165,
            'bottomPadding' => 5,
            'wrapWidth'    => 100,
            'titleFillColor' => 'none',
            'titleBorderColor' => 'none',
            'baseColor' => 'white',
            'accentColor' => 'random',
        ];
        foreach ($settings as $i => $setting) {
            $default[$i] = $setting;
        }

       $default = [
            'backgroundMode' => 'solid',
            'textMode' => 'default',
            'titleFontSize' => 8,
            'lightness'    => 220,
            'maxTitleLines' => 3,
            'saturation'   => 50,
            'size'         => 84,
            'textAlign'    => 'center',
            'titleFont'    => 'OpenSans-Bold.ttf',
            'topPadding'   => 165,
            'bottomPadding' => 5,
            'wrapWidth'    => 100,

        ];


        $default['titleFont']  = $this->fontPath($default['titleFont']);
        $this->settings = (object) $default;
        $this->leaves = $leaves;;
        $this->initImage();
        //$this->initColors();
    }

    /**
     * Initialize colors to be used in the image.
     *
     * @return void
     */
    protected function initColors()
    {
        $this->baseColor = $this->getColor($this->settings->baseColor);
        $this->titleFillColor = $this->getColor($this->settings->titleFillColor);
        $this->titleBorderColor = $this->getColor($this->settings->titleBorderColor);
    }

    /**
     * Initialize the image in the object.
     *
     * @return void
     */
    protected function initImage()
    {
        // Create image
        $parts = explode('x', strtolower($this->settings->size));
        if (count($parts) < 2) {
            $this->width = $this->height = $parts[0];
        } else {
            list($this->width, $this->height) = $parts;
        }
        $this->width = 120;
        $this->height = 80;
       //$this->settings['noCarouselCoverAvailableImageLeaves']="/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf01.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf02.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf03.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf04.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf05.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf06.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf07.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf08.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf09.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf11.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf11.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf12.png";
       //if (isset($this->settings['noCarouselCoverAvailableImageLeaves'])) {
            //$this->$leaves="/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf01.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf02.png,/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf03.png";
            //$leaves = explode(',', array($this->$leaves));
            
            // Commented out by sb174 on 2017-12-14 (call no. F0181929)
            #$leaves = array("/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf01.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf02.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf03.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf04.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf05.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf06.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf07.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf08.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf09.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf10.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf11.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/leaf12.png",
            #);
            // Added by sb174 on 2017-12-14 to change leaves to snowflakes for winter (call no. F0181929)
            #$leaves = array("/usr/local/vufind/themes/scb-soas/images/coverleaves/snow01.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow02.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow03.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow04.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow05.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow06.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow07.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow08.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow09.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow10.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow11.png",
            #"/usr/local/vufind/themes/scb-soas/images/coverleaves/snow12.png",
            #);
			// ADDED 2019-02-19 BY sb174 FOR feb-2019 release
            $leaves = array("/usr/local/vufind/themes/scb-soas/images/coverleaves/bees01.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees02.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees03.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees04.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees05.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees06.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees07.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees08.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees09.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees10.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees11.png",
            "/usr/local/vufind/themes/scb-soas/images/coverleaves/bees12.png",
            );
            $claves_aleatorias = array_rand($leaves, 1);
            $image=$leaves[$claves_aleatorias];
        //}
        
        if (!($this->im = imagecreatefrompng ( $image ))) {
            throw new \Exception("Cannot Initialize new GD image stream");
        }
        /*if (!($this->im = imagecreate($this->width, $this->height))) {
            throw new \Exception("Cannot Initialize new GD image stream");
        }*/
    }

    /**
     * Clear the resources associated with the image in the object.
     *
     * @return void
     */
    protected function destroyImage()
    {
        imagedestroy($this->im);
    }

    /**
     * Render the contents of the image in the object to a PNG; return as string.
     *
     * @return string
     */
    protected function renderPng()
    {
        ob_start();
        imagepng($this->im);
        $img = ob_get_contents();
        ob_end_clean();
        return $img;
    }

    /**
     * Check and allocates color
     *
     * @param string $color Legal color name from HTML4
     *
     * @return allocated color
     */
    protected function getColor($color)
    {
        switch (strtolower($color)){
        case 'black':
            return imagecolorallocate($this->im, 0, 0, 0);
        case 'silver':
            return imagecolorallocate($this->im, 192, 192, 192);
        case 'gray':
            return imagecolorallocate($this->im, 128, 128, 128);
        case 'white':
            return imagecolorallocate($this->im, 255, 255, 255);
        case 'maroon':
            return imagecolorallocate($this->im, 128, 0, 0);
        case 'red':
            return imagecolorallocate($this->im, 255, 0, 0);
        case 'purple':
            return imagecolorallocate($this->im, 128, 0, 128);
        case 'fuchsia':
            return imagecolorallocate($this->im, 255, 0, 255);
        case 'green':
            return imagecolorallocate($this->im, 0, 128, 0);
        case 'lime':
            return imagecolorallocate($this->im, 0, 255, 0);
        case 'olive':
            return imagecolorallocate($this->im, 128, 128, 0);
        case 'yellow':
            return imagecolorallocate($this->im, 255, 255, 0);
        case 'navy':
            return imagecolorallocate($this->im, 0, 0, 128);
        case 'blue':
            return imagecolorallocate($this->im, 0, 0, 255);
        case 'teal':
            return imagecolorallocate($this->im, 0, 128, 128);
        case 'aqua':
            return imagecolorallocate($this->im, 0, 255, 255);
        default:
            if (substr($color, 0, 1) == '#' && strlen($color) == 7) {
                $r = hexdec(substr($color, 1, 2));
                $g = hexdec(substr($color, 3, 2));
                $b = hexdec(substr($color, 5, 2));
                return imagecolorallocate($this->im, $r, $g, $b);
            }
            return false;
        }
    }

    /**
     * Generates a dynamic cover image from elements of the item
     *
     * @param string $title      Title of the book
     * @param string $author     Author of the book
     * @param string $callnumber Callnumber of the book
     *
     * @return string contents of image file
     */
    public function generate($title, $author, $callnumber = null, $collection = null)
    {
        // Build the image
        //$this->drawBackgroundLayer($seed);
        $this->drawTextLayer($title, $author);

        // Render the image
        $png = $this->renderPng();
        $this->destroyImage();
        return $png;
    }
    


    /**
     * Position the text on the image.
     *
     * @param string $title  Title of the book
     * @param string $author Author of the book
     *
     * @return void
     */
    protected function drawTextLayer($title, $author)
    {
        $this->drawDefaultText($title, $author);
    }


    /**
     * Position the text on the image using default rules.
     *
     * @param string $title  Title of the book
     * @param string $author Author of the book
     *
     * @return void
     */
    protected function drawDefaultText($title, $author)
    {
        if (null !== $title) {
            $this->drawTitle($title, $this->height / 8);
        }

    }
    

    /**
     * Generate an accent color from a seed value.
     *
     * @param int $seed Seed value
     *
     * @return int
     */
    protected function getAccentColor($seed)
    {
        // Number to color, hsb to control saturation and lightness
        if ($this->settings->accentColor == 'random') {
            return $this->makeHSBColor(
                $seed % 256,
                $this->settings->saturation,
                $this->settings->lightness
            );
        }
        return $this->getColor($this->settings->accentColor);
    }

    /**
     * Generates a solid color background, ala Google
     *
     * @param int $seed Seed value
     *
     * @return void
     */
    protected function drawSolidBackground($seed)
    {
        $red = imagecolorallocatealpha($this->im, 255, 0, 0, 50);
        imagefilledrectangle($this->im, 0, 0, 0, 0, $red);
    }


    /**
     * Turn number into pattern
     *
     * @param int $seed Seed used to generate the pattern
     *
     * @return string binary string describing a quarter of the pattern
     */
    protected function createPattern($seed)
    {
        // Convert to binary
        $bc = decbin($seed);
        // If we have less that a half of a quarter
        if (strlen($bc) < 8) {
            // Rotate square of the first 4 into a 4x2
            // Simulate matrix rotation on string
            $bc = substr($bc, 0, 3)
                . substr($bc, 0, 1)
                . substr($bc, 2, 2)
                . substr($bc, 3, 1)
                . substr($bc, 1, 1);
        }
        // If we have less than a quarter
        if (strlen($bc) < 16) {
            // Rotate the first 8 as a 4x2 into a 4x4
            $bc .= strrev($bc);
        }
        return $bc;
    }

    /**
     * Render title in wrapped, black text with white border
     *
     * @param string $title      Title to write
     * @param int    $lineHeight Pixels we move down each line
     *
     * @return void
     */
    protected function drawTitle($title, $lineHeight)
    {
    //30 characters at maximum



    $title=urlencode($title);
    $title=str_replace("%C5%AD","u",$title);
    $title=urldecode($title);


//$title = mb_convert_encoding($title, 'ISO-8859-1',"UTF-8");
//$title = mb_encode_numericentity($title,    array (0x0, 0xffff, 0, 0xffff), 'UTF-8');

    $title=trim($title);
    if ($title[strlen($title)-1]==":") $title=trim($title=substr($title,0,strlen($title)-2));
    if ($title[strlen($title)-1]=="/") $title=trim($title=substr($title,0,strlen($title)-2));    
    if ($title[strlen($title)-1]=="=") $title=trim($title=substr($title,0,strlen($title)-2));    
    if(strlen($title)>20) 
       $title=substr($title,0,20)."...";

    //$title=urlencode($title);
//$title=rawurldecode(utf8_decode($title));

//$title = mb_convert_encoding($title, 'ISO-8859-1',"UTF-8");
//$title = mb_encode_numericentity($title,    array (0x0, 0xffff, 0, 0xffff), 'UTF-8');




        $words = explode(' ', $title);
        // Wrap words into image
        // Add words until off image, go back and print
        $line = '';
        $lineCount = 0;
        $i = 0;
        while ($i < count($words)
            && $lineCount < $this->settings->maxTitleLines - 1
        ) {
            $pline = $line;
            // Format
            $text = $words[$i];
            $line .= $text . ' ';
            $textWidth = $this->textWidth(
                rtrim($line, ' '),
                $this->settings->titleFont,
                $this->settings->titleFontSize
            );

            //$this->settings->topPadding="150";
            //$textWidth="100";
            //$this->settings->wrapWidth="150";

            if ($textWidth > $this->settings->wrapWidth) {
                // Print black with white border
                $this->drawText(
                    rtrim($pline, ' '),
                    $this->settings->topPadding + $lineHeight * $lineCount,
                    $this->settings->titleFont,
                    $this->settings->titleFontSize,
                    $this->titleFillColor,
                    $this->titleBorderColor
                );
                $line = $text . ' ';
                $lineCount++;
            }
            $i++;
        }
        // Print the last words
        $this->drawText(
            rtrim($line, ' '),
            $this->settings->topPadding + $lineHeight * $lineCount,
            $this->settings->titleFont,
            $this->settings->titleFontSize,
            $this->titleFillColor,
            $this->titleBorderColor
        );
        // Add ellipses if we've truncated
        if ($i < count($words) - 1) {
            $this->drawText(
                '...',
                $this->settings->topPadding
                + $this->settings->maxTitleLines * $lineHeight,
                $this->settings->titleFont,
                $this->settings->titleFontSize + 1,
                $this->titleFillColor,
                $this->titleBorderColor
            );
        }
    }
    

    /**
     * Find font in the theme folder
     *
     * @param string $font Font_name.ttf
     *
     * @return string file path
     */
    protected function fontPath($font)
    {
        // Check all supported image formats:
        $filenames = ['css/font/' . $font];
        $fileMatch = $this->themeTools->findContainingTheme($filenames, true);
        return empty($fileMatch) ? false : $fileMatch;
    }

    /**
     * Returns the width a string would render to
     *
     * @param string $text Text to test
     * @param string $font Full font path
     * @param string $size Size of the font
     *
     * @return int
     */
    protected function textWidth($text, $font, $size)
    {
        $p = imagettfbbox($size, 0, $font, $text);
        return $p[2] - $p[0];
    }

    /**
     * Returns the height a string would render to
     *
     * @param string $text Text to test
     * @param string $font Full font path
     * @param string $size Size of the font
     *
     * @return int
     */
    protected function textHeight($text, $font, $size)
    {
        $p = imagettfbbox($size, 0, $font, $text);
        return $p[1] - $p[5];
    }

    /**
     * Simulate outlined text
     *
     * @param string $text     Text to render
     * @param int    $y        Top position
     * @param string $font     Full path to font
     * @param int    $fontSize Size of the font
     * @param int    $mcolor   Main text color
     * @param int    $scolor   Secondary border color
     * @param string $align    'left','center','right'
     *
     * @return void
     */
    protected function drawText($text, $y, $font, $fontSize, $mcolor,
        $scolor = false, $align = null
    ) {
        // If the wrap width is smaller than the image width, we want to account
        // for this when right or left aligning to maintain padding on the image.

        
        $wrapGap = ($this->width - $this->settings->wrapWidth) / 2;

        $textWidth = $this->textWidth(
            $text,
            $font,
            $fontSize
        );
        if ($textWidth > $this->width) {
            $align = 'left';
            $wrapGap = 0; // kill wrap gap to maximize text fit
        }
        if (null == $align) {
            $align = $this->settings->textAlign;
        }
        if ($align == 'left') {
            $x = $wrapGap;
        }
        if ($align == 'center') {
            $x = ($this->width - $textWidth) / 2;
        }
        if ($align == 'right') {
            $x = $this->width - ($textWidth + $wrapGap);
        }
//$text="Asŭp";



//$text_encoding = mb_detect_encoding($text, 'UTF-8, ISO-8859-1');
//if ($text_encoding != 'UTF-8') {
//    $item_text = mb_convert_encoding($text, 'UTF-8', $text_encoding);
//}
//$text = $text_encoding .  $text;
//$text = mb_encode_numericentity($text,    array (0x0, 0xffff, 0, 0xffff), 'UTF-8');



//$text = mb_convert_encoding($text, 'ISO-8859-1',"UTF-8");
//$text = mb_convert_encoding($text, 'UTF-8',"ISO-8859-1");

        // 1 centered in main color
        imagettftext($this->im, $fontSize, 0, $x,   $y,   $mcolor, $font, $text);
    }


    
    /**
     * Convert 16 long binary string to 8x8 color grid
     * Reflects vertically and horizontally
     *
     * @param string $bc    Binary string of pattern
     * @param int    $color Fill color
     *
     * @return void
     */
    protected function renderGrid($bc, $color)
    {
        $halfWidth = $this->width / 2;
        $halfHeight = $this->height / 2;
        $boxWidth  = $this->width / 8;
        $boxHeight = $this->height / 8;

        $bc = str_split($bc);
        for ($k = 0;$k < 4;$k++) {
            $x = $k % 2 ? $halfWidth : $halfWidth - $boxWidth;
            $y = $k / 2 < 1 ? $halfHeight : $halfHeight - $boxHeight;
            $u = $k % 2 ? $boxWidth : -$boxWidth;
            $v = $k / 2 < 1 ? $boxHeight : -$boxHeight;
            for ($i = 0;$i < 16;$i++) {
                if ($bc[$i] == "1") {
                    imagefilledrectangle(
                        $this->im, $x, $y,
                        $x + $boxWidth - 1, $y + $boxHeight - 1, $color
                    );
                }
                $x += $u;
                if ($x >= $this->width || $x < 0) {
                    $x = $k % 2 ? $halfWidth : $halfWidth - $boxWidth;
                    $y += $v;
                }
            }
        }
        //imagefilledrectangle($this->im,0,$size-11,$size-1,$size,$color);
    }

    /**
     * Using HSB allows us to control the contrast while allowing randomness
     *
     * @param int $h Hue (0-255)
     * @param int $s Saturation (0-255)
     * @param int $v Lightness (0-255)
     *
     * @return int
     */
    protected function makeHSBColor($h, $s, $v)
    {
        $s /= 256.0;
        if ($s == 0.0) {
            return imagecolorallocate($this->im, $v, $v, $v);
        }
        $h /= (256.0 / 6.0);
        $i = floor($h);
        $f = $h - $i;
        $p = (int)($v * (1.0 - $s));
        $q = (int)($v * (1.0 - $s * $f));
        $t = (int)($v * (1.0 - $s * (1.0 - $f)));
        switch($i) {
        case 0:
            return imagecolorallocate($this->im, $v, $t, $p);
        case 1:
            return imagecolorallocate($this->im, $q, $v, $p);
        case 2:
            return imagecolorallocate($this->im, $p, $v, $t);
        case 3:
            return imagecolorallocate($this->im, $p, $q, $v);
        case 4:
            return imagecolorallocate($this->im, $t, $p, $v);
        default:
            return imagecolorallocate($this->im, $v, $p, $q);
        }
    }
}
