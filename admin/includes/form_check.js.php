<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
?>

<script language="javascript"><!--

var submitted = false;

function check_form() {
  var error = 0;
  var error_message = "<?php echo str_replace(array("\r", "\n", "\r\n"), "", JS_ERROR); ?>";

  if(submitted){ 
    alert( "<?php echo JS_ERROR_SUBMITTED; ?>"); 
    return false; 
  }
   
<?php
   if (ACCOUNT_FIRSTNAME == 'required' || ACCOUNT_FIRSTNAME == 'required_register') echo '  var first_name = document.account_edit.firstname.value;' . "\n";
   if (ACCOUNT_LASTNAME == 'required' || ACCOUNT_LASTNAME == 'required_register') echo '  var last_name = document.account_edit.lastname.value;' . "\n";
   if (ACCOUNT_DOB == 'required' || ACCOUNT_DOB == 'required_register') echo '  var dob = document.account_edit.dob.value;' . "\n";
?>
  var email_address = document.account_edit.email_address.value;  
  var street_address = document.account_edit.street_address.value;
  var postcode = document.account_edit.postcode.value;
  var city = document.account_edit.city.value;
  var telephone = document.account_edit.telephone.value;
  var password = document.account_edit.password.value;
  var confirmation = document.account_edit.confirmation.value;

<?php
   if (ACCOUNT_GENDER == 'required' || ACCOUNT_GENDER == 'required_register') {
?>
  if (document.account_edit.elements['gender'].type != "hidden") {
    if (document.account_edit.gender[0].checked || document.account_edit.gender[1].checked) {
    } else {
      error_message = error_message + "<?php echo JS_GENDER; ?>";
      error = 1;
    }
  }
<?php
  }
?>
 
<?php
   if (ACCOUNT_FIRSTNAME == 'required' || ACCOUNT_FIRSTNAME == 'required_register') {
?>
  if (document.account_edit.elements['firstname'].type != "hidden") {
    if (first_name == '' || first_name.length < <?php echo ENTRY_FIRST_NAME_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_FIRST_NAME; ?>";
      error = 1;
    }
  }
<?php
  }
?>

<?php
   if (ACCOUNT_LASTNAME == 'required' || ACCOUNT_LASTNAME == 'required_register') {
?>
  if (document.account_edit.elements['lastname'].type != "hidden") {
    if (last_name == '' || last_name.length < <?php echo ENTRY_LAST_NAME_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_LAST_NAME; ?>";
      error = 1;
    }
  }
<?php
  }
?>

<?php
   if (ACCOUNT_DOB == 'required' || ACCOUNT_DOB == 'required_register') {
?>
  if (document.account_edit.elements['dob'].type != "hidden") {
    if (dob == '' || dob.length < <?php echo ENTRY_DOB_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_DOB; ?>";
      error = 1;
    }
  }
<?php
  }
?>
  if (document.account_edit.elements['email_address'].type != "hidden") {
    if (email_address == '' || email_address.length < <?php echo ENTRY_EMAIL_ADDRESS_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_EMAIL_ADDRESS; ?>";
      error = 1;
    }
  }

  if (document.account_edit.elements['street_address'].type != "hidden") {
    if (street_address == '' || street_address.length < <?php echo ENTRY_STREET_ADDRESS_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_ADDRESS; ?>";
      error = 1;
    }
  }

  if (document.account_edit.elements['postcode'].type != "hidden") {
    if (postcode == '' || postcode.length < <?php echo ENTRY_POSTCODE_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_POST_CODE; ?>";
      error = 1;
    }
  }

  if (document.account_edit.elements['city'].type != "hidden") {
    if (city == '' || city.length < <?php echo ENTRY_CITY_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_CITY; ?>";
      error = 1;
    }
  }

<?php
  if (ACCOUNT_STATE == 'required' || ACCOUNT_DOB == 'required_register') {
?>
  if (document.account_edit.elements['state'].type != "hidden") {
    if (document.account_edit.state.value == '' || document.account_edit.state.value.length < <?php echo ENTRY_STATE_MIN_LENGTH; ?> ) {
       error_message = error_message + "<?php echo JS_STATE; ?>";
       error = 1;
    }
  }
<?php
  }
?>

  if (document.account_edit.elements['country'].type != "hidden") {
    if (document.account_edit.country.value == 0) {
      error_message = error_message + "<?php echo JS_COUNTRY; ?>";
      error = 1;
    }
  }

  if (document.account_edit.elements['telephone'].type != "hidden") {
    if (telephone == '' || telephone.length < <?php echo ENTRY_TELEPHONE_MIN_LENGTH; ?>) {
      error_message = error_message + "<?php echo JS_TELEPHONE; ?>";
      error = 1;
    }
  }

  if (document.account_edit.elements['password'].type != "hidden") {
    if ((password != confirmation) || (password == '' || password.length < <?php echo ENTRY_PASSWORD_MIN_LENGTH; ?>)) {
      error_message = error_message + "<?php echo JS_PASSWORD; ?>";
      error = 1;
    }
  }

  if (error == 1) { 
    alert(error_message); 
    return false; 
  } else { 
    submitted = true; 
    return true; 
  } 
}
//--></script>