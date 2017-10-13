<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use common\helpers\Seo;
use Yii;

/**
 * default controller to handle user requests.
 */
class CategoriesController extends Sceleton {

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_CATEGORIES_PRODUCTS'];

    private function getCategoryTree($parent_id = '0', $platform_id = false) {
        global $languages_id;

        $filter_by_platform = array();
        if (is_array($platform_id)) {
            $filter_by_platform = $platform_id;
        } else {
            if (!$platform_param = Yii::$app->request->get('platform', false)) {
                $formFilter = Yii::$app->request->get('filter', '');
                parse_str($formFilter, $output);
                if (isset($output['platform']) && is_array($output['platform'])) {
                    $platform_param = $output['platform'];
                }
            }

            if (isset($platform_param) && is_array($platform_param)) {
                foreach ($platform_param as $_platform_id)
                    if ((int) $_platform_id > 0)
                        $filter_by_platform[] = (int) $_platform_id;
            }
        }

        $platform_filter_categories = '';
        if (count($filter_by_platform) > 0) {
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_PLATFORMS_CATEGORIES . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        $categories_query = tep_db_query("select c.categories_id as id, cd.categories_name as text, c.parent_id, c.categories_status from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " c1, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int) $languages_id . "' and c1.parent_id = '" . (int) $parent_id . "' and (c.categories_left >= c1.categories_left and c.categories_right <= c1.categories_right) and affiliate_id = 0 {$platform_filter_categories} order by c.sort_order, cd.categories_name");

        $tmp_cat = [];
        while ($categories = tep_db_fetch_array($categories_query)) {
            $categories['child'] = array();
            $tmp_cat[] = $categories;
        }

        $categoriesTree = self::buildTree($tmp_cat, $parent_id);

//         $categoriesTree = [];
//         $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.parent_id, c.categories_status from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and c.parent_id = '" . (int)$parent_id . "' and affiliate_id = 0 {$platform_filter_categories} order by c.sort_order, cd.categories_name");
//         while ($categories = tep_db_fetch_array($categories_query)) {
//           if ($exclude != $categories['categories_id']) {
//               $categoriesTree[] = [
//                     'id' => $categories['categories_id'],
//                     'text' => $categories['categories_name'],
//                     'categories_status' => $categories['categories_status'],
//                     'child' => $this->getCategoryTree($categories['categories_id'],$filter_by_platform),
//                 ];
//           }
//         }
        return $categoriesTree;
    }

    //transform plain array to tree
    private static function buildTree(array &$elements, $parentId = 0) {

        $branch = array();

        foreach ($elements as &$element) {

            if ($element['parent_id'] == $parentId) {
                $children = self::buildTree($elements, $element['id']);
                if ($children) {
                    $element['child'] = $children;
                }
                //$branch[$element['categories_id']] = $element;
                unset($element['parent_id']);
                $branch[] = $element;
                unset($element);
            }
        }
        return $branch;
    }

