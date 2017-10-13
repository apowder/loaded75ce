<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\helpers;

class Image {

    public static function getNewSize($pic, $reqW, $reqH) {
        $size = @GetImageSize($pic);

        if ($size[0] == 0 || $size[1] == 0) {
            $newsize[0] = $reqW;
            $newsize[1] = $reqH;
            return $newsize;
        }

        $scale = @min($reqW / $size[0], $reqH / $size[1]);
        $newsize[0] = $size[0] * $scale;
        $newsize[1] = $size[1] * $scale;
        return $newsize;
    }

    public static function info_image($image, $alt, $width = '', $height = '') {
        if (tep_not_null($image) && (file_exists(DIR_FS_CATALOG_IMAGES . $image))) {
            if ($width != '' && $height != '') {
                $size = @GetImageSize(DIR_FS_CATALOG_IMAGES . $image);

                if (!($size[0] <= $width && $size[1] <= $height)) {
                    $newsize = self::getNewSize(DIR_FS_CATALOG_IMAGES . $image, $width, $height);

                    $width = $newsize[0];
                    $height = $newsize[1];
                } else {
                    $width = $size[0];
                    $height = $size[1];
                }
            }
            $image = tep_image(DIR_WS_CATALOG_IMAGES . $image, $alt, $width, $height);
        } else {
            $image = TEXT_IMAGE_NONEXISTENT;
        }
        return $image;
    }

}
