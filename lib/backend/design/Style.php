<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design;

use Yii;

class Style
{
    public static function hide($class)
    {
        $arr = array();

        if ($class == 'body') {
            $arr = [
                'hover' => 1,
                'display' => 1,
            ];
        }

        if ($class == 'a') {
            $arr = [
                'font' => [
                    'font_size' => 1,
                    'line_height' => 1,
                    'text_align' => 1,
                    'vertical_align' => 1,
                ],
                'padding' => 1,
                'border' => 1,
                'size' => 1,
                'display' => 1,
            ];
        }

        if ($class == '.main-width, .type-1 > .block') {
            $arr = [
                'hover' => 1,
                'font' => 1,
                'background' => 1,
                'padding' => 1,
                'border' => 1,
                'size' => [
                    'width' => 1,
                    'min_width' => 1,
                    'height' => 1,
                    'min_height' => 1,
                    'max_height' => 1,
                ],
                'display' => 1,
            ];
        }

        if ($class == '.menu-slider .close') {
            $arr = [
                'font' => [
                    'font_family' => 1,
                    'vertical_align' => 1,
                ],
                'size' => [
                    'min_width' => 1,
                    'min_height' => 1,
                    'max_width' => 1,
                    'max_height' => 1,
                ],
                'display' => 1,
            ];
        }

        return $arr;
    }

    public static function show($class)
    {
        $arr = array();

        if (
            $class == '.tab-navigation > div > span, .tab-navigation > li > span, .tab-navigation > div > a, .tab-navigation > li > a' ||
            $class == '.menu-style-1 > ul > li' ||
            $class == '.menu-style-1 > ul > li > ul > li' ||
            $class == '.menu-style-1 > ul > li > ul > li > ul > li' ||
            $class == '.menu-style-1 > ul > li > ul > li > ul > li > ul > li' ||
            $class == '.menu-slider > ul > li' ||
            $class == '.menu-slider > ul > li > ul > li' ||
            $class == '.menu-slider > ul > li > ul > li > ul > li' ||
            $class == '.menu-slider > ul > li > ul > li > ul > li > ul > li' ||
            $class == '.menu-horizontal > ul > li' ||
            $class == '.menu-horizontal > ul > li > ul > li' ||
            $class == '.menu-horizontal > ul > li > ul > li > ul > li' ||
            $class == '.menu-horizontal > ul > li > ul > li > ul > li > ul > li'
        ) {
            $arr = [
                'active' => 1,
            ];
        }

        return $arr;
    }
}