    private function getBrandsList($platform_id = false) {
        $brandsList = [];

        $filter_by_platform = array();
        if (is_array($platform_id)) {
            $filter_by_platform = $platform_id;
        } else {
            if (!$platform_param = Yii::$app->request->get('platform', false)) {
                $formFilter = Yii::$app->request->get('filter', '');
                parse_str($formFilter, $output);
                if (isset($output['platform']) && is_array($output['platform'])) {
                    $platform_param = $output['platform'];
                }
            }

            if (isset($platform_param) && is_array($platform_param)) {
                foreach ($platform_param as $_platform_id)
                    if ((int) $_platform_id > 0)
                        $filter_by_platform[] = (int) $_platform_id;
            }
        }

        $platform_filter_products = '';
//         if ( count($filter_by_platform)>0 ) {
//             $platform_filter_products .= ' and m.manufacturers_id IN (SELECT distinct p.manufacturers_id FROM '.TABLE_PRODUCTS.' p inner join '.TABLE_PLATFORMS_PRODUCTS.' pp WHERE pp.products_id=p.products_id and pp.platform_id IN(\''.implode("','",$filter_by_platform).'\'))  ';
//         }
        if (count($filter_by_platform) > 0) {
            $platform_filter_products .= ' inner join ' . TABLE_PRODUCTS . ' p on m.manufacturers_id = p.manufacturers_id inner join ' . TABLE_PLATFORMS_PRODUCTS . ' pp on pp.products_id=p.products_id and pp.platform_id IN(\'' . implode("','", $filter_by_platform) . '\')  ';
        }

        $manufacturers_query_raw = "select m.manufacturers_id, m.manufacturers_name, m.manufacturers_image, m.date_added, m.last_modified from " . TABLE_MANUFACTURERS . " m {$platform_filter_products} where 1  group by m.manufacturers_id order by m.sort_order, m.manufacturers_name";

        $manufacturers_query = tep_db_query($manufacturers_query_raw);
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $brandsList[] = [
                'id' => $manufacturers['manufacturers_id'],
                'text' => $manufacturers['manufacturers_name'],
            ];
        }
        return $brandsList;
    }

    /**
     * Index action is the default action in a controller.
     */
    public function actionIndex() {
        global $languages_id, $language;

        $this->selectedMenu = array('catalog', 'categories');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('categories/index'), 'title' => HEADING_TITLE);
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('categories/productedit') . '" class="js_create_new_product create_item addprbtn"><i class="icon-cubes"></i>' . TEXT_CREATE_NEW_PRODUCT . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('categories/categoryedit') . '" class="js_create_new_category create_item addprbtn"><i class="icon-folder-close-alt"></i>' . TEXT_CREATE_NEW_CATEGORY . '</a>';
        $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl('categories/brandedit') . '" class="create_item addprbtn"><i class="icon-tag"></i>' . TEXT_CREATE_NEW_BRANDS . '</a>';
        $this->view->headingTitle = HEADING_TITLE;
        $this->view->catalogTable = array(
            array(
                'title' => TABLE_HEADING_CATEGORIES_PRODUCTS,
                'not_important' => 0
            ),
            array(
                'title' => TABLE_HEADING_STATUS,
                'not_important' => 0
            ),
                /* array(
                  'title' => TABLE_HEADING_ACTION,
                  'not_important' => 0
                  ), */
        );

        $this->view->categoriesTree = $this->getCategoryTree();

        $this->view->brandsList = $this->getBrandsList();

        $this->view->filters = new \stdClass();

        $by = [
            [
                'name' => TEXT_ANY,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_NAME,
                'value' => 'name',
                'selected' => '',
            ],
            [
                'name' => TEXT_IN_DESCRIPTION,
                'value' => 'description',
                'selected' => '',
            ],
            [
                'name' => TEXT_CATEGORY_NAME,
                'value' => 'cname',
                'selected' => '',
            ],
            [
                'name' => TEXT_IN_CATEGORY_DESCRIPTION,
                'value' => 'cdescription',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_PAGE_TITLE,
                'value' => 'title',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_HEADER_DESC,
                'value' => 'header',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_KEYWORDS,
                'value' => 'keywords',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_MODEL,
                'value' => 'model',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_EAN,
                'value' => 'ean',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_ASIN,
                'value' => 'asin',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_ISBN,
                'value' => 'isbn',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_FILE,
                'value' => 'file',
                'selected' => '',
            ],
            [
                'name' => TEXT_IMAGE_NAME,
                'value' => 'image',
                'selected' => '',
            ],
            [
                'name' => TEXT_SEARCH_BY_SEO_NAME,
                'value' => 'seo',
                'selected' => '',
            ],
        ];

        foreach ($by as $key => $value) {
            if (isset($_GET['by']) && $value['value'] == $_GET['by']) {
                $by[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->by = $by;

        $search = '';
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
        }
        $this->view->filters->search = $search;

        $barnd = '';
        if (isset($_GET['barnd'])) {
            $barnd = $_GET['barnd'];
        }
        $this->view->filters->barnd = $barnd;

        $supplier = '';
        if (isset($_GET['supplier'])) {
            $supplier = $_GET['supplier'];
        }
        $this->view->filters->supplier = $supplier;

        $stock = [
            [
                'name' => TEXT_ALL,
                'value' => '',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_AVAILABLE,
                'value' => 'y',
                'selected' => '',
            ],
            [
                'name' => TEXT_PRODUCT_NOT_AVAILABLE,
                'value' => 'n',
                'selected' => '',
            ],
        ];
        foreach ($stock as $key => $value) {
            if (isset($_GET['stock']) && $value['value'] == $_GET['stock']) {
                $stock[$key]['selected'] = 'selected';
            }
        }
        $this->view->filters->stock = $stock;

        $price_from = '';
        if (isset($_GET['price_from'])) {
            $price_from = $_GET['price_from'];
        }
        $this->view->filters->price_from = $price_from;

        $price_to = '';
        if (isset($_GET['price_to'])) {
            $price_to = $_GET['price_to'];
        }
        $this->view->filters->price_to = $price_to;

        if (isset($_GET['weight_value']) && $_GET['weight_value'] == 'lbs') {
            $this->view->filters->weight_kg = false;
            $this->view->filters->weight_lbs = true;
        } else {
            $this->view->filters->weight_kg = true;
            $this->view->filters->weight_lbs = false;
        }

        $weight_from = '';
        if (isset($_GET['weight_from'])) {
            $weight_from = $_GET['weight_from'];
        }
        $this->view->filters->weight_from = $weight_from;

        $weight_to = '';
        if (isset($_GET['weight_to'])) {
            $weight_to = $_GET['weight_to'];
        }
        $this->view->filters->weight_to = $weight_to;

        $this->view->filters->prod_attr = (int) $_GET['prod_attr'];

        $this->view->filters->low_stock = (int) $_GET['low_stock'];
        $this->view->filters->featured = (int) $_GET['featured'];
        $this->view->filters->gift = (int) $_GET['gift'];
        $this->view->filters->virtual = (int) $_GET['virtual'];
        $this->view->filters->all_bundles = (int) $_GET['all_bundles'];
        $this->view->filters->sale = (int) $_GET['sale'];

        $this->view->filters->platform = array();
        if (isset($_GET['platform']) && is_array($_GET['platform'])) {
            foreach ($_GET['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $this->view->filters->platform[] = (int) $_platform_id;
        }

        $this->view->filters->row = (int) $_GET['row'];

        $listing_type = 'category';
        if (isset($_GET['listing_type'])) {
            $listing_type = $_GET['listing_type'];
        }
        $this->view->filters->listing_type = $listing_type;
        $this->view->filters->category_id = (int) $_GET['category_id'];
        $this->view->filters->brand_id = (int) $_GET['brand_id'];

        if (is_dir(DIR_FS_CATALOG_IMAGES)) {
            if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
                $this->view->errorMessage = sprintf(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, DIR_FS_CATALOG_IMAGES);
                $this->view->errorMessageType = 'danger';
            }
        } else {
            $this->view->errorMessage = sprintf(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, DIR_FS_CATALOG_IMAGES);
            $this->view->errorMessageType = 'danger';
        }

        return $this->render('index', [
                    'platforms' => \common\classes\platform::getList(),
                    'isMultiPlatforms' => \common\classes\platform::isMulti(),
        ]);
    }

    public function actionList() {
        \common\helpers\Translation::init('admin/categories');

        global $languages_id, $login_id;
        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_category_id = Yii::$app->request->get('id', 0);

        if ($length == -1)
            $length = 10000;

        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);

        $categoriesQty = 0;
        $productsQty = 0;

        $current_page_number = ($start / $length) + 1;
        $responseList = [];
        $_session = Yii::$app->session;
        $_session->remove('products_query_raw');

        $list_bread_crumb = '';
        $search = '';
        if (isset($_GET['search']['value']) && tep_not_null($_GET['search']['value'])) {
            $keywords = tep_db_input(tep_db_prepare_input($_GET['search']['value']));
            $search_condition = " where cd.categories_name like '%" . $keywords . "%' ";
            $search = " and (pd.products_name like '%" . tep_db_input($keywords) . "%' or p.products_model like '%" . tep_db_input($keywords) . "%')";
        } else {
            $search_condition = " where 1 ";
        }

        $search_condition .= " and c.parent_id='" . (int) $current_category_id . "'";

        //--- Apply filter start
        $onlyCategories = false;
        $onlyProducts = false;
        $filter_cat = '';
        $filter_prod = '';
        $use_iventory = false;

        $filter_by_platform = array();
        if (isset($output['platform']) && is_array($output['platform'])) {
            foreach ($output['platform'] as $_platform_id)
                if ((int) $_platform_id > 0)
                    $filter_by_platform[] = (int) $_platform_id;
        }

        $platform_filter_categories = '';
        $platform_filter_products = '';
        if (count($filter_by_platform) > 0) {
//            $filter_cat .= ' and c.categories_id IN (SELECT categories_id FROM '.TABLE_PLATFORMS_CATEGORIES.' WHERE platform_id IN(\''.implode("','",$filter_by_platform).'\'))  ';
//            $filter_prod .= ' and p.products_id IN (SELECT products_id FROM '.TABLE_PLATFORMS_PRODUCTS.' WHERE platform_id IN(\''.implode("','",$filter_by_platform).'\'))  ';
            $platform_filter_categories .= ' and c.categories_id IN (SELECT categories_id FROM ' . TABLE_PLATFORMS_CATEGORIES . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
            $platform_filter_products .= ' and p.products_id IN (SELECT products_id FROM ' . TABLE_PLATFORMS_PRODUCTS . ' WHERE platform_id IN(\'' . implode("','", $filter_by_platform) . '\'))  ';
        }

        if (tep_not_null($output['search'])) {
            $search = tep_db_prepare_input($output['search']);
            switch ($output['by']) {
                case 'name':
                    $filter_prod .= " and (pd.products_name like '%" . tep_db_input($search) . "%' or pdd.products_name like '%" . tep_db_input($search) . "%') ";
                    $onlyProducts = true;
                    break;
                case 'description':
                    $filter_prod .= " and pd.products_description like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'cname': default:
                    $filter_cat .= " and (cd.categories_name like '%" . tep_db_input($search) . "%' or cdd.categories_name like '%" . tep_db_input($search) . "%') ";
                    $onlyCategories = true;
                    break;
                case 'cdescription':
                    $filter_cat .= " and cd.categories_description like '%" . tep_db_input($search) . "%' ";
                    $onlyCategories = true;
                    break;
                case 'title':
                    $filter_prod .= " and pd.products_head_title_tag like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'header':
                    $filter_prod .= " and pd.products_head_desc_tag like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'keywords':
                    $filter_prod .= " and pd.products_head_keywords_tag like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'model':
                    $filter_prod .= " and p.products_model like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'ean':
                    $filter_prod .= " and p.products_ean like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'asin':
                    $filter_prod .= " and p.products_asin like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'isbn':
                    $filter_prod .= " and p.products_isbn like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'file':
                    $filter_prod .= " and p.products_file like '%" . tep_db_input($search) . "%' ";
                    $onlyProducts = true;
                    break;
                case 'image':
                    $filter_prod .= " and p.products_image like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " and c.categories_image like '%" . tep_db_input($search) . "%' ";
                    break;
                case 'seo':
                    $filter_prod .= " and p.products_seo_page_name like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " and c.categories_seo_page_name like '%" . tep_db_input($search) . "%' ";
                    break;

                case '':
                case 'any':
                    $filter_prod .= " and (";
                    $filter_prod .= " pd.products_name like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or pdd.products_name like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or pd.products_description like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or pd.products_head_title_tag like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or pd.products_head_desc_tag like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or pd.products_head_keywords_tag like '%" . tep_db_input($search) . "%' ";

                    $filter_prod .= " or p.products_model like '%" . tep_db_input($search) . "%' ";
                    if (defined('PRODUCTS_INVENTORY') && PRODUCTS_INVENTORY == 'True') {
                        $use_iventory = true;
                        $filter_prod .= " or i.products_model like '%" . tep_db_input($search) . "%' ";
                    }

                    $filter_prod .= " or p.products_ean like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or p.products_asin like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or p.products_isbn like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or p.products_file like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or p.products_image like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= " or p.products_seo_page_name like '%" . tep_db_input($search) . "%' ";
                    $filter_prod .= ") ";
                    $filter_cat .= " and (";
                    $filter_cat .= " cd.categories_name like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or cdd.categories_name like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or cd.categories_description like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or c.categories_image like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= " or c.categories_seo_page_name like '%" . tep_db_input($search) . "%' ";
                    $filter_cat .= ") ";
                    break;
            }
        }

        if (tep_not_null($output['barnd'])) {
            $onlyProducts = true;
            $filter_prod .= " and m.manufacturers_name like '%" . tep_db_input($output['barnd']) . "%'";
        }

        if (tep_not_null($output['supplier'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(sp.products_id) FROM " . TABLE_SUPPLIERS_PRODUCTS . " as sp LEFT JOIN " . TABLE_SUPPLIERS . " as s on (sp.suppliers_id=s.suppliers_id) WHERE s.suppliers_name like '%" . tep_db_input($output['supplier']) . "%'");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }


        if (tep_not_null($output['stock'])) {
            switch ($output['stock']) {
                case 'y':
                    $onlyProducts = true;
                    $filter_prod .= " and p.products_status = '1' ";
                    break;
                case 'n':
                    $onlyProducts = true;
                    $filter_prod .= " and p.products_status = '0' ";
                    break;
                default:
                    break;
            }
        }

        if (tep_not_null($output['price_from'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.products_price >= '" . tep_db_input($output['price_from']) . "' ";
        }
        if (tep_not_null($output['price_to'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.products_price <= '" . tep_db_input($output['price_to']) . "' ";
        }

        if (tep_not_null($output['weight_from'])) {
            $onlyProducts = true;
            if ($output['weight_value'] == 'lbs') {
                $filter_prod .= " and p.weight_in >= '" . tep_db_input($output['weight_from']) . "' ";
            } else {
                $filter_prod .= " and p.weight_cm >= '" . tep_db_input($output['weight_from']) . "' ";
            }
        }
        if (tep_not_null($output['weight_to'])) {
            $onlyProducts = true;
            if ($output['weight_value'] == 'lbs') {
                $filter_prod .= " and p.weight_in <= '" . tep_db_input($output['weight_to']) . "' ";
            } else {
                $filter_prod .= " and p.weight_cm <= '" . tep_db_input($output['weight_to']) . "' ";
            }
        }

        if (tep_not_null($output['prod_attr'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_PRODUCTS_ATTRIBUTES . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (tep_not_null($output['low_stock'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.products_quantity < '" . STOCK_REORDER_LEVEL . "' ";
        }

        if (tep_not_null($output['featured'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_FEATURED . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (tep_not_null($output['gift'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_GIFT_WRAP_PRODUCTS . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (tep_not_null($output['virtual'])) {
            $onlyProducts = true;
            $filter_prod .= " and p.is_virtual = '1' ";
        }

        if (tep_not_null($output['all_bundles'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(sets_id) FROM " . TABLE_SETS_PRODUCTS . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $featuredIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $featuredIds[] = $check_products['sets_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $featuredIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (tep_not_null($output['sale'])) {
            $onlyProducts = true;
            $check_products_query = tep_db_query("SELECT distinct(products_id) FROM " . TABLE_SPECIALS . " WHERE 1");
            if (tep_db_num_rows($check_products_query) > 0) {
                $saleIds = [];
                while ($check_products = tep_db_fetch_array($check_products_query)) {
                    $saleIds[] = $check_products['products_id'];
                }
                $filter_prod .= " and p.products_id IN (" . implode(", ", $saleIds) . ")";
            } else {
                $filter_prod .= " and p.products_id = -1";
            }
        }

        if (!empty($filter_prod) || !empty($filter_cat)) {
            // SEARCH
            $list_bread_crumb = '';
            $rowsCounter = 0;

            if (!$onlyProducts) {
                //categories
                $orderByCategory = "c.sort_order, cd.categories_name";
                $categories_query_raw = "select distinct(c.categories_id), if(length(cd.categories_name) > 0, cd.categories_name, cdd.categories_name) as categories_name, c.categories_status from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id left join " . TABLE_CATEGORIES_DESCRIPTION . " cdd on c.categories_id=cdd.categories_id where 1 " . $filter_cat . " and cd.language_id = '" . (int) $languages_id . "' and cdd.language_id = '" . \common\helpers\Language::get_default_language_id() . "' and cd.affiliate_id = 0 " . $platform_filter_categories . " order by " . $orderByCategory;
                $remind_page_number = $current_page_number;
                $categories_split = new \splitPageResults($current_page_number, $length, $categories_query_raw, $categories_query_numrows, 'c.categories_id');
                $categories_query = tep_db_query($categories_query_raw);
                $categoriesQty = $categories_query_numrows;

                if ($remind_page_number == $current_page_number) {// all categories showed, now show only products
                    while ($categories = tep_db_fetch_array($categories_query)) {
                        $responseList[] = array(
                            '<div class="handle_cat_list state-disabled"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name"><b>' . $categories['categories_name'] . '</b><input class="cell_identify" type="hidden" value="' . $categories['categories_id'] . '"><input class="cell_type" type="hidden" value="category"></div></div>',
                            //$categories['categories_status']
                            ($categories['categories_status'] == 1 ? '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="check_on_off" checked="checked">' : '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="check_on_off">')
                        );
                        $rowsCounter++;
                    }
                }
            }
            if (!$onlyCategories) {
                //products
                $orderByProduct = "p2c.sort_order, pd.products_name";
                $products_query_raw = "select p.products_id, p.products_model, if(length(pd.products_name) > 0, pd.products_name, pdd.products_name) as products_name, p.products_status, p.products_image from " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " as pd on p.products_id = pd.products_id LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " as pdd on p.products_id = pdd.products_id LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " as p2c on p.products_id = p2c.products_id LEFT JOIN " . TABLE_MANUFACTURERS . " as m on p.manufacturers_id=m.manufacturers_id " . ($use_iventory ? " left Join " . TABLE_INVENTORY . " i on i.prid = p.products_id" : "") . " where pd.language_id = '" . (int) $languages_id . "' and pdd.language_id = '" . \common\helpers\Language::get_default_language_id() . "' and pd.affiliate_id = 0 " . $filter_prod . " {$platform_filter_products} group by p.products_id order by " . $orderByProduct;

                $products_query = tep_db_query($products_query_raw);
                $products_query_numrows = tep_db_num_rows($products_query);

                $offset = $start - $categories_query_numrows;
                $products_query_raw .= " limit " . max($offset, 0) . ", " . $length;
                $products_query = tep_db_query($products_query_raw);

                $productsQty = $products_query_numrows;

                $categories_query_numrows += $products_query_numrows;
                if ($rowsCounter < $length) {
                    $products_query = tep_db_query($products_query_raw);
                    while ($products = tep_db_fetch_array($products_query)) {
                        // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')
                        $image = \common\classes\Images::getImage($products['products_id']);
                        //(!empty($image) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($image, $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>');
                        $product_categories_string = '';
                        if (true) {
                            $product_categories = \common\helpers\Categories::generate_category_path($products['products_id'], 'product');
                            $product_categories_string .= '';
                            for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                                $category_path = '';
                                for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                                    $category_path .= '<span class="category_path__location">' . $product_categories[$i][$j]['text'] . '</span>&nbsp;&gt;&nbsp;';
                                }
                                $category_path = substr($category_path, 0, -16);
                                $product_categories_string .= '<li class="category_path">' . $category_path . '</li>';
                            }
                            $product_categories_string = '<span class="category_path" style="display:block">' . TEXT_LIST_PRODUCT_PLACED_IN . '</span> <ul class="category_path_list">' . $product_categories_string . '</ul>';
                        }
                        $responseList[] = array(
                            '<div class="handle_cat_list state-disabled' . ($products['products_status'] == 1 ? '' : ' dis_prod') . '">' .
                            '<span class="handle"><i class="icon-hand-paper-o"></i></span>' .
                            '<div class="prod_name prod_name_double" data-click-double="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $products['products_id']) . '">' .
                            (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                            '<span class="prodNameC">' . $products['products_name'] . $product_categories_string . '</span>' .
                            '<span class="prodIDsC"><span title="' . \common\helpers\Output::output_string($products['products_model']) . '">' . TEXT_SKU . ' ' . $products['products_model'] . '<br>' . TABLE_HEADING_ID . ': ' . $products['products_id'] . '</span></span>' .
                            '<input class="cell_identify" type="hidden" value="' . $products['products_id'] . '"><input class="cell_type" type="hidden" value="product">' .
                            '</div>' .
                            '</div>',
                            ($products['products_status'] == 1 ? '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="check_on_off" checked="checked">' : '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="check_on_off">')
                        );
                        //$categories_query_numrows++;
                        $rowsCounter++;
                        if ($rowsCounter >= $length) {
                            break;
                        }
                    }
                }
            }

            //--- Apply filter end
        } elseif ($output['listing_type'] == 'category') {
            $list_bread_crumb = TEXT_CATALOG_LIST_BREADCRUMB . ' ';

            $list_bread_crumb .= ' &gt; ' . \common\helpers\Categories::output_generated_category_path($current_category_id, 'category', '<span class="category_path__location">%2$s</span>');




            /* if (isset($_GET['order'][0]['column']) && $_GET['order'][0]['dir']) {
              switch ($_GET['order'][0]['column']) {
              case 0:
              $orderByCategory = "c.sort_order, cd.categories_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
              $orderByProduct = "p.sort_order, pd.products_name " . tep_db_prepare_input($_GET['order'][0]['dir']);
              break;
              case 1:
              $orderByCategory = "c.categories_status " . tep_db_prepare_input($_GET['order'][0]['dir']);
              $orderByProduct = "p.products_status " . tep_db_prepare_input($_GET['order'][0]['dir']);
              break;
              default:
              $orderByCategory = "c.sort_order, cd.categories_name";
              $orderByProduct = "p.sort_order, pd.products_name";
              break;
              }
              } else {
              $orderByCategory = "c.sort_order, cd.categories_name";
              $orderByProduct = "p.sort_order, pd.products_name";
              } */
            $orderByCategory = "c.sort_order, cd.categories_name";
            $orderByProduct = "p2c.sort_order, pd.products_name";

            $rowsCounter = 0;

            //$categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.last_xml_export from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and cd.affiliate_id = 0 and cd.categories_name like '%" . tep_db_input($search) . "%' order by c.sort_order, cd.categories_name");
            $categories_query_raw = "select distinct(c.categories_id), if(length(cd.categories_name) > 0, cd.categories_name, cdd.categories_name) as categories_name, c.categories_status, c.categories_image from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id left join " . TABLE_CATEGORIES_DESCRIPTION . " cdd on c.categories_id=cdd.categories_id " . $search_condition . " and cd.language_id = '" . (int) $languages_id . "' and cdd.language_id = '" . \common\helpers\Language::get_default_language_id() . "' and cd.affiliate_id = 0 " . $platform_filter_categories . " order by " . $orderByCategory;

            $remind_page_number = $current_page_number;

            $categories_split = new \splitPageResults($current_page_number, $length, $categories_query_raw, $categories_query_numrows, 'c.categories_id');
            $categories_query = tep_db_query($categories_query_raw);

            if ($current_category_id > 0) {
                $parrent_query = tep_db_query("select parent_id, categories_status from " . TABLE_CATEGORIES . " where categories_id = '" . (int) $current_category_id . "'");
                if ($parrent = tep_db_fetch_array($parrent_query)) {
                    $responseList[] = array(
                        '<span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span><input class="cell_identify" type="hidden" value="' . $parrent['parent_id'] . '"><input class="cell_type" type="hidden" value="parent">',
                        ''
                    );
                }
            }

            $categoriesQty = $categories_query_numrows;

            if ($remind_page_number == $current_page_number) {// all categories showed, now show only products
                while ($categories = tep_db_fetch_array($categories_query)) {
                    $image_path = DIR_WS_CATALOG_IMAGES . $categories['categories_image'];
                    $responseList[] = array(
                        '<div class="handle_cat_list' . ($categories['categories_status'] == 1 ? '' : ' dis_prod') . '"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="cat_name' . ($categories['categories_image'] ? ' catNameImg' : '') . '">' . ($categories['categories_image'] ? '<span class="prodCatImg"><img src="' . $image_path . '"></span>' : '') . '<b>' . $categories['categories_name'] . '</b><input class="cell_identify" type="hidden" value="' . $categories['categories_id'] . '"><input class="cell_type" type="hidden" value="category"></div></div>',
                        //$categories['categories_status']
                        ($categories['categories_status'] == 1 ? '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="check_on_off" checked="checked">' : '<input type="checkbox" value="' . $categories['categories_id'] . '" name="categories_status" class="check_on_off">')
                    );
                    $rowsCounter++;
                }
            }


            /**
             * Recalc products offset
             */
            $products_query_raw = "select p.products_id, p.products_model, if(length(pd.products_name) > 0, pd.products_name, pdd.products_name) as products_name, p.products_status, p.products_image from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_DESCRIPTION . " pdd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and p.products_id = pdd.products_id and pdd.language_id = '" . \common\helpers\Language::get_default_language_id() . "' and p.products_id = p2c.products_id " . $search . " and pd.affiliate_id = 0 and p2c.categories_id = '" . (int) $current_category_id . "' {$platform_filter_products} order by " . $orderByProduct;


            $products_query = tep_db_query($products_query_raw);
            $products_query_numrows = tep_db_num_rows($products_query);

            $offset = $start - $categories_query_numrows;
            $products_query_raw .= " limit " . max($offset, 0) . ", " . $length;
            $products_query = tep_db_query($products_query_raw);

            $productsQty = $products_query_numrows;

            //$products_query_raw ="select p.products_id, pd.products_name, p.products_status, p.products_image from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id " . (tep_session_is_registered('login_vendor')?" and p.vendor_id = '" . $login_id . "'":'') . $search . " and pd.affiliate_id = 0 and p2c.categories_id = '" . (int)$current_category_id . "' order by " . $orderByProduct;
            //$products_split = new \splitPageResults($current_page_number, $length, $products_query_raw, $products_query_numrows, 'p.products_id');
            $categories_query_numrows += $products_query_numrows;
            if ($rowsCounter < $length) {
                //$products_query = tep_db_query("select p.products_id, pd.products_name, p.products_status, p.products_image from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id " . (tep_session_is_registered('login_vendor')?" and p.vendor_id = '" . $login_id . "'":'') . $search . " and pd.affiliate_id = 0 and p2c.categories_id = '" . (int)$current_category_id . "' order by " . $orderByProduct);
                $products_query = tep_db_query($products_query_raw);
                while ($products = tep_db_fetch_array($products_query)) {
                    // (file_exists(DIR_FS_CATALOG_IMAGES . $products['products_image']) ? '<span class="prodImgC">' . \common\helpers\Image::info_image($products['products_image'], $products['products_name'], 50, 50) . '</span>' : '<span class="cubic"></span>')
                    $image = \common\classes\Images::getImage($products['products_id']);
                    $product_categories_string = '';
                    if (true) {
                        $product_categories = \common\helpers\Categories::generate_category_path($products['products_id'], 'product');
                        if (count($product_categories) > 1) {
                            $product_categories_string .= '';
                            for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                                $category_path = '';
                                if (intval($product_categories[$i][count($product_categories[$i]) - 1]['id']) == (int) $current_category_id)
                                    continue;
                                for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                                    $category_path .= '<span class="category_path__location">' . $product_categories[$i][$j]['text'] . '</span>&nbsp;&gt;&nbsp;';
                                }
                                $category_path = substr($category_path, 0, -16);
                                $product_categories_string .= '<li class="category_path">' . $category_path . '</li>';
                            }
                            $product_categories_string = '<span class="category_path" style="display:block">' . TEXT_LIST_PRODUCT_ALSO_PLACED_IN . '</span> <ul class="category_path_list">' . $product_categories_string . '</ul>';
                        }
                    }
                    $responseList[] = array(
                        '<div class="handle_cat_list prod_handle' . ($products['products_status'] == 1 ? '' : ' dis_prod') . '">' .
                        '<span class="handle"><i class="icon-hand-paper-o"></i></span>' .
                        '<div class="prod_name prod_name_double" data-click-double="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $products['products_id']) . '">' .
                        (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                        '<span class="prodNameC">' . $products['products_name'] . $product_categories_string . '</span>' .
                        '<span class="prodIDsC"><span title="' . \common\helpers\Output::output_string($products['products_model']) . '">' . TEXT_SKU . ' ' . $products['products_model'] . '<br>' . TABLE_HEADING_ID . ': ' . $products['products_id'] . '</span></span>' .
                        '<input class="cell_identify" type="hidden" value="' . $products['products_id'] . '">' .
                        '<input class="cell_type" type="hidden" value="product">' .
                        '</div>' .
                        '</div>',
                        ($products['products_status'] == 1 ? '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="check_on_off" checked="checked">' : '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="check_on_off">')
                    );
                    //$categories_query_numrows++;
                    $rowsCounter++;
                    if ($rowsCounter >= $length) {
                        break;
                    }
                }
            }
        } else {
            // BRAND listing
            $list_bread_crumb = '';
            $ff = $search;
            $order = 'p.sort_order, pd.products_name';

            $products_query_raw = "select * from " . TABLE_PRODUCTS . " p " . (intval($output['brand_id']) == -1 ? " left join " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id=p.manufacturers_id " : '') . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on (p.products_id = pd.products_id and pd.language_id='" . intval($languages_id) . "') where pd.affiliate_id = 0 " . (intval($output['brand_id']) > 0 ? " and manufacturers_id = '" . intval($output['brand_id']) . "' " : (intval($output['brand_id']) == -1 ? ' and m.manufacturers_id IS NULL' : '')) . $ff . " {$platform_filter_products} group by p.products_id ORDER BY " . $order;

            $products_split = new \splitPageResults($current_page_number, $length, $products_query_raw, $categories_query_numrows, 'p.products_id');
            $products_query = tep_db_query($products_query_raw);
            while ($products = tep_db_fetch_array($products_query)) {
                $image = \common\classes\Images::getImage($products['products_id']);
                $product_categories_string = '';
                if (true) {
                    $product_categories = \common\helpers\Categories::generate_category_path($products['products_id'], 'product');
                    $product_categories_string .= '';
                    for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                        $category_path = '';
                        for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                            $category_path .= '<span class="category_path__location">' . $product_categories[$i][$j]['text'] . '</span>&nbsp;&gt;&nbsp;';
                        }
                        $category_path = substr($category_path, 0, -16);
                        $product_categories_string .= '<li class="category_path">' . $category_path . '</li>';
                    }
                    $product_categories_string = '<span class="category_path" style="display:block">' . TEXT_LIST_PRODUCT_PLACED_IN . '</span> <ul class="category_path_list">' . $product_categories_string . '</ul>';
                }

                $responseList[] = array(
                    '<div class="handle_cat_list' . ($products['products_status'] == 1 ? '' : ' dis_prod') . '">' .
                    '<span class="handle"><i class="icon-hand-paper-o"></i></span>' .
                    '<div class="prod_name prod_name_double" data-click-double="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $products['products_id']) . '">' .
                    (!empty($image) ? '<span class="prodImgC">' . $image . '</span>' : '<span class="cubic"></span>') .
                    '<span class="prodNameC">' . $products['products_name'] . $product_categories_string . '</span>' .
                    '<span class="prodIDsC"><span title="' . \common\helpers\Output::output_string($products['products_model']) . '">' . TEXT_SKU . ' ' . $products['products_model'] . '<br>' . TABLE_HEADING_ID . ': ' . $products['products_id'] . '</span></span>' .
                    '<input class="cell_identify" type="hidden" value="' . $products['products_id'] . '">' .
                    '<input class="cell_type" type="hidden" value="product" data-id="products-' . $products['products_id'] . '">' .
                    '</div>' .
                    '</div>',
                    //$products['products_status']
                    ($products['products_status'] == 1 ? '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="check_on_off" checked="checked">' : '<input type="checkbox" value="' . $products['products_id'] . '" name="products_status" class="check_on_off">')
                );
                //$categories_query_numrows++;
            }
        }
        if (tep_not_null($products_query_raw))
            $_session->set('products_query_raw', $products_query_raw);
        $response = [
            'draw' => $draw,
            'recordsTotal' => $categories_query_numrows,
            'recordsFiltered' => $categories_query_numrows,
            'data' => $responseList,
            'categories' => $categoriesQty,
            'products' => $productsQty,
            'breadcrumb' => $list_bread_crumb,
        ];
        echo json_encode($response);
    }

    public function actionCategoryactions() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $categories_id = Yii::$app->request->post('categories_id');

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.last_xml_export from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        $category_childs = array('childs_count' => \common\helpers\Categories::childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => \common\helpers\Categories::products_in_category_count($categories['categories_id']));

        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new \objectInfo($cInfo_array);

        $heading = array();
        $contents = array();

        /*  echo '<div class="row_imgs">
          <div class="cat_imgs">' . \common\helpers\Image::info_image($cInfo->categories_image, $cInfo->categories_name, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT) . '</div>
          <div class="cat_img_name">' . $cInfo->categories_image . '</div>
          </div>'; */

        return $this->render('categoryactions.tpl', ['cInfo' => $cInfo]);
    }

    public function actionProductactions() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $currencies = new \common\classes\currencies();

        $this->layout = false;

        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.affiliate_id = 0 and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        $image = \common\classes\Images::getImage($pInfo->products_id, 'Small');

        echo '<div class="prod_box_img">' . $image . '</div>';
        echo '<div class="or_box_head prod_head_box">' . \common\helpers\Product::get_products_name($pInfo->products_id, $languages_id) . '</div>';
        echo '<div class="row_or_wrapp">';
        echo '<div class="row_or">
                    <div>' . TEXT_DATE_ADDED . '</div>
                    <div>' . \common\helpers\Date::date_short($pInfo->products_date_added) . '</div>
             </div>';
        if (tep_not_null($pInfo->products_last_modified)) {
            echo '<div class="row_or">
                <div>' . TEXT_LAST_MODIFIED . '</div>
                <div>' . \common\helpers\Date::date_short($pInfo->products_last_modified) . '</div>
         </div>';
        }
        if (date('Y-m-d') < $pInfo->products_date_available) {
            echo '<div class="row_or">
                <div>' . TEXT_DATE_AVAILABLE . '</div>
                <div>' . \common\helpers\Date::date_short($pInfo->products_date_available) . '</div>
         </div>';
        }

        if (USE_MARKET_PRICES == 'True') {
            echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_PRICE_INFO . '</div>
                    <div>' . $currencies->format(\common\helpers\Product::get_products_price($pInfo->products_id, 1, 0, $currencies->currencies[DEFAULT_CURRENCY]['id'])) . '</div>
             </div>';
            echo '<div class="row_or">
                   <div>' . TEXT_PRODUCTS_QUANTITY_INFO . '</div>
                   <div>' . $pInfo->products_quantity . '</div>
            </div>';
        } else {
            echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_PRICE_INFO . '</div>
                    <div>' . $currencies->format($pInfo->products_price) . '</div>
             </div>';
            echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_QUANTITY_INFO . '</div>
                    <div>' . $pInfo->products_quantity . '</div>
             </div>';
        }
        echo '<div class="row_or">
                    <div>' . TEXT_PRODUCTS_AVERAGE_RATING . '</div>
                    <div>' . number_format($pInfo->average_rating, 2) . '%</div>
             </div>';
        echo '<div class="row_or">
                    <div>' . TEXT_SORT_ORDER . '</div>
                    <div>' . $pInfo->sort_order . '</div>
             </div>';
        echo '</div>';
        echo '<div class="btn-toolbar btn-toolbar-order">';
        echo '<a class="btn btn-primary btn-process-order btn-edit" href="' . tep_href_link(FILENAME_CATEGORIES . '/productedit', 'pID=' . $pInfo->products_id) . '">' . IMAGE_EDIT . '</a>';
        echo '<button class="btn btn-delete btn-no-margin" onclick="confirmDeleteProduct(' . $pInfo->products_id . ')">' . IMAGE_DELETE . '</button>';
        echo '<button class="btn btn-move" onclick="confirmMoveProduct(' . $pInfo->products_id . ')">' . IMAGE_MOVE . '</button>';
        echo '<button class="btn btn-copy btn-no-margin" onclick="confirmCopyProduct(' . $pInfo->products_id . ')">' . IMAGE_COPY_TO . '</button>';

        if (!tep_session_is_registered('login_vendor')) {
            echo '<button class="btn" onclick="confirmCopyProductAttr(' . $pInfo->products_id . ')">' . IMAGE_COPY_ATTRIBUTES . '</button>';
            /* if ($pID) {
              echo '<div>' . ATTRIBUTES_NAMES_HELPER . '</div>';
              } */
        }
        echo '</div>';
    }

    public function actionConfirmCategoryMove() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $categories_id = Yii::$app->request->post('categories_id');

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.last_xml_export from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        $category_childs = array('childs_count' => \common\helpers\Categories::childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => \common\helpers\Categories::products_in_category_count($categories['categories_id']));

        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new \objectInfo($cInfo_array);

        return $this->render('confirmcategorymove.tpl', ['cInfo' => $cInfo]);
    }

    public function actionCategoryMove() {
        $this->layout = false;
        $categories_id = Yii::$app->request->post('categories_id');
        $parent_id = Yii::$app->request->post('move_to_category_id');
        tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int) $parent_id . "' where categories_id = '" . (int) $categories_id . "'");
        $this->view->categoriesTree = $this->getCategoryTree();
        return $this->render('cat_main_box');
    }

    public function actionConfirmcategorydelete() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        if (Yii::$app->request->isPost) {
            $categories_id = Yii::$app->request->post('categories_id');
        } else {
            $categories_id = Yii::$app->request->get('categories_id');
        }

        $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.last_xml_export from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int) $categories_id . "' and c.categories_id = cd.categories_id and cd.affiliate_id = 0 and cd.language_id = '" . (int) $languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        $category_childs = array('childs_count' => \common\helpers\Categories::childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => \common\helpers\Categories::products_in_category_count($categories['categories_id']));

        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new \objectInfo($cInfo_array);

        $heading = array();
        $contents = array();

        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</b>');
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</div>';

        //$contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'action=delete_category_confirm&cPath=') . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        /*  $contents[] = array('text' => TEXT_DELETE_CATEGORY_INTRO);
          $contents[] = array('text' => '<br><b>' . $cInfo->categories_name . '</b>');
          if ($cInfo->childs_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count));
          if ($cInfo->products_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count)); */
        //$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');

        echo tep_draw_form('categories', FILENAME_CATEGORIES, \common\helpers\Output::get_all_get_params(array('action')) . 'action=delete_category_confirm', 'post', 'id="categories_edit" onSubmit="return deleteCategory();"');
        echo '<div class="col_title">' . TEXT_DELETE_CATEGORY_INTRO . '</div>';
        echo '<div class="col_desc">' . $cInfo->categories_name . '</div>';
        if ($cInfo->childs_count > 0)
            echo '<div class="col_desc">' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count) . '</div>';
        if ($cInfo->products_count > 0)
            echo '<div class="col_desc">' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count) . '</div>';
        /* $box = new \box;
          echo $box->infoBox($heading, $contents); */
        ?>
        <div class="btn-toolbar btn-toolbar-order">
            <button class="btn btn-delete btn-no-margin"><?php echo IMAGE_DELETE; ?></button><button class="btn btn-cancel" onClick="return resetStatement()"><?php echo IMAGE_CANCEL; ?></button>
            <?php
            /* echo '<input type="submit" class="btn btn-primary" value="' . IMAGE_DELETE . '" >';
              echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">'; */

            echo tep_draw_hidden_field('categories_id', $cInfo->categories_id);
            ?>
        </div>
        </form>
        <?php
    }

    public function actionConfirmProductMove() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.affiliate_id = 0 and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        $pInfo->categories_id = Yii::$app->request->post('categories_id');

        return $this->render('confirmproductmove.tpl', ['pInfo' => $pInfo]);
    }

    public function actionProductMove() {
        // move_to_category_id products_id categories_id
        $products_id = Yii::$app->request->post('products_id');
        $new_parent_id = Yii::$app->request->post('move_to_category_id');
        $current_category_id = Yii::$app->request->post('categories_id');

        $duplicate_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $new_parent_id . "'");
        $duplicate_check = tep_db_fetch_array($duplicate_check_query);
        if ($duplicate_check['total'] < 1)
            tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int) $new_parent_id . "' where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $current_category_id . "'");

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    public function actionConfirmProductAttrCopy() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.affiliate_id = 0 and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        return $this->render('confirmproductattrcopy.tpl', ['pInfo' => $pInfo]);
    }

    public function actionProductAttrCopy() {
        $products_id = Yii::$app->request->post('products_id');
        $copy_to_products_id = Yii::$app->request->post('copy_to_products_id');
        \common\helpers\Attributes::copy_products_attributes($products_id, $copy_to_products_id);
    }

    public function actionConfirmProductCopy() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.affiliate_id = 0 and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        $pInfo->categories_id = Yii::$app->request->post('categories_id');

        //$heading = array();
        //$contents = array();
        //$box = new \box;
        //echo $box->infoBox($heading, $contents);
        return $this->render('confirmproductcopy.tpl', ['pInfo' => $pInfo]);
    }

    public function actionProductCopy() {
        if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
            $products_id = tep_db_prepare_input($_POST['products_id']);
            $categories_id = tep_db_prepare_input($_POST['categories_id']);

            if ($_POST['copy_as'] == 'link') {
                if ($categories_id != $current_category_id) {
                    $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $categories_id . "'");
                    $check = tep_db_fetch_array($check_query);
                    if ($check['total'] < '1') {
                        tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");
                    }
                } else {
                    $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
                }
            } elseif ($_POST['copy_as'] == 'duplicate') {
                // BOF MaxiDVD: Modified For Ultimate Images Pack!
                $product_query = tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                $product = tep_db_fetch_array($product_query);

                $str = "insert into " . TABLE_PRODUCTS . " set ";
                foreach ($product as $key => $value) {
                    if ($key != 'products_id') {
                        if ($key == 'products_status')
                            $value = 0;
                        if (is_null($value)) {
                            $str .= " " . $key . " = NULL, ";
                        } else {
                            $str .= " " . $key . " = '" . tep_db_input($value) . "', ";
                        }
                    }
                }
                $str = substr($str, 0, strlen($str) - 2);
                tep_db_query($str);

                $dup_products_id = tep_db_insert_id();

                $description_query = tep_db_query("select * from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int) $products_id . "'");
                while ($description = tep_db_fetch_array($description_query)) {
                    $str = "insert into " . TABLE_PRODUCTS_DESCRIPTION . " set ";
                    foreach ($description as $key => $value) {
                        if ($key != 'products_id') {
                            $str .= " " . $key . " = '" . tep_db_input($value) . "', ";
                        } else {
                            $str .= " products_id = '" . $dup_products_id . "', ";
                        }
                    }
                    $str = substr($str, 0, strlen($str) - 2);
                    tep_db_query($str);
                }

                tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $dup_products_id . "', '" . (int) $categories_id . "')");
                $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . tep_db_input($products_id) . "'");
                tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . $dup_products_id . "'");
                while ($data = tep_db_fetch_array($data_query)) {
                    tep_db_query("insert into " . TABLE_PRODUCTS_PRICES . " (products_id, groups_id, currencies_id, products_group_price, products_group_discount_price) values ('" . $dup_products_id . "', '" . $data['groups_id'] . "', '" . $data['currencies_id'] . "', '" . $data['products_group_price'] . "', '" . $data['products_group_discount_price'] . "')");
                }

                // [[ Properties
                if (PRODUCTS_PROPERTIES == 'True') {
                    $properties_query = tep_db_query("select * from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . tep_db_input($products_id) . "'");
                    while ($properties = tep_db_fetch_array($properties_query)) {
                        tep_db_query("insert into " . TABLE_PROPERTIES_TO_PRODUCTS . " (products_id, properties_id, values_id, values_flag) values ('" . (int) $dup_products_id . "', '" . (int) $properties['properties_id'] . "', '" . (int) $properties['values_id'] . "', '" . (int) $properties['values_flag'] . "')");
                    }
                }
                // ]]

                if (PRODUCTS_BUNDLE_SETS == 'True') {
                    $bundle_sets_query = tep_db_query("select * from " . TABLE_SETS_PRODUCTS . " where sets_id = '" . tep_db_input($products_id) . "'");
                    while ($bundle_sets = tep_db_fetch_array($bundle_sets_query)) {
                        tep_db_query("insert into " . TABLE_SETS_PRODUCTS . " (sets_id, product_id, num_product, sort_order) values ('" . (int) $dup_products_id . "', '" . (int) $bundle_sets['product_id'] . "', '" . (int) $bundle_sets['num_product'] . "', '" . (int) $bundle_sets['sort_order'] . "')");
                    }
                }

                // [[ SUPPLEMENT_STATUS
                if (SUPPLEMENT_STATUS == 'True') {
                    $query = tep_db_query("select * from " . TABLE_PRODUCTS_UPSELL . " where products_id = '" . (int) $products_id . "'");
                    while ($data = tep_db_fetch_array($query)) {
                        tep_db_query("insert into " . TABLE_PRODUCTS_UPSELL . " (products_id, upsell_id, sort_order) values ('" . $dup_products_id . "', '" . $data['upsell_id'] . "', '" . $data['sort_order'] . "')");
                    }

                    $query = tep_db_query("select * from " . TABLE_PRODUCTS_XSELL . " where products_id = '" . (int) $products_id . "'");
                    while ($data = tep_db_fetch_array($query)) {
                        tep_db_query("insert into " . TABLE_PRODUCTS_XSELL . " (products_id, xsell_id, sort_order) values ('" . $dup_products_id . "', '" . $data['xsell_id'] . "', '" . $data['sort_order'] . "')");
                    }
                }
                // ]]
                // BOF: WebMakers.com Added: Attributes Copy on non-linked
                $products_id_from = tep_db_input($products_id);
                $products_id_to = $dup_products_id;
                $products_id = $dup_products_id;
                if ($_POST['copy_attributes'] == 'copy_attributes_yes' and $_POST['copy_as'] == 'duplicate') {
                    // WebMakers.com Added: Copy attributes to duplicate product
                    // $products_id_to= $copy_to_products_id;
                    // $products_id_from = $pID;
                    $copy_attributes_delete_first = '1';
                    $copy_attributes_duplicates_skipped = '1';
                    $copy_attributes_duplicates_overwrite = '0';

                    if (DOWNLOAD_ENABLED == 'true') {
                        $copy_attributes_include_downloads = '1';
                        $copy_attributes_include_filename = '1';
                    } else {
                        $copy_attributes_include_downloads = '0';
                        $copy_attributes_include_filename = '0';
                    }
                    \common\helpers\Attributes::copy_products_attributes($products_id_from, $products_id_to);
                    // EOF: WebMakers.com Added: Attributes Copy on non-linked
                }
            }

            if (USE_CACHE == 'true') {
                \common\helpers\System::reset_cache_block('categories');
                \common\helpers\System::reset_cache_block('also_purchased');
            }
        }
    }

    public function actionConfirmproductdelete() {

        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = Yii::$app->request->post('products_id');

        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model, p.sort_order, p.last_xml_export from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.language_id = '" . (int) $languages_id . "'  and pd.affiliate_id = 0 and p.products_id = '" . (int) $products_id . "'");
        $products = tep_db_fetch_array($products_query);

        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int) $products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new \objectInfo($pInfo_array);

        $heading = array();
        $contents = array();

        /* $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</b>'); */
        echo '<div class="or_box_head">' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</div>';
        echo tep_draw_form('products', FILENAME_CATEGORIES, \common\helpers\Output::get_all_get_params(array('action')) . 'action=delete_product_confirm', 'post', 'id="products_edit" onSubmit="return deleteProduct();"');
        //$contents = array('form' => tep_draw_form('products', FILENAME_CATEGORIES, 'action=delete_product_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        echo '<div class="col_title">' . TEXT_DELETE_PRODUCT_INTRO . '</div>';
        /* $contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
          $contents[] = array('text' => '<br><b>' . $pInfo->products_name . '</b>'); */
        echo '<div class="col_desc"><b>' . $pInfo->products_name . '</b></div>';
        $product_categories_string = '';
        $product_categories = \common\helpers\Categories::generate_category_path($pInfo->products_id, 'product');
        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
            $category_path = '';
            for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
                $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
            }
            $category_path = substr($category_path, 0, -16);
            $product_categories_string .= tep_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i]) - 1]['id'], true) . '&nbsp;' . $category_path . '<br>';
        }
        $product_categories_string = substr($product_categories_string, 0, -4);
        echo '<div class="col_desc">' . $product_categories_string . '</div>';
        $contents[] = array('text' => '<br>' . $product_categories_string);
        //$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');


        /*
          $box = new \box;
          echo $box->infoBox($heading, $contents); */
        ?>
        <p class="btn-toolbar btn-toolbar-order">
            <?php
            echo '<button class="btn btn-delete btn-no-margin"><span>' . IMAGE_DELETE . '</span></button>';
            echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return resetStatement()">';

            echo tep_draw_hidden_field('products_id', $pInfo->products_id);
            ?>
        </p>
        </form>
        <?php
    }

    public function actionCategorydelete() {
        $this->layout = false;

        if (isset($_POST['categories_id']) && $_POST['categories_id'] > 0) {
            $categories_id = tep_db_prepare_input($_POST['categories_id']);

            $categories = \common\helpers\Categories::get_category_tree($categories_id, '', '0', '', true);
            $products = array();
            $products_delete = array();

            for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
                $product_ids_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int) $categories[$i]['id'] . "'");

                while ($product_ids = tep_db_fetch_array($product_ids_query)) {
                    $products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
                }
            }

            reset($products);
            while (list($key, $value) = each($products)) {
                $category_ids = '';

                for ($i = 0, $n = sizeof($value['categories']); $i < $n; $i++) {
                    $category_ids .= "'" . (int) $value['categories'][$i] . "', ";
                }
                $category_ids = substr($category_ids, 0, -2);

                $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $key . "' and categories_id not in (" . $category_ids . ")");
                $check = tep_db_fetch_array($check_query);
                if ($check['total'] < '1') {
                    $products_delete[$key] = $key;
                }
            }

            // removing categories can be a lengthy process
            set_time_limit(0);
            for ($i = 0, $n = sizeof($categories); $i < $n; $i++) {
                \common\helpers\Categories::remove_category($categories[$i]['id']);
                if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
                   $ext::deleteCategoryLinks($categories[$i]['id']);
                }
            }

            reset($products_delete);
            while (list($key) = each($products_delete)) {
                \common\helpers\Product::remove_product($key);
                if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
                   $ext::deleteProductLinks($key);
                }
            }
        }

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
        \common\helpers\Categories::update_categories();

        $this->view->categoriesTree = $this->getCategoryTree();
        return $this->render('cat_main_box');
    }

    public function actionProductdelete() {

        $this->layout = false;

        if (isset($_POST['product_categories']) && is_array($_POST['product_categories'])) {
            $product_id = Yii::$app->request->post('products_id');
            $product_categories = $_POST['product_categories'];

            for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
                tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "' and categories_id = '" . (int) $product_categories[$i] . "'");
            }

            $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $product_id . "'");
            $product_categories = tep_db_fetch_array($product_categories_query);

            if ($product_categories['total'] == '0') {
                \common\helpers\Product::remove_product($product_id);
                if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
                   $ext::deleteProductLinks($product_id);
                }
            }
        }

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    public function actionProductedit() {
        global $languages_id, $language, $login_id;
        $affiliate_id = 0;

        \common\helpers\Translation::init('admin/categories');

        $currencies = new \common\classes\currencies();

        $products_id = (int) Yii::$app->request->get('pID'); //products_id

        $in_category_id = intval(Yii::$app->request->get('category_id', 0));

        $product_query = tep_db_query("select p.*, pd.products_name, pd.products_viewed from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int) $products_id . "' and p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "'");
        $product = tep_db_fetch_array($product_query);
        $pInfo = new \objectInfo($product);
        if (!isset($pInfo->products_id)) {
            $pInfo->products_id = $products_id;
        }

        if (!empty($pInfo->products_date_available)) {
            $pInfo->products_date_available = \common\helpers\Date::date_short($pInfo->products_date_available);
        }
        $manufacturers_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id=" . (int) $pInfo->manufacturers_id);
        $manufacturers = tep_db_fetch_array($manufacturers_query);
        $pInfo->manufacturer_name = $manufacturers['manufacturers_name'];

        $this->selectedMenu = array('catalog', 'categories');

        $str_full = strlen($pInfo->products_name);
        if ($str_full > 35) {
            $st_full_name = mb_substr($pInfo->products_name, 0, 35);
            $st_full_name .= '...';
            $st_full_name_view = '<span title="' . $pInfo->products_name . '">' . $st_full_name . '</span>';
        } else {
            $st_full_name_view = $pInfo->products_name;
        }
        $text_new_or_edit = ($products_id == 0) ? TEXT_NEW_PRODUCT : T_EDIT_PROD . ' "' . $st_full_name_view . '"';
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('orders/productedit'), 'title' => $text_new_or_edit);

        $this->view->groups = [];
        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroups')) {
            $ext::getGroups();
        }

        $documents = [];
        if ($ext = \common\helpers\Acl::checkExtension('ProductDocuments', 'getDocuments')) {
            $documents = $ext::getDocuments($products_id);
        }
        $this->view->documents = $documents;

        if ( ($ext = \common\helpers\Acl::checkExtension('Inventory', 'getInventory')) && PRODUCTS_INVENTORY == 'True') {
            $ext::getInventory($products_id, $languages_id);
        } else {
            $this->view->showInventory = false;
        }

            $options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_sort_order, products_options_name");
        if (tep_db_num_rows($options_query)) {
            $attributes = [];

            $options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_sort_order, products_options_name");
            while ($options = tep_db_fetch_array($options_query)) {
                $values_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p where pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id = '" . $options['products_options_id'] . "' and pov.language_id = '" . $languages_id . "' order by products_options_values_sort_order, products_options_values_name");
                $option = [];
                while ($values = tep_db_fetch_array($values_query)) {
                    $option[] = [
                        'value' => $values['products_options_values_id'],
                        'name' => htmlspecialchars($values['products_options_values_name'])
                    ];
                }
                $attributes[] = [
                    'id' => $options['products_options_id'],
                    'label' => htmlspecialchars($options['products_options_name']),
                    'options' => $option,
                ];
            }

            $this->view->attributes = $attributes;

            $selectedAttributes = [];
            $query = tep_db_query(
                    "select pa.products_attributes_id, po.products_options_id, po.products_options_name, pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_discount_price, pa.products_options_sort_order, pa.product_attributes_one_time, pa.products_attributes_weight, pa.products_attributes_weight_prefix, pa.products_attributes_units, pa.products_attributes_units_price, pa.products_attributes_filename, pa.products_attributes_maxdays, pa.products_attributes_maxcount " .
                    "from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS . " po, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov " .
                    "where pa.products_id = '" . $pInfo->products_id . "' and pa.options_id = po.products_options_id and po.language_id = '" . $languages_id . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . $languages_id . "' " .
                    "order by po.products_options_sort_order, po.products_options_name, pa.products_options_sort_order, pov.products_options_values_sort_order, pov.products_options_values_name"
            );
            while ($data = tep_db_fetch_array($query)) {
                if (!isset($selectedAttributes[$data['products_options_id']])) {
                    $prices = [];
                    $prices[0] = \common\helpers\Attributes::get_attributes_price($data['products_attributes_id'], 0, 0);
                    foreach ($this->view->groups as $groups_id => $group) {
                        $prices[$groups_id] = \common\helpers\Attributes::get_attributes_price($data["products_attributes_id"], 0, $groups_id, '0.00');
                    }
                    $products_options_values = [];
                    $products_options_values[] = [
                        'products_attributes_id' => $data['products_attributes_id'],
                        'products_options_values_id' => $data['products_options_values_id'],
                        'products_options_values_name' => $data['products_options_values_name'],
                        'products_attributes_weight_prefix' => $data['products_attributes_weight_prefix'],
                        'products_attributes_weight' => $data['products_attributes_weight'],
                        'price_prefix' => $data['price_prefix'],
                        'prices' => $prices,
                    ];
                    $selectedAttributes[$data['products_options_id']] = [
                        'products_options_id' => $data['products_options_id'],
                        'products_options_name' => $data['products_options_name'],
                        'values' => $products_options_values,
                        'is_ordered_values' => !empty($data['products_options_sort_order']),
                        'ordered_value_ids' => ',' . $data['products_options_values_id'],
                    ];
                } else {
                    $prices = [];
                    $prices[0] = \common\helpers\Attributes::get_attributes_price($data['products_attributes_id'], 0, 0);
                    foreach ($this->view->groups as $groups_id => $group) {
                        $prices[$groups_id] = \common\helpers\Attributes::get_attributes_price($data["products_attributes_id"], 0, $groups_id, '0.00');
                    }
                    $selectedAttributes[$data['products_options_id']]['values'][] = [
                        'products_attributes_id' => $data['products_attributes_id'],
                        'products_options_values_id' => $data['products_options_values_id'],
                        'products_options_values_name' => $data['products_options_values_name'],
                        'products_attributes_weight_prefix' => $data['products_attributes_weight_prefix'],
                        'products_attributes_weight' => $data['products_attributes_weight'],
                        'price_prefix' => $data['price_prefix'],
                        'prices' => $prices,
                    ];
                    $selectedAttributes[$data['products_options_id']]['ordered_value_ids'] .= (',' . $data['products_options_values_id']);
                    $selectedAttributes[$data['products_options_id']]['is_ordered_values'] = $selectedAttributes[$data['products_options_id']]['is_ordered_values'] || !empty($data['products_options_sort_order']);
                }
            }
            foreach ($selectedAttributes as $__opt_id => $__opt_data) {
                if ($__opt_data['is_ordered_values']) {
                    $selectedAttributes[$__opt_id]['ordered_value_ids'] .= ',';
                } else {
                    $selectedAttributes[$__opt_id]['ordered_value_ids'] = '';
                }
            }
            $this->view->selectedAttributes = $selectedAttributes;
        }

        if ($products_id == 0) {
            $this->view->showStatistic = false;
        } else {
            $this->view->showStatistic = true;
            $this->view->statistic = new \stdClass();
            $this->view->statistic->price = $currencies->format(\common\helpers\Product::get_products_price($pInfo->products_id));
            $this->view->statistic->products_date_added = \common\helpers\Date::datetime_short($pInfo->products_date_added);
            $this->view->statistic->products_last_modified = \common\helpers\Date::datetime_short($pInfo->products_last_modified);
            $this->view->statistic->products_viewed = $pInfo->products_viewed;

            if ($this->view->showInventory) {
                $inventoryListing = [];
                $inventory_query = tep_db_query("select * from " . TABLE_INVENTORY . " where prid = '" . (int) $pInfo->products_id . "'");
                while ($inventory_data = tep_db_fetch_array($inventory_query)) {
                    $arr = preg_split("/[{}]/", $inventory_data['products_id']);
                    $label = '';
                    for ($i = 1, $n = sizeof($arr); $i < $n; $i = $i + 2) {
                        $options_name_data = tep_db_fetch_array(tep_db_query("select products_options_name as name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $arr[$i] . "' and language_id  = '" . (int) $languages_id . "'"));
                        $options_values_name_data = tep_db_fetch_array(tep_db_query("select products_options_values_name as name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id  = '" . $arr[$i + 1] . "' and language_id  = '" . (int) $languages_id . "'"));
                        if ($label == '') {
                            $label = $options_name_data['name'] . ' : ' . $options_values_name_data['name'];
                        } else {
                            $label .= ', ' . $options_name_data['name'] . ' : ' . $options_values_name_data['name'];
                        }
                    }

                    $inventoryListing[] = [
                        'label' => $label,
                        'price' => $currencies->format(\common\helpers\Product::get_products_price($pInfo->products_id)),
                    ];
                }
                $this->view->statistic->inventory = $inventoryListing;
            }

            $orders_data_array = array('ordered' => array(), 'price' => array());
            $date_from = date('Y-m-d H:i:s', mktime(0, 0, 0, date('m') + 1, 1, date('Y') - 1));
            $orders_query = tep_db_query("select year(o.date_purchased) as date_year, month(o.date_purchased) as date_month, count(*) as total_orders, avg(op.products_price) as price, sum(op.products_quantity) as total from " . TABLE_ORDERS . " o inner join " . TABLE_ORDERS_PRODUCTS . " op on (o.orders_id = op.orders_id and op.products_id = '" . $pInfo->products_id . "') where o.date_purchased >= '" . tep_db_input($date_from) . "' group by year(o.date_purchased), month(o.date_purchased) order by year(o.date_purchased), month(o.date_purchased)");
            while ($orders = tep_db_fetch_array($orders_query)) {
                $orders_data_array['ordered'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['total'] . ']';
                $orders_data_array['price'][] = '[' . mktime(0, 0, 0, $orders['date_month'], 1, $orders['date_year']) . '000,' . $orders['price'] . ']';
            }
            $this->view->statistic->orderedGrid = implode(" , ", $orders_data_array['ordered']);
            $this->view->statistic->priceGrid = implode(" , ", $orders_data_array['price']);
        }

        $pDescription = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $pDescription[$i]['id'] = $languages[$i]['id'];

            $product_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . $products_id . "' and language_id = '" . (int) $languages[$i]['id'] . "' and affiliate_id = '" . (int) $affiliate_id . "'");
            $product_description = tep_db_fetch_array($product_description_query);
            $productDescription = new \objectInfo($product_description);

            $pDescription[$i]['products_name'] = tep_draw_input_field('products_name[' . $languages[$i]['id'] . ']', $productDescription->products_name, 'class="form-control form-control-small"');
            $pDescription[$i]['products_description_short'] = tep_draw_textarea_field('products_description_short[' . $languages[$i]['id'] . ']', 'soft', '70', '15', $productDescription->products_description_short, 'class="form-control ckeditor text-dox-01"');
            $pDescription[$i]['products_description'] = tep_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '70', '15', $productDescription->products_description, 'class="form-control ckeditor text-dox-02"');

            $pDescription[$i]['products_seo_page_name'] = tep_draw_input_field('products_seo_page_name[' . $languages[$i]['id'] . ']', $productDescription->products_seo_page_name, 'class="form-control form-control-small"');
            $pDescription[$i]['products_self_service'] = tep_draw_textarea_field('products_self_service[' . $languages[$i]['id'] . ']', 'soft', '70', '5', $productDescription->products_self_service, 'class="form-control form-control-small"');
            $pDescription[$i]['products_head_title_tag'] = tep_draw_textarea_field('products_head_title_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', $productDescription->products_head_title_tag, 'class="form-control form-control-small"');
            $pDescription[$i]['products_head_desc_tag'] = tep_draw_textarea_field('products_head_desc_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', $productDescription->products_head_desc_tag, 'class="form-control text-dox-01"');
            $pDescription[$i]['products_head_keywords_tag'] = tep_draw_textarea_field('products_head_keywords_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', $productDescription->products_head_keywords_tag, 'class="form-control text-dox-02"');
            $pDescription[$i]['google_product_category'] = tep_draw_input_field('google_product_category[' . $languages[$i]['id'] . ']', $productDescription->google_product_category, 'size="40" class="form-control form-control-small"');
            $pDescription[$i]['google_product_type'] = tep_draw_pull_down_menu('google_product_type[' . $languages[$i]['id'] . ']', \common\helpers\Categories::get_category_tree(), $productDescription->google_product_type, 'class="form-control form-control-small"');
        }

        $check_data = tep_db_query("select * from " . TABLE_GIVE_AWAY_PRODUCTS . " where products_id ='" . (int) $pInfo->products_id . "'");
        if (tep_db_num_rows($check_data) > 0) {
            $this->view->give_away = 1;
            $check = tep_db_fetch_array($check_data);
            $this->view->shopping_cart_price = $check['shopping_cart_price'];
            $this->view->buy_qty = ($check['buy_qty'] > 0 ? $check['buy_qty'] : '');
            $this->view->products_qty = ($check['products_qty'] > 0 ? $check['products_qty'] : '');
            $this->view->use_in_qty_discount = $check['use_in_qty_discount'];
        } else {
            $this->view->give_away = 0;
            $this->view->shopping_cart_price = '';
            $this->view->buy_qty = '';
            $this->view->products_qty = '';
            $this->view->use_in_qty_discount = 0;
        }

        $check_data = tep_db_query("select * from " . TABLE_GIFT_WRAP_PRODUCTS . " where products_id ='" . (int) $pInfo->products_id . "'");
        if (tep_db_num_rows($check_data) > 0) {
            $this->view->gift_wrap = 1;
            $check = tep_db_fetch_array($check_data);
            $this->view->gift_wrap_price = $check['gift_wrap_price'];
        } else {
            $this->view->gift_wrap = 0;
            $this->view->gift_wrap_price = '';
        }


        $check_data = tep_db_query("select * from " . TABLE_FEATURED . " where products_id ='" . (int) $pInfo->products_id . "'");
        if (tep_db_num_rows($check_data) > 0) {
            $check = tep_db_fetch_array($check_data);
            $this->view->featured = $check['status'];
            $this->view->featured_expires_date = \common\helpers\Date::date_short($check['expires_date']);
        } else {
            $this->view->featured = 0;
            $this->view->featured_expires_date = '';
        }

        $upload_path = \Yii::getAlias('@web');
        $upload_path .= '/uploads/';
        $this->view->upload_path = $upload_path;

        //{Yii::getAlias('@web')}/images/
        $image_path = DIR_WS_CATALOG_IMAGES . 'products' . '/' . $pInfo->products_id . '/';
        //$image_path = Yii::getAlias('@web');


        $images = [];
        $images_query = tep_db_query("select id.*, i.* from " . TABLE_PRODUCTS_IMAGES . " as i left join " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " as id on (i.products_images_id=id.products_images_id and id.language_id=0) where i.products_id = '" . (int) $pInfo->products_id . "' order by i.sort_order");
        while ($images_data = tep_db_fetch_array($images_query)) {

            $description = [];
            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $images_description_query = tep_db_query("select * from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where language_id = '" . (int) $languages[$i]['id'] . "' and products_images_id = '" . (int) $images_data['products_images_id'] . "'");
                $images_description = tep_db_fetch_array($images_description_query);
                $description[$i] = [
                    'key' => ($i + 1),
                    'id' => $languages[$i]['id'],
                    'code' => $languages[$i]['code'],
                    'name' => $languages[$i]['name'],
                    'logo' => $languages[$i]['logo'],
                    'image_title' => $images_description['image_title'],
                    'image_alt' => $images_description['image_alt'],
                    'orig_file_name' => $images_description['orig_file_name'],
                    'hash_file_name' => $images_description['hash_file_name'],
                    'file_name' => $images_description['file_name'],
                    'alt_file_name' => $images_description['alt_file_name'],
                    'no_watermark' => $images_description['no_watermark'],
                    'image_name' => (empty($images_description['hash_file_name']) ? '' : $image_path . $images_description['products_images_id'] . '/' . $images_description['hash_file_name']),
                ];
            }

            $inventory = [];
            if (is_array($this->view->selectedInventory))
                foreach ($this->view->selectedInventory as $key => $value) {
                    $check_data = tep_db_query("select products_images_id from " . TABLE_PRODUCTS_IMAGES_INVENTORY . " where products_images_id='" . $images_data['products_images_id'] . "' and  inventory_id = '" . $value['id'] . "'");
                    if (tep_db_num_rows($check_data)) {
                        $inventory[$key] = 1;
                    } else {
                        $inventory[$key] = 0;
                    }
                }

            $images[] = [
                'products_images_id' => $images_data['products_images_id'],
                'default_image' => $images_data['default_image'],
                'image_status' => $images_data['image_status'],
                'image_name' => (empty($images_data['hash_file_name']) ? '' : $image_path . $images_data['products_images_id'] . '/' . $images_data['hash_file_name']),
                // for language_id = 0
                'image_title' => $images_data['image_title'],
                'image_alt' => $images_data['image_alt'],
                'orig_file_name' => $images_data['orig_file_name'],
                'hash_file_name' => $images_data['hash_file_name'],
                'file_name' => $images_data['file_name'],
                'alt_file_name' => $images_data['alt_file_name'],
                'no_watermark' => $images_data['no_watermark'],
                //
                'description' => $description,
                'inventory' => $inventory,
            ];
        }
        $this->view->images = $images;
        $this->view->imagesQty = count($images);

        $productFile = '';
        if ($pInfo->products_file != '') {
            $productFile .= '<a href="' . tep_href_link(FILENAME_DOWNLOAD, 'filename=' . $pInfo->products_file) . '">' . $pInfo->products_file . '</a><br>';
            $productFile .= tep_draw_hidden_field('products_previous_file', $pInfo->products_file) . '<input type="checkbox" name="delete_products_file" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT;
        }
        $this->view->productFile = $productFile;

        $this->view->tax_classes = ['0' => TEXT_NONE];
        $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
        while ($tax_class = tep_db_fetch_array($tax_class_query)) {
            $this->view->tax_classes[$tax_class['tax_class_id']] = $tax_class['tax_class_title'];
        }

        $this->view->platform_assigned = [];
        if (isset($pInfo->products_id) && intval($pInfo->products_id) > 0) {
            $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_PRODUCTS . " WHERE products_id = '" . intval($pInfo->products_id) . "' ");
            if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
                while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                    $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
                }
            }
        } elseif ($in_category_id > 0) {
            $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id = '" . intval($in_category_id) . "' ");
            if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
                while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                    $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
                }
            }
        } else {
            foreach (\common\classes\platform::getProductsAssignList() as $___data) {
                $this->view->platform_assigned[intval($___data['id'])] = intval($___data['id']);
            }
        }
        $this->view->platform_activate_categories = array();

        foreach (\common\classes\platform::getCategoriesAssignList() as $__category_platform) {
            if (isset($this->view->platform_assigned[$__category_platform['id']]))
                continue;
            $get_notactive_categories_r = tep_db_query(
                    "SELECT p2c.categories_id, plc.platform_id " .
                    "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c " .
                    "  LEFT JOIN " . TABLE_PLATFORMS_CATEGORIES . " plc ON plc.categories_id=p2c.categories_id and plc.platform_id='" . $__category_platform['id'] . "'  " .
                    "WHERE p2c.products_id='" . intval($pInfo->products_id) . "' " .
                    "  /*AND plc.platform_id IS NULL*/"
            );
            while ($_notactive_category = tep_db_fetch_array($get_notactive_categories_r)) {
                foreach (\common\helpers\Categories::generate_category_path($_notactive_category['categories_id']) as $_category_path_array) {
                    if (!isset($this->view->platform_activate_categories[$__category_platform['id']])) {
                        $this->view->platform_activate_categories[$__category_platform['id']] = array();
                    }
                    $this->view->platform_activate_categories[$__category_platform['id']][$_category_path_array[0]['id']] = array(
                        'label' => implode(' &gt; ', array_reverse(array_map(function ($_in) {
                                                    return $_in['text'];
                                                }, $_category_path_array))),
                        'selected' => !is_null($_notactive_category['platform_id']),
                    );
                }
            }
        }


        $this->view->suppliers = [];
        $suppliers_query = tep_db_query("select sp.*, s.suppliers_name from " . TABLE_SUPPLIERS_PRODUCTS . " sp left join " . TABLE_SUPPLIERS . " s on s.suppliers_id = sp.suppliers_id where sp.products_id = '" . (int) $pInfo->products_id . "' and sp.uprid = '" . (int) $pInfo->products_id . "' order by sp.suppliers_id");
        while ($suppliers = tep_db_fetch_array($suppliers_query)) {
            $sInfo = new \objectInfo($suppliers, false);
            $this->view->suppliers[$suppliers['suppliers_id']] = $sInfo;
        }

        $this->view->sale = tep_db_fetch_array(tep_db_query("select * from " . TABLE_SPECIALS . " where products_id = '" . (int) $pInfo->products_id . "'"));

        $this->view->products_group_price_pack_unit = [];
        $this->view->products_group_price_packaging = [];
        $this->view->qty_discounts_pack_unit = [];
        $this->view->qty_discounts_packaging = [];
        $this->view->qty_discounts = [];
        $this->view->defaultCurrenciy = $currencies->currencies[DEFAULT_CURRENCY]['id'];
        $this->view->currenciesTabs = [];
        $this->view->useMarketPrices = (USE_MARKET_PRICES == 'True');
        if ($this->view->useMarketPrices) {
            //$currencies
            // $this->view->qty_discounts[currencies_id][groups_id][$qty]
            foreach ($currencies->currencies as $cur => $value) {
                $this->view->currenciesTabs[$currencies->currencies[$cur]['id']] = $currencies->currencies[$cur]['title'];
                //$this->view->qty_discounts[$currencies->currencies[$cur]['id']] = [];
                //products_group_discount_price
                //groups_id = 0 Main
                // currencies_id

                $products_price_query = tep_db_query("select products_group_discount_price, products_group_discount_price_pack_unit, products_group_discount_price_packaging from " . TABLE_PRODUCTS_PRICES . " where groups_id = 0 and currencies_id=" . (int) $currencies->currencies[$cur]['id']);
                $products_price_data = tep_db_fetch_array($products_price_query);
                if (isset($products_price_data['products_group_discount_price'])) {
                    foreach (explode(';', $products_price_data['products_group_discount_price']) as $qty_discount) {
                        list($qty, $price) = explode(':', $qty_discount);
                        if ($qty > 0 && $price > 0) {
                            $this->view->qty_discounts[$currencies->currencies[$cur]['id']][0][$qty] = $price;
                        }
                    }
                }
                if (isset($products_price_data['products_group_discount_price_pack_unit'])) {
                    foreach (explode(';', $products_price_data['products_group_discount_price_pack_unit']) as $qty_discount) {
                        list($qty, $price) = explode(':', $qty_discount);
                        if ($qty > 0 && $price > 0) {
                            $this->view->qty_discounts_pack_unit[$currencies->currencies[$cur]['id']][0][$qty] = $price;
                        }
                    }
                }
                if (isset($products_price_data['products_group_discount_price_packaging'])) {
                    foreach (explode(';', $products_price_data['products_group_discount_price_packaging']) as $qty_discount) {
                        list($qty, $price) = explode(':', $qty_discount);
                        if ($qty > 0 && $price > 0) {
                            $this->view->qty_discounts_packaging[$currencies->currencies[$cur]['id']][0][$qty] = $price;
                        }
                    }
                }

                if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getMarketGroupDiscounts')) {
                    $ext::getMarketGroupDiscounts($pInfo->products_id, $currencies);
                }
            }
        } else {
            foreach (explode(';', $pInfo->products_price_discount) as $qty_discount) {
                list($qty, $price) = explode(':', $qty_discount);
                if ($qty > 0 && $price > 0) {
                    $this->view->qty_discounts[0][$qty] = $price;
                }
            }
            foreach (explode(';', $pInfo->products_price_discount_pack_unit) as $qty_discount) {
                list($qty, $price) = explode(':', $qty_discount);
                if ($qty > 0 && $price > 0) {
                    $this->view->qty_discounts_pack_unit[0][$qty] = $price;
                }
            }
            foreach (explode(';', $pInfo->products_price_discount_packaging) as $qty_discount) {
                list($qty, $price) = explode(':', $qty_discount);
                if ($qty > 0 && $price > 0) {
                    $this->view->qty_discounts_packaging[0][$qty] = $price;
                }
            }
            if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroupDiscounts')) {
                $ext::getGroupDiscounts($pInfo->products_id, $currencies);
            }
        }
        if ($pInfo->products_price_pack_unit < 0) {
            $pInfo->products_price_pack_unit = '';
        }
        if ($pInfo->products_price_packaging < 0) {
            $pInfo->products_price_packaging = '';
        }

        $xsellProducts = [];
        $query = tep_db_query("select cpxs.xsell_id, cpxs.sort_order, pd.products_name, p.products_status from  " . TABLE_PRODUCTS_XSELL . " cpxs, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where cpxs.xsell_id = p.products_id and cpxs.xsell_id = pd.products_id and pd.language_id = '" . $languages_id . "' and pd.affiliate_id = 0 and cpxs.products_id = '" . (int) $pInfo->products_id . "' order by cpxs.sort_order");
        while ($data = tep_db_fetch_array($query)) {
            $xsellProducts[] = [
                'xsell_id' => $data['xsell_id'],
                'products_name' => $data['products_name'],
                'image' => \common\classes\Images::getImage($data['xsell_id'], 'Small'),
                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['xsell_id'])),
                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
            ];
        }
        $this->view->xsellProducts = $xsellProducts;

        $upsellProducts = [];
        if ($ext = \common\helpers\Acl::checkExtension('UpSell', 'getProducts')) {
            $upsellProducts = $ext::getProducts($pInfo);
        }
        $this->view->upsellProducts = $upsellProducts;

        $bundlesProducts = [];
        if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'getProducts')) {
            $bundlesProducts = $ext::getProducts($pInfo);
        }
        $this->view->bundlesProducts = $bundlesProducts;

        $this->view->properties_tree = \common\helpers\Properties::get_properties_tree('0', '&nbsp;&nbsp;&nbsp;&nbsp;', '', false);

        $this->view->properties_hiddens = '';
        $this->view->properties_array = array();
        $this->view->values_array = array();
        $properties_query = tep_db_query("select properties_id, if(values_id > 0, values_id, values_flag) as values_id from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . (int) $pInfo->products_id . "'");
        while ($properties = tep_db_fetch_array($properties_query)) {
            if (!in_array($properties['properties_id'], $this->view->properties_array)) {
                $this->view->properties_array[] = $properties['properties_id'];
                $this->view->properties_hiddens .= tep_draw_hidden_field('prop_ids[]', $properties['properties_id']);
            }
            $this->view->values_array[$properties['properties_id']][] = $properties['values_id'];
            $this->view->properties_hiddens .= tep_draw_hidden_field('val_ids[' . $properties['properties_id'] . '][]', $properties['values_id']);
        }
        $this->view->properties_tree_array = \common\helpers\Properties::generate_properties_tree(0, $this->view->properties_array, $this->view->values_array);

        $videos_query = tep_db_query("select * from " . TABLE_PRODUCTS_VIDEOS . " where products_id = '" . (int) $pInfo->products_id . "'");
        $videos = array();
        while ($item = tep_db_fetch_array($videos_query)) {
            $videos[$item['language_id']][] = $item;
        }
        $this->view->videos = $videos;

        if (Yii::$app->request->isPost) {
            $this->layout = false;
        }

        $this->view->product_next = $this->view->product_prev = 0;
        $_session = Yii::$app->session;
        if ($_session->has('products_query_raw')) {
            $products_query_raw_real = $_session->get('products_query_raw');
            if (strpos($products_query_raw_real, 'limit')) {
                $products_query_raw_real = preg_replace("/(.*)limit(.*)/", "$1", $products_query_raw_real);
            }
            $group_by = '';
            if (strpos($products_query_raw_real, 'group by')) {
                $group_by = " group by " . preg_replace("/.*group by(.*)order by.*/", "$1", $products_query_raw_real);
                $products_query_raw_real = preg_replace("/(.*)(group by.*)(order by.*)/", "$1 $3", $products_query_raw_real);
            }
            $products_query_raw_right = preg_replace("/(.*)order by(.*)/", "$1 and p.products_id > " . (int) $pInfo->products_id . $group_by . " order by p.products_id asc limit 1", $products_query_raw_real);
            $products_query_raw_left = preg_replace("/(.*)order by(.*)/", "$1 and p.products_id < " . (int) $pInfo->products_id . $group_by . " order by p.products_id desc limit 1", $products_query_raw_real);
            $product_next = tep_db_fetch_array(tep_db_query($products_query_raw_right));
            $product_prev = tep_db_fetch_array(tep_db_query($products_query_raw_left));
            $this->view->product_next = ( isset($product_next['products_id']) ? $product_next['products_id'] : 0);
            if ($product_next['products_id'])
                $this->view->product_next_name = \common\helpers\Product::get_products_name($product_next['products_id']);
            $this->view->product_prev = ( isset($product_prev['products_id']) ? $product_prev['products_id'] : 0);
            if ($product_prev['products_id'])
                $this->view->product_prev_name = \common\helpers\Product::get_products_name($product_prev['products_id']);
        }

        if ($pInfo->products_id > 0) {
            $pInfo->allocated_quantity = \common\helpers\Product::get_allocated_stock_quantity($pInfo->products_id);
            $pInfo->warehouse_quantity = $pInfo->products_quantity + $pInfo->allocated_quantity;
        }

        $seo_url = tep_db_fetch_array(tep_db_query("select products_seo_page_name from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . $products_id . "' and language_id = '" . (int) \common\helpers\Language::get_default_language_id() . "'"));


        $frontends = array();
        foreach (\common\classes\platform::getList(false) as $frontend) {
            if ($this->view->platform_assigned[$frontend['id']]) {
                if ($seo_url['products_seo_page_name']) {
                    $this->view->preview_link[] = [
                        'link' => 'http://' . $frontend['platform_url'] . '/' . $seo_url['products_seo_page_name'],
                        'name' => $frontend['text']
                    ];
                } else {
                    $this->view->preview_link[] = [
                        'link' => 'http://' . $frontend['platform_url'] . '/catalog/product?products_id=' . $pInfo->products_id,
                        'name' => $frontend['text']
                    ];
                }
                $frontends[] = $frontend;
            }
        }
        $this->view->groups_m = array_merge(array(array('groups_id' => 0, 'groups_name' => TEXT_MAIN)), $this->view->groups);
        $gaw_query = tep_db_query("select * from " . TABLE_GIVE_AWAY_PRODUCTS . " where products_id = '" . (int) $pInfo->products_id . "' order by   shopping_cart_price, buy_qty, begin_date, end_date");
        if (tep_db_num_rows($gaw_query) == 0) {
            $this->view->gaw[0] = array();
        } else {
            $tmp = $tmp_group = -1;
            $i = 0;
            while ($gaw = tep_db_fetch_array($gaw_query)) {
                if ($gaw['buy_qty'] == 0) {
                    $this->view->gaw[$gaw['groups_id']][$gaw['currencies_id']] = $gaw;
                    $this->view->gaw[$gaw['groups_id']]['by_total'] = 1;
                } else {
                    $gaw['shopping_cart_price'] = max(0, $gaw['shopping_cart_price']);
                    $this->view->gaw[$gaw['groups_id']][] = $gaw;
                }
                if ($gaw['begin_date'] != '0000-00-00')
                    $this->view->gaw[$gaw['groups_id']]['begin_date'] = \common\helpers\Date::date_short($gaw['begin_date']);
                if ($gaw['end_date'] != '0000-00-00')
                    $this->view->gaw[$gaw['groups_id']]['end_date'] = \common\helpers\Date::date_short($gaw['end_date']);
            }
        }
        
        $this->view->templates = \backend\design\ProductTemplate::productedit($products_id);

        return $this->render('productedit.tpl', [
                    'default_currency' => $currencies->currencies[DEFAULT_CURRENCY],
                    'languages' => $languages,
                    'languages_id' => $languages_id,
                    'pInfo' => $pInfo,
                    'pDescription' => $pDescription,
                    'categories_id' => $in_category_id,
                    'json_platform_activate_categories' => json_encode($this->view->platform_activate_categories),
        ]);
    }

    public function actionPropertyValues() {
        global $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('products_id');
        $properties_id = (int) Yii::$app->request->post('properties_id');

        $values = array();
        $property = tep_db_fetch_array(tep_db_query("select properties_id, properties_type, multi_choice, multi_line, decimals from " . TABLE_PROPERTIES . " where properties_id = '" . (int) $properties_id . "'"));
        $property['properties_name'] = \common\helpers\Properties::get_properties_name($property['properties_id'], $languages_id);

        if ($property['properties_type'] == 'flag') {
            $values = array();
            $values[] = array('values_id' => '1', 'values' => TEXT_PROP_FLAG_YES);
            $values[] = array('values_id' => '0', 'values' => TEXT_PROP_FLAG_NO);
        } else {
            $properties_values_query = tep_db_query("select values_id, values_text, values_number, values_number_upto, values_alt from " . TABLE_PROPERTIES_VALUES . " where properties_id = '" . (int) $properties_id . "' and language_id = '" . (int) $languages_id . "' order by " . ($property['properties_type'] == 'number' || $property['properties_type'] == 'interval' ? 'values_number' : 'values_text'));
            while ($properties_values = tep_db_fetch_array($properties_values_query)) {
                if ($property['properties_type'] == 'interval') {
                    $properties_values['values'] = (float) number_format($properties_values['values_number'], $property['decimals']) . ' - ' . (float) number_format($properties_values['values_number_upto'], $property['decimals']);
                } elseif ($property['properties_type'] == 'number') {
                    $properties_values['values'] = (float) number_format($properties_values['values_number'], $property['decimals']);
                } else {
                    $properties_values['values'] = $properties_values['values_text'];
                }
                $values[$properties_values['values_id']] = $properties_values;
            }
        }

        return $this->render('property-values.tpl', [
                    'property' => $property,
                    'values' => $values,
        ]);
    }

    public function actionUpdatePropertyValues() {
        $properties_array = Yii::$app->request->post('properties_array', array());
        $values_array = Yii::$app->request->post('values_array', array());

        $values_ids = array();
        $properties_hiddens = '';
        foreach ($properties_array as $key => $properties_id) {
            $properties_hiddens .= tep_draw_hidden_field('prop_ids[]', $properties_id);
            foreach ($values_array[$key] as $values_id) {
                $properties_hiddens .= tep_draw_hidden_field('val_ids[' . $properties_id . '][]', $values_id);
                $values_ids[$properties_id][] = $values_id;
            }
        }

        $this->layout = false;

        return $this->render('property-values-selected.tpl', [
                    'properties_hiddens' => $properties_hiddens,
                    'properties_tree_array' => \common\helpers\Properties::generate_properties_tree(0, $properties_array, $values_ids),
        ]);
    }

    public function actionProductNewOption() {
        global $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('products_id');
        $products_options_id = (int) Yii::$app->request->post('products_options_id');
        $products_options_values_id = (int) Yii::$app->request->post('products_options_values_id');

        $this->view->groups = [];
        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroups')) {
            $ext::getGroups();
        }

        $image_path = DIR_WS_CATALOG_IMAGES . 'products' . '/' . $products_id . '/';
        $images = [];
        $images_query = tep_db_query("select id.*, i.* from " . TABLE_PRODUCTS_IMAGES . " as i left join " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " as id on (i.products_images_id=id.products_images_id and id.language_id=0) where i.products_id = '" . (int) $products_id . "' order by i.sort_order");
        while ($images_data = tep_db_fetch_array($images_query)) {
            $images[] = [
                'products_images_id' => $images_data['products_images_id'],
                'image_name' => (empty($images_data['hash_file_name']) ? '' : $image_path . $images_data['products_images_id'] . '/' . $images_data['hash_file_name']),
            ];
        }
        $this->view->images = $images;

        $attributes = [];

        $options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' and products_options_id='" . $products_options_id . "'");
        while ($options = tep_db_fetch_array($options_query)) {
            $values_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p where pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id = '" . $options['products_options_id'] . "' and  pov.products_options_values_id='" . $products_options_values_id . "' and pov.language_id = '" . $languages_id . "'");
            $option = [];
            while ($values = tep_db_fetch_array($values_query)) {
                $option[] = [
                    'products_options_values_id' => $values['products_options_values_id'],
                    'products_options_values_name' => htmlspecialchars($values['products_options_values_name'])
                ];
            }
            $attributes[] = [
                'products_options_id' => $options['products_options_id'],
                'products_options_name' => htmlspecialchars($options['products_options_name']),
                'values' => $option,
            ];
        }

        if ($ext = \common\helpers\Acl::checkExtension('Inventory', 'getProductNewOption')) {
            return $ext::getProductNewOption($products_id, $attributes);
        }
        return $this->render('product-new-option.tpl', [
                    'products_id' => $products_id,
                    'attributes' => $attributes,
        ]);
    }

    public function actionProductInventoryBox() {
        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $this->view->groups = [];
        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroups')) {
            $ext::getGroups();
        }

        if ($ext = \common\helpers\Acl::checkExtension('Inventory', 'productInventoryBox')) {
            return $ext::productInventoryBox();
        }
    }

    public function actionProductNewAttribute() {
        global $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('products_id');
        $products_options_id = (int) Yii::$app->request->post('products_options_id');
        $products_options_values_id = (int) Yii::$app->request->post('products_options_values_id');

        $this->view->groups = [];
        if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'getGroups')) {
            $ext::getGroups();
        }

        $image_path = DIR_WS_CATALOG_IMAGES . 'products' . '/' . $products_id . '/';
        $images = [];
        $images_query = tep_db_query("select id.*, i.* from " . TABLE_PRODUCTS_IMAGES . " as i left join " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " as id on (i.products_images_id=id.products_images_id and id.language_id=0) where i.products_id = '" . (int) $products_id . "' order by i.sort_order");
        while ($images_data = tep_db_fetch_array($images_query)) {
            $images[] = [
                'products_images_id' => $images_data['products_images_id'],
                'image_name' => (empty($images_data['hash_file_name']) ? '' : $image_path . $images_data['products_images_id'] . '/' . $images_data['hash_file_name']),
            ];
        }
        $this->view->images = $images;
        $values_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p where pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id = '" . $products_options_id . "' and  pov.products_options_values_id='" . $products_options_values_id . "' and pov.language_id = '" . $languages_id . "'");
        $option = [];
        while ($values = tep_db_fetch_array($values_query)) {
            $values['products_options_id'] =$products_options_id;
            $option[] = $values;
        }
        if ($ext = \common\helpers\Acl::checkExtension('Inventory', 'getProductNewAttribute')) {
            return $ext::getProductNewAttribute($products_id, $option, $products_options_id);
        }
        return $this->render('product-new-attribute.tpl', [
                    'options' => $option,
                    'products_id' => $products_id,
                    'products_options_id' => $products_options_id,
        ]);
    }

    public function actionProductNewImage($id) {
        global $languages_id, $language, $login_id;

        \common\helpers\Translation::init('admin/categories');

        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();

        $attributes = [];
        $options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_sort_order, products_options_name");
        if (tep_db_num_rows($options_query)) {
            $options_query = tep_db_query("select products_options_id, products_options_name from " . TABLE_PRODUCTS_OPTIONS . " where language_id = '" . $languages_id . "' order by products_options_sort_order, products_options_name");
            while ($options = tep_db_fetch_array($options_query)) {
                $values_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov, " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " p2p where pov.products_options_values_id = p2p.products_options_values_id and p2p.products_options_id = '" . $options['products_options_id'] . "' and pov.language_id = '" . $languages_id . "' order by products_options_values_sort_order, products_options_values_name");
                $option = [];
                while ($values = tep_db_fetch_array($values_query)) {
                    $option[] = [
                        'value' => $values['products_options_values_id'],
                        'name' => htmlspecialchars($values['products_options_values_name'])
                    ];
                }
                $attributes[] = [
                    'id' => $options['products_options_id'],
                    'label' => htmlspecialchars($options['products_options_name']),
                    'options' => $option,
                ];
            }
        }
        $this->view->attributes = $attributes;

        $image_path = \Yii::getAlias('@web');
        $image_path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $file_name = Yii::$app->request->get('name');

        $Item = [
            'products_images_id' => 0,
            'default_image' => 0,
            'image_status' => 1,
            'image_name' => (empty($file_name) ? '' : $image_path . $file_name),
            // for language_id = 0
            'image_title' => '',
            'image_alt' => '',
            'orig_file_name' => $file_name,
            'hash_file_name' => '',
            'file_name' => '',
            'alt_file_name' => '',
            'no_watermark' => 0,
        ];
        $description = [];
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $description[$i] = [
                'key' => ($i + 1),
                'id' => $languages[$i]['id'],
                'code' => $languages[$i]['code'],
                'name' => $languages[$i]['name'],
                'logo' => $languages[$i]['image'],
                'image_title' => '',
                'image_alt' => '',
                'orig_file_name' => '',
                'hash_file_name' => '',
                'file_name' => '',
                'alt_file_name' => '',
                'no_watermark' => 0,
                'image_name' => '',
            ];
        }

        return $this->render('product-new-image.tpl', [
                    'Item' => $Item,
                    'description' => $description,
                    'Key' => $id,
        ]);
    }

    public function actionProductSubmit() {
        global $languages_id, $language, $messageStack;

        \common\helpers\Translation::init('admin/categories');

        $currencies = new \common\classes\currencies();

        $path = \Yii::getAlias('@webroot');
        $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $affiliate_id = 0;
        $products_id = (int) Yii::$app->request->post('products_id');

        if ((int) $products_id > 0) {
            $action = 'update_product';
        } else {
            $action = 'insert_product';
        }


        /**
         * Main details
         */
        $sql_data_array = [];

        $sql_data_array['products_status'] = (int) Yii::$app->request->post('products_status');
        $sql_data_array['products_old_seo_page_name'] = tep_db_prepare_input($_POST['products_old_seo_page_name']);

        $sql_data_array['manufacturers_id'] = (int) Yii::$app->request->post('manufacturers_id');
        $brandName = tep_db_prepare_input(Yii::$app->request->post('barnd'));
        if (empty($brandName)) {
            $sql_data_array['manufacturers_id'] = 0;
        } else {
            $brands_query = tep_db_query("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_name = '" . tep_db_input($brandName) . "'");
            $brands = tep_db_fetch_array($brands_query);
            if (isset($brands['manufacturers_id'])) {
                $sql_data_array['manufacturers_id'] = (int) $brands['manufacturers_id'];
            }
        }
        $sql_data_array['stock_indication_id'] = Yii::$app->request->post('stock_indication_id');
        if (is_null($sql_data_array['stock_indication_id'])) {
            unset($sql_data_array['stock_indication_id']);
        }

        $sql_data_array['stock_delivery_terms_id'] = Yii::$app->request->post('stock_delivery_terms_id');
        if (is_null($sql_data_array['stock_delivery_terms_id'])) {
            unset($sql_data_array['stock_delivery_terms_id']);
        }

        $sql_data_array['products_model'] = Yii::$app->request->post('products_model');
        $sql_data_array['products_ean'] = Yii::$app->request->post('products_ean');
        $sql_data_array['products_upc'] = Yii::$app->request->post('products_upc');
        $sql_data_array['products_asin'] = Yii::$app->request->post('products_asin');
        $sql_data_array['products_isbn'] = Yii::$app->request->post('products_isbn');

        $sql_data_array['subscription'] = (int) Yii::$app->request->post('subscription');
        $sql_data_array['subscription_code'] = Yii::$app->request->post('subscription_code');

        if ($ext = \common\helpers\Acl::checkExtension('MinimumOrderQty', 'saveProduct')) {
            $sql_data_array = array_replace($sql_data_array, $ext::saveProduct());
        }
        if ($ext = \common\helpers\Acl::checkExtension('OrderQuantityStep', 'saveProduct')) {
            $sql_data_array = array_replace($sql_data_array, $ext::saveProduct());
        }

        $products_date_available = Yii::$app->request->post('products_date_available');
        if (!empty($products_date_available)) {
            $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $products_date_available);
            $sql_data_array['products_date_available'] = $date->format('Y-m-d');
        } else {
            $sql_data_array['products_date_available'] = '';
        }

        /**
         * Size and Packaging
         */
        $is_virtual = (int) Yii::$app->request->post('is_virtual');
        $sql_data_array['is_virtual'] = $is_virtual;
        if ($is_virtual == 1) {
            //upload
            if (Yii::$app->request->post('delete_products_file') == 'yes') {
                $products_previous_file = Yii::$app->request->post('products_previous_file');
                @unlink(DIR_FS_DOWNLOAD . $products_previous_file);
                $sql_data_array['products_file'] = '';
            } else {
                $products_file_name = Yii::$app->request->post('products_file');
                if (tep_not_null($products_file_name) && ($products_file_name != 'none')) {
                    $tmp_name = \Yii::getAlias('@webroot');
                    $tmp_name .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
                    $tmp_name .= $products_file_name;
                    $new_name = DIR_FS_DOWNLOAD . $products_file_name;
                    copy($tmp_name, $new_name);
                    @unlink($tmp_name);
                    $sql_data_array['products_file'] = tep_db_prepare_input($products_file_name);
                }
                //$products_file_name = Yii::$app->request->post('products_file');
                //if (tep_not_null($products_file_name) && ($products_file_name != 'none')) {
                /* $products_file = new \upload('products_file');
                  $products_file->set_destination(DIR_FS_DOWNLOAD);
                  if ($products_file->parse() && $products_file->save()) {
                  $products_file_name = $products_file->filename;
                  $sql_data_array['products_file'] = tep_db_prepare_input($products_file_name);
                  } */
                //}
            }
        } else {
            $sql_data_array['dimensions_cm'] = Yii::$app->request->post('dimensions_cm'); //string
            $sql_data_array['length_cm'] = Yii::$app->request->post('length_cm');
            $sql_data_array['width_cm'] = Yii::$app->request->post('width_cm');
            $sql_data_array['height_cm'] = Yii::$app->request->post('height_cm');
            $sql_data_array['products_weight'] = $sql_data_array['weight_cm'] = Yii::$app->request->post('weight_cm');

            $sql_data_array['dimensions_in'] = Yii::$app->request->post('dimensions_in'); //string
            $sql_data_array['length_in'] = Yii::$app->request->post('length_in');
            $sql_data_array['width_in'] = Yii::$app->request->post('width_in');
            $sql_data_array['height_in'] = Yii::$app->request->post('height_in');
            $sql_data_array['weight_in'] = Yii::$app->request->post('weight_in');

            $sql_data_array['inner_carton_size'] = Yii::$app->request->post('inner_carton_size'); //string
            $sql_data_array['inner_carton_dimensions_cm'] = Yii::$app->request->post('inner_carton_dimensions_cm'); //string
            $sql_data_array['inner_length_cm'] = Yii::$app->request->post('inner_length_cm');
            $sql_data_array['inner_width_cm'] = Yii::$app->request->post('inner_width_cm');
            $sql_data_array['inner_height_cm'] = Yii::$app->request->post('inner_height_cm');
            $sql_data_array['inner_weight_cm'] = Yii::$app->request->post('inner_weight_cm');

            $sql_data_array['inner_carton_dimensions_in'] = Yii::$app->request->post('inner_carton_dimensions_in'); //string
            $sql_data_array['inner_length_in'] = Yii::$app->request->post('inner_length_in');
            $sql_data_array['inner_width_in'] = Yii::$app->request->post('inner_width_in');
            $sql_data_array['inner_height_in'] = Yii::$app->request->post('inner_height_in');
            $sql_data_array['inner_weight_in'] = Yii::$app->request->post('inner_weight_in');

            $sql_data_array['outer_carton_size'] = Yii::$app->request->post('outer_carton_size'); //string
            $sql_data_array['outer_carton_dimensions_cm'] = Yii::$app->request->post('outer_carton_dimensions_cm'); //string
            $sql_data_array['outer_length_cm'] = Yii::$app->request->post('outer_length_cm');
            $sql_data_array['outer_width_cm'] = Yii::$app->request->post('outer_width_cm');
            $sql_data_array['outer_height_cm'] = Yii::$app->request->post('outer_height_cm');
            $sql_data_array['outer_weight_cm'] = Yii::$app->request->post('outer_weight_cm');

            $sql_data_array['outer_carton_dimensions_in'] = Yii::$app->request->post('outer_carton_dimensions_in'); //string
            $sql_data_array['outer_length_in'] = Yii::$app->request->post('outer_length_in');
            $sql_data_array['outer_width_in'] = Yii::$app->request->post('outer_width_in');
            $sql_data_array['outer_height_in'] = Yii::$app->request->post('outer_height_in');
            $sql_data_array['outer_weight_in'] = Yii::$app->request->post('outer_weight_in');

            $sql_data_array['pack_unit'] = Yii::$app->request->post('pack_unit');
            $sql_data_array['packaging'] = Yii::$app->request->post('packaging'); //string
        }

        if (PRODUCTS_BUNDLE_SETS == 'True') {
            $sql_data_array['products_sets_price'] = tep_db_prepare_input($_POST['products_sets_price']);
            $sql_data_array['products_sets_discount'] = tep_db_prepare_input($_POST['products_sets_discount']);
        }

        //Shipping Surcharge
        $shipping_surcharge = (int) Yii::$app->request->post('shipping_surcharge');
        if ($shipping_surcharge == 0) {
            $sql_data_array['shipping_surcharge_price'] = 0;
        } else {
            $sql_data_array['shipping_surcharge_price'] = Yii::$app->request->post('shipping_surcharge_price');
        }

        $categories_id = (int) Yii::$app->request->post('categories_id');
        if ($action == 'insert_product') {
            $sql_data_array['products_date_added'] = 'now()';
            tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
            $products_id = tep_db_insert_id();

            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");
        } elseif ($action == 'update_product') {
            $sql_data_array['products_last_modified'] = 'now()';
            tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "'");

            $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "'");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] < '1') {
                tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");
            }
        }
        
        if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
            $ext::saveProductLinks($products_id, $_POST);
        }

        // Update stock quantity
        $products_quantity_update = (int) Yii::$app->request->post('products_quantity_update');
        $products_quantity_update_prefix = (Yii::$app->request->post('products_quantity_update_prefix') == '-' ? '-' : '+');
        if ($products_quantity_update > 0) {
            global $login_id;
            \common\helpers\Product::log_stock_history_before_update($products_id, $products_quantity_update, $products_quantity_update_prefix, ['comments' => TEXT_MANUALL_STOCK_UPDATE, 'admin_id' => $login_id]);
            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity " . $products_quantity_update_prefix . $products_quantity_update . " where products_id = '" . (int) $products_id . "'");
        }

        // Give away
        $give_away = Yii::$app->request->post('give_away');
        $use_in_qty_discount = Yii::$app->request->post('use_in_qty_discount',array());
        $shopping_cart_price = Yii::$app->request->post('shopping_cart_price');
        $buy_qty = Yii::$app->request->post('buy_qty');
        $products_qty = Yii::$app->request->post('products_qty');
        $products_qty_gb = Yii::$app->request->post('products_qty_gb');
        $end_date_a = Yii::$app->request->post('end_date');
        $begin_date_a = Yii::$app->request->post('begin_date');
        tep_db_query("delete from " . TABLE_GIVE_AWAY_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
        if (is_array($give_away)) {
            foreach ($give_away as $group_id => $data) {
                if (!empty($begin_date_a[$group_id])) {
                    $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $begin_date_a[$group_id]);
                    $begin_date = $date->format('Y-m-d');
                } else {
                    $begin_date = '';
                }
                if (!empty($end_date_a[$group_id])) {
                    $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $end_date_a[$group_id]);
                    $end_date = $date->format('Y-m-d');
                } else {
                    $end_date = '';
                }
                if (is_array($buy_qty[$group_id])) {
                    $buy_qty[$group_id] = array_map('intval', $buy_qty[$group_id]);
                }
                if (is_array($buy_qty[$group_id]) && array_sum($buy_qty[$group_id]) > 0) { //exists buy value
                    //buy_get array
                    $products_qty_gb[$group_id] = array_map('intval', $products_qty_gb[$group_id]);
                    $use_in_qty_discount[$group_id] = array_map('intval', isset($use_in_qty_discount[$group_id])?$use_in_qty_discount[$group_id]:array());
                    foreach ($buy_qty[$group_id] as $i => $b_qty) {
                        if ($b_qty <= 0 || $products_qty_gb[$group_id][$i] <= 0)
                            continue;

                        $sql_data_array = array(
                            'products_id' => (int) $products_id,
                            'groups_id' => (int) $group_id,
                            'products_qty' => $products_qty_gb[$group_id][$i],
                            'buy_qty' => $b_qty,
                            'shopping_cart_price' => -1,
                            'use_in_qty_discount' => $use_in_qty_discount[$group_id][$i],
                            'begin_date' => $begin_date,
                            'end_date' => $end_date,
                        );
                        tep_db_perform(TABLE_GIVE_AWAY_PRODUCTS, $sql_data_array);
                    }
                } else {
                    //shopping cart total
                    if (USE_MARKET_PRICES == 'True') {
                        foreach ($shopping_cart_price[$group_id] as $currencies_id => $price) {
                            if ((int) $products_qty[$group_id][$currencies_id] <= 0)
                                continue;
                            $sql_data_array = array(
                                'products_id' => (int) $products_id,
                                'groups_id' => (int) $group_id,
                                'currencies_id' => (int) $currencies_id,
                                'products_qty' => (int) $products_qty[$group_id][$currencies_id],
                                'shopping_cart_price' => (double) $shopping_cart_price[$group_id][$currencies_id],
                                'buy_qty' => 0,
                                'use_in_qty_discount' => (int) $use_in_qty_discount[$group_id][$currencies_id],
                                'begin_date' => $begin_date,
                                'end_date' => $end_date,
                            );
                            tep_db_perform(TABLE_GIVE_AWAY_PRODUCTS, $sql_data_array);
                        }
                    } else {
                        if ((int) $products_qty[$group_id] <= 0)
                            continue;
                        $sql_data_array = array(
                            'products_id' => (int) $products_id,
                            'groups_id' => (int) $group_id,
                            'currencies_id' => 0,
                            'products_qty' => (int) $products_qty[$group_id],
                            'shopping_cart_price' => (double) $shopping_cart_price[$group_id],
                            'buy_qty' => 0,
                            'use_in_qty_discount' => (int) $use_in_qty_discount[$group_id],
                            'begin_date' => $begin_date,
                            'end_date' => $end_date,
                        );
                        tep_db_perform(TABLE_GIVE_AWAY_PRODUCTS, $sql_data_array);
                    }
                }
            }
        }

        // Gift wrap
        $gift_wrap = (int) Yii::$app->request->post('gift_wrap');
        $gift_wrap_price = tep_db_prepare_input(Yii::$app->request->post('gift_wrap_price'));
        if ($gift_wrap == 0) {
            tep_db_query("delete from " . TABLE_GIFT_WRAP_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
        } else {
            $check_data = tep_db_query("select * from " . TABLE_GIFT_WRAP_PRODUCTS . " where products_id ='" . (int) $products_id . "'");
            if (tep_db_num_rows($check_data) > 0) {
                $check = tep_db_fetch_array($check_data);
                tep_db_query("update " . TABLE_GIFT_WRAP_PRODUCTS . " set gift_wrap_price = '" . tep_db_input($gift_wrap_price) . "' where gw_id = '" . (int) $check['gw_id'] . "'");
            } else {
                tep_db_query("insert into " . TABLE_GIFT_WRAP_PRODUCTS . " (products_id, gift_wrap_price) values('" . (int) $products_id . "', '" . tep_db_input($gift_wrap_price) . "')");
            }
        }

        // Featured
        $featured = (int) Yii::$app->request->post('featured');
        $featured_expires_date = Yii::$app->request->post('featured_expires_date');
        if ($featured == 0) {
            tep_db_query("delete from " . TABLE_FEATURED . " where products_id = '" . (int) $products_id . "'");
        } else {
            if (!empty($featured_expires_date)) {
                $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $featured_expires_date);
                $featured_expires_date = $date->format('Y-m-d');
            }
            $check_data = tep_db_query("select * from " . TABLE_FEATURED . " where products_id ='" . (int) $products_id . "'");
            if (tep_db_num_rows($check_data) > 0) {
                $check = tep_db_fetch_array($check_data);
                tep_db_query("update " . TABLE_FEATURED . " set featured_last_modified = now(), status = '1', expires_date = '" . tep_db_input($featured_expires_date) . "' where featured_id = '" . (int) $check['featured_id'] . "'");
            } else {
                tep_db_query("insert into " . TABLE_FEATURED . " (products_id, featured_date_added, expires_date, status, affiliate_id) values ('" . (int) $products_id . "', now(), '" . tep_db_input($featured_expires_date) . "', '1', '0')");
            }
        }

        /**
         * Price and Cost
         */
        $pack_unit_full_prices = Yii::$app->request->post('pack_unit_full_prices', array());
        $packaging_full_prices = Yii::$app->request->post('packaging_full_prices', array());

        if (USE_MARKET_PRICES == 'True') {
            $defaultCurrenciy = $currencies->currencies[DEFAULT_CURRENCY]['id'];
            tep_db_query("update " . TABLE_PRODUCTS . " set products_tax_class_id = '" . (int) Yii::$app->request->post('products_tax_class_id') . "', products_price = '" . (float) Yii::$app->request->post('products_price_' . $defaultCurrenciy) . "' where products_id = '" . (int) $products_id . "'");
            if (Yii::$app->request->post('bonus_points_status')) {
                tep_db_query("update " . TABLE_PRODUCTS . " set bonus_points_price = '" . (float) Yii::$app->request->post('bonus_points_price_' . $defaultCurrenciy) . "', bonus_points_cost = '" . (float) Yii::$app->request->post('bonus_points_cost_' . $defaultCurrenciy) . "' where products_id = '" . (int) $products_id . "'");
            } else {
                tep_db_query("update " . TABLE_PRODUCTS . " set bonus_points_price = '0', bonus_points_cost = '0' where products_id = '" . (int) $products_id . "'");
            }

            //specials_status

            tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . (int) $products_id . "'");
            foreach ($currencies->currencies as $key => $value) {
                $sql_data_array = array('products_id' => $products_id,
                    'groups_id' => '0',
                    'products_group_price' => (float) Yii::$app->request->post('products_price_' . $currencies->currencies[$key]['id'], '-2'),
                    'products_group_discount_price' => '',
                    'currencies_id' => $currencies->currencies[$key]['id'],
                );
                if (Yii::$app->request->post('bonus_points_status')) {
                    $sql_data_array['bonus_points_price'] = Yii::$app->request->post('bonus_points_price_' . $currencies->currencies[$key]['id'] . '_0');
                    $sql_data_array['bonus_points_cost'] = Yii::$app->request->post('bonus_points_cost_' . $currencies->currencies[$key]['id'] . '_0');
                } else {
                    $sql_data_array['bonus_points_price'] = 0;
                    $sql_data_array['bonus_points_cost'] = 0;
                }
                if (Yii::$app->request->post('qty_discount_status')) {
                    $products_price_discount = '';
                    $products_price_discount_array = array();
                    $discount_qty = Yii::$app->request->post('discount_qty_' . $currencies->currencies[$key]['id'] . '_0', array());
                    $discount_price = Yii::$app->request->post('discount_price_' . $currencies->currencies[$key]['id'] . '_0', array());
                    foreach ($discount_qty as $qtykey => $val) {
                        if ($discount_qty[$qtykey] > 0 && $discount_price[$qtykey] > 0) {
                            $products_price_discount_array[$discount_qty[$qtykey]] = $discount_price[$qtykey];
                        }
                    }
                    ksort($products_price_discount_array, SORT_NUMERIC);
                    foreach ($products_price_discount_array as $qty => $price) {
                        $products_price_discount .= $qty . ':' . $price . ';';
                    }
                    $sql_data_array['products_group_discount_price'] = $products_price_discount;
                }
                tep_db_perform(TABLE_PRODUCTS_PRICES, $sql_data_array);

                if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'saveMarketGroup')) {
                    $ext::saveMarketGroup($products_id, $currencies, $key);
                }
            }
        } else {
            $sqlChanges = [];
            if (Yii::$app->request->post('ifpopt_pack_unit_0') == -2 || $pack_unit_full_prices[0] <= 0) {
                $sqlChanges['products_price_pack_unit'] = -2;
                $sqlChanges['products_price_discount_pack_unit'] = '';
            } else {
                $sqlChanges['products_price_pack_unit'] = (float) $pack_unit_full_prices[0];
                if (Yii::$app->request->post('full_qty_discount_status_pack_unit') == 1) {
                    $products_price_discount = '';
                    $products_price_discount_array = array();
                    $discount_qty = Yii::$app->request->post('inventory_discount_full_qty_pack_unit', array());
                    $discount_price = Yii::$app->request->post('inventory_discount_full_price_pack_unit', array());
                    foreach ($discount_qty as $key => $val) {
                        if ($discount_qty[$key] > 0 && $discount_price[$key] > 0) {
                            $products_price_discount_array[$discount_qty[$key]] = $discount_price[$key];
                        }
                    }
                    ksort($products_price_discount_array, SORT_NUMERIC);
                    foreach ($products_price_discount_array as $qty => $price) {
                        $products_price_discount .= $qty . ':' . $price . ';';
                    }
                    $sqlChanges['products_price_discount_pack_unit'] = $products_price_discount;
                } else {
                    $sqlChanges['products_price_discount_pack_unit'] = '';
                }
            }
            if (Yii::$app->request->post('ifpopt_packaging_0') == -2 || $packaging_full_prices[0] <= 0) {
                $sqlChanges['products_price_packaging'] = -2;
                $sqlChanges['products_price_discount_packaging'] = '';
            } else {
                $sqlChanges['products_price_packaging'] = (float) $packaging_full_prices[0];
                if (Yii::$app->request->post('full_qty_discount_status_packaging') == 1) {
                    $products_price_discount = '';
                    $products_price_discount_array = array();
                    $discount_qty = Yii::$app->request->post('inventory_discount_full_qty_packaging', array());
                    $discount_price = Yii::$app->request->post('inventory_discount_full_price_packaging', array());
                    foreach ($discount_qty as $key => $val) {
                        if ($discount_qty[$key] > 0 && $discount_price[$key] > 0) {
                            $products_price_discount_array[$discount_qty[$key]] = $discount_price[$key];
                        }
                    }
                    ksort($products_price_discount_array, SORT_NUMERIC);
                    foreach ($products_price_discount_array as $qty => $price) {
                        $products_price_discount .= $qty . ':' . $price . ';';
                    }
                    $sqlChanges['products_price_discount_packaging'] = $products_price_discount;
                } else {
                    $sqlChanges['products_price_discount_packaging'] = '';
                }
            }

            $sqlChanges['products_tax_class_id'] = (int) Yii::$app->request->post('products_tax_class_id');
            $sqlChanges['products_price'] = (float) Yii::$app->request->post('products_price');
            //tep_db_query("update " . TABLE_PRODUCTS . " set products_tax_class_id = '" . (int)Yii::$app->request->post('products_tax_class_id') . "', products_price = '" . (float)Yii::$app->request->post('products_price') . "' where products_id = '" . (int)$products_id . "'");
            if (Yii::$app->request->post('bonus_points_status')) {
                $sqlChanges['bonus_points_price'] = (float) Yii::$app->request->post('bonus_points_price');
                $sqlChanges['bonus_points_cost'] = (float) Yii::$app->request->post('bonus_points_cost');
                //tep_db_query("update " . TABLE_PRODUCTS . " set bonus_points_price = '" . (float)Yii::$app->request->post('bonus_points_price') . "', bonus_points_cost = '" . (float)Yii::$app->request->post('bonus_points_cost') . "' where products_id = '" . (int)$products_id . "'");
            } else {
                $sqlChanges['bonus_points_price'] = 0;
                $sqlChanges['bonus_points_cost'] = 0;
                //tep_db_query("update " . TABLE_PRODUCTS . " set bonus_points_price = '0', bonus_points_cost = '0' where products_id = '" . (int)$products_id . "'");
            }
            if (count($sqlChanges) > 0) {
                tep_db_perform(TABLE_PRODUCTS, $sqlChanges, 'update', "products_id = '" . (int) $products_id . "'");
            }

            if (Yii::$app->request->post('qty_discount_status')) {
                $products_price_discount = '';
                $products_price_discount_array = array();
                $discount_qty = Yii::$app->request->post('discount_qty', array());
                $discount_price = Yii::$app->request->post('discount_price', array());
                foreach ($discount_qty as $key => $val) {
                    if ($discount_qty[$key] > 0 && $discount_price[$key] > 0) {
                        $products_price_discount_array[$discount_qty[$key]] = $discount_price[$key];
                    }
                }
                ksort($products_price_discount_array, SORT_NUMERIC);
                foreach ($products_price_discount_array as $qty => $price) {
                    $products_price_discount .= $qty . ':' . $price . ';';
                }
                tep_db_query("update " . TABLE_PRODUCTS . " set products_price_discount = '" . tep_db_input($products_price_discount) . "' where products_id = '" . (int) $products_id . "'");
            } else {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_price_discount = '' where products_id = '" . (int) $products_id . "'");
            }

            if (Yii::$app->request->post('specials_status')) {
                $specials_price = Yii::$app->request->post('specials_price');
                if (substr($specials_price, -1) == '%') {
                    $products_price = Yii::$app->request->post('products_price');
                    $specials_price = ($products_price - (($specials_price / 100) * $products_price));
                }
                $specials_expires_date = Yii::$app->request->post('specials_expires_date');
                if (!empty($specials_expires_date)) {
                    $date = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $specials_expires_date);
                    $specials_expires_date = $date->format('Y-m-d');
                }
                $check = tep_db_fetch_array(tep_db_query("select specials_id from " . TABLE_SPECIALS . " where products_id = '" . (int) $products_id . "'"));
                if ($check['specials_id'] > 0) {
                    $specials_id = $check['specials_id'];
                    tep_db_query("update " . TABLE_SPECIALS . " set specials_new_products_price = '" . (float) $specials_price . "', specials_last_modified = now(), expires_date = '" . tep_db_input($specials_expires_date) . "', status = '1' where specials_id = '" . (int) $specials_id . "'");
                } else {
                    tep_db_query("insert into " . TABLE_SPECIALS . " set products_id = '" . (int) $products_id . "', specials_new_products_price = '" . (float) $specials_price . "', specials_date_added = now(), expires_date = '" . tep_db_input($specials_expires_date) . "', status = '1'");
                    $specials_id = tep_db_insert_id();
                }
            } else {
                tep_db_query("update " . TABLE_SPECIALS . " set status = '0' where products_id = '" . (int) $products_id . "'");
            }

            if ($ext = \common\helpers\Acl::checkExtension('UserGroups', 'saveGroup')) {
                $ext::saveGroup($products_id, $specials_id);
            }
        }

        $_platform_list = \common\classes\platform::getProductsAssignList();
        $assign_platform = array();
        if (count($_platform_list) == 1) {
            $assign_platform[] = (int) $_platform_list[0]['id'];
        } else {
            $assign_platform = array_map('intval', Yii::$app->request->post('platform', array()));
        }
        if (count($assign_platform) > 0) {
            tep_db_query("DELETE FROM " . TABLE_PLATFORMS_PRODUCTS . " WHERE products_id='" . (int) $products_id . "' AND platform_id NOT IN('" . implode("','", $assign_platform) . "') ");
        } else {
            tep_db_query("DELETE FROM " . TABLE_PLATFORMS_PRODUCTS . " WHERE products_id='" . (int) $products_id . "'");
        }
        foreach ($assign_platform as $assign_platform_id) {
            $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM " . TABLE_PLATFORMS_PRODUCTS . " WHERE products_id='" . (int) $products_id . "' AND platform_id='" . $assign_platform_id . "' "));
            if ($_check['c'] == 0) {
                tep_db_perform(TABLE_PLATFORMS_PRODUCTS, array(
                    'products_id' => (int) $products_id,
                    'platform_id' => $assign_platform_id,
                ));
            }
        }
        $activate_parent_categories = Yii::$app->request->post('activate_parent_categories', array());
        $__assigned_platform_check = array_flip($assign_platform);
        foreach (\common\classes\platform::getCategoriesAssignList() as $__category_platform) {
            if (!isset($activate_parent_categories[$__category_platform['id']]) || empty($activate_parent_categories[$__category_platform['id']]))
                continue;
            if (!isset($__assigned_platform_check[$__category_platform['id']]))
                continue;
            foreach (explode(',', $activate_parent_categories[$__category_platform['id']]) as $activate_category_id) {
                do {
                    tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_CATEGORIES . " (categories_id, platform_id) VALUES('" . (int) $activate_category_id . "','" . (int) $__category_platform['id'] . "')");
                    $_move_upp = tep_db_fetch_array(tep_db_query("SELECT parent_id FROM " . TABLE_CATEGORIES . " WHERE categories_id='" . (int) $activate_category_id . "' "));
                    $activate_category_id = is_array($_move_upp) ? (int) $_move_upp['parent_id'] : 0;
                } while ($activate_category_id);
            }
        }

        $suppliers_id = Yii::$app->request->post('suppliers_id', array());
        $suppliers_model = Yii::$app->request->post('suppliers_model', array());
        $suppliers_quantity = Yii::$app->request->post('suppliers_quantity', array());
        $suppliers_price = Yii::$app->request->post('suppliers_price', array());
        $supplier_discount = Yii::$app->request->post('supplier_discount', array());
        $suppliers_surcharge_amount = Yii::$app->request->post('suppliers_surcharge_amount', array());
        $suppliers_margin_percentage = Yii::$app->request->post('suppliers_margin_percentage', array());
        $suppliers_data_query = tep_db_query("select * from " . TABLE_SUPPLIERS . " order by suppliers_id");
        while ($suppliers_data = tep_db_fetch_array($suppliers_data_query)) {
            if ($suppliers_id[$suppliers_data['suppliers_id']]) {
                $sql_data_array = [];
                $sql_data_array['suppliers_model'] = $suppliers_model[$suppliers_data['suppliers_id']];
                $sql_data_array['suppliers_price'] = $suppliers_price[$suppliers_data['suppliers_id']];
                $sql_data_array['suppliers_quantity'] = $suppliers_quantity[$suppliers_data['suppliers_id']];
                $sql_data_array['supplier_discount'] = $supplier_discount[$suppliers_data['suppliers_id']];
                $sql_data_array['suppliers_surcharge_amount'] = $suppliers_surcharge_amount[$suppliers_data['suppliers_id']];
                $sql_data_array['suppliers_margin_percentage'] = $suppliers_margin_percentage[$suppliers_data['suppliers_id']];
                $check = tep_db_fetch_array(tep_db_query("select count(*) as suppliers_product_exists from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $products_id . "' and uprid = '" . (int) $products_id . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'"));
                if ($check['suppliers_product_exists']) {
                    $sql_data_array['last_modified'] = 'now()';
                    tep_db_perform(TABLE_SUPPLIERS_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "' and uprid = '" . (int) $products_id . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'");
                } else {
                    $sql_data_array['date_added'] = 'now()';
                    $sql_data_array['products_id'] = $products_id;
                    $sql_data_array['uprid'] = $products_id;
                    $sql_data_array['suppliers_id'] = $suppliers_data['suppliers_id'];
                    tep_db_perform(TABLE_SUPPLIERS_PRODUCTS, $sql_data_array);
                }
            } else {
                tep_db_query("delete from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $products_id . "' and uprid = '" . (int) $products_id . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'");
            }
        }

        /**
         * Splitted by languages
         */
        $languages = \common\helpers\Language::get_languages();

        $products_name = Yii::$app->request->post('products_name');
        $products_description_short = Yii::$app->request->post('products_description_short');
        $products_description = Yii::$app->request->post('products_description');

        $products_seo_page_name = Yii::$app->request->post('products_seo_page_name');
        $products_head_title_tag = Yii::$app->request->post('products_head_title_tag');
        $products_self_service = Yii::$app->request->post('products_self_service');
        $products_head_desc_tag = Yii::$app->request->post('products_head_desc_tag');
        $products_head_keywords_tag = Yii::$app->request->post('products_head_keywords_tag');
        $google_product_category = Yii::$app->request->post('google_product_category');
        $google_product_type = Yii::$app->request->post('google_product_type');


        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];

            /**
             * Name and description
             */
            $sql_data_array = [];
            $sql_data_array['products_name'] = tep_db_prepare_input($products_name[$language_id]);
            $sql_data_array['products_description_short'] = tep_db_prepare_input($products_description_short[$language_id]);
            $sql_data_array['products_description'] = tep_db_prepare_input($products_description[$language_id]);

            /**
             * SEO
             */
            $sql_data_array['products_seo_page_name'] = tep_db_prepare_input($products_seo_page_name[$language_id]);
            $sql_data_array['products_head_title_tag'] = tep_db_prepare_input($products_head_title_tag[$language_id]);
            $sql_data_array['products_self_service'] = tep_db_prepare_input($products_self_service[$language_id]);
            $sql_data_array['products_head_desc_tag'] = tep_db_prepare_input($products_head_desc_tag[$language_id]);
            $sql_data_array['products_head_keywords_tag'] = tep_db_prepare_input($products_head_keywords_tag[$language_id]);
            $sql_data_array['google_product_category'] = tep_db_prepare_input($google_product_category[$language_id]);
            $sql_data_array['google_product_type'] = tep_db_prepare_input($google_product_type[$language_id]);
            if (empty($sql_data_array['products_seo_page_name'])) {
                $sql_data_array['products_seo_page_name'] = $products_seo_page_name[$language_id] = Seo::makeSlug($products_name[$languages_id]);
            }
            if (empty($sql_data_array['products_seo_page_name'])) {
                $sql_data_array['products_seo_page_name'] = $products_seo_page_name[$language_id] = Seo::makeSlug(Yii::$app->request->post('products_model'));
            }
            if (empty($sql_data_array['products_seo_page_name'])) {
                $sql_data_array['products_seo_page_name'] = $products_id;
            }

            $check_product_description_query = tep_db_query("SELECT products_id FROM " . TABLE_PRODUCTS_DESCRIPTION . " WHERE products_id = '" . (int) $products_id . "' and language_id = '" . (int) $language_id . "' and affiliate_id = '" . (int) $affiliate_id . "'");
            if (tep_db_num_rows($check_product_description_query) > 0) {
                tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "' and language_id = '" . (int) $language_id . "' and affiliate_id = '" . (int) $affiliate_id . "'");
            } else {
                $sql_data_array['products_id'] = (int) $products_id;
                $sql_data_array['language_id'] = (int) $language_id;
                $sql_data_array['affiliate_id'] = (int) $affiliate_id;
                tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
            }
        }

        /**
         * Attributes and inventory (variations)
         */
        $products_price = (float) Yii::$app->request->post('products_price');
        $products_price_full = Yii::$app->request->post('products_price_full');
        tep_db_query("update " . TABLE_PRODUCTS . " set products_price_full = '" . (int) $products_price_full . "' where products_id = '" . (int) $products_id . "'");

        // Update Product Attributes
        $attributes_array = array();
        if (isset($_POST['price_prefix']) && !empty($_POST['price_prefix']) && isset($_POST['price_prefix'])) {
            foreach ($_POST['price_prefix'] as $groups => $attributes) {
                $__attr_order_array = array_flip(explode(',', strval($_POST['products_option_values_sort_order'][$groups])));
                foreach ($_POST['price_prefix'][$groups] as $key => $value) {
                    if (isset($_POST['price_prefix'][$groups][$key])) {
                        $attributes_array[] = $groups . '-' . $key;
// {{
                        if (count($_POST['price_prefix']) == 1) { // Only 1 attribute
                            if ($products_price_full) {
                                $_POST['products_attributes_price'][$groups][$key][0] = abs($products_price - (float) $_POST['inventoryfullprice_' . $products_id . '{' . $groups . '}' . $key][0]);
                                if ((float) $_POST['inventoryfullprice_' . $products_id . '{' . $groups . '}' . $key][0] < $products_price) {
                                    $_POST['price_prefix'][$groups][$key] = '-';
                                } else {
                                    $_POST['price_prefix'][$groups][$key] = '+';
                                }
                            } else {
                                $_POST['price_prefix'][$groups][$key] = $_POST['inventorypriceprefix_' . $products_id . '{' . $groups . '}' . $key];
                                $_POST['products_attributes_price'][$groups][$key][0] = $_POST['inventoryprice_' . $products_id . '{' . $groups . '}' . $key][0];
                            }
                        } else {
                            $_POST['products_attributes_price'][$groups][$key][0] = 0;
                        }
// }}
                        $Qcheck = tep_db_query("select products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $products_id . "' and options_id = '" . (int) $groups . "' and options_values_id = '" . (int) $key . "'");
                        if (tep_db_num_rows($Qcheck)) {
                            $Qdata = tep_db_fetch_array($Qcheck);
                            $products_attributes_id = $Qdata['products_attributes_id'];
                            tep_db_query(
                                    "update " . TABLE_PRODUCTS_ATTRIBUTES . " set " .
                                    " options_values_price = '" . tep_db_input($_POST['products_attributes_price'][$groups][$key][0]) . "', " .
                                    " price_prefix = '" . tep_db_input($_POST['price_prefix'][$groups][$key]) . "', " .
                                    " products_options_sort_order = '" . (int) (isset($__attr_order_array[$key]) ? (int) $__attr_order_array[$key] : 0) . "', " .
                                    " product_attributes_one_time = '" . tep_db_input($_POST['product_attributes_one_time'][$groups][$key]) . "', products_attributes_weight = '" . tep_db_input($_POST['products_attributes_weight'][$groups][$key]) . "', products_attributes_weight_prefix = '" . tep_db_input($_POST['products_attributes_weight_prefix'][$groups][$key]) . "', products_attributes_units = '" . tep_db_input($_POST['products_attributes_units'][$groups][$key]) . "', products_attributes_units_price = '" . tep_db_input($_POST['products_attributes_units_price'][$groups][$key]) . "', products_attributes_discount_price = '" . tep_db_input($_POST['products_attributes_discount_price'][$groups][$key][0]) . "', products_attributes_filename = '" . tep_db_input($_POST['products_attributes_filename_name'][$groups][$key]) . "', products_attributes_maxdays = '" . tep_db_input($_POST['products_attributes_maxdays'][$groups][$key]) . "', products_attributes_maxcount = '" . tep_db_input($_POST['products_attributes_maxcount'][$groups][$key]) . "' where products_id = '" . (int) $products_id . "' and options_id = '" . (int) $groups . "' and options_values_id = '" . (int) $key . "'");
                        } else {
                            tep_db_query("insert into " . TABLE_PRODUCTS_ATTRIBUTES . " (products_attributes_id, products_id, options_id, options_values_id, options_values_price, price_prefix, products_options_sort_order, product_attributes_one_time, products_attributes_weight, products_attributes_weight_prefix, products_attributes_units, products_attributes_units_price, products_attributes_discount_price, products_attributes_filename, products_attributes_maxdays, products_attributes_maxcount) values ('', '" . (int) $products_id . "', '" . (int) $groups . "', '" . (int) $key . "', '" . tep_db_input($_POST['products_attributes_price'][$groups][$key][0]) . "', '" . tep_db_input($_POST['price_prefix'][$groups][$key]) . "', " .
                                    " '" . (int) (isset($__attr_order_array[$key]) ? (int) $__attr_order_array[$key] : 0) . "', " .
                                    " '" . tep_db_input($_POST['product_attributes_one_time'][$groups][$key]) . "', '" . tep_db_input($_POST['products_attributes_weight'][$groups][$key]) . "', '" . tep_db_input($_POST['products_attributes_weight_prefix'][$groups][$key]) . "', '" . tep_db_input($_POST['products_attributes_units'][$groups][$key]) . "', '" . tep_db_input($_POST['products_attributes_units_price'][$groups][$key]) . "', '" . tep_db_input($_POST['products_attributes_discount_price'][$groups][$key][0]) . "', '" . tep_db_input($_POST['products_attributes_filename_name'][$groups][$key]) . "', '" . tep_db_input($_POST['products_attributes_maxdays'][$groups][$key]) . "', '" . tep_db_input($_POST['products_attributes_maxcount'][$groups][$key]) . "' )");
                            $products_attributes_id = tep_db_insert_id();
                        }
                    }
                }
            }

            if (PRODUCTS_INVENTORY == 'True') {
                $all_inventory_ids_array = array();
                foreach ($_POST['price_prefix'] as $groups => $attributes) {
                    foreach ($_POST['price_prefix'][$groups] as $key => $value) {
                        $options_name_data = tep_db_fetch_array(tep_db_query("select products_options_name as name from " . TABLE_PRODUCTS_OPTIONS . " where products_options_id = '" . $groups . "' and language_id  = '" . (int) $languages_id . "'"));
                        $options_values_name_data = tep_db_fetch_array(tep_db_query("select products_options_values_name as name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id  = '" . $key . "' and language_id  = '" . (int) $languages_id . "'"));
                        if (isset($options[$groups])) {
                            $options[$groups][] = $key;
                        } else {
                            $options[$groups] = array();
                            $options[$groups][] = $key;
                        }
                    }
                }

                ksort($options);
                reset($options);
                $i = 0;
                $idx = 0;
                foreach ($options as $key => $value) {
                    if ($i == 0) {
                        $idx = $key;
                        $i = 1;
                    }
                    asort($options[$key]);
                }
                $inventory_options = \common\helpers\Inventory::get_inventory_uprid($options, $idx);

                for ($i = 0, $n = sizeof($inventory_options); $i < $n; $i++) {
                    $arr = preg_split("/[{}]/", '0' . $inventory_options[$i]);
                    $label = $_POST['products_name'][$languages_id];
                    for ($j = 1, $m = sizeof($arr); $j < $m; $j = $j + 2) {
                        $options_values_name_data = tep_db_fetch_array(tep_db_query("select products_options_values_name as name from " . TABLE_PRODUCTS_OPTIONS_VALUES . " where products_options_values_id  = '" . (int) $arr[$j + 1] . "' and language_id  = '" . (int) $languages_id . "'"));
                        $label .= ' ' . $options_values_name_data['name'];
                    }
                    if ($action == 'insert_product') {// non_existent
                        $prid = '0';
                    } else {
                        $prid = $products_id;
                    }
// {{
                    $inventory_discount_full_price_table = '';
                    if ($products_price_full) {
                        $_POST['inventoryprice_' . $prid . $inventory_options[$i]][0] = abs($products_price - (float) $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][0]);
                        if ((float) $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][0] < $products_price) {
                            $_POST['inventorypriceprefix_' . $prid . $inventory_options[$i]] = '-';
                        } else {
                            $_POST['inventorypriceprefix_' . $prid . $inventory_options[$i]] = '+';
                        }
                    } else {
                        if ($_POST['inventorypriceprefix_' . $prid . $inventory_options[$i]] == '-') {
                            $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][0] = $products_price - (float) $_POST['inventoryprice_' . $prid . $inventory_options[$i]][0];
                        } else {
                            $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][0] = $products_price + (float) $_POST['inventoryprice_' . $prid . $inventory_options[$i]][0];
                        }
                    }
