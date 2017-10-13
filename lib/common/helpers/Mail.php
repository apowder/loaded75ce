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

class Mail {

    public static function get_parsed_email_template($template_key, $email_params = '', $language_id = -1, $platform_id = -1, $aff_id = -1) {
        global $languages_id;
        if ($platform_id == -1){
            $platform_id = \common\classes\platform::currentId();
        } else {
            $platform_id = \common\classes\platform::validId($platform_id);
        }
        $data_query = tep_db_query("select ett.email_templates_subject, ett.email_templates_body from " . TABLE_EMAIL_TEMPLATES . " et, " . TABLE_EMAIL_TEMPLATES_TEXTS . " ett where et.email_templates_id = ett.email_templates_id and et.email_templates_key = '" . tep_db_input($template_key) . "' and ett.language_id = '" . (int) ($language_id > 0 ? $language_id : $languages_id) . "' and ett.affiliate_id = '" . (int) ($aff_id >= 0 ? $aff_id : 0) . "' and et.email_template_type = '" . (EMAIL_USE_HTML != 'true' ? 'plaintext' : 'html') . "' and ett.platform_id = '" . $platform_id . "'");
        $data = tep_db_fetch_array($data_query);

        $params = [
            'STORE_NAME' => '',
            'HTTP_HOST' => '',
            'STORE_OWNER_EMAIL_ADDRESS' => '',
            'CUSTOMER_EMAIL' => '',
            'CUSTOMER_FIRSTNAME' => '',
            'NEW_PASSWORD' => '',
            'USER_GREETING' => '',
            'ORDER_NUMBER' => '',
            'ORDER_DATE_LONG' => '',
            'ORDER_DATE_SHORT' => '',
            'BILLING_ADDRESS' => '',
            'DELIVERY_ADDRESS' => '',
            'PAYMENT_METHOD' => '',
            'ORDER_COMMENTS' => '',
            'NEW_ORDER_STATUS' => '',
            'ORDER_TOTALS' => '',
            'PRODUCTS_ORDERED' => '',
            'ORDER_INVOICE_URL' => '',
            'COUPON_AMOUNT' => '',
            'COUPON_NAME' => '',
            'COUPON_DESCRIPTION' => '',
            'COUPON_CODE' => '',
        ];
        if (is_array($email_params)) {
            foreach ($email_params as $key => $value) {
                $params[$key] = $value;
            }
        }
        if (is_array($params) && count($params) > 0) {
            $patterns = array();
            $replace = array();
            foreach ($params as $k => $v) {
                $patterns[] = "(##" . preg_quote($k) . "##)";
                $replace[] = str_replace('$', '/$/', $v);
            }

            $data['email_templates_subject'] = str_replace('/$/', '$', preg_replace($patterns, $replace, $data['email_templates_subject']));
            $data['email_templates_body'] = str_replace('/$/', '$', preg_replace($patterns, $replace, $data['email_templates_body']));
        }

        return array($data['email_templates_subject'], $data['email_templates_body']);
    }

    public static function get_email_templates_body($email_templates_id, $language_id, $platform_id = -1) {
        if ($platform_id == -1){
            $platform_id = \common\classes\platform::currentId();
        }
        $data_query = tep_db_query("select email_templates_body from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where email_templates_id = '" . (int) $email_templates_id . "' and language_id = '" . (int) $language_id . "' and platform_id = '" . $platform_id . "'");
        $data = tep_db_fetch_array($data_query);
        return $data['email_templates_body'];
    }

    public static function get_email_templates_subject($email_templates_id, $language_id, $platform_id = -1) {
        if ($platform_id == -1){
            $platform_id = \common\classes\platform::currentId();
        }
        $data_query = tep_db_query("select email_templates_subject from " . TABLE_EMAIL_TEMPLATES_TEXTS . " where email_templates_id = '" . (int) $email_templates_id . "' and language_id = '" . (int) $language_id . "' and platform_id = '" . $platform_id . "'");
        $data = tep_db_fetch_array($data_query);
        return $data['email_templates_subject'];
    }

    public static function send($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address, $email_params = array(), $headers = '') {
        if (SEND_EMAILS != 'true')
            return false;

        try {
            $message = new \common\classes\email(array('X-Mailer: True Loaded Mailer'));
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $text = strip_tags(preg_replace('/<br( \/)?>/ims', "\n", $email_text));
        if (EMAIL_USE_HTML == 'true') {
            
            $email_text = str_replace(array("\r\n", "\n", "\r"), '<br>', $email_text);

            if (function_exists('tep_catalog_href_link')) {
                $contents = @file_get_contents(tep_catalog_href_link('email-template'));
            } else {
                $contents = @file_get_contents(tep_href_link('email-template', '', 'NONSSL', false));
            }
            if (empty($contents)) {
                $contents = '##EMAIL_TEXT##';
            }
            $contents = str_replace(array("\r\n", "\n", "\r"), '', $contents);

            $email_subject = str_replace('$', '/$/', $email_subject);
            $email_text = str_replace('$', '/$/', $email_text);
            $search = array("'##EMAIL_TITLE##'i",
                "'##EMAIL_TEXT##'i");
            $replace = array($email_subject, $email_text);
            if (is_array($email_params) && count($email_params) > 0) {
                foreach ($email_params as $key => $value) {
                    $search[] = "'##" . $key . "##'i";
                    $replace[] = $value;
                }
            }
            $email_text = str_replace('/$/', '$', preg_replace($search, $replace, $contents));
			if (function_exists('tep_catalog_href_link')) {
				$_tmp_site_url= parse_url(tep_catalog_href_link('link'));
			} else {
				$_tmp_site_url = parse_url(tep_href_link('link'));
			}
            $HOST = $_tmp_site_url['scheme'] . '://' . $_tmp_site_url['host'];
            $PATH = rtrim(substr($_tmp_site_url['path'], 0, strpos($_tmp_site_url['path'], 'link')), '/');
            $email_text = preg_replace('/(<img[^>]+src=)"\/([^"]+)"/i', '$1"' . $HOST . '/$2"', $email_text);
            $email_text = preg_replace('/(<img[^>]+src=)"(?![a-z]{3,5}:\/\/)([^"]+)"/i', '$1"' . $HOST . $PATH . '/$2"', $email_text);
            //VL generally [a-z]{3,5} could be replaced either with https? (images by http(s) protocol in emails) or [a-z][a-z0-9\-+.]+ (by any protocol)
            $has_tag_p = (preg_match("/<p>/", $email_text) ? true : false);
            if ($has_tag_p) {
                $email_text = str_replace(array("\r\n", "\n", "\r"), '', $email_text);
                $email_text = preg_replace("/(<\/p>)(<br[\s\/]*>)(<p>)?/mi", "$1$3", $email_text);
            }

            $message->add_html($email_text, $text);
        } else {
            $message->add_text($text);
        }

        $message->build_message();
        $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $headers);
    }

    public static function sendPlain($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address, $email_params = array(), $headers = '') {
      if (SEND_EMAILS != 'true') {
        return false;
      }

      try {
        $message = new \common\classes\email(array('X-Mailer: True Loaded Mailer'));
      } catch (Exception $e) {
        echo $e->getMessage();
      }

      $message->add_text($email_text);
      $message->build_message();
      $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject, $headers);
    }

}
