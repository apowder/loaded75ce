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

class Output {

    public static function parse_input_field_data($data, $parse) {
        return strtr(trim($data), $parse);
    }

    public static function output_string($string, $translate = false, $protected = false) {
        if ($protected == true) {
            return htmlspecialchars($string);
        } else {
            if ($translate == false) {
                return self::parse_input_field_data($string, array('"' => '&quot;'));
            } else {
                return self::parse_input_field_data($string, $translate);
            }
        }
    }

    public static function output_string_protected($string) {
        return self::output_string($string, false, true);
    }
    
    public static function break_string($string, $len, $break_char = '-') {
        $l = 0;
        $output = '';
        for ($i = 0, $n = strlen($string); $i < $n; $i++) {
            $char = substr($string, $i, 1);
            if ($char != ' ') {
                $l++;
            } else {
                $l = 0;
            }
            if ($l > $len) {
                $l = 1;
                $output .= $break_char;
            }
            $output .= $char;
        }

        return $output;
    }

    public static function get_all_get_params($exclude_array = '', $as_fields = false) {
        global $HTTP_GET_VARS;

        if (!is_array($exclude_array))
            $exclude_array = array();

        $get_url = '';
        if (is_array($HTTP_GET_VARS) && (sizeof($HTTP_GET_VARS) > 0)) {
            reset($HTTP_GET_VARS);
            while (list($key, $value) = each($HTTP_GET_VARS)) {
                if (((!is_array($value) && strlen($value) > 0) || (is_array($value) && sizeof($value) > 0)) && ($key != session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y')) {
                    if (is_array($value)) {
                        for ($i = 0, $n = sizeof($value); $i < $n; $i++) {
                            if ($as_fields) {
                                $get_url .= tep_draw_hidden_field($key . '[]', $value[$i]);
                            } else {
                                $get_url .= $key . rawurlencode('[]') . '=' . rawurlencode(stripslashes($value[$i])) . '&';
                            }
                        }
                    } else {
                        if ($as_fields) {
                            $get_url .= tep_draw_hidden_field($key, $value);
                        } else {
                            $get_url .= $key . '=' . rawurlencode(stripslashes($value)) . '&';
                        }
                    }
                }
            }
        }
        return $get_url;
    }

    public static function parse_search_string($search_str = '', &$objects, $msearch_enable = MSEARCH_ENABLE) {
        $search_str = stripslashes(trim(strtolower($search_str)));
        $pieces = preg_split('/[\s]+/', $search_str);
        $objects = array();
        $tmpstring = '';
        $flag = '';
        for ($k = 0; $k < count($pieces); $k++) {
            while (substr($pieces[$k], 0, 1) == '(') {
                $objects[] = '(';
                if (strlen($pieces[$k]) > 1) {
                    $pieces[$k] = substr($pieces[$k], 1);
                } else {
                    $pieces[$k] = '';
                }
            }
            $post_objects = array();
            while (substr($pieces[$k], -1) == ')') {
                $post_objects[] = ')';
                if (strlen($pieces[$k]) > 1) {
                    $pieces[$k] = substr($pieces[$k], 0, -1);
                } else {
                    $pieces[$k] = '';
                }
            }
            if ((substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"')) {
                $objects[] = trim($pieces[$k]);
                for ($j = 0; $j < count($post_objects); $j++) {
                    $objects[] = $post_objects[$j];
                }
            } else {
                $tmpstring = trim(str_replace('"', ' ', $pieces[$k]));
                if (substr($pieces[$k], -1) == '"') {
                    $flag = 'off';
                    $objects[] = trim($pieces[$k]);
                    for ($j = 0; $j < count($post_objects); $j++) {
                        $objects[] = $post_objects[$j];
                    }
                    unset($tmpstring);
                    continue;
                }
                $flag = 'on';
                $k++;
                while (($flag == 'on') && ($k < count($pieces))) {
                    while (substr($pieces[$k], -1) == ')') {
                        $post_objects[] = ')';
                        if (strlen($pieces[$k]) > 1) {
                            $pieces[$k] = substr($pieces[$k], 0, -1);
                        } else {
                            $pieces[$k] = '';
                        }
                    }
                    if (substr($pieces[$k], -1) != '"') {
                        $tmpstring .= ' ' . $pieces[$k];
                        $k++;
                        continue;
                    } else {
                        $tmpstring .= ' ' . trim(str_replace('"', ' ', $pieces[$k]));
                        $objects[] = trim($tmpstring);
                        for ($j = 0; $j < count($post_objects); $j++) {
                            $objects[] = $post_objects[$j];
                        }
                        unset($tmpstring);
                        $flag = 'off';
                    }
                }
            }
        }
        if ($msearch_enable == 'true') {
            $pares = array();
            for ($i = 0; $i < sizeof($objects); $i++) {
                $objects[$i] = str_replace(array(",", ";", ".", "&", "!", ":", "\""), array("", "", "", "", "", "", ""), $objects[$i]);
                if (($objects[$i] == 'and') || ($objects[$i] == 'or') || ($objects[$i] == '(') || ($objects[$i] == ')')) {
                    $pares[] = $objects[$i];
                } else {
                    $pieces = preg_split('/[\s]+/', $objects[$i]);
                    foreach ($pieces as $piece) {
                        if (strlen($piece) >= MSEARCH_WORD_LENGTH) {
                            $ks_hash = tep_db_fetch_array(tep_db_query("select soundex('" . addslashes($piece) . "') as sx"));
                            $pares[] = $ks_hash["sx"];
                        } else {
                            $pares[] = '';
                        }
                    }
                }
            }
            $objects = $pares;
        }
        $temp = array();
        for ($i = 0; $i < (count($objects) - 1); $i++) {
            $temp[] = $objects[$i];
            if (($objects[$i] != 'and') &&
                    ($objects[$i] != 'or') &&
                    ($objects[$i] != '(') &&
                    ($objects[$i + 1] != 'and') &&
                    ($objects[$i + 1] != 'or') &&
                    ($objects[$i + 1] != ')')) {
                $temp[] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
            }
        }
        $temp[] = $objects[$i];
        $objects = $temp;
        $keyword_count = 0;
        $operator_count = 0;
        $balance = 0;
        for ($i = 0; $i < count($objects); $i++) {
            if ($objects[$i] == '(')
                $balance --;
            if ($objects[$i] == ')')
                $balance ++;
            if (($objects[$i] == 'and') || ($objects[$i] == 'or')) {
                $operator_count ++;
            } elseif (($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')')) {
                $keyword_count ++;
            }
        }
        if (($operator_count < $keyword_count) && ($balance == 0)) {
            return true;
        } else {
            return false;
        }
    }

    public static function array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
        if (!is_array($exclude))
            $exclude = array();

        $get_string = '';
        if (sizeof($array) > 0) {
            while (list($key, $value) = each($array)) {
                if ((!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y')) {
                    $get_string .= $key . $equals . $value . $separator;
                }
            }
            $remove_chars = strlen($separator);
            $get_string = substr($get_string, 0, -$remove_chars);
        }

        return $get_string;
    }

    public static function highlight_text($text, $search_terms) {
        for ($i = 0; $i < sizeof($search_terms); $i++) {
            switch ($search_terms[$i]) {
                case '(':
                case ')':
                case 'and':
                case 'or':
                    break;
                default:
                    if (MSEARCH_HIGHLIGHT_ENABLE == 'true') {
                        $text = preg_replace('/' . preg_quote($search_terms[$i], "/") . '/i', '<span style="background:' . MSEARCH_HIGHLIGHT_BGCOLOR . '">\\0</span>', $text);
                    }
            }
        }
        return $text;
    }

    public static function unhtmlentities($string) {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        return strtr($string, $trans_tbl);
    }

    public static function get_clickable_link($tep_href_link) {
        if (EMAIL_USE_HTML == 'true') {
            if (\common\helpers\Validations::validate_email($tep_href_link)) {
                return '<a href="mailto:' . $tep_href_link . '">' . $tep_href_link . '</a>';
            }
            return '<a href="' . $tep_href_link . '">' . $tep_href_link . '</a>';
        }
        return $tep_href_link;
    }

}
