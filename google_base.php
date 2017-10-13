<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$NoSessionStart = true;
require_once("includes/application_top.php");

require_once 'lib/frontend/design/Info.php';
require_once 'lib/common/classes/Images.php';
define(DIR_WS_THEME, 'themes/wristbands');

$platform_code = '';
$google_platform_id = \common\classes\platform::activeId();
if ( preg_match('/^(.*):(\d+)$/', GOOGLE_BASE_SHOP_PLATFORM_ID, $match) ) {
    $platform_code = $match[1];
    $google_platform_id = (int)$match[2];
}

define(GOOGLE_BASE_FILE, "google_base_export.txt");
define(GOOGLE_CSV_DELIMITER, "\t");
define(GOOGLE_CSV_SURROUND, '"');
define(GOOGLE_CSV_EOL, "\r\n");

set_time_limit(30 * 60);

$escape_values = array();
$safe_strings = array();
$escape_values[] = "\r";
$safe_strings[] = '\r';
$escape_values[] = "\n";
$safe_strings[] = '\n';
$escape_values[] = "\"";
$safe_strings[] = '""';

//	$output = fopen("feeds/google.tmp", "w");
$output = fopen("php://output", "w");
if ($output) {
    $fields = array(
        "title" => "get_name",
        "description" => "get_description",
        "link" => "make_url",
        "thumbnail" => "make_thumbnail_url",
        "image_link" => "make_image_url",
        "id" => "products_id",
        "expiration_date" => "make_expiration_date",
        "price" => "get_price",
        "currency" => "get_currency",
        "model_number" => "products_model",
        "quantity" => "products_quantity",
        "weight" => "products_weight",
        "condition" => "get_condition",
        "brand" => "manufacturers_name",
        "mpn" => "products_model",
        "availability" => "availability",
        "product_type" => "get_google_product_type",
        "google_product_category" => "get_google_product_category",
    );

    if ( GOOGLE_BASE_FIELD_LIST!='' ) {
        $selectedFields = explode(",", GOOGLE_BASE_FIELD_LIST);
        foreach ($fields as $key => $value) {
            if (!in_array($key, $selectedFields)) {
                unset($fields[$key]);
            }
        }
    }


    $query = tep_db_query("
			select	p.products_id, p.products_model, m.manufacturers_name, if(products_quantity > 0, products_quantity, 100) products_quantity, 
			    if(products_quantity > 0,'in stock', 'preorder') as availability, p.google_product_category, p.google_product_type,
					p.products_tax_class_id, cd.categories_name, p2c.categories_id, p.products_image_med, if(pda.products_name is NULL or pda.products_name = '', pd.products_name, pda.products_name) products_name, pda.products_description_short description_short_affiliate, 
					pd.products_description_short description_short, pda.products_description description_affiliate, pd.products_description description, 
					products_weight, IFNULL(s.specials_new_products_price, p.products_price) AS products_price
			from	" . TABLE_PRODUCTS . " p 
                                        inner join " . TABLE_PLATFORMS_PRODUCTS . " p2p on p2p.products_id = p.products_id and p2p.platform_id = '" . \common\classes\platform::currentId() . "' 
                                        inner join " . TABLE_PLATFORMS_PRODUCTS . " p2pg on p2pg.products_id = p.products_id and p2pg.platform_id = '" . $google_platform_id . "'" . "
                                        inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c on p2c.products_id = p.products_id    
                                        inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on plc.categories_id = p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' 
					left join " . TABLE_SPECIALS . " s on ( s.products_id = p.products_id AND ( ( (s.expires_date > CURRENT_DATE) OR (s.expires_date = 0) ) AND ( s.status != 0 ) ) )
					left join " . TABLE_PRODUCTS_DESCRIPTION . " pda on pda.products_id = p.products_id and pda.language_id = '1' and pda.affiliate_id = '" . $HTTP_SESSION_VARS['affiliate_ref'] . "'
					left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on pd.products_id = p.products_id and pd.language_id = '1' and pd.affiliate_id = 0
					left join " . TABLE_MANUFACTURERS . " m on m.manufacturers_id = p.manufacturers_id
					left join " . TABLE_CATEGORIES . " c on c.categories_id = p2c.categories_id
					left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on cd.categories_id = c.categories_id and cd.affiliate_id = 0 and cd.language_id = '1'
			where	p.products_status != 0 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . "
				and	c.categories_status != 0
			group	by p.products_id
		");

    $rows_number = tep_db_num_rows($query);
    $counter = 0;
    $header = false;
    while ($row = tep_db_fetch_array($query)) {
        $data = array();
        foreach ($fields as $field => $source)
            if (key_exists($source, $row))
                $data[$field] = strip_tags($row[$source]);
            elseif (function_exists($source))
                $data[$field] = strip_tags($source($row));
            else {
                print_r($row);
                trigger_error("Unknown source: $source", E_USER_ERROR);
            }

        foreach ($data as $key => $value)
            $data[$key] = str_replace($escape_values, $safe_strings, $value);

        if (!$header) {
            $header = array_keys($data);
            fwrite($output, GOOGLE_CSV_SURROUND . join(GOOGLE_CSV_SURROUND . GOOGLE_CSV_DELIMITER . GOOGLE_CSV_SURROUND, $header) . GOOGLE_CSV_SURROUND . GOOGLE_CSV_EOL);
        }
        fwrite($output, GOOGLE_CSV_SURROUND . join(GOOGLE_CSV_SURROUND . GOOGLE_CSV_DELIMITER . GOOGLE_CSV_SURROUND, $data) . GOOGLE_CSV_SURROUND . GOOGLE_CSV_EOL);

        ShowProgress($counter++ / $rows_number);
    }

    fclose($output);
//		rename("feeds/google.tmp", "feeds/" . GOOGLE_BASE_FILE);
//		copy("feeds/" . GOOGLE_BASE_FILE, "ftp://" . GOOGLE_BASE_FTP_USER . ":" . GOOGLE_BASE_FTP_PASSWORD . "@" . GOOGLE_BASE_FTP_SERVER . "/" . GOOGLE_BASE_FILE);
}

Message("Done       ");

function make_url(&$product) {
    global $platform_code;
    return tep_href_link(FILENAME_PRODUCT_INFO, "products_id=" . $product["products_id"] .(empty($platform_code)?'':'&code='.$platform_code), 'NONSSL', false);
}

function get_name(&$product) {
    $name = trim($product["products_name"]);
    $manufacturer = trim($product["manufacturers_name"]);
    if ($manufacturer && stripos($name, $manufacturer) !== 0)
        $name = "$manufacturer $name";

    return $name;
}

function make_thumbnail_url(&$product) {
    $image_url = common\classes\Images::getImageUrl($product["products_id"]);
    return preg_match('@^[^:]{3,5}://@',$image_url)?$image_url:(HTTP_SERVER . DIR_WS_HTTP_CATALOG . $image_url);
}

function make_image_url(&$product) {
    $image_url = common\classes\Images::getImageUrl($product["products_id"], 'Large');
    return preg_match('@^[^:]{3,5}://@',$image_url)?$image_url:(HTTP_SERVER . DIR_WS_HTTP_CATALOG . $image_url);
    //return file_exists(DIR_WS_IMAGES . $product["products_image_med"]) && is_file(DIR_WS_IMAGES . $product["products_image_med"]) ? HTTP_SERVER . DIR_WS_CATALOG . DIR_WS_IMAGES . $product["products_image_med"] : "";
}

function make_expiration_date() {
    return strftime("%Y-%m-%d", strtotime("+28 day"));
}

function get_currency() {
    return DEFAULT_CURRENCY;
}

function get_price(&$product) {
    return \common\helpers\Tax::add_tax(\common\helpers\Product::get_products_price($product["products_id"], 1, $product['products_price']), \common\helpers\Tax::get_tax_rate($product['products_tax_class_id']), true);
}

function get_description(&$product) {
    foreach (array($product["description_short_affiliate"], $product["description_short"], $product["description_affiliate"], $product["description"]) as $description)
        if ($description) {
            $description = preg_replace('/\s+/ims', ' ', strip_tags($description));
            if (strlen($description) > 255) {
                $description = substr($description, 0, 250);
                $description = substr($description, 0, strrpos($description, ' ')) . " ...";
            }
            break;
        }

    return $description;
}

function get_condition() {
    return 'new';
}

function ShowProgress($Progress, $Precision = 1) {
//		static $previous_step_percent_completed, $rotor_state;
//		$rotor = "|/-\\";
//		
//		$percent_completed = sprintf("%.{$Precision}f", round(($Progress * 100), 1));
//		if ($previous_step_percent_completed != time())
//		{
//			$rotor_state = ($rotor_state + 1) % 4;
//			echo "\r" . $rotor[$rotor_state] . " $percent_completed% ";
//			$previous_step_percent_completed = time();
//		}
}

function Message($text) {
//		echo "\r$text\r\n";
}

function get_google_product_category(&$product) {
    $google_product_category = "";
    if ($product['google_product_category'] != "")
        $google_product_category = $product['google_product_category'];
    elseif ($product['google_product_type'] > 0) {
        $google_product_category = get_google_product_category_parent($product['google_product_type']);
    } elseif ($product['categories_id'] > 0) {
        $google_product_category = get_google_product_category_parent($product['categories_id']);
    }
    return $google_product_category;
}

function get_google_product_category_parent($categories_id) {
    $google_product_category = "";
    $query = "select parent_id, google_product_category from " . TABLE_CATEGORIES . " where categories_id='" . $categories_id . "'";
    $result = tep_db_query($query);
    $array = tep_db_fetch_array($result);
    if ($array['google_product_category'] != "")
        $google_product_category = $array['google_product_category'];
    elseif ($array['parent_id'] > 0)
        $google_product_category = get_google_product_category_parent($array['parent_id']);
    return $google_product_category;
}

function get_google_product_type(&$product) {
    $google_product_type = "";
    $categories = array();
    if ($product['google_product_type'] > 0) {
        \common\helpers\Categories::get_parent_categories($categories, $product['google_product_type']);
        $categories = array_reverse($categories);
        $categories[] = $product['google_product_type'];
    } else {
        \common\helpers\Categories::get_parent_categories($categories, $product['categories_id']);
        $categories = array_reverse($categories);
        $categories[] = $product['categories_id'];
    }
    if (count($categories) > 0) {
        for ($cat = 0; $cat < count($categories); $cat++) {
            if ($google_product_type != "")
                $google_product_type .= " > ";
            $google_product_type .= tep_get_category_name($categories[$cat], 1);
        }
    }
    return $google_product_type;
}

function tep_get_category_name($category_id) {
    global $languages_id;
    global $HTTP_SESSION_VARS;
    $affiliate_id = (int) $HTTP_SESSION_VARS['affiliate_ref'];
    $query = "select categories_name from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int) $category_id . "' and language_id = '" . (int) $languages_id . "' and affiliate_id = '" . (int) $affiliate_id . "'";
    $category_query = tep_db_query($query);
    $category = tep_db_fetch_array($category_query);
    return $category['categories_name'];
}
