<?php

/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace common\classes;

class language {

    var $languages, $catalog_languages, $browser_languages, $language;
    var $paltform_languages;
    var $dp_language;

    function __construct($lng = '') {
        $this->languages = array('ar' => 'ar([-_][a-zA-Z]{2})?|arabic',
            'bg' => 'bg|bulgarian',
            'br' => 'pt[-_]br|brazilian portuguese',
            'ca' => 'ca|catalan',
            'cs' => 'cs|czech',
            'da' => 'da|danish',
            'de' => 'de([-_][a-zA-Z]{2})?|german',
            'el' => 'el|greek',
            'en' => 'en([-_][a-zA-Z]{2})?|english',
            'es' => 'es([-_][a-zA-Z]{2})?|spanish',
            'et' => 'et|estonian',
            'fi' => 'fi|finnish',
            'fr' => 'fr([-_][a-zA-Z]{2})?|french',
            'gl' => 'gl|galician',
            'he' => 'he|hebrew',
            'hu' => 'hu|hungarian',
            'id' => 'id|indonesian',
            'it' => 'it|italian',
            'ja' => 'ja|japanese',
            'ko' => 'ko|korean',
            'ka' => 'ka|georgian',
            'lt' => 'lt|lithuanian',
            'lv' => 'lv|latvian',
            'nl' => 'nl([-_][a-zA-Z]{2})?|dutch',
            'no' => 'no|norwegian',
            'pl' => 'pl|polish',
            'pt' => 'pt([-_][a-zA-Z]{2})?|portuguese',
            'ro' => 'ro|romanian',
            'ru' => 'ru|russian',
            'sk' => 'sk|slovak',
            'sr' => 'sr|serbian',
            'sv' => 'sv|swedish',
            'th' => 'th|thai',
            'tr' => 'tr|turkish',
            'uk' => 'uk|ukrainian',
            'tw' => 'zh[-_]tw|chinese traditional',
            'zh' => 'zh|chinese simplified');

        $this->catalog_languages = array();
        $this->dp_language = \frontend\design\Info::platformDefLanguage();
        if (!$this->dp_language) {
            $this->dp_language = strtolower(DEFAULT_LANGUAGE);
        }
        $paltform_languages = \frontend\design\Info::platformLanguages();
        if (\frontend\design\Info::isTotallyAdmin()) {
            $paltform_languages = \yii\helpers\ArrayHelper::getColumn(\common\helpers\Language::get_languages(), 'code');
        }
        if (!is_array($paltform_languages) || count($paltform_languages) == 0) {
            $paltform_languages = array(strtolower(DEFAULT_LANGUAGE));
        }

        $this->paltform_languages = $paltform_languages;
        $this->catalog_languages = self::get_all();

        $this->browser_languages = '';
        $this->language = '';

        $this->set_language($lng);
    }

    public static function get_all() {
        static $languages = false;
        if (!is_array($languages)) {
            $languages = array();
            $languages_query = tep_db_query("select languages_id, name, code, image_svg as image, directory, locale, image_svg  from " . TABLE_LANGUAGES . " where languages_status = 1 order by sort_order");
            while ($language = tep_db_fetch_array($languages_query)) {
                $languages[strtolower($language['code'])] = array(
                    'id' => $language['languages_id'],
                    'code' => strtolower($language['code']),
                    'name' => $language['name'],
                    'image' => $language['image'],
                    'image_svg' => $language['image_svg'],
                    'directory' => $language['directory'],
                    'locale' => $language['locale'],
                );
            }
        }
        return $languages;
    }

    function set_language($language, $update = false) {
        global $login_id;
        if ((tep_not_null($language)) && (isset($this->catalog_languages[strtolower($language)])) && in_array(strtolower($language), $this->paltform_languages)) {
            $this->language = $this->catalog_languages[strtolower($language)];
        } else {
            if (in_array($this->dp_language, $this->paltform_languages)) {
                $this->language = $this->catalog_languages[$this->dp_language];
            } else {
                $this->language = $this->catalog_languages[strtolower(DEFAULT_LANGUAGE)];
            }
        }
        if (tep_session_is_registered('login_id') && $language != '' && $update) {
            tep_db_query("update " . TABLE_ADMIN . " set languages = '" . $this->language['code'] . "' where admin_id = '" . (int) $login_id . "'");
        }
    }

    function get_browser_language() {
        global $login_id;
        if (tep_session_is_registered('login_id')) {
            $check_languages_query = tep_db_query("select languages from " . TABLE_ADMIN . " where admin_id = '" . (int) $login_id . "'");
            if (tep_db_num_rows($check_languages_query) > 0) {
                $check_languages = tep_db_fetch_array($check_languages_query);
                if (isset($this->catalog_languages[$check_languages['languages']])) {
                    $this->language = $this->catalog_languages[$check_languages['languages']];
                    return true;
                }
            }
        }
        $this->browser_languages = explode(',', getenv('HTTP_ACCEPT_LANGUAGE'));

        for ($i = 0, $n = sizeof($this->browser_languages); $i < $n; $i++) {
            reset($this->languages);
            while (list($key, $value) = each($this->languages)) {
                if (preg_match('/^(' . $value . ')(;q=[0-9]\\.[0-9])?$/i', $this->browser_languages[$i]) && isset($this->catalog_languages[$key]) && in_array($key, $this->paltform_languages)) {
                    $this->language = $this->catalog_languages[$key];
                    break 2;
                }
            }
        }
    }

    function set_locale() {
        global $locale;
        @setlocale(LC_TIME, $locale . '.UTF-8');
        if (class_exists('\Yii', false)) {
            \Yii::$app->language = $locale;
        }
    }

    function load_vars() {
        global $languages_id;

        if (($id = platform::currentId()) > 0) {
            $query = tep_db_query("select configuration_key, configuration_value from " . TABLE_PLATFORM_FORMATS . " where platform_id = '" . (int) $id . "' and language_id = '" . (int) $languages_id . "'");
            if (tep_db_num_rows($query)) {
                while ($row = tep_db_fetch_array($query)) {
                    defined($row['configuration_key']) or define($row['configuration_key'], $row['configuration_value']);
                }
            }
        }

        $query = tep_db_query("select configuration_key, configuration_value from " . TABLE_LANGUAGES_FORMATS . " where language_id = '" . (int) $languages_id . "'");
        if (tep_db_num_rows($query)) {
            while ($row = tep_db_fetch_array($query)) {
                defined($row['configuration_key']) or define($row['configuration_key'], $row['configuration_value']);
            }
        }
    }

    public static function defaultId() {

        $query = tep_db_fetch_array(tep_db_query("select l.languages_id from " . TABLE_PLATFORMS . " p, " . TABLE_LANGUAGES . " l where p.platform_id = '" . (int) \common\classes\platform::currentId() . "' and l.code = p.default_language"));

        return $query['languages_id'];
    }

    public static function get_code($for_language = null) {
        if (is_null($for_language))
            $for_language = $_SESSION['languages_id'];

        $code = \frontend\design\Info::platformDefLanguage();
        if (!$code) {
            $code = strtolower(DEFAULT_LANGUAGE);
        }

        foreach (self::get_all() as $_info) {
            if (is_numeric($for_language) && (int) $_info['id'] == (int) $for_language) {
                $code = $_info['code'];
                break;
            } elseif (!is_numeric($for_language) && $_info['directory'] == $for_language) {
                $code = $_info['code'];
                break;
            }
        }

        return $code;
    }

}
