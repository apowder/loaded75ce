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

if (SEARCH_ENGINE_UNHIDE == 'True' && \common\helpers\Acl::rule(['BOX_HEADING_SEO_CMS', 'BOX_META_TAGS'])) {
    $submenu[] = array('meta_tags', '', BOX_META_TAGS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_SEO_CMS', 'BOX_MARKETING_SITEMAP'])) {
    $submenu[] = array('sitemap', '', BOX_MARKETING_SITEMAP);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_SEO_CMS', 'BOX_MARKETING_GOOGLE_BASE'])) {
    $submenu[] = array('google_base', '', BOX_MARKETING_GOOGLE_BASE);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_SEO_CMS', 'BOX_HEADING_GOOGLE_ANALYTICS'])) {
    $submenu[] = array('google_analytics', '', BOX_HEADING_GOOGLE_ANALYTICS);
}
if (\common\helpers\Acl::rule(['BOX_HEADING_SEO_CMS', 'BOX_HEADING_REDIRECTS'])) {
    $extState = (false === \common\helpers\Acl::checkExtension('SeoRedirects', 'allowed'));
    $submenu[] = array('seo_redirects', '', BOX_HEADING_REDIRECTS, false, $extState);
}