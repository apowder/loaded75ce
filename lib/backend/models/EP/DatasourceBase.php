<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP;

use yii\base\Object;

abstract class DatasourceBase extends Object
{

    public $code = '';
    public $settings = [];

    public function __construct(array $config = [])
    {
        if ( isset($config['settings']) && is_string($config['settings']) ) $config['settings'] = json_decode($config['settings'],true);
        if ( !is_array($config['settings']) ) $config['settings'] = array();
        $initConfig = [];
        foreach ($config as $key => $val) {
            if (isset($this->$key)) {
                $initConfig[$key] = $val;
            }
        }
        parent::__construct($initConfig);
    }

    abstract public function getName();

    abstract public function getViewTemplate();

    /**
     * @deprecated
     * @param $configArray
     * @return mixed
     */
    static public function configureArray($configArray)
    {
        return $configArray;
    }

    public function prepareConfigForView($configArray)
    {
        return $configArray;
    }


    static public function beforeSettingSave($data)
    {
        $settings = is_array($data) ? $data : [];

        return $settings;
    }

    public function update($settings)
    {
        $settings = self::beforeSettingSave($settings);
        $this->settings = $settings;
        tep_db_query("UPDATE ep_datasources SET settings='".tep_db_input(json_encode($this->settings))."' WHERE code='".tep_db_input($this->code)."' ");
    }

    public function configureView()
    {
        $settings = $this->prepareConfigForView($this->settings);
        $settings['code'] = $this->code;
        return [
            $this->getViewTemplate(),
            $settings,
        ];
    }

    public function getJobConfig()
    {
        return $this->settings;
    }

}