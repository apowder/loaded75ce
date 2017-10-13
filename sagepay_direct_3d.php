<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if (!tep_session_is_registered('sage_pay_direct_acsurl')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  if (!tep_session_is_registered('payment') || ($payment != 'sage_pay_direct')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_CONFIRMATION);
  require(DIR_WS_LANGUAGES . $language . '/modules/payment/sage_pay_direct.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<FORM name="form" action="<?php echo $sage_pay_direct_acsurl; ?>" method="POST">
<input type="hidden" name="PaReq" value="<?php echo $sage_pay_direct_pareq; ?>" />
<input type="hidden" name="TermUrl" value="<?php echo tep_href_link('redirect_sage_pay.php', '', 'SSL'); ?>" />
<input type="hidden" name="MD" value="<?php echo $sage_pay_direct_md; ?>" />
<NOSCRIPT>
<?php echo '<center><p>' . MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_INFO . '</p><p><input type="submit" value="' . MODULE_PAYMENT_SAGE_PAY_DIRECT_3DAUTH_BUTTON . '"/></p></center>'; ?>
</NOSCRIPT>
<script language="javascript"><!--
document.form.submit();
//--></script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
