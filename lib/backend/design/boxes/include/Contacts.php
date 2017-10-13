<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip') {
  $widgets[] = array('name' => 'Contacts', 'title' => 'Contacts', 'description' => '', 'type' => 'general', 'class' => 'contacts');
}