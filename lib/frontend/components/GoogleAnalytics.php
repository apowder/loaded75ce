<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace frontend\components;

use yii\base\Widget;
use yii\helpers\Html;

class GoogleAnalytics extends Widget
{

  public function init()
  {
    parent::init();
  }

  public function run()
  {
      if (defined('GOOGLE_ANALYTICS_ACCOUNT') && GOOGLE_ANALYTICS_ACCOUNT!='') {
        return "<script>
 (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
 (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
 m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
 })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
ga('create', '" . GOOGLE_ANALYTICS_ACCOUNT . "', 'auto');
ga('send', 'pageview');
</script>";
      }
      return '';
  }
}


