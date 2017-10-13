<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\design\Info;

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Sceleton extends Controller {

    public $enableCsrfValidation = false;
    
    /**
     * @var array the breadcrumbs of the current page.
     */
    public $navigation = array();

    /**
     * @var stdClass the variables for smarty.
     */
    public $view = null;

    /**
     * Selected items in menu
     * @var array 
     */
    public $selectedMenu = array();
    
    function __construct($id,$module=null) {
        global $language, $languages_id;

        $params = Yii::$app->request->get();
      
        if ($params['theme_name']) {
          $theme = $params['theme_name'];
        } else {
            /**
             * Switch the theme via the platform 
             */
            //PLATFORM_ID
            $theme_array = tep_db_fetch_array(tep_db_query("select t.theme_name from " . TABLE_PLATFORMS_TO_THEMES . " AS p2t INNER JOIN " . TABLE_THEMES . " as t ON (p2t.theme_id=t.id) where p2t.is_default = 1 and p2t.platform_id = " . (int)PLATFORM_ID));
            //$theme_array = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES . " where is_default = 1"));
            if ($theme_array['theme_name']){
                $theme = $theme_array['theme_name'];
            } else {
                $theme = 'theme-1';
            }

        }
        $themes_path = $this->themesPath(array($theme));
        $themes_path[] = 'basic';
        $GLOBALS['themeMap'] = $themes_path;

        $pathMapView = array();
        foreach ($themes_path as $item){
          $pathMapView[] = '@app/themes/' . $item;
        }
        Yii::$app->view->theme = new \yii\base\Theme([
          'pathMap' => [
            '@app/views' => $pathMapView
          ],
          'baseUrl' => '@web/themes/' . $theme,
        ]);

        Yii::setAlias('@theme', '@app/themes/' . $theme);
        Yii::setAlias('@webTheme', '@web/themes/' . $theme);
        Yii::setAlias('@themeImages', '@web/themes/' . $theme . '/img/');
        define('DIR_WS_THEME', Yii::getAlias('@webTheme'));
        define('THEME_NAME', $theme);
        define('DIR_WS_THEME_IMAGES', Yii::getAlias('@themeImages'));

        global $request_type;
        define('BASE_URL', (($request_type == 'SSL') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_HTTP_CATALOG));

        \Yii::$app->view->title = \Yii::$app->name;
        $this->view = new \stdClass();
        parent::__construct($id,$module);

        if ($_GET['gl']){
            $_SESSION['gl'] = tep_db_prepare_input($_GET['gl']);
        }
        if (!isset($_SESSION['gl'])){
            $_SESSION['gl'] = 'grid';
        }

        if ($_GET['max_items']){
            $_SESSION['max_items'] = tep_db_prepare_input($_GET['max_items']);
        }
        
        \common\models\Socials::loadSocialAddons(PLATFORM_ID);
        //$this->setMeta();
    }

    public function themesPath ($themes_path){
      $query = tep_db_fetch_array(tep_db_query("select parent_theme from " . TABLE_THEMES . " where theme_name = '" . tep_db_input($themes_path[count($themes_path)-1]) . "'"));
      if ($query['parent_theme']){
        $themes_path[] = $query['parent_theme'];
        $themes_path = $this->themesPath($themes_path);
      }
      return $themes_path;
    }

    public function runAction($id, $params = [])
    {
      if (!Yii::$app->request->isAjax){
        $this->setMeta($id, $params);
      }        
      return parent::runAction($id, $params);
    }


    protected function setMeta($id, $params)
    {

        global $languages_id, $currency_id, $customer_groups_id;

        $params = !is_array($params)?array():$params;
        $controller = $this->id;
        $full_action = $this->id.'/'.$id;
//        echo '<pre>'; var_dump($full_action, $params); echo '</pre>';
        // catalog/featured_products
        //catalog/specials
        //catalog/products_new
        // reviews/ || reviews/index

        $get_def_q = tep_db_query(
          "select m.meta_tags_key, m.meta_tags_value ".
          "from " . TABLE_META_TAGS . " m ".
          "where m.language_id = '".(int)$languages_id."' and m.platform_id='".\common\classes\platform::currentId()."' and m.affiliate_id = 0"
        );
        if (tep_db_num_rows($get_def_q)>0) {
            while($get_def = tep_db_fetch_array($get_def_q)) {
                if ( defined(trim($get_def['meta_tags_key'])) ) continue;
                define(trim($get_def['meta_tags_key']), $get_def['meta_tags_value']);
            }
        }

        $HEAD_DESC_TAG_ALL = defined('HEAD_DESC_TAG_ALL')?HEAD_DESC_TAG_ALL:'';
        $HEAD_KEY_TAG_ALL = defined('HEAD_KEY_TAG_ALL')?HEAD_KEY_TAG_ALL:'';
        $HEAD_TITLE_TAG_ALL = defined('HEAD_TITLE_TAG_ALL')?HEAD_TITLE_TAG_ALL:'';

        $the_desc = defined('HEAD_DESC_TAG_ALL')?HEAD_DESC_TAG_ALL:'';
        $the_key_words = defined('HEAD_KEY_TAG_ALL')?HEAD_KEY_TAG_ALL:'';
        $the_title = defined('HEAD_TITLE_TAG_ALL')?HEAD_TITLE_TAG_ALL:'';

        global $current_category_id;
        $_current_category_id = $current_category_id;
        
        //$with_category_path = (isset($params['cPath']) && !empty($params['cPath']));
        $with_category = isset($_current_category_id) && $_current_category_id>0;
        $with_manufacturer = (isset($params['manufacturers_id']) && !empty($params['manufacturers_id']));
        
        // Define specific settings per page:
        switch (true) {
            // Index page
            case $full_action=='index/' || $full_action=='index/index' || ($full_action=='catalog/index' && !($with_category || $with_manufacturer) ) :
                $the_title = (defined('HEAD_TITLE_TAG_DEFAULT') && tep_not_null(HEAD_TITLE_TAG_DEFAULT)?HEAD_TITLE_TAG_DEFAULT:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_DEFAULT') && tep_not_null(HEAD_KEY_TAG_DEFAULT)?HEAD_KEY_TAG_DEFAULT:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_DEFAULT') && tep_not_null(HEAD_DESC_TAG_DEFAULT)?HEAD_DESC_TAG_DEFAULT:$HEAD_DESC_TAG_ALL);
                break;
            // categories
            case $full_action=='catalog/index' && ($with_category || $with_manufacturer):
                $the_data = false;
                if ( $with_category ) {
                    $the_category_query = tep_db_query(
                      "select if(length(cd1.categories_name), cd1.categories_name, cd.categories_name) AS name, ".
                      " if(length(cd1.categories_head_title_tag), cd1.categories_head_title_tag, cd.categories_head_title_tag) as head_title_tag, ".
                      " if(length(cd1.categories_head_desc_tag), cd1.categories_head_desc_tag, cd.categories_head_desc_tag) as head_desc_tag, ".
                      " if(length(cd1.categories_head_keywords_tag), cd1.categories_head_keywords_tag, cd.categories_head_keywords_tag) as head_keywords_tag ".
                      "from " . TABLE_CATEGORIES_DESCRIPTION . " cd, " . TABLE_CATEGORIES . " c ".
                      " left join " . TABLE_CATEGORIES_DESCRIPTION . " cd1 on cd1.categories_id = c.categories_id and cd1.language_id='" . (int)$languages_id . "' and cd1.affiliate_id = '" . (isset($_SESSION['affiliate_ref'])?(int)$_SESSION['affiliate_ref']:0) . "' ".
                      "where c.categories_id = '" . (int)$_current_category_id . "' and cd.categories_id = c.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int)$languages_id . "'"
                    );
                    if ( tep_db_num_rows($the_category_query) ) {
                        $the_data = tep_db_fetch_array($the_category_query);
                    }
                }else{
                    $the_manufacturers_query= tep_db_query(
                      "select m.manufacturers_name AS name, ".
                      " mi.manufacturers_meta_description AS head_desc_tag, ".
                      " mi.manufacturers_meta_key AS head_keywords_tag, ".
                      " mi.manufacturers_meta_title AS head_title_tag ".
                      "from " . TABLE_MANUFACTURERS . " m ".
                      " left join ".TABLE_MANUFACTURERS_INFO." mi ON mi.manufacturers_id = m.manufacturers_id AND mi.languages_id='".(int)$languages_id."' ".
                      "where m.manufacturers_id = '" . (int)$params['manufacturers_id'] . "'"
                    );
                    if ( tep_db_num_rows($the_manufacturers_query) ) {
                        $the_data = tep_db_fetch_array($the_manufacturers_query);
                    }
                }

                if ( !is_array($the_data) || empty($the_data['head_title_tag'])) {
                    $the_title= (defined('HEAD_TITLE_TAG_DEFAULT') && tep_not_null(HEAD_TITLE_TAG_DEFAULT)?HEAD_TITLE_TAG_DEFAULT:$HEAD_TITLE_TAG_ALL) . ' ' .
                      $the_data['name'];
                } else {
                    $the_title = $the_data['head_title_tag'];
                    /*
                    $the_title = (defined('HEAD_TITLE_TAG_DEFAULT') && tep_not_null(HEAD_TITLE_TAG_DEFAULT)?HEAD_TITLE_TAG_DEFAULT:$HEAD_TITLE_TAG_ALL) . ' ' .
                      \common\helpers\HeaderTags::seo_correct($the_data['head_title_tag'], array(
                        'products_name'=>$the_data['name'],
                        'manufacturers_name' => '',
                        'breadcrumbmone' => (isset($breadcrumb) && is_object($breadcrumb))?$breadcrumb->seo_trail():'') , false);
                    */
                }

                if ( !is_array($the_data) || empty($the_data['head_keywords_tag'])) {
                    $the_key_words = $the_data['name'].', '.(defined('HEAD_KEY_TAG_DEFAULT') && tep_not_null(HEAD_KEY_TAG_DEFAULT)?HEAD_KEY_TAG_DEFAULT:$HEAD_KEY_TAG_ALL);
                } else {
                    $the_key_words = $the_data['head_keywords_tag'];
                    /*
                    $the_key_words= \common\helpers\HeaderTags::seo_correct($the_data['head_keywords_tag'], array('products_name'=>$the_data['name'], 'manufacturers_name' => '','breadcrumbmone' => (isset($breadcrumb) && is_object($breadcrumb))?$breadcrumb->seo_trail():'' ), false) .
                      ', '.(defined('HEAD_KEY_TAG_DEFAULT') && tep_not_null(HEAD_KEY_TAG_DEFAULT)?HEAD_KEY_TAG_DEFAULT:$HEAD_KEY_TAG_ALL);
                    */
                }

                if ( !is_array($the_data) || empty($the_data['head_desc_tag'])) {
                    $the_desc= $the_data['name'].' '.(defined('HEAD_DESC_TAG_DEFAULT') && tep_not_null(HEAD_DESC_TAG_DEFAULT)?HEAD_DESC_TAG_DEFAULT:$HEAD_DESC_TAG_ALL);
                } else {
                    $the_desc= $the_data['head_desc_tag'];
                    /*
                    $the_desc= \common\helpers\HeaderTags::seo_correct($the_data['head_desc_tag'], array('products_name'=>$the_data['name'], 'manufacturers_name' => '','breadcrumbmone' => (isset($breadcrumb) && is_object($breadcrumb))?$breadcrumb->seo_trail():''), false) .
                      ' '.(defined('HEAD_DESC_TAG_DEFAULT') && tep_not_null(HEAD_DESC_TAG_DEFAULT)?HEAD_DESC_TAG_DEFAULT:$HEAD_DESC_TAG_ALL);
                    */
                }
                break;

            // PRODUCT_INFO
            case ( $full_action=='catalog/product' &&  isset($params['products_id']) && $params['products_id']>0 ):
                $the_product_info = false;
                if (USE_MARKET_PRICES == 'True' || CUSTOMERS_GROUPS_ENABLE == 'True'){
                    $the_product_info_query = tep_db_query("select pd.language_id, p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description, if(length(pd1.products_head_title_tag), pd1.products_head_title_tag, pd.products_head_title_tag) as products_head_title_tag, if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) as products_head_keywords_tag, if(length(pd1.products_head_desc_tag), pd1.products_head_desc_tag, pd.products_head_desc_tag) as products_head_desc_tag, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (isset($_SESSION['affiliate_ref'])?(int)$_SESSION['affiliate_ref']:0) . "' " . " left join " . TABLE_PRODUCTS_PRICES . " pp on p.products_id = pp.products_id and pp.groups_id = '" . (int)$customer_groups_id . "' and pp.currencies_id = '" . (USE_MARKET_PRICES == 'True'?$currency_id:'0'). "' where p.products_id = '" . (int)$params['products_id'] . "'  " . " and if(pp.products_group_price is null, 1, pp.products_group_price != -1 ) and pd.products_id = '" . (int)$params['products_id'] . "'" . " and pd.affiliate_id = 0 and pd.language_id ='" .  (int)$languages_id . "'");
                }else{
                    $the_product_info_query = tep_db_query("select pd.language_id, p.products_id, if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name, if(length(pd1.products_description), pd1.products_description, pd.products_description) as products_description, if(length(pd1.products_head_title_tag), pd1.products_head_title_tag, pd.products_head_title_tag) as products_head_title_tag, if(length(pd1.products_head_keywords_tag), pd1.products_head_keywords_tag, pd.products_head_keywords_tag) as products_head_keywords_tag, if(length(pd1.products_head_desc_tag), pd1.products_head_desc_tag, pd.products_head_desc_tag) as products_head_desc_tag, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (isset($_SESSION['affiliate_ref'])?(int)$_SESSION['affiliate_ref']:0) . "'  " . " where p.products_id = '" . (int)$params['products_id'] . "' " . " and pd.products_id = '" . (int)$params['products_id'] . "'" . " and pd.affiliate_id = 0 and pd.language_id ='" .  (int)$languages_id . "'");
                }
                if ( tep_db_num_rows($the_product_info_query)>0 ) {
                    $the_product_info = tep_db_fetch_array($the_product_info_query);
                }

                if (!is_array($the_product_info) || empty($the_product_info['products_head_title_tag'])) {
                    $the_title= (defined('HEAD_TITLE_TAG_PRODUCT_INFO') && tep_not_null(HEAD_TITLE_TAG_PRODUCT_INFO)?HEAD_TITLE_TAG_PRODUCT_INFO:$HEAD_TITLE_TAG_ALL) . ' ' .$the_product_info['products_name'];
                } else {
                    $the_title = $the_product_info['products_head_title_tag'];
                    /*
                    $the_title= (defined('HEAD_TITLE_TAG_PRODUCT_INFO') && tep_not_null(HEAD_TITLE_TAG_PRODUCT_INFO)?HEAD_TITLE_TAG_PRODUCT_INFO:$HEAD_TITLE_TAG_ALL) . ' ' .
                      \common\helpers\HeaderTags::seo_correct($the_product_info['products_head_title_tag'], array('products_name'=>$the_product_info['products_name'], 'manufacturers_name' => \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $the_product_info['manufacturers_id']), 'breadcrumbmone' => (isset($breadcrumb) && is_object($breadcrumb))?$breadcrumb->seo_trail():''));;
                    */
                }

                if (!is_array($the_product_info) || empty($the_product_info['products_head_keywords_tag'])) {
                    $the_key_words = $the_product_info['products_name'] . ', ' . (defined('HEAD_KEY_TAG_PRODUCT_INFO') && tep_not_null(HEAD_KEY_TAG_PRODUCT_INFO)?HEAD_KEY_TAG_PRODUCT_INFO:$HEAD_KEY_TAG_ALL);
                } else {
                    $the_key_words = $the_product_info['products_head_keywords_tag'];
                    /*
                    $the_key_words = \common\helpers\HeaderTags::seo_correct($the_product_info['products_head_keywords_tag'], array('products_name'=>$the_product_info['products_name'], 'manufacturers_name' => \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $the_product_info['manufacturers_id']),'breadcrumbmone' => (isset($breadcrumb) && is_object($breadcrumb))?$breadcrumb->seo_trail():''))
                      . ', ' . (defined('HEAD_KEY_TAG_PRODUCT_INFO') && tep_not_null(HEAD_KEY_TAG_PRODUCT_INFO)?HEAD_KEY_TAG_PRODUCT_INFO:$HEAD_KEY_TAG_ALL);
                    */
                }

                if (!is_array($the_product_info) || empty($the_product_info['products_head_desc_tag'])) {
                    $the_desc = $the_product_info['products_name'] . ' ' . (defined('HEAD_DESC_TAG_PRODUCT_INFO') && tep_not_null(HEAD_DESC_TAG_PRODUCT_INFO)?HEAD_DESC_TAG_PRODUCT_INFO:$HEAD_DESC_TAG_ALL);
                } else {
                    $the_desc = $the_product_info['products_head_desc_tag'];
                    /*
                    $the_desc = \common\helpers\HeaderTags::seo_correct($the_product_info['products_head_desc_tag'], array('products_name'=>$the_product_info['products_name'], 'manufacturers_name' => \common\helpers\Manufacturers::get_manufacturer_info('manufacturers_name', $the_product_info['manufacturers_id']),'breadcrumbmone' => (isset($breadcrumb) && is_object($breadcrumb))?$breadcrumb->seo_trail():''))
                      . ' ' . (defined('HEAD_DESC_TAG_PRODUCT_INFO') && tep_not_null(HEAD_DESC_TAG_PRODUCT_INFO)?HEAD_DESC_TAG_PRODUCT_INFO:$HEAD_DESC_TAG_ALL);
                    */
                }
                break;

            // PRODUCTS_NEW
            case ( $full_action=='catalog/products_new' ):
                $the_title = (defined('HEAD_TITLE_TAG_WHATS_NEW') && tep_not_null(HEAD_TITLE_TAG_WHATS_NEW)?HEAD_TITLE_TAG_WHATS_NEW:$HEAD_TITLE_TAG_ALL);
                $the_key_words = (defined('HEAD_KEY_TAG_WHATS_NEW') && tep_not_null(HEAD_KEY_TAG_WHATS_NEW)?HEAD_KEY_TAG_WHATS_NEW:$HEAD_KEY_TAG_ALL);
                $the_desc = (defined('HEAD_DESC_TAG_WHATS_NEW') && tep_not_null(HEAD_DESC_TAG_WHATS_NEW)?HEAD_DESC_TAG_WHATS_NEW:$HEAD_DESC_TAG_ALL);
                break;

            // SPECIALS.PHP
            case ( $full_action=='catalog/specials' ):
                $the_title = (defined('HEAD_TITLE_TAG_SPECIALS') && tep_not_null(HEAD_TITLE_TAG_SPECIALS)?HEAD_TITLE_TAG_SPECIALS:$HEAD_TITLE_TAG_ALL);

                $products_join = '';
                if ( \common\classes\platform::activeId() ) {
                  $products_join .=
                    " inner join " . TABLE_PLATFORMS_PRODUCTS . " plp on p.products_id = plp.products_id  and plp.platform_id = '" . \common\classes\platform::currentId() . "' ".
                    " inner join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p2c.products_id=p.products_id ".
                    " inner join " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id AND plc.platform_id = '" . \common\classes\platform::currentId() . "' ";
                }

                $new = tep_db_query(
                  "select distinct if(length(pd1.products_name), pd1.products_name, pd.products_name) as products_name ".
                  "from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_SPECIALS . " s, " . TABLE_PRODUCTS . " p {$products_join} " . "  left join " . TABLE_PRODUCTS_DESCRIPTION . " pd1 on pd1.products_id = p.products_id and pd1.language_id='" . (int)$languages_id ."' and pd1.affiliate_id = '" . (isset($_SESSION['affiliate_ref'])?(int)$_SESSION['affiliate_ref']:0) . "' where p.products_status = 1 " . \common\helpers\Product::get_sql_product_restrictions(array('p', 'pd', 's', 'sp', 'pp')) . " and s.products_id = p.products_id and p.products_id = pd.products_id  " . " and pd.language_id = '" . (int)$languages_id . "' and s.status = '1' and pd.affiliate_id = 0 order by s.specials_date_added DESC ".
                  "limit 10"
                );

                $the_specials='';
                while ($new_values = tep_db_fetch_array($new)) {
                    $the_specials .= $new_values['products_name'] . ', ';
                }
                $the_key_words = $the_specials . ', ' . (defined('HEAD_KEY_TAG_SPECIALS') && tep_not_null(HEAD_KEY_TAG_SPECIALS)?HEAD_KEY_TAG_SPECIALS:$HEAD_KEY_TAG_ALL);

                $the_desc = (defined('HEAD_DESC_TAG_SPECIALS') && tep_not_null(HEAD_DESC_TAG_SPECIALS)?HEAD_DESC_TAG_SPECIALS:$HEAD_DESC_TAG_ALL);
                break;

// PRODUCTS_REVIEWS_INFO.PHP and PRODUCTS_REVIEWS.PHP
            case ( strpos($full_action,'reviews/')===0 ):
    //case ( strstr($_SERVER['PHP_SELF'],'product_reviews_info.php') or strstr($_SERVER['PHP_SELF'],'product_reviews.php') or strstr($_SERVER['PHP_SELF'],'product_reviews_write.php')  or strstr($PHP_SELF,'product_reviews_info.php') or strstr($PHP_SELF,'product_reviews.php') or strstr($PHP_SELF,'product_reviews_write.php') ):
                $the_title = (defined('HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO)?HEAD_TITLE_TAG_PRODUCT_REVIEWS_INFO:$HEAD_TITLE_TAG_ALL) . ' ' . \common\helpers\HeaderTags::get_header_tag_products_title($HTTP_GET_VARS['products_id']);
                $the_key_words = \common\helpers\HeaderTags::get_header_tag_products_keywords($HTTP_GET_VARS['products_id']) . ', ' . (defined('HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO)?HEAD_KEY_TAG_PRODUCT_REVIEWS_INFO:$HEAD_KEY_TAG_ALL);
                $the_desc = \common\helpers\HeaderTags::get_header_tag_products_desc($HTTP_GET_VARS['products_id']) . ' ' . (defined('HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO') && tep_not_null(HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO)?HEAD_DESC_TAG_PRODUCT_REVIEWS_INFO:$HEAD_DESC_TAG_ALL);
                break;

// INFORMATION.PHP
      case (($full_action=='info/index' || $full_action=='info/') && (isset($params['info_id']) && $params['info_id']>0) ):
                /*
        $query_infromation_page = tep_db_query(
          "select page_title, meta_description, meta_key ".
          "from ".TABLE_INFORMATION." ".
          "WHERE visible='1' AND information_id='".(int)$params['info_id']."' AND languages_id = '" . $languages_id . "'"
        );
                */
                  $query_infromation_page = tep_db_query(
                    "select if(length(i1.info_title), i1.info_title, i.info_title) as info_title, ".
                    "if(length(i1.page_title), i1.page_title, i.page_title) as page_title, ".
                    "if(length(i1.meta_title), i1.meta_title, i.meta_title) as meta_title, ".
                    "if(length(i1.meta_description), i1.meta_description, i.meta_description) as meta_description, ".
                    "if(length(i1.meta_key), i1.meta_key, i.meta_key) as meta_key, ".
                    "i.information_id ".
                    "from " . TABLE_INFORMATION . " i LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id  and i1.languages_id = '" . (int)$languages_id . "' and i1.affiliate_id = '" . (isset($_SESSION['affiliate_ref'])?(int)$_SESSION['affiliate_ref']:0) . "' ".(\common\classes\platform::activeId()?" AND i1.platform_id='".\common\classes\platform::currentId()."' ":'')." where i.information_id = '" . (int)$params['info_id'] . "' and i.languages_id = '" . (int)$languages_id . "' and i.visible = 1 and i.affiliate_id = 0 ".(\common\classes\platform::activeId()?" AND i.platform_id='".\common\classes\platform::currentId()."' ":'')." ".
                    "limit 1"
                  );

        if(tep_db_num_rows($query_infromation_page))
        {
            $row_info_page = tep_db_fetch_array($query_infromation_page);
            $the_desc = (strlen($row_info_page['meta_description'])>0?$row_info_page['meta_description']:$HEAD_DESC_TAG_ALL);
            $the_key_words = (strlen($row_info_page['meta_key'])>0?$row_info_page['meta_key']:$HEAD_KEY_TAG_ALL);
            if ( !empty($row_info_page['meta_title']) ) {
                $the_title = $row_info_page['meta_title'];
            }else {
                $the_title = strlen($row_info_page['page_title']) > 0 ? $row_info_page['page_title'] . ' ' . $HEAD_TITLE_TAG_ALL : $HEAD_TITLE_TAG_ALL;
            }
        }

        break;
// ALL OTHER PAGES NOT DEFINED ABOVE
    default:

        // SEO addon
/*        $query_infromation_page = tep_db_query(
          "select page_title, meta_description, meta_key from ".TABLE_INFORMATION." WHERE page = '" . tep_db_input($full_action) . "' AND visible = '1' AND languages_id = '" . $languages_id . "' AND affiliate_id = '" . (int)$_SESSION['affiliate_ref'] . "'"
        );*/
        $query_infromation_page = tep_db_query(
            "select if(length(i1.info_title), i1.info_title, i.info_title) as info_title, ".
            "if(length(i1.page_title), i1.page_title, i.page_title) as page_title, ".
            "if(length(i1.meta_title), i1.meta_title, i.meta_title) as meta_title, ".
            "if(length(i1.meta_description), i1.meta_description, i.meta_description) as meta_description, ".
            "if(length(i1.meta_key), i1.meta_key, i.meta_key) as meta_key, ".
            "i.information_id ".
            "from " . TABLE_INFORMATION . " i LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id  and i1.languages_id = '" . (int)$languages_id . "' and i1.affiliate_id = '" . (isset($_SESSION['affiliate_ref'])?(int)$_SESSION['affiliate_ref']:0) . "'  where i.page = '" . tep_db_input($full_action) . "' and i.languages_id = '" . (int)$languages_id . "' and i.visible = 1 and i.affiliate_id = 0 ".
            "limit 1"
        );
        if(tep_db_num_rows($query_infromation_page))
        {
            $row_info_page = tep_db_fetch_array($query_infromation_page);
            $the_desc = (strlen($row_info_page['meta_description'])>0?$row_info_page['meta_description']:$HEAD_DESC_TAG_ALL);
            $the_key_words = (strlen($row_info_page['meta_key'])>0?$row_info_page['meta_key']:$HEAD_KEY_TAG_ALL);
            $the_title = strlen($row_info_page['page_title'])>0?$row_info_page['page_title'] . ' ' . $HEAD_TITLE_TAG_ALL:$HEAD_TITLE_TAG_ALL;
        }

        // eof SEO addon
        break;

}
/*function prepare_tags($value) {
    $value = \common\helpers\Output::unhtmlentities($value);
    $value = str_replace('"', "'", $value);
    $value = str_replace(array("\n","\r","\r\n","\n\r"), " ", $value);
    $value = strip_tags($value);
    return $value;
}*/

//echo '  <title>' . prepare_tags($the_title) . '</title>' . "\n";
//echo '  <META NAME="Description" Content="' . prepare_tags($the_desc) . '">' . "\n";
//echo '  <META NAME="Keywords" CONTENT="' . prepare_tags($the_key_words) . '">' . "\n";

        $the_title = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), " ", str_replace('<', ' <', $the_title)));
        $the_title = preg_replace('/\s{2,}/', ' ', $the_title);
        $the_title = trim($the_title);

        $the_key_words = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), " ", str_replace('<', ' <', $the_key_words)));
        $the_key_words = preg_replace('/\s{2,}/', ' ', $the_key_words);
        $the_key_words = trim(trim($the_key_words),',');

        $the_desc = strip_tags(str_replace(array("\n","\r","\r\n","\n\r"), " ", str_replace('<', ' <', $the_desc)));
        $the_desc = preg_replace('/\s{2,}/', ' ', $the_desc);
        $the_desc = trim(trim($the_desc),',');

        if ( !empty($the_title) ) {
            \Yii::$app->view->title = $the_title;
        }else{
            \Yii::$app->view->title = STORE_NAME;
        }
        if ( !empty($the_desc) ) {
            $this->getView()->registerMetaTag([
              'name' => 'Description',
              'content' => $the_desc
            ], 'Description');
        }
        if ( !empty($the_key_words) ) {
            $this->getView()->registerMetaTag([
              'name' => 'Keywords',
              'content' => $the_key_words
            ], 'Keywords');
        }

        $this->getView()->registerMetaTag([
          'name' => 'Reply-to',
          'content' => STORE_OWNER_EMAIL_ADDRESS
        ],'Reply-to');
        $this->getView()->registerMetaTag([
          'name' => 'Author',
          'content' => STORE_OWNER
        ],'Author');
        $this->getView()->registerMetaTag([
          'name' => 'Robots',
          'content' => 'index,follow'
        ],'Robots');

        if ( false && defined('TRUSTPILOT_VERIFY_META_TAG') && TRUSTPILOT_VERIFY_META_TAG!='' ){
            if ( preg_match('/name="([^"]+)"/i',TRUSTPILOT_VERIFY_META_TAG, $nameMatch) && preg_match('/content="([^"]+)"/i',TRUSTPILOT_VERIFY_META_TAG, $contentMatch) ) {
                $this->getView()->registerMetaTag([
                    'name' => $nameMatch[1],
                    'content' => $contentMatch[1]
                ],'Trustpilot');
            }
        }
    }

    public function bindActionParams($action, $params)
    {
        global $language, $languages_id;

        if ( IS_IMAGE_CDN_SERVER && $action->controller->id!='image' ) {
          tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

        if ($ext = \common\helpers\Acl::checkExtension('BusinessToBusiness', 'bindParams')) {
            $ext::bindParams($action);
        }

        if (Yii::$app->request->isAjax) {// getIsAjax()

            $this->layout = 'ajax.tpl';

        } else {
          
          if ($action->controller->id == 'index' && $action->id == 'index' ||
              $action->controller->id == 'index' && $action->id == 'design' ||
              $action->controller->id == 'catalog' && $action->id == 'index' ||
              $action->controller->id == 'catalog' && $action->id == 'product' ||
              $action->controller->id == 'catalog' && $action->id == 'product-attributes' ||
              $action->controller->id == 'catalog' && $action->id == 'gift-card' ||
              $action->controller->id == 'catalog' && $action->id == 'advanced-search-result' ||
              $action->controller->id == 'catalog' && $action->id == 'specials' ||
              $action->controller->id == 'catalog' && $action->id == 'featured_products' ||
              $action->controller->id == 'catalog' && $action->id == 'products_new' ||
              $action->controller->id == 'checkout' && $action->id == 'success' ||
              $action->controller->id == 'contact' && $action->id == 'index' ||
              $action->controller->id == 'shopping-cart' && $action->id == 'index' ||
              $action->controller->id == 'blog' && $action->id == 'index' ||
              $action->controller->id == 'info' && $action->id == 'index'
          ){
            $this->view->page_layout = 'custom';
          } else {
            $this->view->page_layout = 'default';
          }

          $this->layout = 'main.tpl';  
        }

        $params = Yii::$app->request->get();
        if ($params['get_block']){
          $this->view->block_id = $params['get_block'];
          $this->layout = 'get-block.tpl';
        }

        if ($action->id == 'index') {
            \common\helpers\Translation::init($action->controller->id);
        } else {
            \common\helpers\Translation::init($action->controller->id . '/' . $action->id);
        }
        \common\helpers\Translation::init('main');

		if (!\frontend\design\Info::isAdminOrders())
			\app\components\CartFactory::work();
        
        return parent::bindActionParams($action, $params);
    }

    public function render($view, $params = [])
    {
        $this->view->page_params = $params['params'];

        $content = $this->getView()->render($view, $params, $this);
        return $this->renderContent($content);
    }
}