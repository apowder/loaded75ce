<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\design\boxes;

use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Banner extends Widget
{

    public $file;
    public $params;
    public $settings;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        global $languages_id, $language;
        $banners = array();
        $banner_type = '';
        $banner_effect = '';
        $banner_speed = '';

        if (!$this->settings[0]['banners_group'] && $this->settings[0]['params'])
            $this->settings[0]['banners_group'] = $this->settings[0]['params'];

        $sql = tep_db_query("select * from " . TABLE_BANNERS_TO_PLATFORM . " nb2p, " . TABLE_BANNERS_NEW . " nb, " . TABLE_BANNERS_LANGUAGES . " bl where bl.banners_id = nb.banners_id AND bl.language_id='" . $languages_id . "' AND nb2p.banners_id=nb.banners_id AND nb2p.platform_id='" . \common\classes\platform::currentId() . "'  and nb.banners_group = '" . $this->settings[0]['banners_group'] . "' AND (bl.banners_html_text!='' OR bl.banners_image!='' OR bl.banners_url) order by nb.sort_order");
        if (!$this->settings[0]['banners_type']) {
            $type_sql_query = tep_db_query("select nb.banner_type from " . TABLE_BANNERS_TO_PLATFORM . " nb2p, " . TABLE_BANNERS_NEW . " nb where nb.banners_group = '" . $this->settings[0]['banners_group'] . "' AND nb2p.banners_id=nb.banners_id AND nb2p.platform_id='" . \common\classes\platform::currentId() . "' limit 1");
            if (tep_db_num_rows($type_sql_query) > 0) {
                $type_sql = tep_db_fetch_array($type_sql_query);
                $type_array = $type_sql['banner_type'];
                $type_exp = explode(';', $type_array);
                if (isset($type_exp) && !empty($type_exp)) {
                    $this->settings[0]['banners_type'] = $type_exp[0];
                } else {
                    $this->settings[0]['banners_type'] = $type_sql['banner_type'];
                }
            }
        }
        while ($row = tep_db_fetch_array($sql)) {
            switch ($row['text_position']) {
                case '0':
                    $row['text_position'] = 'top-left';
                    break;
                case '1':
                    $row['text_position'] = 'top-center';
                    break;
                case '2':
                    $row['text_position'] = 'top-right';
                    break;
                case '3':
                    $row['text_position'] = 'middle-left';
                    break;
                case '4':
                    $row['text_position'] = 'middle-center';
                    break;
                case '5':
                    $row['text_position'] = 'middle-right';
                    break;
                case '6':
                    $row['text_position'] = 'bottom-left';
                    break;
                case '7':
                    $row['text_position'] = 'bottom-center';
                    break;
                case '8':
                    $row['text_position'] = 'bottom-right';
                    break;                
            }
            $banners[] = $row;
        }

        if (!$this->settings[0]['effect'] && $banner_effect) {
            $this->settings[0]['effect'] = $banner_effect;
        } elseif (!$this->settings[0]['effect'] && !$banner_effect) {
            $this->settings[0]['effect'] = 'random';
        }
        if (!$this->settings[0]['slices'])
            $this->settings[0]['slices'] = 15;
        if (!$this->settings[0]['boxCols'])
            $this->settings[0]['boxCols'] = 8;
        if (!$this->settings[0]['boxRows'])
            $this->settings[0]['boxRows'] = 4;
        if (!$this->settings[0]['animSpeed'])
            $this->settings[0]['animSpeed'] = 500;
        if (!$this->settings[0]['pauseTime'])
            $this->settings[0]['pauseTime'] = 3000;
        if (!$this->settings[0]['directionNav'])
            $this->settings[0]['directionNav'] = 'true';
        if (!$this->settings[0]['controlNav'])
            $this->settings[0]['controlNav'] = 'true';
        if (!$this->settings[0]['controlNavThumbs'])
            $this->settings[0]['controlNavThumbs'] = 'false';
        if (!$this->settings[0]['pauseOnHover'])
            $this->settings[0]['pauseOnHover'] = 'true';
        if (!$this->settings[0]['manualAdvance'])
            $this->settings[0]['manualAdvance'] = 'false';

        return IncludeTpl::widget(['file' => 'boxes/banner.tpl', 'params' => [
            'banners' => $banners,
            'banner_type' => $this->settings[0]['banners_type'],
            'banner_speed' => $banner_speed,
            'settings' => $this->settings[0]
        ]]);
    }

}
