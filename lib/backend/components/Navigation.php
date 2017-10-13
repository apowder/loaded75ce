<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\components;

use yii\base\Widget;

class Navigation extends Widget {

    public $box_files_list = array();
    public $selectedMenu = array();

    public function run() {

        if (isset(\Yii::$app->controller->selectedMenu)) {
            $this->selectedMenu = \Yii::$app->controller->selectedMenu;
        } else {
            $this->selectedMenu = array("index");
        }

        $box_files_list = [];
        if (\common\helpers\Acl::rule('BOX_HEADING_CUSTOMERS')) {
            $box_files_list[] = array('customers', 'customers.php', BOX_HEADING_CUSTOMERS);
        }
        if (\common\helpers\Acl::rule('BOX_HEADING_CATALOG')) {
            $box_files_list[] = array('catalog', 'catalog.php', BOX_HEADING_CATALOG);
        }
        if (\common\helpers\Acl::rule('BOX_HEADING_REPORTS')) {
            $box_files_list[] = array('reports', 'reports.php', BOX_HEADING_REPORTS);
        }
        if (\common\helpers\Acl::rule('BOX_HEADING_MARKETING_TOOLS')) {
            $box_files_list[] = array('marketing', 'marketing.php', BOX_HEADING_MARKETING_TOOLS);
        }
        if (\common\helpers\Acl::rule('BOX_HEADING_SEO_CMS')) {
            $box_files_list[] = array('seo_cms', 'seo_cms.php', BOX_HEADING_SEO_CMS);
        }
        if (\common\helpers\Acl::rule('BOX_HEADING_DESIGN_CONTROLS')) {
            $box_files_list[] = array('design_controls', 'design_controls.php', BOX_HEADING_DESIGN_CONTROLS);
        }
        if (\common\helpers\Acl::rule('BOX_HEADING_MODULES')) {
            $box_files_list[] = array('modules', 'modules.php', BOX_HEADING_MODULES);
        }
        if (\common\helpers\Acl::rule('BOX_HEADING_ADMINISTRATOR')) {
            $box_files_list[] = array('administrator', 'administrator.php', BOX_HEADING_ADMINISTRATOR);
        }
        if (\common\helpers\Acl::rule('TEXT_SETTINGS')) {
            $box_files_list[] = array('settings', 'settings.php', TEXT_SETTINGS);
        }
        \common\helpers\Acl::rule('BOX_HEADING_FRONENDS');
        
        foreach ($box_files_list as $item_key => $item_menu) {
            if (file_exists(DIR_WS_BOXES. $item_menu[1])) {
                $submenu = array();
                require(DIR_WS_BOXES. $item_menu[1] );
                $box_files_list[$item_key][3] = $submenu;
            } else {
                unset($box_files_list[$item_key]);
            }
        }
        
        $this->box_files_list = $box_files_list;

        return $this->render('Navigation', [
          'context' => $this,
        ]);
    }

}

