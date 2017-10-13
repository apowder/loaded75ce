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

use backend\models\EP\Provider\ProviderAbstract;
use Yii;
use backend\models\EP\Provider\ImportInterface;

class Providers
{

    protected $providers = [];

    public function __construct()
    {
        \common\helpers\Translation::init('admin/categories');

        $this->providers = [
            'product\products' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PRODUCT,
                'class' => 'Provider\\Products',
                'export' =>[
                    'filters' => ['category'],
                ],
            ],
            'product\categories' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_CATEGORIES,
                'class' => 'Provider\\Categories',
                'export' =>[
                    'filters' => ['category'],
                ],
            ],
            'product\products_to_categories' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PRODUCTS_TO_CATEGORIES,
                'class' => 'Provider\\ProductsToCategories',
                'export' =>[
                    'filters' => ['category'],
                    'disableSelectFields' => true,
                ],
            ],
            'product\attributes' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_ATTRIBUTES,
                'class' => 'Provider\\Attributes',
                'export' =>[
                    'filters' => ['category'],
                ],
            ],
            'product\inventory' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_INVENTORY,
                'class' => 'Provider\\Inventory',
                'export' =>[
                    'filters' => ['category'],
                ],
            ],
            'product\stock' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => 'Stock',
                'class' => 'Provider\\Stock',
                'export' =>[
                    'filters' => ['category'],
                ],
            ],
            'product\images' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_IMAGES,
                'class' => 'Provider\\Images',
                'export' =>[
                    'filters' => ['category'],
                ],
            ],
            'product\imageszip' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_IMAGES.' Zip',
                'class' => 'Provider\\ImagesZip',
                'export' =>[
                    'allow_format' => ['ZIP'],
                    'filters' => ['category'],
                ],
            ],
            'product\properties' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PROPERTIES,
                'class' => 'Provider\\Properties',
                'export' =>[
                    'filters' => ['category'],
                    'disableSelectFields' => true,
                ],
            ],
            'product\catalog_properties' => [
                'group' => TEXT_CATALOG_PRODUCTS,
                'name' => TEXT_OPTION_PROPERTIES_SETTINGS,
                'class' => 'Provider\\CatalogProperties',
                'export' =>[
                    'filters' => ['properties'],
                ],

            ],
            'statistic\orders' => [
                'group' => TEXT_SITE_STATISTIC,
                'name' => 'Order Statistic',
                'class' => 'Provider\\OrderStatistic',
                'export' =>[
                    'filters' => ['orders-date-range'],
                    'disableSelectFields' => true,
                ],
            ],
            'BrightPearl\\Stock' => [
                'group' => TEXT_BRIGHT_PEARL,
                'name' => 'Stock',
                'class' => 'Provider\\BrightPearl\\Stock',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'BrightPearl\\ExportPrice' => [
                'group' => TEXT_BRIGHT_PEARL,
                'name' => 'Export Price',
                'class' => 'Provider\\BrightPearl\\ExportPrice',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'BrightPearl\\ExportOrder' => [
                'group' => TEXT_BRIGHT_PEARL,
                'name' => 'Export Order',
                'class' => 'Provider\\BrightPearl\\ExportOrder',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HolbiLink\\Products' => [
                'group' => 'Holbi Link',
                'name' => 'Import products',
                'class' => 'Provider\\HolbiLink\\ImportProducts',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ],
            'HPCap\\ImportProducts' => [
                'group' => 'HP Cap',
                'name' => 'Import products',
                'class' => 'Provider\\HPCap\\ImportProducts',
                'export' =>[
                    'disableSelectFields' => true,
                ],
            ]
        ];

        $get_custom_r = tep_db_query(
            "SELECT custom_provider_id, name, parent_provider, provider_configure ".
            "FROM " . TABLE_EP_CUSTOM_PROVIDERS . " ".
            "WHERE 1 ".
            "ORDER BY 1"
        );
        if ( tep_db_num_rows($get_custom_r)>0 ) {
            while( $custom = tep_db_fetch_array($get_custom_r) ){
                $parentProvider = $custom['parent_provider'];
                if ( !isset($this->providers[$parentProvider]) ) continue;
                $provider_info = $this->providers[$parentProvider];

                $provider_info['name'] = $custom['name'];
                $provider_key = 'custom\\'.$custom['custom_provider_id'];

                //$provider_info['provider_configure'];

                $this->providers[ $provider_key ] = $provider_info;

            }
        }

    }

    public function getAvailableProviders($type, $filterGroup='')
    {
        $providerList = array();
        foreach ( $this->providers as $provider_key=>$provider_info ) {
            if ( !empty($filterGroup) && strpos($provider_key,$filterGroup.'\\')!==0 ) continue;
            if ( $type=='Import' && is_subclass_of('backend\models\EP\\'.$provider_info['class'] ,'backend\models\EP\Provider\ImportInterface',true)){
                $provider_info['key'] = $provider_key;
                $providerList[] = $provider_info;
            }elseif ( $type=='Export' && is_subclass_of('backend\models\EP\\'.$provider_info['class'] ,'backend\models\EP\Provider\ExportInterface',true)){
                $provider_info['key'] = $provider_key;
                $providerList[] = $provider_info;
            }elseif ( $type=='Datasource' && is_subclass_of('backend\models\EP\\'.$provider_info['class'] ,'backend\models\EP\Provider\DatasourceInterface',true)) {
                $provider_info['key'] = $provider_key;
                $providerList[] = $provider_info;
            }
        }
        return $providerList;
    }

    public function pullDownVariants($for='Import', $pullDownData = [], $filterGroup='')
    {
        if ( !isset($pullDownData['items']) ) $pullDownData['items'] = [];
        if ( !isset($pullDownData['options']) ) $pullDownData['options'] = [];
        if ( !isset($pullDownData['options']['options']) ) $pullDownData['options']['options'] = [];

        $option_key = strtolower($for);
        foreach($this->getAvailableProviders($for, $filterGroup) as $providerInfo)
        {
            $group = $providerInfo['group'];
            if ( !isset($pullDownData['items'][$group]) ) $pullDownData['items'][$group] = [];
            $pullDownData['items'][$group][$providerInfo['key']] = $providerInfo['name'];

            if ( isset($providerInfo[$option_key]) ) {
                $providerOptions = $providerInfo[$option_key];
                $options_data = [];
                if (!isset($providerOptions['disableSelectFields']) || !$providerOptions['disableSelectFields']) {
                    $options_data['data-select-fields'] = 'true';
                }
                if (isset($providerOptions['filters']) && count($providerOptions['filters']) > 0) {
                    foreach ($providerOptions['filters'] as $filterCode) {
                        $options_data['data-allow-select-' . $filterCode] = 'true';
                    }
                }
                if (isset($providerOptions['allow_format']) && count($providerOptions['allow_format']) > 0) {
                    $options_data['data-allow-format'] = implode(',',$providerOptions['allow_format']);
                }else{
                    $options_data['data-allow-format'] = 'CSV';
                }

                if (count($options_data) > 0) {
                    $pullDownData['options']['options'][$providerInfo['key']] = $options_data;
                }
            }
        }

        return $pullDownData;
    }

    public function getProviderName($provider)
    {
        if ( isset($this->providers[$provider]) ) {
            return $this->providers[$provider]['name'];
        }
        return 'Unknown';
    }

    /**
     * @param $key
     * @param array $providerConfig
     * @return bool|ProviderAbstract
     */
    public function getProviderInstance($key, $providerConfig=[])
    {
        if ( isset($this->providers[$key]) ) {
            $providerClassName = 'backend\\models\\EP\\' . $this->providers[$key]['class'];
            $obj = Yii::createObject($providerClassName, [$providerConfig]);
            if ( method_exists($obj,'customConfig') ) $obj->customConfig($providerConfig);
            return $obj;
        }
        return false;
    }

    public function bestMatch(array $fileColumns)
    {
        $providersMatchRate = array();
        foreach( $this->getAvailableProviders('Import') as $providerInfo)
        {
            if ( strpos($providerInfo['key'],'BrightPearl')!==false ) continue;
            $provider = $this->getProviderInstance($providerInfo['key']);
            if ( !is_object($provider) ) continue;
            /**
             * @var $provider ProviderAbstract
             */

            $score = $provider->getColumnMatchScore($fileColumns);
            if ( $score>0 ) {
                $providersMatchRate[$providerInfo['key']] = $score;
            }
        }
        arsort($providersMatchRate, SORT_NUMERIC);

        return $providersMatchRate;
    }

}
