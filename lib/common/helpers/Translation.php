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

class Translation
{
    public static function init($entity = '', $language_id = '', $skipEmptyKeys = true)
    {
        global $languages_id, $language;

        if (!$language_id) $language_id = $languages_id;

        $translation_query = tep_db_query("select translation_key, translation_value from " . TABLE_TRANSLATION . " where translation_entity = '" . tep_db_input($entity) . "' and language_id = '" . (int)$language_id . "'");
        while ($translation = tep_db_fetch_array($translation_query)) {
            if ($skipEmptyKeys && empty($translation['translation_value'])) {
                continue;
            }
            
            if (!defined($translation['translation_key'])) {
              
                $translation['translation_value'] = self::checkIncludedConstants($translation['translation_value']);
                
                define($translation['translation_key'], $translation['translation_value']);
            }
        }
        
        $lang = tep_db_fetch_array(tep_db_query("select languages_id from " . TABLE_LANGUAGES . " where code ='" . DEFAULT_LANGUAGE . "'"));
        if (isset($lang['languages_id']) && $lang['languages_id'] !=$language_id) {
            $translation_query = tep_db_query("select translation_key, translation_value from " . TABLE_TRANSLATION . " where translation_entity = '" . tep_db_input($entity) . "' and language_id = '" . (int)$lang['languages_id'] . "'");
            while ($translation = tep_db_fetch_array($translation_query)) {
                if (!defined($translation['translation_key'])) {
                    
                    $translation['translation_value'] = self::checkIncludedConstants($translation['translation_value']);
                    
                    define($translation['translation_key'], $translation['translation_value']);
                }
            }
        }
    }
    
    public static function checkIncludedConstants($value){
        $value = preg_replace_callback(
           '/##(.*?)##/',
            function ($found) {
              return ( defined($found[1]) ? CONSTANT($found[1]) : '');
            },
            $value
        );
        return $value;
    }

    public static function getTranslationValue($translation_key, $translation_entity = '', $language_id = '')
    {
        global $languages_id;

        if (!$language_id) $language_id = $languages_id;
  
        $translation_query = tep_db_query("select translation_value from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        $translation = tep_db_fetch_array($translation_query);
        return $translation['translation_value'];
    }

    public static function setTranslationValue($translation_key, $translation_entity, $language_id, $translation_value)
    {
        $translation_query = tep_db_query("select * from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        if (tep_db_num_rows($translation_query) > 0) {
            $sql_data_array = [
                'translation_value' => $translation_value,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "'");
        } else {
            $hash = md5($translation_key . '-' . $translation_entity);
            $sql_data_array = [
                'language_id' => (int)$language_id,
                'translation_key' => $translation_key,
                'translation_entity' => $translation_entity,
                'translation_value' => $translation_value,
                'hash' => $hash,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array);
        }
    }
    
    public static function replaceTranslationValueByKey($translation_key, $translation_entity, $language_id, $translation_value)
    {
        $translation_query = tep_db_query("select * from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        if (tep_db_num_rows($translation_query) == 0) {
            $hash = md5($translation_key . '-' . $translation_entity);
            $sql_data_array = [
                'language_id' => (int)$language_id,
                'translation_key' => $translation_key,
                'translation_entity' => $translation_entity,
                'translation_value' => $translation_value,
                'hash' => $hash,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array);
        }
        $sql_data_array = [
            'translation_value' => $translation_value,
        ];
        tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_key = '" . tep_db_input($translation_key) . "'");
    }
    
    public static function replaceTranslationValueByOldValue($translation_key, $translation_entity, $language_id, $translation_value)
    {
        $translation_query = tep_db_query("select * from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        if (tep_db_num_rows($translation_query) > 0) {
            $translation = tep_db_fetch_array($translation_query);
            $old_translation_value = $translation['translation_value'];
            if (!empty($old_translation_value)) {
                $sql_data_array = [
                    'translation_value' => $translation_value,
                ];
                tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_value = '" . tep_db_input($old_translation_value) . "'");
            } else {
                $sql_data_array = [
                    'translation_value' => $translation_value,
                ];
                tep_db_perform(TABLE_TRANSLATION, $sql_data_array, 'update', "language_id = '" . (int)$language_id . "' and translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "'");
            }
        } else {
            $hash = md5($translation_key . '-' . $translation_entity);
            $sql_data_array = [
                'language_id' => (int)$language_id,
                'translation_key' => $translation_key,
                'translation_entity' => $translation_entity,
                'translation_value' => $translation_value,
                'hash' => $hash,
            ];
            tep_db_perform(TABLE_TRANSLATION, $sql_data_array);
        }
    }
    
    public static function loadJS($translation_entity, $language_id = 0){
      global $languages_id, $lng;
      
      $language_id =  !$language_id ? $languages_id : $language_id;

      $translation_query = tep_db_query("select t1.translation_key, if(length(t1.translation_value)>0, t1.translation_value, t2.translation_value) as translation_value from " . TABLE_TRANSLATION . " t1 left join " . TABLE_TRANSLATION . " t2 on (t2.language_id = (select l.languages_id from " . TABLE_LANGUAGES . " l where l.code = '" . DEFAULT_LANGUAGE . "') and t1.translation_key = t2.translation_key and t1.translation_entity = t2.translation_entity) where t1.translation_entity = '" . tep_db_input($translation_entity) . "' and t1.language_id = '" . (int)$language_id . "'");

      $translations = [];
      
      if (tep_db_num_rows($translation_query)){
            while ($translation = tep_db_fetch_array($translation_query)) {
                if (!isset($translations[$translation['translation_key']])) {
                    $translations[$translation['translation_key']] = $translation['translation_value'];
                }
            }        
      }
      
      return $translations;
    }
    
    public static function isTranslated($translation_key, $translation_entity = '', $language_id = '')
    {
        global $languages_id;

        if (!$language_id) $language_id = $languages_id;

        $translation_query = tep_db_query("select translated from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        $translation = tep_db_fetch_array($translation_query);
        return $translation['translated'];
        
    }

    public static function setTranslated($translation_key, $translation_entity, $language_id, $status = 0)
    {
      tep_db_query("update " . TABLE_TRANSLATION . " set translated = " . (int)$status . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
    } 
    
    public static function isChecked($translation_key, $translation_entity = '', $language_id = '')
    {
        global $languages_id;

        if (!$language_id) $language_id = $languages_id;

        $translation_query = tep_db_query("select checked from " . TABLE_TRANSLATION . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
        $translation = tep_db_fetch_array($translation_query);
        return $translation['checked'];
        
    }    
    
    public static function setChecked($translation_key, $translation_entity, $language_id, $status = 0)
    {
      tep_db_query("update " . TABLE_TRANSLATION . " set checked = " . (int)$status . " where translation_key = '" . tep_db_input($translation_key) . "' and translation_entity = '" . tep_db_input($translation_entity) . "' and language_id = '" . (int)$language_id . "'");
    }
}