// }}
                    $inventory_row_addon = '';
                    if (!is_null($_POST['inventorystock_indication_' . $prid . $inventory_options[$i]])) {
                        $inventory_row_addon .= ", stock_indication_id='" . intval($_POST['inventorystock_indication_' . $prid . $inventory_options[$i]]) . "' ";
                    }
                    if (!is_null($_POST['inventorystock_delivery_terms_' . $prid . $inventory_options[$i]])) {
                        $inventory_row_addon .= ", stock_delivery_terms_id='" . intval($_POST['inventorystock_delivery_terms_' . $prid . $inventory_options[$i]]) . "' ";
                    }
                    $check_data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($products_id . $inventory_options[$i]) . "'"));
                    if ($check_data) {
                        tep_db_query("update " . TABLE_INVENTORY . " set products_name = '" . tep_db_input($label) . "', non_existent = '" . (int) $_POST['inventoryexistent_' . $prid . $inventory_options[$i]] . "', inventory_price = '" . tep_db_input($_POST['inventoryprice_' . $prid . $inventory_options[$i]][0]) . "', price_prefix = '" . tep_db_input($_POST['inventorypriceprefix_' . $prid . $inventory_options[$i]]) . "', inventory_full_price = '" . tep_db_input($_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][0]) . "' {$inventory_row_addon} where inventory_id = '" . (int) $check_data['inventory_id'] . "'");
                        $inventory_id = $check_data['inventory_id'];
                    } else {
                        tep_db_query("insert into " . TABLE_INVENTORY . " set inventory_id = '', products_id = '" . tep_db_input($products_id . $inventory_options[$i]) . "', prid = '" . (int) $products_id . "', products_name = '" . tep_db_input($label) . "', non_existent = '" . (int) $_POST['inventoryexistent_' . $prid . $inventory_options[$i]] . "', inventory_price = '" . tep_db_input($_POST['inventoryprice_' . $prid . $inventory_options[$i]][0]) . "', price_prefix = '" . tep_db_input($_POST['inventorypriceprefix_' . $prid . $inventory_options[$i]]) . "', inventory_full_price = '" . tep_db_input($_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][0]) . "' {$inventory_row_addon}");
                        $inventory_id = tep_db_insert_id();
                    }
                    if ($ext = \common\helpers\Acl::checkExtension('AttributesQuantity', 'saveProduct')) {
                        $ext::saveProduct($inventory_id, $prid . $inventory_options[$i]);
                    }
                    if ($ext = \common\helpers\Acl::checkExtension('AttributesDetails', 'saveProduct')) {
                        $ext::saveProduct($inventory_id, $prid . $inventory_options[$i]);
                    }

                    // Update stock quantity
                    $inventory_quantity_update = (int) $_POST['inventoryqtyupdate_' . $prid . $inventory_options[$i]];
                    $inventory_quantity_update_prefix = ($_POST['inventoryqtyupdateprefix_' . $prid . $inventory_options[$i]] == '-' ? '-' : '+');
                    if ($inventory_quantity_update > 0) {
                        global $login_id;
                        \common\helpers\Product::log_stock_history_before_update($products_id . $inventory_options[$i], $inventory_quantity_update, $inventory_quantity_update_prefix, ['comments' => TEXT_MANUALL_STOCK_UPDATE, 'admin_id' => $login_id]);
                        tep_db_query("update " . TABLE_INVENTORY . " set products_quantity = products_quantity " . $inventory_quantity_update_prefix . $inventory_quantity_update . " where products_id = '" . tep_db_input($products_id . $inventory_options[$i]) . "'");
                    }

                    tep_db_query("delete from " . TABLE_INVENTORY_PRICES . " where inventory_id = '" . (int) $inventory_id . "'");
                    $data_query_groups = tep_db_query("select * from " . TABLE_GROUPS . " order by groups_id");
                    while ($data_groups = tep_db_fetch_array($data_query_groups)) {
// {{
                        $inventory_discount_price_table = '';
                        $inventory_discount_full_price_table = '';
                        if ($ext = \common\helpers\Acl::checkExtension('AttributesQuantity', 'getFields')) {
                            list($inventory_discount_price_table, $inventory_discount_full_price_table) = $ext::getFields($prid . $inventory_options[$i], $data_groups, $products_price);
                        }
                        if ($products_price_full) {
                            if ($_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] > 0) {
                                $_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] = abs($products_price - (float) $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']]);
                            } else {
                                $_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] = $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']];
                            }
                        } else {
                            if ($_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] > 0) {
                                if ($_POST['inventorypriceprefix_' . $prid . $inventory_options[$i]] == '-') {
                                    $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] = $products_price - (float) $_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']];
                                } else {
                                    $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] = $products_price + (float) $_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']];
                                }
                            } else {
                                $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] = $_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']];
                            }
                        }
