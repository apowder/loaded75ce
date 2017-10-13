<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\models;

use Yii;

/**
 * This is the model class for table "google_settings".
 *
 * @property integer $google_settings_id
 * @property string $setting_type
 * @property integer $setting_priority
 * @property string $setting_code
 * @property integer $setting_status
 */
class Google {

    const DEFAULT_STATUS = 0;

    public static $modules = ['analytics', 'adwords', 'ecommerce', 'certifiedshop',];
    public static $RESERVED_KEYS = ['mapskey' => [
            'description' => TEXT_GOOGLE_MAPSKEY_DESCRIPTION,
        ],
    ];

    public static function getInstalledModules($platform_id) {
        if (\frontend\design\Info::isAdmin()) {
            $list_query = tep_db_query("select * from " . TABLE_GOOGLE_SETTINGS . " where platform_id = '" . (int) $platform_id . "'");
        } else {
            $list_query = tep_db_query("select * from " . TABLE_GOOGLE_SETTINGS . " where platform_id = '" . (int) $platform_id . "' and status = 1");
        }
        $mods = [];

        if (tep_db_num_rows($list_query)) {
            while ($mod = tep_db_fetch_array($list_query)) {
                $mods[$mod['module']] = $mod;
                $mods[$mod['module']]['info'] = unserialize($mod['info']);
            }
        }
        return $mods;
    }

    public static function getInstalledModule($id, $overload = true) {
        $module_info = tep_db_fetch_array(tep_db_query("select * from " . TABLE_GOOGLE_SETTINGS . " where google_settings_id = '" . (int) $id . "'"));
        if ($module_info && class_exists('\\common\\models\\google\\' . $module_info['module'])) {
            $_name = "\common\models\google\\" . $module_info['module'];
            $module = new $_name;
            $params = $module->getParams();
            list($code, $config) = each($params);
            if (tep_not_null($module_info['info']) && $overload) {
                $module->overloadConfig($module_info['info']);
            }
            return $module;
        }
        return false;
    }

    public static function getUninstalledModules() { // return all modules
        $mods = [];

        foreach (self::$modules as $_mod) {
            if (class_exists('\\common\\models\\google\\' . $_mod)) {
                $_name = "\common\models\google\\" . $_mod;
                $module = new $_name;
                $params = $module->getParams();
                list($code, $config) = each($params);
                $mods[$code] = $config;
            }
        }
        return $mods;
    }

    public static function perform($module, $action, $platform_id, $status = 0) {
        $_name = '\\common\\models\\google\\' . $module;
        if (class_exists($_name)) {
            $module = new $_name;
            if (method_exists($module, $action)) {
                $module->$action(['platform_id' => $platform_id, 'status' => $status]);
            }
        }
    }

    public function overloadConfig($config) {
        $this->config = unserialize($config);
        return $this;
    }

    public function save($id) {
        tep_db_query("update " . TABLE_GOOGLE_SETTINGS . " set info ='" . tep_db_input(serialize($this->config)) . "' where google_settings_id = '" . (int) $id . "'");
    }

    protected function install($params) {
        $settings = $this->getParams();
        list($code, $config) = each($settings);
        if (!$this->checkInstalled($code, $params['platform_id'])) {
            $sql_array = array(
                'platform_id' => (int) $params['platform_id'],
                'module' => $code,
                'status' => 0,
                'module_name' => $config['name'],
            );
            tep_db_perform(TABLE_GOOGLE_SETTINGS, $sql_array);
            return true;
        }
        return false;
    }

    protected function status($params) {
        $settings = $this->getParams();
        list($code, $config) = each($settings);
        if ($this->checkInstalled($code, $params['platform_id']) && isset($params['status'])) {
            tep_db_query("update " . TABLE_GOOGLE_SETTINGS . " set status = '" . tep_db_input($params['status']) . "' where module = '" . tep_db_input($code) . "' and platform_id = '" . (int) $params['platform_id'] . "'");
            return true;
        }
        return false;
    }

    protected function remove($params) {
        $settings = $this->getParams();
        list($code, $config) = each($settings);
        var_dump($this->checkInstalled($code, $params['platform_id']));
        if ($this->checkInstalled($code, $params['platform_id'])) {
            tep_db_query("delete from " . TABLE_GOOGLE_SETTINGS . " where module = '" . tep_db_input($code) . "' and platform_id = '" . (int) $params['platform_id'] . "'");
            return true;
        }
        return false;
    }

    protected function checkInstalled($code, $platform_id) {
        $check = tep_db_query("select module from " . TABLE_GOOGLE_SETTINGS . " where module = '" . tep_db_input($code) . "' and platform_id = '" . (int) $platform_id . "'");
        return tep_db_num_rows($check);
    }

