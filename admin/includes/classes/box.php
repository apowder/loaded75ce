<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  class box extends tableBlock {
    function __construct() {
      $this->heading = array();
      $this->contents = array();
    }

    function infoBox($heading, $contents) {
      $this->heading='';
      $this->contents='';
        $this->table_parameters= '';
        $this->table_data_parameters = 'class="BoxContent1"';
        $heading[0]['text'] = '&nbsp;' . $heading[0]['text'] . '&nbsp;';
        $this->heading = $this->tableBlockInfo_heading($heading);
        $this->table_data_parameters = 'class="boxlisting"';
        $this->contents = parent::__construct($contents);
        return $this->heading . $this->contents . $dhtml_contents;
    }

    function menuBox($heading, $contents) {

    global $menu_dhtml;              // add for dhtml_menu
    if ($menu_dhtml == false ) {     // add for dhtml_menu
      $this->heading='';
      $this->contents='';
      if($heading!="")
      {
        $this->table_parameters= '';
        $this->table_data_parameters = 'class="BoxContent"';
        if ($heading[0]['link']) {
          $this->table_data_parameters .= ' onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . $heading[0]['link'] . '\'"';
          $heading[0]['text'] = '&nbsp;<a href="' . $heading[0]['link'] . '" class="menuBoxHeadingLink">' . $heading[0]['text'] . '</a>&nbsp;';
        } else {
          $heading[0]['text'] = '&nbsp;' . $heading[0]['text'] . '&nbsp;';
        }
        $this->heading = $this->tableBlock_heading($heading);
      }
      if($contents!="")
      {
//        $this->table_parameters= ' background="images/infobox/header_bg.gif" ';
        $this->table_data_parameters = 'class="boxlisting"';
        $this->contents = parent::__construct($contents);
      }
      return $this->heading . $this->contents . $dhtml_contents;
// ## add for dhtml_menu
    } else {
      $selected = substr(strrchr ($heading[0]['link'], '='), 1);
      $dhtml_contents = $contents[0]['text'];
      $change_style = array ('<br>'=>' ','<BR>'=>' ', 'a href='=> 'a class="menuItem" href=','class="menuBoxContentLink"'=>' ');
      $dhtml_contents = strtr($dhtml_contents,$change_style);
      $dhtml_contents = '<div id="'.$selected.'Menu" class="menu" onmouseover="menuMouseover(event)">'. $dhtml_contents . '</div>';
      return $dhtml_contents;
      }
// ## eof add for dhtml_menu
    }
    function menuBoxIndex($heading, $contents) {

    global $menu_dhtml;              // add for dhtml_menu
    if ($menu_dhtml == false ) {     // add for dhtml_menu
      $this->heading='';
      $this->contents='';
      $this->table_parameters= ' background="images/infobox/header_bg.gif" ';
      $this->table_data_parameters = 'class="menuBoxHeading"';
      if ($heading[0]['link']) {
        $this->table_data_parameters .= ' onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . $heading[0]['link'] . '\'"';
        $heading[0]['text'] = '&nbsp;<a href="' . $heading[0]['link'] . '" class="menuBoxHeadingLink">' . $heading[0]['text'] . '</a>&nbsp;';
      } else {
        $heading[0]['text'] = '&nbsp;' . $heading[0]['text'] . '&nbsp;';
      }
      $this->heading = $this->tableBlockIndex_heading($heading);
      if($contents!="")
      {
      $this->table_parameters= '';
      $this->table_data_parameters = 'class="BoxContent"';
      $this->contents = $this->tableBlockIndex($contents);
      }
      return $this->heading . $this->contents . $dhtml_contents;
// ## add for dhtml_menu
    } else {
      $selected = substr(strrchr ($heading[0]['link'], '='), 1);
      $dhtml_contents = $contents[0]['text'];
      $change_style = array ('<br>'=>' ','<BR>'=>' ', 'a href='=> 'a class="menuItem" href=','class="menuBoxContentLink"'=>' ');
      $dhtml_contents = strtr($dhtml_contents,$change_style);
      $dhtml_contents = '<div id="'.$selected.'Menu" class="menu" onmouseover="menuMouseover(event)">'. $dhtml_contents . '</div>';
      return $dhtml_contents;
      }
// ## eof add for dhtml_menu
    }
  }