// }}
                        tep_db_query("insert into " . TABLE_INVENTORY_PRICES . " (inventory_id, products_id, prid, groups_id, inventory_group_price, inventory_group_discount_price, inventory_full_price, inventory_discount_full_price, currencies_id) values ('" . (int) $inventory_id . "', '" . tep_db_input($products_id . $inventory_options[$i]) . "', '" . (int) $products_id . "', '" . (int) $data_groups['groups_id'] . "', '" . tep_db_input($_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] ? $_POST['inventoryprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] : '-2') . "', '" . tep_db_input($inventory_discount_price_table) . "', '" . tep_db_input($_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] ? $_POST['inventoryfullprice_' . $prid . $inventory_options[$i]][$data_groups['groups_id']] : '-2') . "', '" . tep_db_input($inventory_discount_full_price_table) . "', 0)");
                    }

                    $suppliers_id = Yii::$app->request->post('suppliers_id_' . $prid . $inventory_options[$i], array());
                    $suppliers_model = Yii::$app->request->post('suppliers_model_' . $prid . $inventory_options[$i], array());
                    $suppliers_quantity = Yii::$app->request->post('suppliers_quantity_' . $prid . $inventory_options[$i], array());
                    $suppliers_price = Yii::$app->request->post('suppliers_price_' . $prid . $inventory_options[$i], array());
                    $supplier_discount = Yii::$app->request->post('supplier_discount_' . $prid . $inventory_options[$i], array());
                    $suppliers_surcharge_amount = Yii::$app->request->post('suppliers_surcharge_amount_' . $prid . $inventory_options[$i], array());
                    $suppliers_margin_percentage = Yii::$app->request->post('suppliers_margin_percentage_' . $prid . $inventory_options[$i], array());
                    $suppliers_data_query = tep_db_query("select * from " . TABLE_SUPPLIERS . " order by suppliers_id");
                    while ($suppliers_data = tep_db_fetch_array($suppliers_data_query)) {
                        if ($suppliers_id[$suppliers_data['suppliers_id']]) {
                            $sql_data_array = [];
                            $sql_data_array['suppliers_model'] = $suppliers_model[$suppliers_data['suppliers_id']];
                            $sql_data_array['suppliers_price'] = $suppliers_price[$suppliers_data['suppliers_id']];
                            $sql_data_array['suppliers_quantity'] = $suppliers_quantity[$suppliers_data['suppliers_id']];
                            $sql_data_array['supplier_discount'] = $supplier_discount[$suppliers_data['suppliers_id']];
                            $sql_data_array['suppliers_surcharge_amount'] = $suppliers_surcharge_amount[$suppliers_data['suppliers_id']];
                            $sql_data_array['suppliers_margin_percentage'] = $suppliers_margin_percentage[$suppliers_data['suppliers_id']];
                            $check = tep_db_fetch_array(tep_db_query("select count(*) as suppliers_product_exists from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $products_id . "' and uprid = '" . tep_db_input($products_id . $inventory_options[$i]) . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'"));
                            if ($check['suppliers_product_exists']) {
                                $sql_data_array['last_modified'] = 'now()';
                                tep_db_perform(TABLE_SUPPLIERS_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int) $products_id . "' and uprid = '" . tep_db_input($products_id . $inventory_options[$i]) . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'");
                            } else {
                                $sql_data_array['date_added'] = 'now()';
                                $sql_data_array['products_id'] = $products_id;
                                $sql_data_array['uprid'] = $products_id . $inventory_options[$i];
                                $sql_data_array['suppliers_id'] = $suppliers_data['suppliers_id'];
                                tep_db_perform(TABLE_SUPPLIERS_PRODUCTS, $sql_data_array);
                            }
                        } else {
                            tep_db_query("delete from " . TABLE_SUPPLIERS_PRODUCTS . " where products_id = '" . (int) $products_id . "' and uprid = '" . tep_db_input($products_id . $inventory_options[$i]) . "' and suppliers_id = '" . (int) $suppliers_data['suppliers_id'] . "'");
                        }
                    }

                    $all_inventory_ids_array[] = $inventory_id;
                }
                tep_db_query("delete from " . TABLE_INVENTORY . " where prid = '" . (int) $products_id . "' and inventory_id not in ('" . implode("','", $all_inventory_ids_array) . "')");
                tep_db_query("delete from " . TABLE_INVENTORY_PRICES . " where prid = '" . (int) $products_id . "' and inventory_id not in ('" . implode("','", $all_inventory_ids_array) . "')");
            }
            $inventory_quantity = tep_db_fetch_array(tep_db_query(
                            "SELECT SUM(products_quantity) AS left_quantity " .
                            "FROM " . TABLE_INVENTORY . " " .
                            "WHERE prid = '" . (int) $products_id . "' AND IFNULL(non_existent,0)=0 " .
                            " AND products_quantity>0"
            ));
            if (\common\helpers\Acl::checkExtension('Inventory', 'allowed')) {
                tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int) $inventory_quantity['left_quantity'] . "' where products_id = '" . (int) $products_id . "'");
            }
        } else {
            if (PRODUCTS_INVENTORY == 'True') {
                tep_db_query("delete from " . TABLE_INVENTORY . " where prid = '" . $products_id . "'");
                tep_db_query("delete from " . TABLE_INVENTORY_PRICES . " where prid = '" . (int) $products_id . "'");
            }
        }
        $switch_off_stock_ids = \common\classes\StockIndication::productDisableByStockIds();
        tep_db_query(
                "update " . TABLE_PRODUCTS . " " .
                "set products_status = 0 " .
                "where products_id = '" . (int) $products_id . "' " .
                " AND products_quantity<=0 " .
                " AND stock_indication_id IN ('" . implode("','", $switch_off_stock_ids) . "')"
        );

        $Qcheck = tep_db_query("select products_attributes_id, products_attributes_filename from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int) $products_id . "' and concat(options_id, '-', options_values_id) not in ('" . implode("', '", $attributes_array) . "')");
        if (tep_db_num_rows($Qcheck)) {
            while ($data = tep_db_fetch_array($Qcheck)) {
                tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . (int) $data['products_attributes_id'] . "'");
                if (DOWNLOAD_ENABLED == true || $data['products_attributes_filename'] != '') {
                    @unlink(DIR_FS_DOWNLOAD . $data['products_attributes_filename']);
                }
                tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_attributes_id = '" . (int) $data['products_attributes_id'] . "'");
                if (USE_MARKET_PRICES == 'True') {
                    tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES_PRICES . " where products_attributes_id = '" . (int) $data['products_attributes_id'] . "'");
                }
            }
        }

        /**
         * Images
         */
        $Images = new \common\classes\Images();

        $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $products_id . DIRECTORY_SEPARATOR;
        if (!file_exists($image_location)) {
            mkdir($image_location, 0777, true);
        }

        $default_image = (int) Yii::$app->request->post('default_image'); //pointer

        $image_status = Yii::$app->request->post('image_status');
        $products_images_id = Yii::$app->request->post('products_images_id');
        $products_images_deleted = Yii::$app->request->post('products_images_deleted');

        $orig_file_name = Yii::$app->request->post('orig_file_name'); //new uploaded images
        $image_title = Yii::$app->request->post('image_title');
        $image_alt = Yii::$app->request->post('image_alt');
        $alt_file_name_flag = Yii::$app->request->post('alt_file_name_flag');
        $alt_file_name = Yii::$app->request->post('alt_file_name');
        $no_watermark = Yii::$app->request->post('no_watermark');
        //hash_file_name

        $images_sort = [];
        if (is_array($products_images_id)) {
            $images_sort_order = Yii::$app->request->post('images_sort_order');
            if (!empty($images_sort_order)) {
                parse_str($images_sort_order, $images_sort);
                if (isset($images_sort['image-box']) && is_array($images_sort['image-box'])) {
                    $images_sort = $images_sort['image-box'];
                    array_flip($images_sort);
                }
            }

            foreach ($products_images_id as $pointer => $imageId) {
                tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES . " where products_images_id = '" . (int) $imageId . "'");
                tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_INVENTORY . " where products_images_id = '" . (int) $imageId . "'");
                if ((int) $products_images_deleted[$pointer] == 1) {
                    if ((int) $imageId > 0) {
                        // unset images
                        /* $check_product_images_description_query = tep_db_query("SELECT * FROM " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " WHERE products_images_id = '" . (int)$imageId . "'");
                          $check_product_images_description = tep_db_fetch_array($check_product_images_description_query);
                          if (!empty($check_product_images_description['hash_file_name'])) {
                          @unlink($image_location . $check_product_images_description['hash_file_name']);
                          } */
                        @unlink($image_location . $imageId . DIRECTORY_SEPARATOR);

                        tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " where products_images_id = '" . (int) $imageId . "'");
                        tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_images_id = '" . (int) $imageId . "'");
                    }
                    continue;
                }

                $sql_data_array = [];
                if ($pointer == $default_image) {
                    $sql_data_array['default_image'] = 1;
                } else {
                    $sql_data_array['default_image'] = 0;
                }
                $sql_data_array['image_status'] = (int) $image_status[$pointer];

                if (isset($images_sort[$pointer])) {
                    $sql_data_array['sort_order'] = (int) $images_sort[$pointer];
                } else {
                    $sql_data_array['sort_order'] = (int) $pointer;
                }

                if ((int) $imageId > 0) {
                    tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array, 'update', "products_images_id = '" . (int) $imageId . "'");
                } else {
                    $sql_data_array['products_id'] = (int) $products_id;
                    tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
                    $imageId = tep_db_insert_id();
                    $products_images_id[$pointer] = $imageId;
                }


                if (!file_exists($image_location . $imageId . DIRECTORY_SEPARATOR)) {
                    mkdir($image_location . $imageId . DIRECTORY_SEPARATOR, 0777, true);
                }

                foreach ($orig_file_name[$pointer] as $language_id => $orig_file) {
                    $sql_data_array = [];

                    $sql_data_array['image_title'] = $image_title[$pointer][$language_id];
                    $sql_data_array['image_alt'] = $image_alt[$pointer][$language_id];

                    if (isset($products_seo_page_name[$language_id])) {
                        $file_name = $products_seo_page_name[$language_id];
                    } else {
                        $file_name = $products_seo_page_name[$languages_id];
                    }

                    if ((int) $alt_file_name_flag[$pointer][$language_id] == 0) {
                        $sql_data_array['alt_file_name'] = '';
                    } else {
                        $sql_data_array['alt_file_name'] = $alt_file_name[$pointer][$language_id];
                    }

                    $sql_data_array['no_watermark'] = (int) $no_watermark[$pointer][$language_id];

                    $lang = '';
                    for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                        if ($languages[$i]['id'] == $language_id) {
                            $lang = $languages[$i]['code'];
                            break;
                        }
                    }

                    //$uploadfile = $path . basename($_FILES['file']['name']);
                    if (!empty($orig_file)) {
                        $tmp_name = $path . $orig_file;
                        if (file_exists($tmp_name)) {

                            $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
                            $file_name .= '.' . $uploadExtension;
                            $sql_data_array['file_name'] = /* $lang . '/' . */ $file_name;

                            if (!empty($sql_data_array['alt_file_name'])) {
                                $file_name = $sql_data_array['alt_file_name'];
                            }

                            $hashName = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                            $new_name = $image_location . $imageId . DIRECTORY_SEPARATOR . $hashName;
                            copy($tmp_name, $new_name);
                            @unlink($tmp_name);
                            $sql_data_array['hash_file_name'] = $hashName;
                            $sql_data_array['orig_file_name'] = $orig_file;

                            $Images->createImages($products_id, $imageId, $hashName, $file_name, $lang); //$orig_file
                        }
                    } elseif (!empty($sql_data_array['alt_file_name'])) {
                        $tmp_name = $path . $orig_file;
                        if (file_exists($tmp_name)) {
                            $file_name = $sql_data_array['alt_file_name'];
                        }
                        $check_image_description_query = tep_db_query("SELECT hash_file_name FROM " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " WHERE products_images_id = '" . (int) $imageId . "' and language_id = 0 /*'" . (int) $language_id . "'*/");
                        if (tep_db_num_rows($check_image_description_query) > 0) {
                            $check_image_description = tep_db_fetch_array($check_image_description_query);
                            $hashName = $check_image_description['hash_file_name'];
                            $Images->createImages($products_id, $imageId, $hashName, $file_name, $lang);
                        }
                    }

                    $check_image_description_query = tep_db_query("SELECT products_images_id FROM " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " WHERE products_images_id = '" . (int) $imageId . "' and language_id = '" . (int) $language_id . "'");
                    if (tep_db_num_rows($check_image_description_query) > 0) {
                        tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array, 'update', "products_images_id = '" . (int) $imageId . "' and language_id = '" . (int) $language_id . "'");
                    } else {
                        $sql_data_array['products_images_id'] = (int) $imageId;
                        $sql_data_array['language_id'] = (int) $language_id;
                        tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array);
                    }
                }

                if ($ext = \common\helpers\Acl::checkExtension('AttributesImages', 'productSave')) {
                    $ext::productSave($imageId, $pointer);
                }

                if ($ext = \common\helpers\Acl::checkExtension('InventortyImages', 'productSave')) {
                    $ext::productSave($imageId, $pointer);
                }
            }
        }

        $check_image_query = tep_db_query("SELECT products_images_id FROM " . TABLE_PRODUCTS_IMAGES . " WHERE default_image = '1' and products_id = '" . (int) $products_id . "'");
        if (tep_db_num_rows($check_image_query) == 0) {
            $check_image_query = tep_db_query("SELECT products_images_id FROM " . TABLE_PRODUCTS_IMAGES . " WHERE products_id = '" . (int) $products_id . "'"); //add sort order
            if (tep_db_num_rows($check_image_query) > 0) {
                $check_image = tep_db_fetch_array($check_image_query);
                tep_db_query("update " . TABLE_PRODUCTS_IMAGES . " set default_image = '1' where products_images_id = '" . (int) $check_image['products_images_id'] . "'");
            }
        }

        /**
         * Marketing
         */
        $xsell = Yii::$app->request->post('xsell_id');
        tep_db_query("delete from " . TABLE_PRODUCTS_XSELL . " where products_id  = '" . (int) $products_id . "'");
        if (is_array($xsell)) {
            $xsell_sort = [];
            $xsell_sort_order = Yii::$app->request->post('xsell_sort_order');
            if (!empty($xsell_sort_order)) {
                parse_str($xsell_sort_order, $xsell_sort);
                if (isset($xsell_sort['xsell-box']) && is_array($xsell_sort['xsell-box'])) {
                    $xsell_sort = $xsell_sort['xsell-box'];
                    $xsell_sort = array_flip($xsell_sort);
                }
            }

            foreach ($xsell as $pointer => $xsell_id) {
                if ($products_id != $xsell_id) {
                    $sql_data_array = [];
                    $sql_data_array['products_id'] = (int) $products_id;
                    $sql_data_array['xsell_id'] = (int) $xsell_id;
                    if (isset($xsell_sort[$xsell_id])) {
                        $sql_data_array['sort_order'] = (int) $xsell_sort[$xsell_id];
                    }
                    tep_db_perform(TABLE_PRODUCTS_XSELL, $sql_data_array);
                }
            }
        }

        if ($ext = \common\helpers\Acl::checkExtension('UpSell', 'productSave')) {
            $ext::productSave($products_id);
        }

        /**
         * Properties
         */
        tep_db_query("delete from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id  = '" . (int) $products_id . "'");
        $prop_ids = Yii::$app->request->post('prop_ids', array());
        $val_ids = Yii::$app->request->post('val_ids', array());
        foreach ($prop_ids as $properties_id) {
            if (is_array($val_ids[$properties_id])) {
                $property = tep_db_fetch_array(tep_db_query("select properties_id, properties_type from " . TABLE_PROPERTIES . " where properties_id = '" . (int) $properties_id . "'"));
                foreach ($val_ids[$properties_id] as $values_id) {
                    $sql_data_array = [];
                    $sql_data_array['products_id'] = (int) $products_id;
                    $sql_data_array['properties_id'] = (int) $properties_id;
                    if ($property['properties_type'] == 'flag') {
                        $sql_data_array['values_flag'] = (int) $values_id;
                    } else {
                        $sql_data_array['values_id'] = (int) $values_id;
                    }
                    tep_db_perform(TABLE_PROPERTIES_TO_PRODUCTS, $sql_data_array);
                }
            }
        }

        /**
         * Bundles
         */
        if ($ext = \common\helpers\Acl::checkExtension('ProductBundles', 'productSave')) {
            $ext::productSave($products_id);
        }

        /**
         * Videos
         */
        $video = Yii::$app->request->post('video');
        tep_db_query("delete from " . TABLE_PRODUCTS_VIDEOS . " where products_id  = '" . (int) $products_id . "'");
        if (is_array($video)) {

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                $language_id = $languages[$i]['id'];

                if (isset($video[$language_id]) && is_array($video[$language_id])) {
                    foreach ($video[$language_id] as $item) {

                        if ($item) {
                            $sql_data_array = array(
                                'products_id' => $products_id,
                                'video' => $item,
                                'language_id' => $language_id,
                            );
                            tep_db_perform(TABLE_PRODUCTS_VIDEOS, $sql_data_array);
                        }
                    }
                }
            }
        }

        /**
         * Documents
         */
        if ($ext = \common\helpers\Acl::checkExtension('ProductDocuments', 'productSave')) {
            $ext::productSave($products_id, $languages);
        }
        
        \backend\design\ProductTemplate::productSubmit($products_id);

        $message = TEXT_PRODUCT_UPDATED_NOTICE;
        $messageType = 'success';
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
        <?= $message ?>
                    </div>
                </div>
                <div class="noti-btn">
                    <div></div>
                    <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                </div>
            </div>
            <script>
                $('body').scrollTop(0);
                $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                    $(this).parents('.pop-mess').remove();
                });
            </script>
        </div>


        <?php
        echo '<script> window.location.href="' . Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $products_id]) . '";</script>';

        //return $this->redirect(Yii::$app->urlManager->createUrl(['categories/productedit', 'pID' => $products_id]));
    }

    public function actionProductSearch() {
        global $languages_id, $language;

        $q = Yii::$app->request->get('q');
        $products_id = (int) Yii::$app->request->get('not');

        $products_string = '';

        $categories = \common\helpers\Categories::get_category_tree(0, '', '0', '', true);
        foreach ($categories as $category) {
            //$products_query = tep_db_query("select distinct p.products_id, pd.products_name, p.products_status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id != '" . (int)$products_id . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and (p.products_model like '%" . tep_db_input($q) . "%' or pd.products_name like '%" . tep_db_input($q) . "%') group by p.products_id order by p.sort_order, pd.products_name limit 0, 100");
            $products_query = tep_db_query("select distinct p.products_id, pd.products_name, p.products_status from " . TABLE_PRODUCTS . " p LEFT JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c ON p.products_id = p2c.products_id LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id = pd.products_id where p2c.categories_id = '" . $category['id'] . "' and p.products_id != '" . (int) $products_id . "' and pd.language_id = '" . (int) $languages_id . "' and (p.products_model like '%" . tep_db_input($q) . "%' or pd.products_name like '%" . tep_db_input($q) . "%') group by p.products_id order by p.sort_order, pd.products_name limit 0, 100");
            if (tep_db_num_rows($products_query) > 0) {
                $products_string .= '<optgroup label="' . $category['text'] . '">';
                while ($products = tep_db_fetch_array($products_query)) {
                    /* $w = preg_quote(trim($q));
                      if (!empty($w)) {
                      $regexp = "/($w)(?![^<]+>)/i";
                      $replacement = '<b style="color:#ff0000">\\1</b>';
                      $products['products_name'] = preg_replace ($regexp,$replacement ,$products['products_name']);
                      } */
                    $products_string .= '<option value="' . $products['products_id'] . '" ' . ($products['products_status'] == 0 ? ' class="dis_prod"' : '') . '>' . $products['products_name'] . '</option>';
                }
                $products_string .= '</optgroup>';
            }
        }

        echo $products_string;
    }

    public function actionProductNewBundles() {
        global $languages_id, $language, $login_id;

        $this->layout = false;

        $currencies = new \common\classes\currencies();

        $products_id = (int) Yii::$app->request->post('products_id');

        $query = tep_db_query("select products_id, products_name from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . $languages_id . "' and affiliate_id = 0 and products_id = '" . $products_id . "'");
        if (tep_db_num_rows($query) > 0) {
            $data = tep_db_fetch_array($query);
            $bundlesProducts = [
                'bundles_id' => $data['products_id'],
                'products_name' => $data['products_name'],
                'num_product' => '1',
                'price' => '0.00',
                'discount' => '0.00',
                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
            ];

            return $this->render('product-new-bundles.tpl', [
                        'bundles' => $bundlesProducts,
            ]);
        }
    }

    private static function getProductsDetails($products_id) {
        global $languages_id;
        //probably random platform
        $query = tep_db_query("select p.products_id, pd.products_name, p.products_status from " . TABLE_PRODUCTS_DESCRIPTION . " pd," . TABLE_PRODUCTS . " p where language_id = '" . $languages_id . "' and /*affiliate_id = 0 and */ p.products_id = '" . $products_id . "' and pd.products_id = '" . $products_id . "' limit 1");
        if (tep_db_num_rows($query) > 0) {
            $ret = tep_db_fetch_array($query);
        } else {
            $ret = array();
        }
        return $ret;
    }

    public function actionProductNewXsell() {
        global $languages_id, $language, $login_id;

        $this->layout = false;

        $currencies = new \common\classes\currencies();

        $products_id = (int) Yii::$app->request->post('products_id');
        $data = self::getProductsDetails($products_id);

        if (count($data) > 0) {
            $xsellProduct = [
                'xsell_id' => $data['products_id'],
                'products_name' => $data['products_name'],
                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['products_id'])),
                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
            ];

            return $this->render('product-new-xsell.tpl', [
                        'xsell' => $xsellProduct,
            ]);
        }
    }

    public function actionProductNewUpsell() {
        global $languages_id, $language, $login_id;

        $this->layout = false;

        $currencies = new \common\classes\currencies();

        $products_id = (int) Yii::$app->request->post('products_id');

        $data = self::getProductsDetails($products_id);

        if (count($data) > 0) {
            $upsellProduct = [
                'upsell_id' => $data['products_id'],
                'products_name' => $data['products_name'],
                'image' => \common\classes\Images::getImage($data['products_id'], 'Small'),
                'price' => $currencies->format(\common\helpers\Product::get_products_price($data['products_id'])),
                'status_class' => ($data['products_status'] == 0 ? 'dis_prod' : ''),
            ];

            return $this->render('product-new-upsell.tpl', [
                        'upsell' => $upsellProduct,
            ]);
        }
    }

    public function actionProductImageGenerator() {

        // product-image-generator
        $Images = new \common\classes\Images();

        $path = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES;

        $languages = \common\helpers\Language::get_languages();

        //TRUNCATE TABLE `products_images`
        //TRUNCATE TABLE `products_images_description`

        $check_product_query = tep_db_query("SELECT products_id, products_image, products_image_lrg, products_image_xl_1, products_image_xl_2, products_seo_page_name FROM " . TABLE_PRODUCTS . " WHERE 1");
        if (tep_db_num_rows($check_product_query) > 0) {
            while ($product = tep_db_fetch_array($check_product_query)) {

                $orig_file = $product['products_image_lrg'];

                $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int) $product['products_id'] . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '%" . tep_db_input($orig_file) . "'"));

                $tmp_name = $path . $orig_file;

                if (!empty($orig_file) && file_exists($tmp_name) && !($check['products_images_id'] > 0)) {

                    $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['default_image'] = 1;
                    $sql_data_array['image_status'] = 1;
                    $sql_data_array['products_id'] = (int) $product['products_id'];
                    tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
                    $imageId = tep_db_insert_id();

                    $image_location .= $imageId . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['language_id'] = 0;

                    $file_name = $product['products_seo_page_name'];
                    $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
                    $file_name .= '.' . $uploadExtension;
                    $sql_data_array['file_name'] = $file_name;

                    $hashName = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                    $new_name = $image_location . $hashName;

                    copy($tmp_name, $new_name);
                    $sql_data_array['hash_file_name'] = $hashName;

                    $sql_data_array['orig_file_name'] = $orig_file;

                    $product_name = \common\helpers\Product::get_products_name($product['products_id']);
                    $sql_data_array['image_title'] = $product_name;
                    $sql_data_array['image_alt'] = $product_name;

                    $lang = '';
                    $Images->createImages($product['products_id'], $imageId, $hashName, $file_name, $lang); //$orig_file

                    $sql_data_array['products_images_id'] = (int) $imageId;
                    $sql_data_array['language_id'] = (int) $language_id;
                    tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array);

                    /* for( $i = 0, $n = sizeof( $languages ); $i < $n; $i++ ) {



                      } */
                }



                $orig_file = $product['products_image_xl_1'];

                $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int) $product['products_id'] . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '%" . tep_db_input($orig_file) . "'"));

                $tmp_name = $path . $orig_file;

                if (!empty($orig_file) && file_exists($tmp_name) && !($check['products_images_id'] > 0)) {

                    $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['default_image'] = 0;
                    $sql_data_array['image_status'] = 1;
                    $sql_data_array['products_id'] = (int) $product['products_id'];
                    tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
                    $imageId = tep_db_insert_id();

                    $image_location .= $imageId . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['language_id'] = 0;

                    $file_name = $product['products_seo_page_name'];
                    $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
                    $file_name .= '.' . $uploadExtension;
                    $sql_data_array['file_name'] = $file_name;

                    $hashName = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                    $new_name = $image_location . $hashName;

                    copy($tmp_name, $new_name);
                    $sql_data_array['hash_file_name'] = $hashName;

                    $sql_data_array['orig_file_name'] = $orig_file;

                    $product_name = \common\helpers\Product::get_products_name($product['products_id']);
                    $sql_data_array['image_title'] = $product_name . ' 1';
                    $sql_data_array['image_alt'] = $product_name . ' 1';

                    $lang = '';
                    $Images->createImages($product['products_id'], $imageId, $hashName, $file_name, $lang); //$orig_file

                    $sql_data_array['products_images_id'] = (int) $imageId;
                    $sql_data_array['language_id'] = (int) $language_id;
                    tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array);

                    /* for( $i = 0, $n = sizeof( $languages ); $i < $n; $i++ ) {



                      } */
                }

                $orig_file = $product['products_image_xl_2'];

                $check = tep_db_fetch_array(tep_db_query("select pi.products_images_id from " . TABLE_PRODUCTS_IMAGES . " pi, " . TABLE_PRODUCTS_IMAGES_DESCRIPTION . " pid where pi.products_id = '" . (int) $product['products_id'] . "' and pi.products_images_id = pid.products_images_id and pid.language_id = '0' and pid.orig_file_name like '%" . tep_db_input($orig_file) . "'"));

                $tmp_name = $path . $orig_file;

                if (!empty($orig_file) && file_exists($tmp_name) && !($check['products_images_id'] > 0)) {

                    $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $product['products_id'] . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['default_image'] = 0;
                    $sql_data_array['image_status'] = 1;
                    $sql_data_array['products_id'] = (int) $product['products_id'];
                    tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);
                    $imageId = tep_db_insert_id();

                    $image_location .= $imageId . DIRECTORY_SEPARATOR;
                    if (!file_exists($image_location)) {
                        mkdir($image_location, 0777, true);
                    }

                    $sql_data_array = [];
                    $sql_data_array['language_id'] = 0;

                    $file_name = $product['products_seo_page_name'];
                    $uploadExtension = strtolower(pathinfo($tmp_name, PATHINFO_EXTENSION));
                    $file_name .= '.' . $uploadExtension;
                    $sql_data_array['file_name'] = $file_name;

                    $hashName = md5($orig_file . "_" . date('dmYHis') . "_" . microtime(true));
                    $new_name = $image_location . $hashName;

                    copy($tmp_name, $new_name);
                    $sql_data_array['hash_file_name'] = $hashName;

                    $sql_data_array['orig_file_name'] = $orig_file;

                    $product_name = \common\helpers\Product::get_products_name($product['products_id']);
                    $sql_data_array['image_title'] = $product_name . ' 2';
                    $sql_data_array['image_alt'] = $product_name . ' 2';

                    $lang = '';
                    $Images->createImages($product['products_id'], $imageId, $hashName, $file_name, $lang); //$orig_file

                    $sql_data_array['products_images_id'] = (int) $imageId;
                    $sql_data_array['language_id'] = (int) $language_id;
                    tep_db_perform(TABLE_PRODUCTS_IMAGES_DESCRIPTION, $sql_data_array);

                    /* for( $i = 0, $n = sizeof( $languages ); $i < $n; $i++ ) {



                      } */
                }
            }
        }
    }

    public function actionCategoryedit() {
        global $languages_id, $language, $affiliate_id;

        $this->view->usePopupMode = false;
        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            $this->view->usePopupMode = true;
        }

        \common\helpers\Translation::init('admin/categories');

        $popup = 0;
        if (Yii::$app->request->isPost) {
            $categories_id = (int) Yii::$app->request->getBodyParam('categories_id');
            $popup = (int) Yii::$app->request->post('popup');
            if ($popup == 0) {
                $this->view->usePopupMode = false;
            }
        } else {
            $categories_id = (int) Yii::$app->request->get('categories_id');
        }
        $this->view->contentAlreadyLoaded = $popup;

        $category = [];
        if ($categories_id > 0) {
            $categories_query = tep_db_query("select c.categories_id, cd.categories_name, cd.categories_heading_title, cd.categories_description, cd.categories_head_title_tag, cd.categories_head_desc_tag, cd.categories_head_keywords_tag,  c.categories_image,  c.categories_image_2, c.parent_id, c.categories_seo_page_name, c.sort_order, c.date_added, c.last_modified, c.categories_status, c.google_product_category, c.categories_old_seo_page_name from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . $categories_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . $languages_id . "' and cd.affiliate_id = 0 order by c.sort_order, cd.categories_name");
            $category = tep_db_fetch_array($categories_query);
        } else {
            $category['parent_id'] = (int) Yii::$app->request->get('category_id', 0);
        }
        $cInfo = new \objectInfo($category);

        $imageScript = '$("#category_logo").hide();';
        if ($cInfo->categories_image) {
            $image_path = DIR_WS_CATALOG_IMAGES . $cInfo->categories_image;
            $imageScript = '
                 $("#category_logo").attr("src","' . $image_path . '");
                 $("#category_logo").show();
            ';
        } else {
            $image_path = false;
        }


        $cDescription = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $cDescription[$i]['code'] = $languages[$i]['code'];

            $category_description_query = tep_db_query("select * from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . $categories_id . "' and language_id = '" . (int) $languages[$i]['id'] . "' and affiliate_id = '" . (int) $affiliate_id . "'");
            $category_description = tep_db_fetch_array($category_description_query);
            $categoryDescription = new \objectInfo($category_description);
            $cDescription[$i]['categories_name'] = tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', $categoryDescription->categories_name, 'class="form-control"');
            $cDescription[$i]['categories_description'] = tep_draw_textarea_field('categories_description[' . $languages[$i]['id'] . ']', 'soft', '70', '15', $categoryDescription->categories_description, 'class="ckeditor form-control"');
            $cDescription[$i]['categories_seo_page_name'] = tep_draw_input_field('categories_seo_page_name[' . $languages[$i]['id'] . ']', $categoryDescription->categories_seo_page_name, 'class="form-control"');
            $cDescription[$i]['google_product_category'] = tep_draw_input_field('google_product_category[' . $languages[$i]['id'] . ']', $categoryDescription->google_product_category, 'class="form-control"');
            $cDescription[$i]['categories_head_title_tag'] = tep_draw_input_field('categories_head_title_tag[' . $languages[$i]['id'] . ']', $categoryDescription->categories_head_title_tag, 'class="form-control"');
            $cDescription[$i]['categories_head_desc_tag'] = tep_draw_textarea_field('categories_head_desc_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', $categoryDescription->categories_head_desc_tag, 'class="form-control"');
            $cDescription[$i]['categories_head_keywords_tag'] = tep_draw_textarea_field('categories_head_keywords_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '5', $categoryDescription->categories_head_keywords_tag, 'class="form-control"');
        }

        $this->view->platform_assigned = [];
        $this->view->platform_switch_notice = [];
        if (isset($cInfo->categories_id) && intval($cInfo->categories_id) > 0) {
            $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id = '" . intval($cInfo->categories_id) . "' ");
            if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
                while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                    $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
                }
            }

            foreach (\common\classes\platform::getList() as $__platform) {
                $this->view->platform_switch_notice[strval($__platform['id'])] = array(
                    'categories' => [0, 0],
                    'products' => [0, 0],
                    'original_state' => isset($this->view->platform_assigned[(int) $__platform['id']]),
                );
            }
            $sub_categories = array();
            \common\helpers\Categories::get_subcategories($sub_categories, $cInfo->categories_id, true);
            if (count($sub_categories) > 0) {
                foreach (\common\classes\platform::getCategoriesAssignList() as $_check_notice_platform) {
                    //category assigned, can switch OFF - check assigned subcategories
                    $__check = tep_db_fetch_array(tep_db_query(
                                    "SELECT COUNT(*) AS c " .
                                    "FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
                                    "WHERE platform_id='" . $_check_notice_platform['id'] . "' AND categories_id IN('" . implode("','", $sub_categories) . "') "
                    ));
                    if ($__check['c'] > 0) {
                        $this->view->platform_switch_notice[$_check_notice_platform['id']]['categories'][1] = $__check['c'];
                    }
                    //category not assigned, can switch ON - check not assigned subcategories
                    $__check = tep_db_fetch_array(tep_db_query(
                                    "SELECT COUNT(*) AS c " .
                                    "FROM " . TABLE_CATEGORIES . " c " .
                                    " LEFT JOIN " . TABLE_PLATFORMS_CATEGORIES . " pc ON pc.categories_id=c.categories_id AND pc.platform_id='" . $_check_notice_platform['id'] . "' " .
                                    "WHERE c.categories_id IN('" . implode("','", $sub_categories) . "') AND pc.categories_id IS NULL "
                    ));
                    if ($__check['c'] > 0) {
                        $this->view->platform_switch_notice[$_check_notice_platform['id']]['categories'][0] = $__check['c'];
                    }
                }
            }

            $sub_categories[] = $cInfo->categories_id;
            foreach (\common\classes\platform::getProductsAssignList() as $_check_notice_platform) {
                //category assigned, can switch OFF - check assigned products
                $__check = tep_db_fetch_array(tep_db_query(
                                "SELECT COUNT(*) AS c " .
                                "FROM " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PLATFORMS_PRODUCTS . " plp " .
                                "WHERE p2c.products_id=p.products_id AND p2c.categories_id IN('" . implode("','", $sub_categories) . "') " .
                                "  AND plp.platform_id='" . $_check_notice_platform['id'] . "' AND plp.products_id=p.products_id "
                ));
                if ($__check['c'] > 0) {
                    $this->view->platform_switch_notice[$_check_notice_platform['id']]['products'][1] = $__check['c'];
                }
                //category not assigned, can switch ON - check not assigned products
                $__check = tep_db_fetch_array(tep_db_query(
                                "SELECT COUNT(*) AS c " .
                                "FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_PRODUCTS . " p " .
                                "  LEFT JOIN " . TABLE_PLATFORMS_PRODUCTS . " plp ON plp.platform_id='" . $_check_notice_platform['id'] . "' AND plp.products_id=p.products_id " .
                                "WHERE p2c.products_id=p.products_id AND p2c.categories_id IN('" . implode("','", $sub_categories) . "') " .
                                "  AND plp.products_id IS NULL "
                ));
                if ($__check['c'] > 0) {
                    $this->view->platform_switch_notice[$_check_notice_platform['id']]['products'][0] = $__check['c'];
                }
            }
        } elseif (isset($cInfo->parent_id) && !empty($cInfo->parent_id)) {
            $get_assigned_platforms_r = tep_db_query("SELECT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id = '" . intval($cInfo->parent_id) . "' ");
            if (tep_db_num_rows($get_assigned_platforms_r) > 0) {
                while ($_assigned_platform = tep_db_fetch_array($get_assigned_platforms_r)) {
                    $this->view->platform_assigned[(int) $_assigned_platform['platform_id']] = (int) $_assigned_platform['platform_id'];
                }
            }
        } else {
            foreach (\common\classes\platform::getCategoriesAssignList() as $___data) {
                $this->view->platform_assigned[intval($___data['id'])] = intval($___data['id']);
            }
        }        

        $this->selectedMenu = array('catalog', 'categories');
        $text_new_or_edit = ($categories_id == 0) ? TEXT_INFO_HEADING_NEW_CATEGORY : (TEXT_INFO_HEADING_EDIT_CATEGORY . (empty($cInfo->categories_name) ? '' : ' &quot;' . $cInfo->categories_name . '&quot;'));
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('categories/index'), 'title' => sprintf($text_new_or_edit, \common\helpers\Categories::output_generated_category_path($current_category_id)));
        return $this->render('categoryedit', [
                    'categories_id' => $categories_id,
                    'cInfo' => $cInfo,
                    'imageScript' => $imageScript,
                    'languages' => $languages,
                    'cDescription' => $cDescription,
                    'js_platform_switch_notice' => json_encode($this->view->platform_switch_notice),
                    'image' => $cInfo->categories_image,
                    'image_2' => $cInfo->categories_image_2,
        ]);
    }

    public function actionCategorySubmit() {
        global $languages_id, $language, $messageStack;

        \common\helpers\Translation::init('admin/categories');

        $this->view->errorMessageType = 'success';
        $this->view->errorMessage = '';
        $this->layout = false;

        $current_category_id = (int) Yii::$app->request->post('parent_category_id', 0); //can change current category
        $popup = (int) Yii::$app->request->post('popup');
        $categories_id = (int) Yii::$app->request->post('categories_id');
        if ($categories_id > 0) {
            $action = 'update_category';
        } else {
            $action = 'insert_category';
        }

        $categories_status = (int)tep_db_prepare_input($_POST['categories_status']);
        $categories_image_loaded = $_POST['categories_image_loaded'];
        $categories_image_loaded_2 = $_POST['categories_image_loaded_2'];
        $delete_image = Yii::$app->request->post( 'delete_image' );
        $delete_image_2 = Yii::$app->request->post( 'delete_image_2' );

        $sql_data_array = array( 'categories_status' => $categories_status );



        $sql_data_array['categories_old_seo_page_name'] = tep_db_prepare_input($_POST['categories_old_seo_page_name']);

        $categories_image = Yii::$app->request->post('categories_image');
        $categories_image_2 = Yii::$app->request->post('categories_image_2');
        $categories_image = str_replace('images/', '', $categories_image);
        $categories_image_2 = str_replace('images/', '', $categories_image_2);
        $sql_data_array['categories_image'] = $categories_image;
        $sql_data_array['categories_image_2'] = $categories_image_2;
        if ((int)$categories_id != 0 && $delete_image) {
            $sql_data_array['categories_image'] = '';
            $category = tep_db_fetch_array(tep_db_query("select categories_image from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'"));
            if (!empty($category['categories_image'])) {
                $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $category['categories_image'];
                if (file_exists($image_location)) @unlink($image_location);
            }
        }
        if ((int)$categories_id != 0 && $delete_image_2) {
            $sql_data_array['categories_image_2'] = '';
            $category = tep_db_fetch_array(tep_db_query("select categories_image_2 from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$categories_id . "'"));
            if (!empty($category['categories_image_2'])) {
                $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $category['categories_image_2'];
                if (file_exists($image_location)) @unlink($image_location);
            }
        }

        if ($categories_image_loaded != '') {
            $val = \backend\design\Uploads::move($categories_image_loaded, 'images', false);
            $sql_data_array['categories_image'] = $val;
        }
        if ($categories_image_loaded_2 != '') {
            $val = \backend\design\Uploads::move($categories_image_loaded_2, 'images', false);
            $sql_data_array['categories_image_2'] = $val;
        }

        if ($action == 'insert_category') {
            $insert_sql_data = [
                'parent_id' => $current_category_id,
                'date_added' => 'now()'
            ];
            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
            tep_db_perform(TABLE_CATEGORIES, $sql_data_array);
            $categories_id = tep_db_insert_id();
            Yii::$app->request->setBodyParams(['categories_id' => $categories_id, 'popup' => $popup]);
            $this->view->errorMessage = TEXT_INFO_SAVED;
        } elseif ($action == 'update_category') {
            $update_sql_data = [
                'last_modified' => 'now()'
            ];
            \common\helpers\Categories::set_categories_status($categories_id, $categories_status);
            $sql_data_array = array_merge($sql_data_array, $update_sql_data);
            tep_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int) $categories_id . "'");
            $this->view->errorMessage = TEXT_INFO_UPDATED;
        }
        
        if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
            $ext::saveCategoryLinks($categories_id, $_POST);
        }

        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $language_id = $languages[$i]['id'];

            $sql_data_array = [
                'categories_name' => tep_db_prepare_input($_POST['categories_name'][$language_id]),
                'categories_description' => tep_db_prepare_input($_POST['categories_description'][$language_id]),
                'categories_seo_page_name' => tep_db_prepare_input($_POST['categories_seo_page_name'][$language_id]),
                'categories_head_title_tag' => tep_db_prepare_input($_POST['categories_head_title_tag'][$language_id]),
                'categories_head_desc_tag' => tep_db_prepare_input($_POST['categories_head_desc_tag'][$language_id]),
                'categories_head_keywords_tag' => tep_db_prepare_input($_POST['categories_head_keywords_tag'][$language_id]),
                'google_product_category' => tep_db_prepare_input($_POST['google_product_category'][$language_id]),
            ];

            if (empty($sql_data_array['categories_seo_page_name'])) {
                $sql_data_array['categories_seo_page_name'] = Seo::makeSlug(tep_db_prepare_input($_POST['categories_name'][$languages_id]));
            }

            $check_category = tep_db_query("select * from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . $categories_id . "' and language_id = '" . $languages[$i]['id'] . "' and affiliate_id = 0");
            if ($action == 'insert_category' || !tep_db_num_rows($check_category)) {
                $insert_sql_data = [
                    'categories_id' => $categories_id,
                    'language_id' => $languages[$i]['id']
                ];
                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
                tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
            } elseif ($action == 'update_category') {
                tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = '" . (int) $categories_id . "' and language_id = '" . (int) $languages[$i]['id'] . "' and affiliate_id = 0");
            }
        }

        $_platform_list = \common\classes\platform::getCategoriesAssignList();
        $assign_platform = array();
        if (count($_platform_list) == 1) {
            $assign_platform[] = (int) $_platform_list[0]['id'];
        } else {
            $assign_platform = array_map('intval', Yii::$app->request->post('platform', array()));
        }
        $category_product_assign = Yii::$app->request->post('category_product_assign', array());
        $sub_categories = array((int) $categories_id);
        \common\helpers\Categories::get_subcategories($sub_categories, (int) $categories_id);
        $removed_mapping_pool = array();
        if (count($assign_platform) > 0) {
            $get_removed_r = tep_db_query(
                    "SELECT DISTINCT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
                    "WHERE categories_id IN('" . implode("','", $sub_categories) . "') AND platform_id NOT IN('" . implode("','", $assign_platform) . "') "
            );
            while ($_removed = tep_db_fetch_array($get_removed_r)) {
                $removed_mapping_pool[] = $_removed;
            }
            tep_db_query("DELETE FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id IN('" . implode("','", $sub_categories) . "') AND platform_id NOT IN('" . implode("','", $assign_platform) . "') ");
        } else {
            $get_removed_r = tep_db_query(
                    "SELECT DISTINCT platform_id FROM " . TABLE_PLATFORMS_CATEGORIES . " " .
                    "WHERE categories_id IN('" . implode("','", $sub_categories) . "') "
            );
            while ($_removed = tep_db_fetch_array($get_removed_r)) {
                $removed_mapping_pool[] = $_removed;
            }
            tep_db_query("DELETE FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id IN('" . implode("','", $sub_categories) . "')");
        }
        if (count($removed_mapping_pool) > 0) {
            foreach ($removed_mapping_pool as $removed_mapping) {
                $__remove_ids = array();
                $get_cleanup_ids_r = tep_db_query(
                        "  SELECT /*count(*) as ttl,*/ plp.products_id/*,  max(IF(plc.categories_id is null , if(p2c.categories_id=0,0,-1), plc.categories_id)) AS plc_categories_id*/ " .
                        "  FROM " . TABLE_PLATFORMS_PRODUCTS . " plp " .
                        "    INNER JOIN " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c on p2c.products_id=plp.products_id " .
                        "    LEFT JOIN " . TABLE_PLATFORMS_CATEGORIES . " plc on plc.categories_id=p2c.categories_id AND plc.platform_id=plp.platform_id " .
                        "  WHERE plp.platform_id='{$removed_mapping['platform_id']}' " .
                        "  GROUP BY plp.products_id HAVING MAX(IF(plc.categories_id IS NULL, IF(p2c.categories_id=0,0,-1), plc.categories_id))=-1 "
                );
                while ($_cleanup_ids = tep_db_fetch_array($get_cleanup_ids_r)) {
                    $__remove_ids[] = $_cleanup_ids['products_id'];
                    if (count($__remove_ids) > 99) {
                        tep_db_query(
                                "DELETE FROM " . TABLE_PLATFORMS_PRODUCTS . " " .
                                "WHERE platform_id='{$removed_mapping['platform_id']}' AND products_id IN(" . implode(',', $__remove_ids) . ") "
                        );
                        $__remove_ids = array();
                    }
                }
                if (count($__remove_ids) > 0) {
                    tep_db_query(
                            "DELETE FROM " . TABLE_PLATFORMS_PRODUCTS . " " .
                            "WHERE platform_id='{$removed_mapping['platform_id']}' AND products_id IN(" . implode(',', $__remove_ids) . ") "
                    );
                    $__remove_ids = array();
                }
            }
        }
        foreach ($assign_platform as $assign_platform_id) {
            $_check = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM " . TABLE_PLATFORMS_CATEGORIES . " WHERE categories_id='" . (int) $categories_id . "' AND platform_id='" . $assign_platform_id . "' "));
            if ($_check['c'] == 0) {
                tep_db_perform(TABLE_PLATFORMS_CATEGORIES, array(
                    'categories_id' => (int) $categories_id,
                    'platform_id' => $assign_platform_id,
                ));
            }
            if (isset($category_product_assign[$assign_platform_id]) && $category_product_assign[$assign_platform_id] == 'yes') {
                tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_PRODUCTS . " (products_id, platform_id) SELECT p2c.products_id, '" . $assign_platform_id . "' FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.categories_id='" . (int) $categories_id . "' ");
                for ($_sub_category_idx = 1; $i < count($sub_categories) - 1; $_sub_category_idx++) {
                    $sub_categories[$_sub_category_idx];
                    tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_CATEGORIES . " (categories_id, platform_id) VALUES('" . (int) $sub_categories[$_sub_category_idx] . "','" . $assign_platform_id . "') ");
                    tep_db_query("REPLACE INTO " . TABLE_PLATFORMS_PRODUCTS . " (products_id, platform_id) SELECT p2c.products_id, '" . $assign_platform_id . "' FROM " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c WHERE p2c.categories_id='" . (int) $sub_categories[$_sub_category_idx] . "' ");
                }
            }
        }


        /* if (SUPPLEMENT_STATUS == 'True') {
          tep_db_query("delete from " . TABLE_CATS_PRODUCTS_XSELL . " where categories_id = '" . (int)$categories_id . "'");
          if (is_array($_POST['xsell_product_id'])){
          foreach ($_POST['xsell_product_id'] as $key => $value){
          tep_db_query("insert into " . TABLE_CATS_PRODUCTS_XSELL . " (categories_id, xsell_products_id, sort_order) values ('" . tep_db_input($categories_id) . "', '" . tep_db_input($value) . "', '" . tep_db_input($_POST['xsell_products_sort_order'][$key]). "')");
          }
          }
          tep_db_query("delete from " . TABLE_CATS_PRODUCTS_UPSELL . " where categories_id = '" . (int)$categories_id . "'");
          if (is_array($_POST['upsell_product_id'])){
          foreach ($_POST['upsell_product_id'] as $key => $value){
          tep_db_query("insert into " . TABLE_CATS_PRODUCTS_UPSELL . " (categories_id, upsell_products_id, sort_order) values ('" . tep_db_input($categories_id) . "', '" . tep_db_input($value) . "', '" . tep_db_input($_POST['upsell_products_sort_order'][$key]). "')");
          }
          }

          tep_db_query("delete from " . TABLE_CATEGORIES_UPSELL . " where categories_id = '" . (int)$categories_id . "'");
          if (is_array($_POST['upsell_category_id'])){
          foreach ($_POST['upsell_category_id'] as $key => $value){
          tep_db_query("insert into " . TABLE_CATEGORIES_UPSELL . " (categories_id, upsell_id, sort_order) values ('" . tep_db_input($categories_id) . "', '" . tep_db_input($value) . "', '" . tep_db_input($_POST['upsell_category_sort_order'][$key]). "')");
          }
          }

          } */