    public function getPriority() {
        return (isset($this->config[$this->code]['priority']) ? $this->config[$this->code]['priority'] : 99);
    }

    public function getAvailablePages() {
        $_pages = [];
        if (isset($this->config[$this->code]['pages'])) {
            foreach ($this->config[$this->code]['pages'] as $key => $_page) {
                $_pages[$key] = strtolower($_page);
            }
        }
        return (count($_pages) ? $_pages : ['all']);
    }

    public function loaded() {
        if (is_array($_POST)) {
            $elements = $this->config[$this->code];
            foreach ($elements as $key => $element) {
                if (isset($_POST[$key])) {
                    if ($key == 'fields') {
                        for ($i = 0; $i < count($element); $i++) {
                            if ($elements[$key][$i]['type'] == 'checkbox') {
                                $elements[$key][$i]['value'] = 0;
                                if (!isset($_POST[$key][$i])) {
                                    $_POST[$key][$i] = [$elements[$key][$i]['name'] => 0];
                                } else {
                                    $_POST[$key][$i] = [$elements[$key][$i]['name'] => 1];
                                }
                            }
                            if (is_array($_POST[$key][$i])){
                                foreach ($_POST[$key][$i] as $field => $value) {
                                    if ($field == $elements[$key][$i]['name']) {
                                        if ($elements[$key][$i]['type'] == 'checkbox') {
                                            $elements[$key][$i]['value'] = $value;
                                        } else {
                                            $elements[$key][$i]['value'] = $value;
                                        }
                                    }
                                }
                            }
                        }
                    } else if ($key == 'type') {
                        $elements[$key]['selected'] = $_POST[$key];
                    } elseif ($key == 'pages') {
                        $elements[$key] = $_POST[$key];
                    }
                }
            }
            //echo '<pre>';print_r($_POST);print_r($elements);die;
            $this->config[$this->code] = $elements;
        }
        return $this;
    }

    public function render() {
        $elements = $this->config[$this->code];

        ob_start();

        echo '<tr>
				<td align="left" colspan="2"><label>' . $elements['name'] . '</label></td>
			 </tr>
			 <tr>
				<td align="left" colspan="2">&nbsp;</td>
			 </tr>';

        if (is_array($elements['fields'])) {
            foreach ($elements['fields'] as $_key => $field) {
                echo '<tr>
							<td align="left" width="30%"><label>' . ucfirst($field['name']) . ':' . (isset($field['comment']) ? $field['comment'] : '') . '</label></td>
							<td><input type="' . $field['type'] . '" name="fields[' . $_key . '][' . $field['name'] . ']" value="' . \common\helpers\Output::output_string($field['value']) . '"  ' . ($field['type'] == 'checkbox' && $field['value'] ? 'checked' : '') . ' ' . ($field['type'] == 'text' ? 'class="form-control"' : '') . '></td>
						 </tr>';
            }
        }
        if (is_array($elements['type'])) {
            $selected = '';
            foreach ($elements['type'] as $type => $value) {
                if ($type == 'selected') {
                    $selected = $value;
                } else {
                    echo '<tr>
							<td align="left"><label for="' . $type . '">' . $value . ':</label></td>
							<td><input type="radio" name="type" value="' . $type . '" id="' . $type . '" ' . ( $type == $selected ? "checked" : "") . '></td>
						 </tr>';
                }
            }
        }

        if (is_array($elements['pages'])) {
            echo '<tr>
					<td align="left" colspan="2">&nbsp;</td>
				</tr>';

            echo '<tr>
						<td align="left"><label>Pages:</label></td>
						<td valign="top">';
            $pages = $elements['pages'];
            $controllers = ['all' => 'All'];
            $_excluded = ['Callback', 'EmailTemplate', 'GetWidget', 'ListDemo', 'Sitemap', 'Xmlsitemap'];
            $_dir = \Yii::$aliases['@frontend'] . '/controllers/';
            foreach (glob($_dir . '*.php') as $file) {
                $controller = substr(basename($file), 0, strpos(basename($file), 'Controller'));
                if (!in_array($controller, $_excluded) && tep_not_null($controller)) {
                    $controllers[strtolower($controller)] = $controller;
                }
            }

            echo \yii\helpers\Html::checkboxList('pages', $pages, $controllers, ['class' => 'page-selector', 'multiple' => 'multiple', 'data-role' => 'multiselect']);

            echo '</td>
						 </tr>';
        }

        if (isset($elements['example']) && $elements['example']) {
            echo '<tr>
					<td align="left" ><label>Example:</label></td>
					<td align="left" >' . $this->renderExample() . '</td>
				</tr>';
        }

        $buf = ob_get_contents();
        ob_end_clean();
        return $buf;
    }

