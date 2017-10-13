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


use yii\helpers\FileHelper;

class DataSources
{

    static public function getAvailableList()
    {
        static $list = false;

        if ( !is_array($list) ) {
            $list = [];
            try {
                foreach( FileHelper::findFiles(dirname(__FILE__).'/Datasource/', ['recursive' => false, 'only'=>['pattern'=>'*.php']]) as $file){
                    $className = pathinfo($file,PATHINFO_FILENAME);
                    $instance = \Yii::createObject('\\backend\\models\\EP\\Datasource\\'.$className);
                    if ( is_object($instance) && $instance instanceof DatasourceBase ) {
                        $list[] = [
                            'class' => $className,
                            'className' => '\\backend\\models\\EP\\Datasource\\'.$className,
                            'name' => $instance->getName(),
                        ];
                    }
                }
            }catch(\Exception $ex){}
        }

        return $list;
    }

    static public function add($data)
    {
        tep_db_perform('ep_datasources',[
            'code' => $data['name'],
            'class' => $data['class'],
        ]);
        $dsRoot = Directory::loadById(5);
        FileHelper::createDirectory(  $dsRoot->filesRoot().$data['name'],0777);
        $dsRoot->synchronizeDirectories(false);
        $get_created_id_r = tep_db_query(
            "SELECT directory_id FROM ".TABLE_EP_DIRECTORIES." WHERE directory='".tep_db_input($data['name'])."' AND parent_id=5"
        );
        if ( tep_db_num_rows($get_created_id_r)>0 ) {
            $get_created_id = tep_db_fetch_array($get_created_id_r);
            $createdDir = Directory::loadById($get_created_id['directory_id']);

            FileHelper::createDirectory(  $createdDir->filesRoot().'processed',0777);
            $createdDir->synchronizeDirectories(false);
        }
    }

    static public function getByName($name)
    {
        $datasource = false;
        $get_data_r = tep_db_query("SELECT * FROM ep_datasources WHERE code='".tep_db_input($name)."'");

        if ( tep_db_num_rows($get_data_r)>0 ) {
            $data = tep_db_fetch_array($get_data_r);

            $datasource = \Yii::createObject([
                'class' => '\\backend\\models\\EP\\Datasource\\'.$data['class'],
                'code' => $data['code'],
                'settings' => $data['settings'],
            ]);
        }
        return $datasource;
    }

}