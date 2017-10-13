<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design;

use Yii;
use common\classes\design;

class Product
{

    public static function pageName($products_id)
    {
        $template_query = tep_db_fetch_array(tep_db_query("
            select template_name from " . TABLE_PRODUCT_TO_TEMPLATE . " where
                products_id = '" . $products_id . "' and
                platform_id = '" . \common\classes\platform::currentId() . "' and
                theme_name = '" . THEME_NAME . "'"));

        if ($template_query['template_name']) {
            $page_name = $template_query['template_name'];
        } else {
            $page_name = self::templateByRules($products_id);
        }

        return design::pageName($page_name);
    }

    public static function templateByRules($products_id)
    {
        $get = Yii::$app->request->get();

        $query = tep_db_query("
select aps.setting_value as rule, ap.setting_value as page_title
from " . TABLE_THEMES_SETTINGS . " ap left join " . TABLE_THEMES_SETTINGS . " aps on ap.setting_value = aps.setting_name
where
    ap.theme_name = '" . THEME_NAME . "' and
    aps.theme_name = '" . THEME_NAME . "' and
    ap.setting_group = 'added_page' and
    aps.setting_group = 'added_page_settings' and
    ap.setting_name = 'product'");

        $pages = array();
        while ($page = tep_db_fetch_array($query)) {
            $pages[$page['page_title']][] = $page['rule'];
        }
        $selected = array();
        foreach ($pages as $page => $rules) {
            $selected[$page] = 1;
            foreach ($rules as $rule) {
                if ($selected[$page] !== false) {
                    switch ($rule) {

                        case 'has_attributes':
                            if (\common\helpers\Attributes::has_product_attributes($products_id)) {
                                $selected[$page] ++;
                            } else {
                                $selected[$page] = false;
                            }
                            break;

                        case 'is_bundle':
                            $bundle = tep_db_fetch_array(tep_db_query("select count(*) as total from " . TABLE_SETS_PRODUCTS . " where sets_id = '" . $products_id . "'"));                            
                            if ($bundle['total'] > 0) {
                                $selected[$page] ++;
                            } else {
                                $selected[$page] = false;
                            }
                            break;
                    }
                }
            }
        }
        arsort($selected);
        reset($selected);
        if (current($selected) !== false) {
            $page_name = key($selected);
        }

        if ($get['page_name']) {
            $page_name = $get['page_name'];
        } elseif (!$page_name || Info::isAdmin()) {
            $page_name = 'product';
        }

        return $page_name;
    }

}