    public static function getReservedKeyValue($key) {
        return tep_db_fetch_array(tep_db_query("select * from " . TABLE_GOOGLE_SETTINGS . " where module = '" . (string) $key . "' and platform_id = 0"));
    }

    public static function getAllReservedKeys() {
        $sets = tep_db_query("select * from " . TABLE_GOOGLE_SETTINGS . " where platform_id = 0");

        $list = false;
        if (tep_db_num_rows($sets)) {
            $list = [];
            while ($row = tep_db_fetch_array($sets)) {
                $list[] = $row;
            }
        }

        return $list;
    }

    public static function checkReservedInstalled() {
        if (is_array(self::$RESERVED_KEYS)) {
            foreach (self::$RESERVED_KEYS as $key => $item) {
                $check = tep_db_query("select module from " . TABLE_GOOGLE_SETTINGS . " where module = '" . tep_db_input($key) . "' and platform_id = 0");
                if (!tep_db_num_rows($check)) {
                    self::insertRow(['module' => $key, 'code' => '', 'description' => $item['description']]);
                }
            }
        }
    }

    public static function insertRow($params) {

        $sql_array = array('module' => $params['module'],
            'info' => $params['code'],
            'platform_id' => 0,
            'module_name' => $params['description'],
        );

        tep_db_perform(TABLE_GOOGLE_SETTINGS, $sql_array);

        return tep_db_insert_id();
    }

    public static function saveRow($params) {

        if (!tep_not_null($params['google_settings_id'])) {
            return self::insertRow($params);
        }

        $sql_array = array(
            'info' => $params['code'],
        );

        if (isset($params['module'])) {
            $sql_array['module'] = $params['module'];
        }

        tep_db_perform(TABLE_GOOGLE_SETTINGS, $sql_array, 'update', 'google_settings_id="' . (int) $params['google_settings_id'] . '"');

        return true;
    }

    public static function deleteRow($id) {
        tep_db_query("delete from " . TABLE_GOOGLE_SETTINGS . " where google_settings_id ='" . (int) $id . "'");
        return true;
    }

    public static function notify() {
        \common\helpers\Translation::init('checkout/success');
        \common\helpers\Mail::send(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, IMAGE_BUTTON_NOTIFICATIONS, TEXT_NEED_SETUP_ANALYTICS, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    }

    public function checkOrderPosition($order_id) {
        if (!$order_id)
            return false;
        $info_query = tep_db_query("select o.orders_id, concat(o.customers_postcode, ' ', o.customers_city, ' ', o.customers_country) as nostreetaddress, concat(o.customers_postcode, ' ', o.customers_street_address, ' ', o.customers_city, ' ', o.customers_country) as address, concat(o.customers_street_address, ' ', o.customers_city, ' ', o.customers_country) as addressnocode from " . TABLE_ORDERS . " o where o.orders_id = '" . (int) $order_id . "' and o.lat = 0 and o.lng = 0 ");
        if (tep_db_num_rows($info_query)) {
            $info = tep_db_fetch_array($info_query);
            $key_data = $this->getReservedKeyValue('mapskey');
            $params = ['key' => $key_data['info'],];
            $params['address'] = $info['address'];
            $response = json_decode($this->getApiResult('https://maps.googleapis.com/maps/api/geocode/json', "GET", $params));
            if ($response->status == 'ZERO_RESULTS') {
                $params['address'] = $info['addressnocode'];
                $response = json_decode($this->getApiResult('https://maps.googleapis.com/maps/api/geocode/json', "GET", $params));
                if ($response->status == 'ZERO_RESULTS') {
                    $params['address'] = $info['nostreetaddress'];
                    $response = json_decode($this->getApiResult('https://maps.googleapis.com/maps/api/geocode/json', "GET", $params));
                }
            }
            if (is_object($response) && !empty($response->results) && $response->status == 'OK') {
                $response = $response->results[0];
                if (is_object($response) && property_exists($response, 'geometry')) {
                    $detail = $response->geometry;
                    if (property_exists($detail, 'location')) {
                        $detail = $detail->location;
                        if (property_exists($detail, 'lat') && property_exists($detail, 'lng')) {
                            tep_db_query("update " . TABLE_ORDERS . " set lat = '" . (float) $detail->lat . "', lng = '" . (float) $detail->lng . "' where orders_id = '" . (int) $order_id . "'");
                        }
                    }
                    return true;
                }
            }
        }
        return false;
    }

    public function getApiResult($url, $method, $params = array()) {
        $data = http_build_query($params);
        $fp = @file_get_contents($url . '?' . $data, false);
        return $fp;
    }

}
