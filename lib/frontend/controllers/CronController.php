<?php
namespace frontend\controllers;

use Yii;

/**
 * Site controller
 */
class CronController extends Sceleton
{

    public function actionIndex()
    {
    }
/*
    public function actionNotifyBackInStock()
    {
      global $languages_id;
      $products = tep_db_query("select distinct products_notify_products_id from " . TABLE_PRODUCTS_NOTIFY . " where products_notify_sent is null");
      while ($product = tep_db_fetch_array($products)) {
        if (tep_get_products_stock($product['products_notify_products_id']) > 0) {
          $products_id = tep_get_prid($product['products_notify_products_id']);
          $notifies = tep_db_query("select * from " . TABLE_PRODUCTS_NOTIFY . " where products_notify_products_id = '" . tep_db_input($product['products_notify_products_id']) . "' and products_notify_sent is null");
          $product = tep_db_fetch_array(tep_db_query("select p.products_image, pd.products_name, p.products_id as prid from " . TABLE_PRODUCTS . " as p left join " . TABLE_PRODUCTS_DESCRIPTION . " as pd on pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' where p.products_id = '" . (int)$products_id . "' and p.products_status = '1'"));
          if (!$product) {
            continue;
          }
          while ($notify = tep_db_fetch_array($notifies)) {
            // {{
            $email_params = array();
            $email_params['STORE_NAME'] = STORE_NAME;
            $email_params['CUSTOMER_NAME'] = ($notify['products_notify_name'] ? $notify['products_notify_name'] : 'Customer');
            $email_params['PRODUCT_NAME'] = tep_get_products_name($products_id);
            $email_params['PRODUCT_URL'] = tep_href_link('catalog/product', 'products_id='. $products_id);
            $email_params['PRODUCT_IMAGE'] = \common\classes\Images::getImageUrl($products_id, 'Small');
            list($email_subject, $email_text) = get_parsed_email_template('Notify Back in Stock', $email_params);
            // }}
            tep_mail($notify['products_notify_name'], $notify['products_notify_email'], $email_subject, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            tep_db_query("update " . TABLE_PRODUCTS_NOTIFY . " set products_notify_sent = now() where products_notify_id = '" . tep_db_input($notify['products_notify_id']) . "'");
          }
        }
      }
    }
*/
    public function actionGooglePagespeedCrawl()
    {
        global $languages_id;
      tep_db_query("CREATE TABLE IF NOT EXISTS google_pagespeed_urls (
        id int(11) NOT NULL AUTO_INCREMENT,
        google_pagespeed_url varchar(255) NOT NULL,
        date_processed datetime DEFAULT NULL,
        google_pagespeed_result TEXT NOT NULL,
        PRIMARY KEY (id),
        INDEX idx_google_pagespeed_url (google_pagespeed_url)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

      // Home page
      google_pagespeed_url_process(tep_href_link(FILENAME_DEFAULT));

      // Information pages
      $information_query = tep_db_query("select i.information_id, i.info_title, i.page, i.page_type from " . TABLE_INFORMATION ." i where i.visible = '1' and i.languages_id = " . (int)$languages_id . " and i.affiliate_id = 0 order by i.v_order");
      while ($information = tep_db_fetch_array($information_query)) {
        $url = tep_href_link(FILENAME_INFORMATION, 'info_id=' . $information['information_id'], 'NONSSL', false);
        google_pagespeed_url_process($url);
      }

      // Categories pages
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c inner join " . TABLE_PLATFORMS_CATEGORIES . " plc on c.categories_id = plc.categories_id  and plc.platform_id = '" . \common\classes\platform::currentId() . "' where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.categories_status = '1' and cd.affiliate_id = '0' order by c.parent_id, sort_order, categories_name");
      while ($categories = tep_db_fetch_array($categories_query)) {
        $url = tep_href_link('catalog', \common\helpers\Categories::get_path($categories['categories_id']), 'NONSSL', false);
        google_pagespeed_url_process($url);
      }

      // Products pages
      $products_sql = \frontend\design\ListingSql::query(array('filename' => FILENAME_PRODUCTS_NEW, 'sort' => 'dd'));
      $products_query = tep_db_query($products_sql);
      while ($products = tep_db_fetch_array($products_query)) {
        $url = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products['products_id'], 'NONSSL', false);
        google_pagespeed_url_process($url);
      }

      // Additional pages
      if (defined('GOOGLE_PAGESPEED_CRAWL_PAGES') && tep_not_null(GOOGLE_PAGESPEED_CRAWL_PAGES)) {
        foreach(explode("\n", GOOGLE_PAGESPEED_CRAWL_PAGES) as $page) {
          $url = tep_href_link(trim($page), '', 'NONSSL', false);
          google_pagespeed_url_process($url);
        }
      }
    }

}

// =============================================================

function google_pagespeed_url_process($url) {
  $check = tep_db_fetch_array(tep_db_query("select id from google_pagespeed_urls where google_pagespeed_url = '" . tep_db_input($url) . "' and to_days(date_processed) = to_days(now())"));
  if ( !($check['id'] > 0) ) {
    echo $result = file_get_contents(tep_href_link('google_pagespeed.php', 'page=' . str_replace(HTTP_SERVER . DIR_WS_HTTP_CATALOG, '', $url), 'NONSSL', false));
    tep_db_query("insert into google_pagespeed_urls set google_pagespeed_url = '" . tep_db_input($url) . "', google_pagespeed_result = '" . tep_db_input($result) . "', date_processed = now()");
    exit;
  }
}

