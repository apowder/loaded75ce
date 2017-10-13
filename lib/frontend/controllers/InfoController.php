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

/**
 * Site controller
 */
class InfoController extends Sceleton
{

    public function actionIndex()
    {
        global $HTTP_SESSION_VARS, $languages_id, $breadcrumb;


      if(!$_GET['info_id'])
        die("No page found.");
      $info_id = (int)$_GET['info_id'];

      $sql = tep_db_query("select if(length(i1.info_title), i1.info_title, i.info_title) as info_title, if(length(i1.description), i1.description, i.description) as description, i.information_id from " . TABLE_INFORMATION . " i LEFT JOIN " . TABLE_INFORMATION . " i1 on i.information_id = i1.information_id  and i1.languages_id = '" . (int)$languages_id . "' ".(\common\classes\platform::activeId()?" AND i1.platform_id='".\common\classes\platform::currentId()."' ":'')." and i1.affiliate_id = '" . (int)$HTTP_SESSION_VARS['affiliate_ref'] . "'  where i.information_id = '" . (int)$info_id . "' and i.languages_id = '" . (int)$languages_id . "' and i.visible = 1 ".(\common\classes\platform::activeId()?" AND i.platform_id='".\common\classes\platform::currentId()."' ":'')." and i.affiliate_id = 0");
      $row=tep_db_fetch_array($sql);

      if ($row['page_title'] == ''){
        $title = $row['info_title'];
      }else{
        $title = $row['page_title'];
      }
      if ( $title ) {
        $breadcrumb->add($title, tep_href_link(FILENAME_INFORMATION, 'info_id=' . $row['information_id']));
      }
        $params = tep_db_prepare_input(Yii::$app->request->get());
        if ($params['page_name']){
            $page_name = $params['page_name'];
        } else {
            $page_name = 'info';
        }
      
      return $this->render('index.tpl', [
        'description' => $row['description'],
        'title' => $title,
        'page' => 'info',
        'page_name' => $page_name
      ]);
    }

    public function actions()
    {        
        
        return [
                'custom' => [
                    'class' => '\frontend\controllers\CustomPageAction',
                    'page' => $_GET['page'],
                ],
        ];
    }
}
