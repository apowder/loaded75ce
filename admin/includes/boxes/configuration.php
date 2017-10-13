<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

global $languages_id;
$submenu = array();

$configuration_groups_query = tep_db_query( "select configuration_group_id as cgID, configuration_group_title as cgTitle from " . TABLE_CONFIGURATION_GROUP . " where visible = '1' order by sort_order" );
while( $configuration_groups = tep_db_fetch_array( $configuration_groups_query ) ) {

    $groupid = $configuration_groups['cgID'];
    $link    =  "configuration/index?groupid=$groupid";

    $title = \common\helpers\Translation::getTranslationValue('GROUP_' . $configuration_groups['cgID'] . '_TITLE', 'configuration', $languages_id);
    if (!tep_not_null($title)){
      $title = $configuration_groups['cgTitle'];
    }

    $submenu[] = array(
        $link,
        '',
        $title
    );
}
  