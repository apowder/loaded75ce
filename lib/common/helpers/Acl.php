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

use Yii;

class Acl
{
    
    public static function rule($rules, $rootId = 0, $selectedIds = '')
    {
        global $login_id;
        if (is_string($rules)) {
            $rules = [
                $rules,
            ];
        }
        
        if (is_string($selectedIds)) {
            if (empty($selectedIds)) {
                $checkAdmin = tep_db_query("select access_levels_id, admin_persmissions from " . TABLE_ADMIN . " where admin_id = '" . (int)$login_id . "'");
                if (tep_db_num_rows($checkAdmin) == 0) {
                    return false;
                }
                $admin = tep_db_fetch_array($checkAdmin);
                $adminPersmissions = explode(",", $admin['admin_persmissions']);
                
                $checkAccess = tep_db_query("select access_levels_persmissions from " . TABLE_ACCESS_LEVELS . " where access_levels_id = '" . (int)$admin['access_levels_id'] . "'");
                $access = tep_db_fetch_array($checkAccess);
                $selectedIds = explode(",", $access['access_levels_persmissions']);
                
                if (count($adminPersmissions) > 0) {
                    foreach ($adminPersmissions as $permissionIs) {
                        if ($permissionIs > 0) {
                            if (!in_array($permissionIs, $selectedIds)) {
                                $selectedIds[] = $permissionIs;//add to list
                            }
                        } elseif ($permissionIs < 0) {
                            if (false !== $key = array_search(abs($permissionIs), $selectedIds)) {
                                unset($selectedIds[$key]);//remove from list
                            }
                        }
                    }
                }
            } else {
                $selectedIds = explode(",", $selectedIds);
            }
        }
        
        $currentKey = array_shift($rules);
        
        $checkQuery = tep_db_query("select * from " . TABLE_ACCESS_CONTROL_LIST . " where access_control_list_key = '" . tep_db_input($currentKey) . "' and parent_id = '" . (int)$rootId . "'");
        if (tep_db_num_rows($checkQuery) > 0) {
            $check = tep_db_fetch_array($checkQuery);
            $currentId = $check['access_control_list_id'];
        } else {
            $sql_data_array = [
                'parent_id' => (int)$rootId,
                'access_control_list_key' => $currentKey,
            ];
            tep_db_perform(TABLE_ACCESS_CONTROL_LIST, $sql_data_array);
            $currentId = tep_db_insert_id();
        }
        
        if (count($rules) > 0) {
            $response = self::rule($rules, $currentId, $selectedIds);
        } else {
            $response = true;
        }
        
        if (!in_array($currentId, $selectedIds)) {
            $response = false;
        }
        
        return $response;
    }

    public static function buildTree($selectedIds = '', $rootId = 0)
    {
        $response = [];
        if (is_string($selectedIds)) {
            $selectedIds = explode(",", $selectedIds);
        }
        if (!is_array($selectedIds)) {
            $selectedIds = [];
        }
        
        $accessQuery = tep_db_query("select * from " . TABLE_ACCESS_CONTROL_LIST . " where parent_id = '" . $rootId . "'");
        while ($access = tep_db_fetch_array( $accessQuery )) {
            $currentId = $access['access_control_list_id'];
            eval('$currentName =  ' . $access['access_control_list_key'] . ';');
            /*if (in_array($currentId, $selectedIds)) {
                $child = self::buildTree($selectedIds, $currentId);
            } else {
                $child = [];
            }*/
            $child = self::buildTree($selectedIds, $currentId);
            $response[] = [
                'id' => $currentId,
                'text' => $currentName,
                'selected' => in_array($currentId, $selectedIds),
                'child' => $child,
            ];
        }
        
        
        return $response;
    }
    
    public static function buildOverrideTree($selectedIds = '', $adminPersmissions = '', $rootId = 0)
    {
        $response = [];
        
        if (is_string($selectedIds)) {
            $selectedIds = explode(",", $selectedIds);
        }
        if (is_string($adminPersmissions)) {
            $adminPersmissions = explode(",", $adminPersmissions);
        }
        
        $accessQuery = tep_db_query("select * from " . TABLE_ACCESS_CONTROL_LIST . " where parent_id = '" . $rootId . "'");
        while ($access = tep_db_fetch_array( $accessQuery )) {
            $currentId = $access['access_control_list_id'];
            eval('$currentName =  ' . $access['access_control_list_key'] . ';');
            
            $showChilds = true;
            $selected = 0;
            if (in_array($currentId, $selectedIds)) {
                if (in_array(($currentId*-1), $adminPersmissions)) {
                    $currentName = '<font color="red">' . $currentName . '</font>';//red - removed
                    $showChilds = false;
                    $selected = 0;
                } else {
                    //normal mode
                    $showChilds = true;
                    $selected = 1;
                }
            } elseif (in_array($currentId, $adminPersmissions)) {
                $currentName = '<font color="green">' . $currentName . '</font>';//green - added
                $showChilds = true;
                $selected = 1;
            } else {
                //normal mode
                $showChilds = false;
                $selected = 0;
            }
            
            /*if ($showChilds) {
                $child = self::buildOverrideTree($selectedIds, $adminPersmissions, $currentId);
            } else {
                $child = [];
            }*/
            $child = self::buildOverrideTree($selectedIds, $adminPersmissions, $currentId);
            $response[] = [
                'id' => $currentId,
                'text' => $currentName,
                'selected' => $selected,
                'child' => $child,
            ];
        }
        
        return $response;
    }
    
    public static function checkAccess($rules) {
        if (false == \common\helpers\Acl::rule($rules)) {
            die('Access denied.');
        }
    }

    const extPath = '\\common\\extensions\\';

    public static function checkExtension($class, $method, $own_method = false) {
        $path = $class;
        if (strpos($class, '\\')){
            $parts = explode('\\', $class);
            $class = $parts[sizeof($parts)-1];
            $path = implode('\\', $parts);
        }
        if (!class_exists(self::extPath . $path . '\\' . $class)) {
            return false;
        }
        if (!method_exists(self::extPath . $path . '\\' . $class, $method)) {
            return false;
        }
        
        if ($own_method){
            $ref = new \ReflectionClass(self::extPath . $path . '\\' . $class);
            if ($ref->hasMethod($method)){
                $_method = $ref->getMethod($method);
                if ($_method->class == 'yii\base\Widget') return false;
            }
            if (!$ref->hasMethod($method)) return false;
        }
        
        return self::extPath . $path . '\\' . $class;
    }
    
    public static function get($class) {
        return self::extPath . $class . '\\' . $class;
    }
    
    public static function getExtensionWidgets($type){
        $widgets = [];
        $extensioins = new \DirectoryIterator(Yii::$aliases['@common'] . '/extensions/');
        foreach($extensioins as $ext){
            $class = $ext->getFilename();
            if ($_w = self::checkExtension($class, 'getWidgets')){
                $_widgets = $_w::getWidgets($type);
                if (is_array($_widgets) && count($_widgets)){
                    foreach($_widgets as $wd){
                        $widgets[] = $wd;
                    }
                }
                if (!is_array($widgets)) $widgets = [];
            }
        }
        return $widgets;
    }
    
}
