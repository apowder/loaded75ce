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

  if ( isset($HTTP_GET_VARS['payment_error']) && tep_not_null($HTTP_GET_VARS['payment_error']) ) {
    $redirect_url = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $HTTP_GET_VARS['payment_error'] . (isset($HTTP_GET_VARS['error']) && tep_not_null($HTTP_GET_VARS['error']) ? '&error=' . $HTTP_GET_VARS['error'] : ''), 'SSL');
  } else {
    $hidden_params = '';

    if ($payment == 'sage_pay_direct') {
      $redirect_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, 'check=3D', 'SSL');
      $hidden_params = tep_draw_hidden_field('MD', $HTTP_POST_VARS['MD']) . tep_draw_hidden_field('PaRes', $HTTP_POST_VARS['PaRes']);
    } else {
      $redirect_url = tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL');
    }
  }

  //require(DIR_WS_LANGUAGES . $language . '/' . 'checkout-confirmation.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<form name="redirect" action="<?php echo $redirect_url; ?>" method="post" target="_top"><?php echo $hidden_params; ?>
<noscript>
  <p align="center" class="main">The transaction is being finalized. Please click continue to finalize your order.</p>
  <p align="center" class="main"><input type="submit" value="Continue" /></p>
</noscript>
</form>
<script language="javascript">
document.redirect.submit();
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