// {{ Filters
        if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'saveCategory')) {
            $ext::saveCategory($categories_id);
        }
// }}

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }

        \common\helpers\Categories::update_categories();
        if ($popup == 1) {
            $this->view->categoriesTree = $this->getCategoryTree();
            return $this->render('cat_main_box');
        }

        if ($messageStack->size > 0) {
            $this->view->errorMessage = $messageStack->output(true);
            $this->view->errorMessageType = $messageStack->messageType;
        }
        echo $this->render('error');
        //die();
        //return $this->actionCategoryedit();
    }

    public function actionBundleSearch() {
        global $languages_id, $language;
        $q = Yii::$app->request->getParam('q');
        $prid = Yii::$app->request->getParam('prid', 0);

        $products_string = '';
        $products_query = tep_db_query("select distinct p.products_id, pd.products_name, count(sp.sets_id) is_bundle_set from " . TABLE_PRODUCTS . " p left join " . TABLE_SETS_PRODUCTS . " sp on sp.sets_id = p.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and (p.products_model like '%" . tep_db_input($q) . "%' or pd.products_name like '%" . tep_db_input($q) . "%') and p.products_id <> '" . (int) $prid . "' group by p.products_id having is_bundle_set = 0 order by p.sort_order, pd.products_name");
        while ($products = tep_db_fetch_array($products_query)) {
            $products_string .= '<option id="' . $products['products_id'] . '" value="prod_' . $products['products_id'] . '" style="COLOR:#555555">' . $products['products_name'] . '</option>';
        }

        echo json_encode(array(
            'tf' => '<select name="sets_select" size="16" style="width:100%">' . $products_string . '</select>'
        ));
    }

    /* public function actionEditcategorypopup() {
      global $languages_id, $language;

      \common\helpers\Translation::init('admin/categories');

      $this->layout = false;
      return $this->render('editcategorypopup');
      } */

    public function actionSwitchStatus() {
        $type = Yii::$app->request->post('type');
        $id = Yii::$app->request->post('id');
        $status = Yii::$app->request->post('status');
        switch ($type) {
            case 'products_status':
                tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '" . ($status == 'true' ? 1 : 0) . "', products_last_modified = now() where products_id = '" . (int) $id . "'");
                break;
            case 'categories_status':
                \common\helpers\Categories::set_categories_status((int) $id, ($status == 'true' ? 1 : 0));
                break;
            default:
                break;
        }
        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('categories');
            \common\helpers\System::reset_cache_block('also_purchased');
        }
    }

    private function changeCategoryTree($categories = [], $parent_id = 0) {
        if (is_array($categories)) {
            foreach ($categories as $sortOrder => $category) {
                if (isset($category['id'])) {
                    tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . (int) $sortOrder . "', parent_id = '" . (int) $parent_id . "' where categories_id = '" . (int) $category['id'] . "'");
                    if (isset($category['children'])) {
                        $this->changeCategoryTree($category['children'], $category['id']);
                    }
                }
            }
        }
    }

    public function actionSortOrder() {
        global $languages_id, $login_id;
        $this->layout = false;
        if (isset($_POST['brands'])) {
            $brands = Yii::$app->request->post('brands');
            foreach ($brands as $key => $value) {
                tep_db_query("update " . TABLE_MANUFACTURERS . " set sort_order = '" . $key . "' where manufacturers_id = '" . (int) $value . "'");
            }
        }
        if (isset($_POST['categories'])) {
            $categories = Yii::$app->request->post('categories');
            $categories = stripslashes($categories);
            $categories = json_decode($categories, true);
            $this->changeCategoryTree($categories);
            if (USE_CACHE == 'true') {
                \common\helpers\System::reset_cache_block('categories');
                \common\helpers\System::reset_cache_block('also_purchased');
            }
            \common\helpers\Categories::update_categories();
        }
        if (isset($_GET['listing_type']) && $_GET['listing_type'] == 'category') {
            $parent_id = Yii::$app->request->get('category_id');
            $categories = Yii::$app->request->post('category');
            if (is_array($categories)) {

                $orderByCategory = "c.sort_order, cd.categories_name";
                $search_condition = " where 1 ";
                $search_condition .= " and c.parent_id='" . (int) $parent_id . "'";
                $categories_query_raw = "select distinct(c.categories_id), cd.categories_name, c.categories_status from " . TABLE_CATEGORIES . " c left join " . TABLE_CATEGORIES_DESCRIPTION . " cd on c.categories_id=cd.categories_id " . $search_condition . " and cd.language_id = '" . (int) $languages_id . "' and cd.affiliate_id = 0 " . " order by " . $orderByCategory;
                $categories_query = tep_db_query($categories_query_raw);
                $sortOrder = 0;
                $offsets = array_flip($categories);
                $gridOffset = 0;
                while ($category = tep_db_fetch_array($categories_query)) {
                    $categoryId = $category['categories_id'];
                    if (isset($offsets[$categoryId])) {
                        tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . (int) ($sortOrder + $offsets[$categoryId]) . "' where parent_id = '" . (int) $parent_id . "'  and categories_id = '" . (int) $categoryId . "'");
                        $gridOffset++;
                    } else {
                        $sortOrder += $gridOffset;
                        $gridOffset = 0;
                        tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . (int) $sortOrder . "' where parent_id = '" . (int) $parent_id . "'  and categories_id = '" . (int) $categoryId . "'");
                        $sortOrder++;
                    }
                }


                /* foreach ($categories as $sortOrder => $categoryId) {
                  tep_db_query("update " . TABLE_CATEGORIES . " set sort_order = '" . (int)$sortOrder . "' where parent_id = '" . (int)$parent_id . "'  and categories_id = '" . (int)$categoryId . "'");
                  } */
            }
            $products = Yii::$app->request->post('product');
            if (is_array($products)) {

                $orderByProduct = "p2c.sort_order, pd.products_name";
                $products_query_raw = "select p.products_id, pd.products_name, p.products_status, p.products_image from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int) $languages_id . "' and p.products_id = p2c.products_id " . (tep_session_is_registered('login_vendor') ? " and p.vendor_id = '" . $login_id . "'" : '') . " and pd.affiliate_id = 0 and p2c.categories_id = '" . (int) $parent_id . "' order by " . $orderByProduct;
                $products_query = tep_db_query($products_query_raw);
                $sortOrder = 0;
                $offsets = array_flip($products);
                $gridOffset = 0;
                while ($product = tep_db_fetch_array($products_query)) {
                    $productId = $product['products_id'];
                    if (isset($offsets[$productId])) {
                        tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set sort_order = '" . (int) ($sortOrder + $offsets[$productId]) . "' where categories_id = '" . (int) $parent_id . "'  and products_id = '" . (int) $productId . "'");
                        $gridOffset++;
                    } else {
                        $sortOrder += $gridOffset;
                        $gridOffset = 0;
                        tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set sort_order = '" . (int) $sortOrder . "' where categories_id = '" . (int) $parent_id . "'  and products_id = '" . (int) $productId . "'");
                        $sortOrder++;
                    }
                }

                /* foreach ($products as $sortOrder => $productId) {
                  tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set sort_order = '" . (int)$sortOrder . "' where categories_id = '" . (int)$parent_id . "'  and products_id = '" . (int)$productId . "'");
                  } */
            }
            $this->view->categoriesTree = $this->getCategoryTree();
            return $this->render('cat_main_box');
        }
        if (isset($_GET['listing_type']) && $_GET['listing_type'] == 'brand') {
            $brandId = Yii::$app->request->get('brand_id');
            $products = Yii::$app->request->post('product');
            if (is_array($products)) {
                $ff = '';
                $order = 'p.sort_order, pd.products_name';
                //$products_query_raw = "select * from ".TABLE_PRODUCTS." p left join ".TABLE_PRODUCTS_DESCRIPTION." pd on (p.products_id = pd.products_id and pd.language_id='".intval($languages_id)."') where pd.affiliate_id = 0 " . (intval($brandId) > 0 ? " and manufacturers_id = '" . intval($brandId) ."' " : "") . $ff." group by p.products_id ORDER BY ".$order;
                $products_query_raw = "select * from " . TABLE_PRODUCTS . " p " . (intval($brandId) == -1 ? " left join " . TABLE_MANUFACTURERS . " m ON m.manufacturers_id=p.manufacturers_id " : '') . " left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on (p.products_id = pd.products_id and pd.language_id='" . intval($languages_id) . "') where pd.affiliate_id = 0 " . (intval($brandId) > 0 ? " and manufacturers_id = '" . intval($brandId) . "' " : (intval($brandId) == -1 ? ' and m.manufacturers_id IS NULL' : '')) . $ff . " group by p.products_id ORDER BY " . $order;

                $products_query = tep_db_query($products_query_raw);
                $sortOrder = 0;
                $offsets = array_flip($products);
                $gridOffset = 0;
                while ($product = tep_db_fetch_array($products_query)) {
                    $productId = $product['products_id'];
                    if (isset($offsets[$productId])) {
                        tep_db_query("update " . TABLE_PRODUCTS . " set sort_order = '" . (int) ($sortOrder + $offsets[$productId]) . "' where products_id = '" . (int) $productId . "'");
                        $gridOffset++;
                    } else {
                        $sortOrder += $gridOffset;
                        $gridOffset = 0;
                        tep_db_query("update " . TABLE_PRODUCTS . " set sort_order = '" . (int) $sortOrder . "' where products_id = '" . (int) $productId . "'");
                        $sortOrder++;
                    }
                }
            }
        }
    }

    public function actionCopyMove() {
        $this->layout = false;
        $type = Yii::$app->request->post('type');
        switch ($type) {
            case 'product':
                $copy_to = Yii::$app->request->post('copy_to');
                switch ($copy_to) {
                    case 'move':
                        $products_id = Yii::$app->request->post('products_id');
                        $new_parent_id = Yii::$app->request->post('categories_id');
                        $current_category_id = Yii::$app->request->post('current_category_id');
                        $duplicate_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $new_parent_id . "'");
                        $duplicate_check = tep_db_fetch_array($duplicate_check_query);
                        if ($duplicate_check['total'] < 1)
                            tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int) $new_parent_id . "' where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $current_category_id . "'");
                        break;
                    case 'link':
                        $products_id = Yii::$app->request->post('products_id');
                        $categories_id = Yii::$app->request->post('categories_id');

                        $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int) $products_id . "' and categories_id = '" . (int) $categories_id . "'");
                        $check = tep_db_fetch_array($check_query);
                        if ($check['total'] < '1') {
                            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $products_id . "', '" . (int) $categories_id . "')");
                        }
                        break;
                    case 'dublicate':
                        $products_id = Yii::$app->request->post('products_id');
                        $categories_id = Yii::$app->request->post('categories_id');



                        $product_query = tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . (int) $products_id . "'");
                        $product = tep_db_fetch_array($product_query);

                        $str = "insert into " . TABLE_PRODUCTS . " set ";
                        foreach ($product as $key => $value) {
                            if ($key != 'products_id') {
                                if ($key == 'products_status')
                                    $value = 0;
                                if (is_null($value)) {
                                    $str .= " " . $key . " = NULL, ";
                                } else {
                                    $str .= " " . $key . " = '" . tep_db_input($value) . "', ";
                                }
                            }
                        }
                        $str = substr($str, 0, strlen($str) - 2);
                        tep_db_query($str);

                        $dup_products_id = tep_db_insert_id();

                        $description_query = tep_db_query("select * from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int) $products_id . "'");
                        while ($description = tep_db_fetch_array($description_query)) {
                            $str = "insert into " . TABLE_PRODUCTS_DESCRIPTION . " set ";
                            foreach ($description as $key => $value) {
                                if ($key != 'products_id') {
                                    $str .= " " . $key . " = '" . tep_db_input($value) . "', ";
                                } else {
                                    $str .= " products_id = '" . $dup_products_id . "', ";
                                }
                            }
                            $str = substr($str, 0, strlen($str) - 2);
                            tep_db_query($str);
                        }

                        tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int) $dup_products_id . "', '" . (int) $categories_id . "')");
                        $data_query = tep_db_query("select * from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . tep_db_input($products_id) . "'");
                        tep_db_query("delete from " . TABLE_PRODUCTS_PRICES . " where products_id = '" . $dup_products_id . "'");
                        while ($data = tep_db_fetch_array($data_query)) {
                            tep_db_query("insert into " . TABLE_PRODUCTS_PRICES . " (products_id, groups_id, currencies_id, products_group_price, products_group_discount_price) values ('" . $dup_products_id . "', '" . $data['groups_id'] . "', '" . $data['currencies_id'] . "', '" . $data['products_group_price'] . "', '" . $data['products_group_discount_price'] . "')");
                        }

                        // [[ Properties
                        if (PRODUCTS_PROPERTIES == 'True') {
                            $properties_query = tep_db_query("select * from " . TABLE_PROPERTIES_TO_PRODUCTS . " where products_id = '" . tep_db_input($products_id) . "'");
                            while ($properties = tep_db_fetch_array($properties_query)) {
                                tep_db_query("insert into " . TABLE_PROPERTIES_TO_PRODUCTS . " (products_id, properties_id, language_id, set_value, additional_info) values ('" . $dup_products_id . "', '" . $properties['properties_id'] . "', '" . $properties['language_id'] . "', '" . $properties['set_value'] . "', '" . $properties['additional_info'] . "')");
                            }
                        }
                        // ]]

                        if (PRODUCTS_BUNDLE_SETS == 'True') {
                            $bundle_sets_query = tep_db_query("select * from " . TABLE_SETS_PRODUCTS . " where sets_id = '" . tep_db_input($products_id) . "'");
                            while ($bundle_sets = tep_db_fetch_array($bundle_sets_query)) {
                                tep_db_query("insert into " . TABLE_SETS_PRODUCTS . " (sets_id, product_id, num_product, sort_order) values ('" . (int) $dup_products_id . "', '" . (int) $bundle_sets['product_id'] . "', '" . (int) $bundle_sets['num_product'] . "', '" . (int) $bundle_sets['sort_order'] . "')");
                            }
                        }

                        // [[ SUPPLEMENT_STATUS
                        if (SUPPLEMENT_STATUS == 'True') {
                            $query = tep_db_query("select * from " . TABLE_PRODUCTS_UPSELL . " where products_id = '" . (int) $products_id . "'");
                            while ($data = tep_db_fetch_array($query)) {
                                tep_db_query("insert into " . TABLE_PRODUCTS_UPSELL . " (products_id, upsell_id, sort_order) values ('" . $dup_products_id . "', '" . $data['upsell_id'] . "', '" . $data['sort_order'] . "')");
                            }

                            $query = tep_db_query("select * from " . TABLE_PRODUCTS_XSELL . " where products_id = '" . (int) $products_id . "'");
                            while ($data = tep_db_fetch_array($query)) {
                                tep_db_query("insert into " . TABLE_PRODUCTS_XSELL . " (products_id, xsell_id, sort_order) values ('" . $dup_products_id . "', '" . $data['xsell_id'] . "', '" . $data['sort_order'] . "')");
                            }
                        }
                        // ]]
                        // BOF: WebMakers.com Added: Attributes Copy on non-linked
                        $products_id_from = tep_db_input($products_id);
                        $products_id_to = $dup_products_id;
                        $products_id = $dup_products_id;

                        $copy_attributes = Yii::$app->request->post('copy_attributes');
                        if ($copy_attributes == 'yes') {
                            // WebMakers.com Added: Copy attributes to duplicate product
                            // $products_id_to= $copy_to_products_id;
                            // $products_id_from = $pID;
                            $copy_attributes_delete_first = '1';
                            $copy_attributes_duplicates_skipped = '1';
                            $copy_attributes_duplicates_overwrite = '0';

                            if (DOWNLOAD_ENABLED == 'true') {
                                $copy_attributes_include_downloads = '1';
                                $copy_attributes_include_filename = '1';
                            } else {
                                $copy_attributes_include_downloads = '0';
                                $copy_attributes_include_filename = '0';
                            }
                            \common\helpers\Attributes::copy_products_attributes($products_id_from, $products_id_to);
                            // EOF: WebMakers.com Added: Attributes Copy on non-linked
                        }


                        break;
                }
                if (USE_CACHE == 'true') {
                    \common\helpers\System::reset_cache_block('categories');
                    \common\helpers\System::reset_cache_block('also_purchased');
                }
                break;
            case 'category':
                $categories_id = Yii::$app->request->post('categories_id');
                $parent_id = Yii::$app->request->post('parent_id');
                if ($categories_id != $parent_id) {
                    tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int) $parent_id . "' where categories_id = '" . (int) $categories_id . "'");
                }
                $this->view->categoriesTree = $this->getCategoryTree();
                return $this->render('cat_main_box');
                break;
            case 'brand':
                // products_id brand_id
                $brandId = Yii::$app->request->post('brand_id');
                $productId = Yii::$app->request->post('products_id');
                if ($brandId >= 0) {
                    tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '" . (int) $brandId . "' where products_id 	 = '" . (int) $productId . "'");
                } else {
                    tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = NULL where products_id 	 = '" . (int) $productId . "'");
                }
                break;
            default:
                break;
        }
    }

    /**
     * Autocomplette
     */
    public function actionBrands() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($term)) {
            $search = "manufacturers_name like '%" . tep_db_input($term) . "%'";
        }

        $brands = [];
        $brands_query = tep_db_query("select manufacturers_name  from " . TABLE_MANUFACTURERS . " where " . $search . " group by manufacturers_name order by manufacturers_name");
        while ($response = tep_db_fetch_array($brands_query)) {
            $brands[] = $response['manufacturers_name'];
        }
        echo json_encode($brands);
    }

    public function actionSuppliers() {
        $term = tep_db_prepare_input(Yii::$app->request->get('term'));

        $search = "1";
        if (!empty($term)) {
            $search = "suppliers_name like '%" . tep_db_input($term) . "%'";
        }

        $suppliers = [];
        $suppliers_query = tep_db_query("select suppliers_name  from " . TABLE_SUPPLIERS . " where " . $search . " group by suppliers_name order by suppliers_name");
        while ($response = tep_db_fetch_array($suppliers_query)) {
            $suppliers[] = $response['suppliers_name'];
        }
        echo json_encode($suppliers);
    }

    public function actionBrandedit() {
        global $languages_id, $language;

        $this->view->usePopupMode = false;
        if (Yii::$app->request->isAjax) {
            $this->layout = false;
            $this->view->usePopupMode = true;
        }

        \common\helpers\Translation::init('admin/categories');

        $popup = 0;
        if (Yii::$app->request->isPost) {
            $manufacturers_id = (int) Yii::$app->request->getBodyParam('manufacturers_id');
            $popup = (int) Yii::$app->request->post('popup');
            if ($popup == 0) {
                $this->view->usePopupMode = false;
            }
        } else {
            $manufacturers_id = (int) Yii::$app->request->get('manufacturers_id');
        }
        $this->view->contentAlreadyLoaded = $popup;

        $manufacturers = [];

        if ($manufacturers_id > 0) {
            $manufacturers_query_raw = "select * from " . TABLE_MANUFACTURERS . "  where manufacturers_id = '" . $manufacturers_id . "'";
            $manufacturers_query = tep_db_query($manufacturers_query_raw);
            $manufacturers = tep_db_fetch_array($manufacturers_query);
        }
        $mInfo = new \objectInfo($manufacturers);

        $imageScript = '$("#manufacturer_logo").hide();';
        if ($mInfo->manufacturers_image) {
            $image_path = DIR_WS_CATALOG_IMAGES . $mInfo->manufacturers_image;
            $imageScript = '
                 $("#manufacturer_logo").attr("src","' . $image_path . '");
                 $("#manufacturer_logo").show();
            ';
        }

        $mDescription = [];
        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $languages[$i]['logo'] = $languages[$i]['image'];
            $mDescription[$i]['code'] = $languages[$i]['code'];
            $mDescription[$i]['manufacturers_url'] = tep_draw_input_field('manufacturers_url[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturer_url($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_seo_name'] = tep_draw_input_field('manufacturers_seo_name[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturer_seo_name($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_meta_description'] = tep_draw_textarea_field('manufacturers_meta_description[' . $languages[$i]['id'] . ']', 'soft', '25', '7', \common\helpers\Manufacturers::get_manufacturer_meta_descr($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_meta_key'] = tep_draw_textarea_field('manufacturers_meta_key[' . $languages[$i]['id'] . ']', 'soft', '25', '7', \common\helpers\Manufacturers::get_manufacturer_meta_key($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
            $mDescription[$i]['manufacturers_meta_title'] = tep_draw_input_field('manufacturers_meta_title[' . $languages[$i]['id'] . ']', \common\helpers\Manufacturers::get_manufacturer_meta_title($mInfo->manufacturers_id, $languages[$i]['id']), 'class="form-control"');
        }

        $this->selectedMenu = array('catalog', 'categories');
        $text_new_or_edit = ($manufacturers_id == 0) ? TEXT_INFO_HEADING_NEW_BRAND : TEXT_INFO_HEADING_EDIT_BRAND;
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('categories/index'), 'title' => $text_new_or_edit);
        return $this->render('brandedit', ['manufacturers_id' => $manufacturers_id, 'mInfo' => $mInfo, 'imageScript' => $imageScript, 'languages' => $languages, 'mDescription' => $mDescription]);
    }

    function actionBrandSubmit() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/manufacturers');

        $this->layout = FALSE;
        $error = FALSE;
        $message = '';
        $script = '';

        $messageType = 'success';

        $popup = (int) Yii::$app->request->post('popup');
        $manufacturers_id = (int) Yii::$app->request->post('manufacturers_id');
        $manufacturers_name = tep_db_prepare_input(Yii::$app->request->post('manufacturers_name'));
        $remove_image = Yii::$app->request->post('remove_image');
        $delete_image = Yii::$app->request->post('delete_image');
        $manufacturers_url = Yii::$app->request->post('manufacturers_url');
        $manufacturers_old_seo_page_name = Yii::$app->request->post('manufacturers_old_seo_page_name');
        $manufacturers_meta_title = Yii::$app->request->post('manufacturers_meta_title');
        $manufacturers_meta_description = Yii::$app->request->post('manufacturers_meta_description');
        $manufacturers_meta_key = Yii::$app->request->post('manufacturers_meta_key');
        $manufacturers_seo_name = Yii::$app->request->post('manufacturers_seo_name');
        $manufacturers_image_loaded = $_POST['manufacturers_image_loaded']; //tep_db_prepare_input( Yii::$app->request->getParam( '$manufacturers_image_loaded' ) );

        $sql_data_array = array('manufacturers_name' => $manufacturers_name);
        $sql_data_array['manufacturers_old_seo_page_name'] = $manufacturers_old_seo_page_name;

        if (isset($remove_image) && ( $remove_image == 'on' )) {
            $sql_data_array['manufacturers_image'] = '';
        }

        if ((int) $manufacturers_id != 0 && isset($delete_image) && ( $delete_image == 'on' )) {
            $sql_data_array['manufacturers_image'] = '';
            $manufacturer = tep_db_fetch_array(tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int) $manufacturers_id . "'"));
            if (!empty($manufacturer['manufacturers_image'])) {
                $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $manufacturer['manufacturers_image'];
                if (file_exists($image_location))
                    @unlink($image_location);
            }
        }

        $action = '';
        if ($error === FALSE) {
            if ($manufacturers_id > 0) {
                // Update
                $action = 'update';
                $update_sql_data = array('last_modified' => 'now()');

                $sql_data_array = array_merge($sql_data_array, $update_sql_data);

                tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array, 'update', "manufacturers_id = '" . (int) $manufacturers_id . "'");

                $message = TEXT_INFO_UPDATED;
            } else {
                // Insert
                $action = 'insert';
                $insert_sql_data = array('date_added' => 'now()');

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_MANUFACTURERS, $sql_data_array);
                $manufacturers_id = (int) tep_db_insert_id();
                Yii::$app->request->setBodyParams(['manufacturers_id' => $manufacturers_id, 'popup' => $popup]);

                if ($manufacturers_id > 0) {
                    $script = '
                     <script type="text/javascript">
                        setTimeout(function(data){
                            $("form[name=save_manufacturer_form] input[name=manufacturers_id]").val(' . $manufacturers_id . ');
                        }, 500);
                     </script>
                    ';
                }

                $message = TEXT_INFO_SAVED;
            }
            if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
                $ext::saveBrandLinks($manufacturers_id, $_POST);
            }
        }

        if ($manufacturers_image_loaded != '') {
            $path = \Yii::getAlias('@webroot');
            $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
            $tmp_name = $path . $manufacturers_image_loaded;
            $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES;
            $new_name = $image_location . $manufacturers_image_loaded;
            copy($tmp_name, $new_name);
            @unlink($tmp_name);
            tep_db_query("update " . TABLE_MANUFACTURERS . " set manufacturers_image = '" . tep_db_input($manufacturers_image_loaded) . "' where manufacturers_id = '" . (int) $manufacturers_id . "'");
        }

        $languages = \common\helpers\Language::get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
            $manufacturers_url_array = $manufacturers_url;
            $manufacturers_meta_title = $manufacturers_meta_title;
            $manufacturers_meta_description = $manufacturers_meta_description;
            $manufacturers_meta_key = $manufacturers_meta_key;
            $manufacturers_seo_name = $manufacturers_seo_name;

            $language_id = $languages[$i]['id'];

            if (!tep_not_null($manufacturers_seo_name[$language_id])) {
                $manufacturers_seo_name[$language_id] = Seo::makeSlug($manufacturers_name);
            }

            $sql_data_array = array('manufacturers_url' => tep_db_prepare_input($manufacturers_url_array[$language_id]),
                'manufacturers_meta_description' => tep_db_prepare_input($manufacturers_meta_description[$language_id]),
                'manufacturers_meta_key' => tep_db_prepare_input($manufacturers_meta_key[$language_id]),
                'manufacturers_meta_title' => tep_db_prepare_input($manufacturers_meta_title[$language_id]),
                'manufacturers_seo_name' => tep_db_prepare_input($manufacturers_seo_name[$language_id]));

            if ($action == 'insert') {
                $insert_sql_data = array('manufacturers_id' => $manufacturers_id,
                    'languages_id' => $language_id);

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array);
            } elseif ($action == 'update') {
                tep_db_perform(TABLE_MANUFACTURERS_INFO, $sql_data_array, 'update', "manufacturers_id = '" . (int) $manufacturers_id . "' and languages_id = '" . (int) $language_id . "'");
            }
        }

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('manufacturers');
        }

        if ($error === TRUE) {
            $messageType = 'warning';

            if ($message == '')
                $message = WARN_UNKNOWN_ERROR;
        }

        if ($popup == 1) {
            $this->view->brandsList = $this->getBrandsList();
            return $this->render('brand_box');
        }
        ?>
        <div class="popup-box-wrap pop-mess">
            <div class="around-pop-up"></div>
            <div class="popup-box">
                <div class="pop-up-close pop-up-close-alert"></div>
                <div class="pop-up-content">
                    <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                    <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
        <?= $message ?>
        <?= $script ?>
                    </div>
                </div>
                <div class="noti-btn">
                    <div></div>
                    <div><a href="javascript:void(0)" class="btn btn-primary" onClick="return backStatement();"><?php echo TEXT_BTN_OK; ?></a></div>
                </div>
            </div>
            <script>
                $('body').scrollTop(0);
                /* $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function(){
                 console.log('1');
                 $(this).parents('.pop-mess').remove();
                 }); */
            </script>
        </div>

        <?php
        return $this->actionBrandedit();
    }

    public function actionConfirmManufacturerDelete() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/manufacturers');
        \common\helpers\Translation::init('admin/faqdesk');

        $this->layout = FALSE;

        $manufacturers_id = Yii::$app->request->get('manufacturers_id');

        $message = '';
        $heading = array();
        $contents = array();

        $manufacturers_query_raw = "select manufacturers_id, manufacturers_name, manufacturers_image, date_added, last_modified from " . TABLE_MANUFACTURERS . " where  manufacturers_id = '$manufacturers_id' ";
        $manufacturers_query = tep_db_query($manufacturers_query_raw);
        while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
            $manufacturer_products_query = tep_db_query("select count(*) as products_count from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int) $manufacturers['manufacturers_id'] . "'");
            $manufacturer_products = tep_db_fetch_array($manufacturer_products_query);
            $mInfo_array = array_merge($manufacturers, $manufacturer_products);
            $mInfo = new \objectInfo($mInfo_array);
        }

        $heading[] = array('text' => '<b>' . TEXT_HEADING_DELETE_MANUFACTURER . '</b>');
        $contents[] = array('text' => TEXT_DELETE_INTRO . '<br>');
        $contents[] = array('text' => '<br><b>' . $mInfo->manufacturers_name . '</b>');

        if ($mInfo->products_count > 0) {
            $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_products') . ' ' . TEXT_DELETE_PRODUCTS);
            //$contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count));
            $messageType = 'warning';
            $message = sprintf(TEXT_DELETE_WARNING_PRODUCTS, $mInfo->products_count);
            ?>
            <div class="popup-box-wrap pop-mess">
                <div class="around-pop-up"></div>
                <div class="popup-box">
                    <div class="pop-up-close pop-up-close-alert"></div>
                    <div class="pop-up-content">
                        <div class="popup-heading"><?php echo TEXT_NOTIFIC; ?></div>
                        <div class="popup-content pop-mess-cont pop-mess-cont-<?= $messageType ?>">
            <?= $message ?>
                        </div>
                    </div>
                    <div class="noti-btn">
                        <div></div>
                        <div><span class="btn btn-primary"><?php echo TEXT_BTN_OK; ?></span></div>
                    </div>
                </div>
                <script>
                    $('body').scrollTop(0);
                    $('.pop-mess .pop-up-close-alert, .noti-btn .btn').click(function () {
                        $(this).parents('.pop-mess').remove();
                    });
                </script>
            </div>

            <?php
        }

        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('delete_image', '', TRUE) . ' ' . TEXT_DELETE_IMAGE);
        echo '<div class="brand_pad">';
        echo tep_draw_form('manufacturer_delete', FILENAME_MANUFACTURERS, \common\helpers\Output::get_all_get_params(array('action')) . 'action=update', 'post', 'id="manufacturer_delete" onSubmit="return deleteManufacturer();"');
        echo '<div class="or_box_head">' . TEXT_HEADING_DELETE_MANUFACTURER . '</div>';
        echo '<div class="col_desc">' . TEXT_DELETE_INTRO . ' <b>' . $mInfo->manufacturers_name . '</b></div>';
        echo '<div class="check_linear">' . tep_draw_checkbox_field('delete_image', '', TRUE) . ' <span>' . TEXT_DELETE_IMAGE . '</span></div>';
        /* $box = new \box;
          echo $box->infoBox( $heading, $contents ); */
        ?>
        <div class="btn-toolbar btn-toolbar-order">
        <?php
        echo '<button class="btn btn-delete btn-no-margin">' . IMAGE_DELETE . '</button>';
        echo '<input type="button" class="btn btn-cancel" value="' . IMAGE_CANCEL . '" onClick="return closePopup()">';

        echo tep_draw_hidden_field('manufacturers_id', $manufacturers_id);
        ?>
        </div>
        </form>
        </div>
        <?php
    }

    //manufacturer-delete
    public function actionManufacturerDelete() {
        $this->layout = FALSE;

        $manufacturers_id = (int) Yii::$app->request->post('manufacturers_id');
        $delete_image = Yii::$app->request->post('delete_image');
        $delete_products = Yii::$app->request->post('delete_products');

        $messageType = 'success';
        $message = TEXT_INFO_DELETED;

        if (isset($delete_image) && ( $delete_image == 'on' )) {
            $manufacturer_query = tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int) $manufacturers_id . "'");
            $manufacturer = tep_db_fetch_array($manufacturer_query);

            $image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES . $manufacturer['manufacturers_image'];

            if (file_exists($image_location))
                @unlink($image_location);
        }

        tep_db_query("delete from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int) $manufacturers_id . "'");
        tep_db_query("delete from " . TABLE_MANUFACTURERS_INFO . " where manufacturers_id = '" . (int) $manufacturers_id . "'");
        
        if ($ext = \common\helpers\Acl::checkExtension('SeoRedirectsNamed', 'allowed')){
            $ext::deleteBrandLinks($manufacturers_id);
        }

        if (isset($delete_products) && ( $delete_products == 'on' )) {
            $products_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where manufacturers_id = '" . (int) $manufacturers_id . "'");
            while ($products = tep_db_fetch_array($products_query)) {
                \common\helpers\Product::remove_product($products['products_id']);
            }
        } else {
            tep_db_query("update " . TABLE_PRODUCTS . " set manufacturers_id = '' where manufacturers_id = '" . (int) $manufacturers_id . "'");
        }

        if (USE_CACHE == 'true') {
            \common\helpers\System::reset_cache_block('manufacturers');
        }

        $this->view->brandsList = $this->getBrandsList();
        return $this->render('brand_box');
    }

    public function actionTemporaryUpload() {
        $path = \Yii::getAlias('@webroot');
        $path .= DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;

        $filename = '';
        $status = 0;

        if (isset($_FILES['filedrop_files']['name'])) {
            if ((int) $_FILES['filedrop_files']['error'] === 0) {

                $tmp_name = $_FILES['filedrop_files']['tmp_name'];

                //$image_location = DIR_FS_DOCUMENT_ROOT . DIR_WS_CATALOG_IMAGES;
                $new_name = $path . $_FILES['filedrop_files']['name'];
                copy($tmp_name, $new_name);
                $filename = $_FILES['filedrop_files']['name'];
                $status = 1;
            }
        }

        $response = array('status' => $status, 'filename' => $filename);
        echo json_encode($response);
    }

    public function actionSupplierSelect() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/suppliers');

        $this->layout = false;

        $this->view->suppliers = ['0' => TEXT_NEW_SUPPLIER];
        $this->view->suppliers_js = "arSurcharge = []; arMargin = [];\n";
        $suppliers_query = tep_db_query("select suppliers_id, suppliers_name, suppliers_surcharge_amount, suppliers_margin_percentage from " . TABLE_SUPPLIERS . " order by suppliers_name");
        while ($suppliers = tep_db_fetch_array($suppliers_query)) {
            $this->view->suppliers[$suppliers['suppliers_id']] = $suppliers['suppliers_name'];
            $this->view->suppliers_js .= "arSurcharge[$suppliers[suppliers_id]] = '$suppliers[suppliers_surcharge_amount]'; arMargin[$suppliers[suppliers_id]] = '$suppliers[suppliers_margin_percentage]';\n";
        }

        return $this->render('supplierselect', ['uprid' => $_GET['uprid']]);
    }

    public function actionSupplierPrice() {
        global $languages_id, $language, $currencies;

        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/suppliers');

        $currencies = new \common\classes\currencies();

        $this->layout = false;

        $this->view->suppliers = [];
        $groups_id = Yii::$app->request->get('gID', 0);
        $products_tax_class_id = Yii::$app->request->post('products_tax_class_id', 0);
        $suppliers_id = Yii::$app->request->post('suppliers_id', array());
        $suppliers_model = Yii::$app->request->post('suppliers_model', array());
        $suppliers_quantity = Yii::$app->request->post('suppliers_quantity', array());
        $suppliers_price = Yii::$app->request->post('suppliers_price', array());
        $supplier_discount = Yii::$app->request->post('supplier_discount', array());
        $suppliers_surcharge_amount = Yii::$app->request->post('suppliers_surcharge_amount', array());
        $suppliers_margin_percentage = Yii::$app->request->post('suppliers_margin_percentage', array());
        $suppliers_data_query = tep_db_query("select * from " . TABLE_SUPPLIERS . " order by suppliers_id");
        while ($suppliers_data = tep_db_fetch_array($suppliers_data_query)) {
            if ($suppliers_price[$suppliers_data['suppliers_id']] > 0) {
                $this->view->suppliers[$suppliers_data['suppliers_id']]['groups_id'] = (int) $groups_id;
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_name'] = $suppliers_data['suppliers_name'];
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_model'] = $suppliers_model[$suppliers_data['suppliers_id']];
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_price'] = $suppliers_price[$suppliers_data['suppliers_id']];
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_quantity'] = (int) $suppliers_quantity[$suppliers_data['suppliers_id']];
                $this->view->suppliers[$suppliers_data['suppliers_id']]['supplier_discount'] = $supplier_discount[$suppliers_data['suppliers_id']];
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_surcharge_amount'] = $suppliers_surcharge_amount[$suppliers_data['suppliers_id']];
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_margin_percentage'] = $suppliers_margin_percentage[$suppliers_data['suppliers_id']];

                $suppliers_calculated_price = ($suppliers_price[$suppliers_data['suppliers_id']] * (1 - $supplier_discount[$suppliers_data['suppliers_id']] / 100)) * (1 + $suppliers_margin_percentage[$suppliers_data['suppliers_id']] / 100) + $suppliers_surcharge_amount[$suppliers_data['suppliers_id']];
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_calculated_price_net'] = $currencies->display_price($suppliers_calculated_price, 0);
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_calculated_price_gross'] = $currencies->display_price($suppliers_calculated_price, \common\helpers\Tax::get_tax_rate_value($products_tax_class_id));
                $this->view->suppliers[$suppliers_data['suppliers_id']]['suppliers_calculated_profit'] = $currencies->format($suppliers_calculated_price - $suppliers_price[$suppliers_data['suppliers_id']] * (1 - $supplier_discount[$suppliers_data['suppliers_id']] / 100));
            }
        }

        return $this->render('supplierprice');
    }

    public function actionSupplierAdd() {
        global $languages_id, $language;

        \common\helpers\Translation::init('admin/categories');
        \common\helpers\Translation::init('admin/suppliers');

        if (Yii::$app->request->post('add', 0)) {
            $suppliers_name = Yii::$app->request->post('suppliers_name', '');
            $suppliers_surcharge_amount = Yii::$app->request->post('suppliers_surcharge_amount', 0);
            $suppliers_margin_percentage = Yii::$app->request->post('suppliers_margin_percentage', 0);
            $sql_data_array = array('suppliers_name' => $suppliers_name,
                'suppliers_surcharge_amount' => $suppliers_surcharge_amount,
                'suppliers_margin_percentage' => $suppliers_margin_percentage,
                'date_added' => 'now()');
            tep_db_perform(TABLE_SUPPLIERS, $sql_data_array);
            $suppliers_id = tep_db_insert_id();
        } else {
            $suppliers_id = Yii::$app->request->post('suppliers_id', 0);
        }

        if ($suppliers_id > 0) {
            $suppliers = tep_db_fetch_array(tep_db_query("select suppliers_id, suppliers_name, suppliers_surcharge_amount, suppliers_margin_percentage, suppliers_script, date_added, last_modified from " . TABLE_SUPPLIERS . " where suppliers_id = '" . (int) $suppliers_id . "'"));
            $sInfo = new \objectInfo($suppliers, false);
            $this->layout = false;
            if (strpos($_POST['uprid'], '{') !== false) {
                return $this->render('supplierinventory', ['sInfo' => $sInfo, 'uprid' => $_POST['uprid']]);
            } else {
                return $this->render('supplierproduct', ['sInfo' => $sInfo]);
            }
        }
    }

    public function actionFilterTabList() {
        global $languages_id;

        \common\helpers\Translation::init('admin/categories');

        $draw = Yii::$app->request->get('draw', 1);
        $categories_id = Yii::$app->request->get('cID', 0);

        $categories_array = array($categories_id => $categories_id);
        \common\helpers\Categories::get_subcategories($categories_array, $categories_id);

        $responseList = array();
        $filters_query = tep_db_query("

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_KEYWORDS) . "' as name, '' as values_array, 'keywords' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_FILTERS . " f on f.filters_type = 'keywords' and f.categories_id = '" . (int) $categories_id . "' where p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by id)

