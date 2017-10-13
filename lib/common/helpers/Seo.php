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

class Seo {

    public static function get_seo_page_path($id, $platform_id) {
        global $languages_id;
        $info_query = tep_db_query("select seo_page_name from " . TABLE_INFORMATION . " where languages_id = '" . (int) $languages_id . "' and information_id = '" . (int) $id . "' and platform_id = '" . (int) $platform_id . "' and affiliate_id = 0");
        $info = tep_db_fetch_array($info_query);
        return $info['seo_page_name'];
    }

    public static function transliterate($input)
    {
        $gost = array(
            "Є"=>"YE","І"=>"I","Ѓ"=>"G","і"=>"i","№"=>"-","є"=>"ye","ѓ"=>"g",
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G","Д"=>"D",
            "Е"=>"E","Ё"=>"YO","Ж"=>"ZH",
            "З"=>"Z","И"=>"I","Й"=>"J","К"=>"K","Л"=>"L",
            "М"=>"M","Н"=>"N","О"=>"O","П"=>"P","Р"=>"R",
            "С"=>"S","Т"=>"T","У"=>"U","Ф"=>"F","Х"=>"X",
            "Ц"=>"C","Ч"=>"CH","Ш"=>"SH","Щ"=>"SHH","Ъ"=>"'",
            "Ы"=>"Y","Ь"=>"","Э"=>"E","Ю"=>"YU","Я"=>"YA",
            "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d",
            "е"=>"e","ё"=>"yo","ж"=>"zh",
            "з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"x",
            "ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"shh","ъ"=>"",
            "ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
            " "=>"-","—"=>"-",","=>"-","!"=>"-","@"=>"-",
            "#"=>"-","$"=>"","%"=>"","^"=>"","&"=>"","*"=>"",
            "("=>"",")"=>"","+"=>"","="=>"",";"=>"",":"=>"",
            "'"=>"",'"'=>"","~"=>"","`"=>"","?"=>"","/"=>"",
            "\\"=>"","["=>"","]"=>"","{"=>"", "}"=>"","|"=>"",
            "."=>"-", "Ä"=>"A", "ä"=>"a", "Ǟ"=>"A", "ǟ"=>"a",
            "Ë"=>"E", "ë"=>"e", "Ḧ"=>"H", "ḧ"=>"h", "Ï"=>"I",
            "ï"=>"i", "Ḯ"=>"I", "ḯ"=>"i", "Ö"=>"O", "ö"=>"o",
            "Ȫ"=>"O", "ȫ"=>"o", "Ṏ"=>"O", "ṏ"=>"o", "ẗ"=>"t",
            "Ü"=>"U", "ü"=>"u", "Ǖ"=>"U", "ǖ"=>"u", "Ǘ"=>"U",
            "ǘ"=>"u", "Ǚ"=>"U", "ǚ"=>"u", "Ǜ"=>"U", "ǜ"=>"u",
            "Ṳ"=>"U", "ṳ"=>"u", "Ṻ"=>"U", "ṻ"=>"u", "Ẅ"=>"W",
            "ẅ"=>"w", "Ẍ"=>"X", "ẍ"=>"x", "Ÿ"=>"Y", "ÿ"=>"y",
            "–"=>"-", "«"=>"", "»"=>"");

        $input = strtr($input, $gost);
        $input = preg_replace("/(-){1,}/", "-", $input);
        if (substr($input, -1) == '-') $input = substr($input, 0, -1);
        return $input;
    }

    public static function makeSlug($string)
    {
        $seo_name = preg_replace("/(%[\da-f]{2}|\+)/i", '-', urlencode(self::transliterate($string)));
        $seo_name = preg_replace('/-{2,}/','-',$seo_name);
        return strtolower($seo_name);
    }
}
