<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$submenu = array();

$extState = (false === \common\helpers\Acl::checkExtension('SMS', 'allowed'));
$group_id = 0;
if (!$extState){
    \common\extensions\SMS\SMS::checkSMSConfiguration();
    $group_id = \common\extensions\SMS\SMS::getConfigurationGroupID();
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_SMS', 'BOX_SMS_CONFIGURATION']) && $group_id) {
    $submenu[] = array('configuration?groupid='.$group_id, '', BOX_SMS_CONFIGURATION, false, $extState);
}
if (\common\helpers\Acl::rule(['TEXT_SETTINGS', 'BOX_HEADING_SMS', 'BOX_SMS_MESSAGES'])) {
    $submenu[] = array('sms_messages', '', BOX_SMS_MESSAGES, false, $extState);
}
