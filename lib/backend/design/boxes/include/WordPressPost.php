<?php

if ($type != 'email' && $type != 'invoice' && $type != 'packingslip') {
	$widgets[] = array('name' => 'WordPressPost', 'title' => 'WordPress Post', 'description' => '', 'type' => 'general', 'class' => '');
}