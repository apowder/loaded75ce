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

use frontend\design\Info;
use Yii;
use yii\base\Widget;
use frontend\design\IncludeTpl;

class Contacts extends Widget
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

        $data = Info::platformData();

        switch ($this->settings[0]['view_item']) {
            case 'phone_number':
                return $data['telephone'];
            case 'email':
                return $data['email_address'];
            case 'address':

                $address = $data;
                $address['name'] = '';
                $address['reg_number'] = '';

                if (!$this->settings[0]['address_spacer']){
                    $this->settings[0]['address_spacer'] = '<br>';
                }

                return \common\helpers\Address::address_format(
                    \common\helpers\Address::get_address_format_id($data['country_id']),
                    $address,
                    0,
                    ' ',
                    $this->settings[0]['address_spacer'],
                    true);
            case 'company_no':
                return $data['reg_number'];
            case 'company_vat_id':
                return $data['company_vat'];
            case 'opening_hours':
                if ($this->settings[0]['time_format'] == '24') {
                    foreach ($data['open'] as $key => $item) {
                        $data['open'][$key]['time_from'] = date("G:i", strtotime($item['time_from']));
                        $data['open'][$key]['time_to'] = date("G:i", strtotime($item['time_to']));
                    }
                }
                $ours = '';
                foreach ($data['open'] as $item){
                    if (!$item['days_short']) {
                        $item['days_short'] = 'Everyday';
                    }
                    $ours .= '<p>' . $item['days_short'] . ' (' . $item['time_from'] . '-' . $item['time_to'] . ')</p>';
                }

                return $ours;
        }

        return '';

    }
}