union

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_PRICE) . "' as name, group_concat(distinct round(p.products_price, 2) order by p.products_price asc separator ',') as values_array, 'price' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_FILTERS . " f on f.filters_type = 'price' and f.categories_id = '" . (int) $categories_id . "' where p.products_price > 0 and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by id)

union

(select 0 as id, '" . tep_db_input(TEXT_PRODUCT . ': ' . TEXT_MANUFACTURER) . "' as name, group_concat(distinct p.manufacturers_id order by p.manufacturers_id asc separator ',') as values_array, 'brand' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_FILTERS . " f on f.filters_type = 'brand' and f.categories_id = '" . (int) $categories_id . "' where p.manufacturers_id > 0 and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by id)

union

(select po.products_options_id as id, concat('" . tep_db_input(TEXT_ATTRIBUTE . ': ') . "', po.products_options_name) as name, group_concat(distinct pa.options_values_id order by pa.options_values_id asc separator ',') as values_array, 'attribute' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PRODUCTS_OPTIONS . " po left join " . TABLE_FILTERS . " f on f.options_id = po.products_options_id and f.filters_type = 'attribute' and f.categories_id = '" . (int) $categories_id . "', " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where po.products_options_id = pa.options_id and po.language_id = '" . (int) $languages_id . "' and pa.products_id = p.products_id and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by po.products_options_id order by f.status desc, f.sort_order, po.products_options_sort_order, po.products_options_name)

union

(select pr.properties_id as id, concat('" . tep_db_input(TEXT_PROPERTY . ': ') . "', prd.properties_name) as name, group_concat(distinct pr2p.values_id order by pr2p.values_id asc separator ',') as values_array, 'property' as type, f.status as status, f.sort_order as sort_order from " . TABLE_PROPERTIES . " pr left join " . TABLE_FILTERS . " f on f.properties_id = pr.properties_id and f.filters_type = 'property' and f.categories_id = '" . (int) $categories_id . "', " . TABLE_PROPERTIES_DESCRIPTION . " prd, " . TABLE_PROPERTIES_TO_PRODUCTS . " pr2p, " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where pr.properties_id = pr2p.properties_id and pr.display_filter = '1' and pr.properties_id = prd.properties_id and prd.language_id = '" . (int) $languages_id . "' and pr2p.products_id = p.products_id and p.products_id = p2c.products_id and p2c.categories_id in ('" . implode("','", $categories_array) . "') group by pr.properties_id order by f.status desc, f.sort_order, pr.sort_order, prd.properties_name)

order by status desc, sort_order

");

        while ($filters = tep_db_fetch_array($filters_query)) {
            if ($ext = \common\helpers\Acl::checkExtension('ProductPropertiesFilters', 'getRowData')) {
                $responseList[] = $ext::getRowData($filters);
            } else {
                $responseList[] = array(
                    '<div class="handle_cat_list dis_module"><span class="handle"><i class="icon-hand-paper-o"></i></span><div class="module_title">' . $filters['name'] . '</div></div>',
                    '<div class="count_block dis_module">' . (tep_not_null($filters['values_array']) ? '<span class="count_values">' . count(explode(',', $filters['values_array'])) . '</span><a href="javascript:void(0)" class="view_filter_values">' . TEXT_VIEW_VALUES . '</a>' : '&nbsp;') . '</div>',
                    '<input type="checkbox" value="1" class="check_on_off" disabled>'
                );
            }
        }

        $response = [
            'draw' => $draw,
            'data' => $responseList
        ];
        echo json_encode($response);
    }

    public function actionViewvalues() {
        global $languages_id;

        $this->layout = false;
        $this->view->usePopupMode = true;

        $type = Yii::$app->request->get('type');
        $id = Yii::$app->request->get('id');
        $values = Yii::$app->request->get('values', array());

        $values_html = '';
        switch ($type) {
            case 'price':
                $currencies = new \common\classes\currencies();
                foreach (explode(',', $values) as $price) {
                    $values_html .= '<div>' . $currencies->format($price) . '</div>';
                }
                break;
            case 'brand':
                $values_query = tep_db_query("select manufacturers_name from " . TABLE_MANUFACTURERS . " where manufacturers_id in ('" . implode("','", explode(',', $values)) . "') order by manufacturers_name");
                while ($values = tep_db_fetch_array($values_query)) {
                    $values_html .= '<div>' . $values['manufacturers_name'] . '</div>';
                }
                break;
            case 'attribute':
                $values_query = tep_db_query("select pov.products_options_values_name from " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS . " pov2po, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pov2po.products_options_values_id = pov.products_options_values_id and pov2po.products_options_id = '" . (int) $id . "' and pov.products_options_values_id in ('" . implode("','", explode(',', $values)) . "') and pov.language_id = '" . (int) $languages_id . "' order by pov.products_options_values_name");
                while ($values = tep_db_fetch_array($values_query)) {
                    $values_html .= '<div>' . $values['products_options_values_name'] . '</div>';
                }
                break;
            case 'property':
                $values_query = tep_db_query("select p.properties_type, p.decimals, pv.values_text, pv.values_number, pv.values_number_upto, pv.values_alt from " . TABLE_PROPERTIES . " p, " . TABLE_PROPERTIES_VALUES . " pv where p.properties_id = pv.properties_id and pv.properties_id = '" . (int) $id . "' and pv.values_id in ('" . implode("','", explode(',', $values)) . "') and pv.language_id = '" . (int) $languages_id . "' order by pv.values_number, pv.values_text");
                while ($values = tep_db_fetch_array($values_query)) {
                    if ($values['properties_type'] == 'number' || $values['properties_type'] == 'interval') {
                        $values_html .= '<div>' . (float) number_format($values['values_number'], $values['decimals']) . '</div>';
                    } elseif ($values['properties_type'] == 'interval') {
                        $values_html .= '<div>' . (float) number_format($values['values_number'], $values['decimals']) . ' - ' . (float) number_format($values['values_number_upto'], $values['decimals']) . '</div>';
                    } else {
                        $values_html .= '<div>' . $values['values_text'] . '</div>';
                    }
                }
                break;
        }

        $html = '<div class="viewContent">' . $values_html . '</div>';

        return $html;
    }

    public function actionFileManager() {
        $this->layout = false;

        unset($_SESSION['uploaded_file_name']);

        $fsPath = DIR_FS_CATALOG . 'documents/';
        $wsPath = DIR_WS_CATALOG . 'documents/';

        $fileList = [];
        $downloadList = array_diff(scandir($fsPath), array('..', '.'));
        foreach ($downloadList as $downloadFile) {
            if (is_file($fsPath . '/' . $downloadFile)) {
                $fileList[] = $downloadFile;
            }
        }
        return $this->render('file-manager', [
                    'fileList' => $fileList,
        ]);
    }

    public function actionFileManagerUpload() {
        $response = ['status' => 'error'];
        if (isset($_FILES['files'])) {
            $path = DIR_FS_CATALOG . 'documents/';
            $uploadfile = $path . basename($_FILES['files']['name']);

            if (move_uploaded_file($_FILES['files']['tmp_name'], $uploadfile)) {
                $text = '';
                $_SESSION['uploaded_file_name'][] = $_FILES['files']['name'];
                $response = ['status' => 'ok', 'text' => $text, 'file_name' => $_FILES['files']['name']];
            }
        }
        echo json_encode($response);
    }

    public function actionFileManagerListing() {
        $this->layout = false;
        global $languages_id;
        \common\helpers\Translation::init('admin/categories');

        $fsPath = DIR_FS_CATALOG . 'documents/';
        $wsPath = DIR_WS_CATALOG . 'documents/';

        $draw = Yii::$app->request->get('draw', 1);
        $start = Yii::$app->request->get('start', 0);
        $length = Yii::$app->request->get('length', 10);
        $current_category_id = Yii::$app->request->get('id', 0);
        $search = Yii::$app->request->get('search');

        if ($length == -1)
            $length = 10000;

        $documents = [];
        $documents[] = array('id' => '', 'text' => 'Please choose group to link');
        $documents_data_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " where language_id='" . $languages_id . "' order by document_types_name");
        while ($documents_data = tep_db_fetch_array($documents_data_query)) {
            $documents[] = array('id' => $documents_data['document_types_id'], 'text' => $documents_data['document_types_name']);
        }
        $formFilter = Yii::$app->request->get('filter');
        parse_str($formFilter, $output);
        $files_arr = array();
        if (is_array($output['filename'])) {
            foreach ($output['filename'] as $item1) {
                foreach ($item1 as $item2) {
                    $files_arr[] = $item2;
                }
            }
        }

        /**
         * products_documents_id
         * document_types_id
         * filename
         * title
         */
        $products_id = (int) $output['global_id'];

        $fileList = [];
        $downloadList = array_diff(scandir($fsPath), array('..', '.'));
        $uploaded_file_names = $_SESSION['uploaded_file_name'];
        if ($uploaded_file_names) {
            $downloadList = array_merge($uploaded_file_names, $downloadList);
            $new_files = count($uploaded_file_names);
        } else {
            $uploaded_file_names = array();
            $new_files = 0;
        }
        $counter = 0;
        foreach ($downloadList as $downloadFile) {
            if ($search['value']) {
                if (strpos(strtolower($downloadFile), strtolower($search['value'])) === false) {
                    continue;
                }
            }
            if ($counter > $new_files && in_array($downloadFile, $uploaded_file_names)) {
                continue;
            }
            if (is_file($fsPath . '/' . $downloadFile)) {
                //file not used?
                $docs_data_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DOCUMENTS . " where filename='" . tep_db_input($downloadFile) . "'");
                $docs_data = tep_db_fetch_array($docs_data_query);
                $actions = '';
                $delete = '';
                if ($docs_data['total'] == 0 && !in_array($downloadFile, $files_arr)) {
                    $delete = '<span class="file-remove" onclick="deleteFile(\'' . $downloadFile . '\')" title="' . IMAGE_DELETE . '"></span>';
                }
                /* $docs_data_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_DOCUMENTS . " where filename='" . tep_db_input($downloadFile) . "' and products_id=" . (int)$products_id);
                  $docs_data = tep_db_fetch_array($docs_data_query); */

                if (!in_array($downloadFile, $files_arr)/* $docs_data['total'] == 0 */) {
                    $actions .= tep_draw_pull_down_menu('doc_type_' . $counter, $documents, '', 'class="form-control"') . '<span onclick="addFile(\'' . $downloadFile . '\', \'' . $products_id . '\', \'' . 'doc_type_' . $counter . '\')" class="btn">' . TEXT_ADD . '</span>';
                } else {
                    $actions .= '&nbsp;<span onclick="removeFile(\'' . $downloadFile . '\', \'' . $products_id . '\')" class="unlink">' . UNLINK_FROM_PRODUCT . '</span>';
                }

                $downloadFile = '<span onclick="renameFile(\'' . $downloadFile . '\')" class="btn-edit-file" title="' . EDIT_FILE_NAME . '"></span><span class="file-name" data-name="' . $downloadFile . '">' . $downloadFile . '</span>';

                if ($counter == $new_files - 1) {
                    $downloadFile .= '
<script type="text/javascript">
  $("#document_list tbody tr").each(function(i){
    if (i < ' . $new_files . ') $(this).addClass("new-file")
  })
</script>';
                }


                $fileList[] = [
                    $downloadFile,
                    $actions,
                    $delete
                ];
                $counter++;
            }
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $counter,
            'recordsFiltered' => $counter,
            'data' => $fileList
        ];
        echo json_encode($response);
    }

    public function actionFileManagerDelete() {
        $this->layout = false;

        $fsPath = DIR_FS_CATALOG . 'documents/';

        $downloadFile = Yii::$app->request->get('name');
        if (is_file($fsPath . '/' . $downloadFile)) {
            @unlink($fsPath . '/' . $downloadFile);
        }
    }

    public function actionFileManagerRemove() {
        $this->layout = false;

        $products_id = (int) Yii::$app->request->post('id');
        $downloadFile = tep_db_prepare_input(Yii::$app->request->post('name'));

        $query = tep_db_query("select products_documents_id from " . TABLE_PRODUCTS_DOCUMENTS . " where products_id  = '" . (int) $products_id . "'");
        while ($item = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_PRODUCTS_DOCUMENTS_TITLES . " where products_documents_id  = '" . (int) $item['products_documents_id'] . "'");
        }
        tep_db_query("delete from " . TABLE_PRODUCTS_DOCUMENTS . " where products_id = '" . $products_id . "' and filename = '" . tep_db_input($downloadFile) . "'");
    }

    public function actionFileManagerAdd() {
        $this->layout = false;
        global $languages_id;
        \common\helpers\Translation::init('admin/categories');

        $formFilter = Yii::$app->request->post('filter');
        parse_str($formFilter, $output);
        $products_id = (int) $output['global_id'];

        $name = Yii::$app->request->post('name');
        $type = (int) Yii::$app->request->post('type');


        /**
         * products_documents_id
         * document_types_id
         * filename
         * title
         */
        $products_documents_id = $output['products_documents_id'];
        $document_types_id = $output['document_types_id'];
        $filename = $output['filename'];
        $title = $output['title'];
        $sort_order = $output['sort_order'];

        $languages = \common\helpers\Language::get_languages();
        $this->view->documents = [];
        $documents_data_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " where language_id='" . $languages_id . "' order by document_types_name");
        while ($documents_data = tep_db_fetch_array($documents_data_query)) {
            $docs = [];
            if (isset($products_documents_id[$documents_data['document_types_id']]) && is_array($products_documents_id[$documents_data['document_types_id']])) {
                foreach ($products_documents_id[$documents_data['document_types_id']] as $key => $value) {
                    $doc_title = array();
                    foreach ($languages as $language) {
                        $doc_title[$language['id']] = $title[$language['id']][$documents_data['document_types_id']][$key];
                    }
                    $docs[] = [
                        'products_documents_id' => $value,
                        'document_types_id' => $document_types_id[$documents_data['document_types_id']][$key],
                        'filename' => $filename[$documents_data['document_types_id']][$key],
                        'title' => $doc_title,
                        'sort_order' => $sort_order[$documents_data['document_types_id']][$key],
                    ];
                }
            }
            if ($documents_data['document_types_id'] == $type) {
                $docs[] = [
                    'products_documents_id' => '',
                    'document_types_id' => $type,
                    'filename' => $name,
                    'title' => '',
                ];
            }

            /* $docs_data_query = tep_db_query("select * from " . TABLE_PRODUCTS_DOCUMENTS . " where document_types_id=" . $documents_data['document_types_id'] . " and products_id=" . (int)$products_id);
              while ($docs_data = tep_db_fetch_array($docs_data_query)) {
              $docs[] = $docs_data;
              } */
            $this->view->documents[$documents_data['document_types_id']] = [
                'id' => $documents_data['document_types_id'],
                'title' => $documents_data['document_types_name'],
                'docs' => $docs,
            ];
        }

        return $this->render('file-manager-add', [
                    'global_id' => $products_id,
                    'languages' => $languages,
                    'languages_id' => $languages_id,
        ]);
    }

    public function actionFileGroups() {
        \common\helpers\Translation::init('admin/categories');
        global $languages_id;
        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();


        $types = array();
        $types_list = array();
        $documents_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " order by document_types_name");
        while ($documents = tep_db_fetch_array($documents_query)) {
            $types[$documents['language_id']][$documents['document_types_id']] = $documents;
            $types_list[$documents['document_types_id']] = $documents['document_types_id'];
        }

        return $this->render('file-groups', [
                    'languages' => $languages,
                    'languages_id' => $languages_id,
                    'types' => $types,
                    'types_list' => $types_list,
        ]);
    }

    public function actionFileGroupsSave() {
        global $languages_id;
        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();

        $types = Yii::$app->request->post('type');

        foreach ($languages as $language) {
            foreach ($types[$language['id']] as $id => $type) {

                $types_icon = '';
                if ($type['document_types_icon']) {
                    $icon = tep_db_fetch_array(tep_db_query("select document_types_icon from " . TABLE_DOCUMENT_TYPES . " where document_types_id = '" . (int) $id . "' and language_id = '" . $language['id'] . "'"));
                    if ($icon['document_types_icon'] == $type['document_types_icon']) {
                        $types_icon = $type['document_types_icon'];
                    } else {

                        $path = \Yii::getAlias('@webroot') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
                        if (!is_file($path . $type['document_types_icon'])) {
                            $types_icon = Uploads::move($type['document_types_icon']);
                        } else {
                            $types_icon = $type['document_types_icon'];
                        }
                    }
                }

                $document_types = tep_db_query("select document_types_icon from " . TABLE_DOCUMENT_TYPES . " where document_types_id = '" . (int) $id . "' and language_id = '" . $language['id'] . "'");
                if (tep_db_num_rows($document_types) > 0) {
                    $sql_data_array = array(
                        'document_types_name' => $type['document_types_name'],
                        'document_types_icon' => $types_icon,
                    );
                    tep_db_perform(TABLE_DOCUMENT_TYPES, $sql_data_array, 'update', "document_types_id = '" . (int) $id . "' and language_id = '" . $language['id'] . "'");
                } else {
                    $sql_data_array = array(
                        'document_types_id' => $id,
                        'language_id' => $language['id'],
                        'document_types_name' => $type['document_types_name'],
                        'document_types_icon' => $types_icon,
                    );
                    tep_db_perform(TABLE_DOCUMENT_TYPES, $sql_data_array);
                }
            }
        }

        $types = array();
        $types_list = array();
        $documents_query = tep_db_query("select * from " . TABLE_DOCUMENT_TYPES . " order by document_types_name");
        while ($documents = tep_db_fetch_array($documents_query)) {
            $types[$documents['language_id']][$documents['document_types_id']] = $documents;
            $types_list[$documents['document_types_id']] = $documents['document_types_id'];
        }

        return '';
    }

    public function actionFileGroupsAdd() {
        global $languages_id;
        $this->layout = false;
        $languages = \common\helpers\Language::get_languages();

        $documents = tep_db_fetch_array(tep_db_query("select max(document_types_id) as id from " . TABLE_DOCUMENT_TYPES . " "));

        $sql_data_array = array(
            'document_types_id' => $documents['id'] + 1,
            'language_id' => $languages_id,
            'document_types_name' => '',
            'document_types_icon' => '',
        );
        tep_db_perform(TABLE_DOCUMENT_TYPES, $sql_data_array);

        $types = array();
        foreach ($languages as $language) {
            $types[] = array(
                'language_id' => $language['id'],
                'content' => $this->render('file-groups-add', [
                    'language_id' => $language['id'],
                    'type_id' => $documents['id'] + 1,
                ])
            );
        }

        return json_encode($types);
    }

    public function actionFileGroupsRemove() {
        $this->layout = false;

        $document_types_id = Yii::$app->request->get('document_types_id');

        $query = tep_db_query("select products_documents_id from " . TABLE_PRODUCTS_DOCUMENTS . " where document_types_id  = '" . (int) $document_types_id . "'");
        while ($item = tep_db_fetch_array($query)) {
            tep_db_query("delete from " . TABLE_PRODUCTS_DOCUMENTS_TITLES . " where products_documents_id  = '" . (int) $item['products_documents_id'] . "'");
        }
        tep_db_query("delete from " . TABLE_DOCUMENT_TYPES . " where document_types_id = '" . (int) $document_types_id . "'");


        return json_encode('ok');
    }

    public function actionFileManagerRename() {
        $this->layout = false;

        $name = Yii::$app->request->get('name');
        $new_name = Yii::$app->request->get('new_name');

        $sql_data_array = array(
            'filename' => $new_name,
        );
        tep_db_perform(TABLE_PRODUCTS_DOCUMENTS, $sql_data_array, 'update', "filename='" . $name . "'");

        $fsPath = DIR_FS_CATALOG . 'documents/';

        if (is_file($fsPath . '/' . $name)) {
            rename($fsPath . '/' . $name, $fsPath . '/' . $new_name);
        }

        return $new_name;
    }

    public function actionStockHistory() {
        \common\helpers\Translation::init('admin/categories');
        $prid = Yii::$app->request->get('prid');
        if (strpos($prid, '{') !== false) {
            $stock_history_query = tep_db_query("select * from " . TABLE_STOCK_HISTORY . " where products_id = '" . tep_db_input($prid) . "' order by stock_history_id desc");
        } else {
            $stock_history_query = tep_db_query("select * from " . TABLE_STOCK_HISTORY . " where prid = '" . (int) $prid . "' order by stock_history_id desc");
        }
        $history = [];
        while ($stock_history = tep_db_fetch_array($stock_history_query)) {
            $admin = '';
            if ($stock_history['admin_id'] > 0) {
                $check_admin_query = tep_db_query("select * from " . TABLE_ADMIN . " where admin_id = '" . (int) $stock_history['admin_id'] . "'");
                $check_admin = tep_db_fetch_array($check_admin_query);
                if (is_array($check_admin)) {
                    $admin = $check_admin['admin_firstname'] . ' ' . $check_admin['admin_lastname'];
                }
            }
            $history[] = [
                'id' => $stock_history['stock_history_id'],
                'date' => \common\helpers\Date::datetime_short($stock_history['date_added']),
                'model' => $stock_history['products_model'],
                'stock_before' => $stock_history['products_quantity_before'],
                'stock_update' => $stock_history['products_quantity_update_prefix'] . $stock_history['products_quantity_update'],
                'stock_after' => ($stock_history['products_quantity_update_prefix'] == '-' ?
                        $stock_history['products_quantity_before'] - $stock_history['products_quantity_update'] :
                        $stock_history['products_quantity_before'] + $stock_history['products_quantity_update'] ),
                'order' => ($stock_history['orders_id'] > 0 ? '<a target="_blank" href="' . \yii\helpers\Url::to([FILENAME_ORDERS . '/process-order', 'orders_id' => $stock_history['orders_id']]) . '">' . $stock_history['orders_id'] . '</a>' : ''),
                'comments' => $stock_history['comments'],
                'admin' => $admin,
            ];
        }
        return $this->renderAjax('stock-history', ['history' => $history]);
    }

    public function actionProductQuantityUpdate() {
        \common\helpers\Translation::init('admin/categories');
        if (strpos($_POST['uprid'], '{') !== false && \common\helpers\Inventory::get_prid($_POST['uprid']) > 0) {
            $inventory_quantity_update = (int) $_POST['inventoryqtyupdate_' . $_POST['uprid']];
            $inventory_quantity_update_prefix = ($_POST['inventoryqtyupdateprefix_' . $_POST['uprid']] == '-' ? '-' : '+');
            if ($inventory_quantity_update > 0) {
                $check_data = tep_db_fetch_array(tep_db_query("select products_quantity from " . TABLE_INVENTORY . " where products_id = '" . tep_db_input($_POST['uprid']) . "'"));
                if (!$check_data) {
                    tep_db_query("insert into " . TABLE_INVENTORY . " set inventory_id = '', products_id = '" . tep_db_input($_POST['uprid']) . "', prid = '" . (int) \common\helpers\Inventory::get_prid($_POST['uprid']) . "'");
                    $check_data['products_quantity'] = 0;
                }

                global $login_id;
                \common\helpers\Product::log_stock_history_before_update($_POST['uprid'], $inventory_quantity_update, $inventory_quantity_update_prefix, ['comments' => TEXT_MANUALL_STOCK_UPDATE, 'admin_id' => $login_id]);
                tep_db_query("update " . TABLE_INVENTORY . " set products_quantity = products_quantity " . $inventory_quantity_update_prefix . $inventory_quantity_update . " where products_id = '" . tep_db_input($_POST['uprid']) . "'");

                if ($inventory_quantity_update_prefix == '-') {
                    $check_data['products_quantity'] -= $inventory_quantity_update;
                } else {
                    $check_data['products_quantity'] += $inventory_quantity_update;
                }
                $check_data['allocated_quantity'] = \common\helpers\Product::get_allocated_stock_quantity($_POST['uprid']);
                $check_data['warehouse_quantity'] = $check_data['products_quantity'] + $check_data['allocated_quantity'];

                echo json_encode($check_data);
            }
        } elseif ($_POST['uprid'] > 0) {
            $products_quantity_update = (int) $_POST['products_quantity_update'];
            $products_quantity_update_prefix = ($_POST['products_quantity_update_prefix'] == '-' ? '-' : '+');
            if ($products_quantity_update > 0) {
                $check_data = tep_db_fetch_array(tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . (int) $_POST['uprid'] . "'"));

                global $login_id;
                \common\helpers\Product::log_stock_history_before_update($_POST['uprid'], $products_quantity_update, $products_quantity_update_prefix, ['comments' => TEXT_MANUALL_STOCK_UPDATE, 'admin_id' => $login_id]);
                tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = products_quantity " . $products_quantity_update_prefix . $products_quantity_update . " where products_id = '" . (int) $_POST['uprid'] . "'");

                if ($products_quantity_update_prefix == '-') {
                    $check_data['products_quantity'] -= $products_quantity_update;
                } else {
                    $check_data['products_quantity'] += $products_quantity_update;
                }
                $check_data['allocated_quantity'] = \common\helpers\Product::get_allocated_stock_quantity($_POST['uprid']);
                $check_data['warehouse_quantity'] = $check_data['products_quantity'] + $check_data['allocated_quantity'];

                echo json_encode($check_data);
            }
        }
    }

}
