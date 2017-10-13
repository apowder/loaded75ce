<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\design;

use Yii;
use \yii\base\Widget;

class PDFBox extends \TCPDF {

  public $sizes;
  public $ds;

  public function BlockSizes($name, $page_params, $width, $pdf_params) {
    $ds = $pdf_params['dimension_scale'];

    $items_query = tep_db_query("select id, widget_name, widget_params from " . TABLE_DESIGN_BOXES . " where block_name = '" . $name . "' and theme_name = '" . $page_params['theme_name'] . "' order by sort_order");

    while ($item = tep_db_fetch_array($items_query)){

      $this->sizes[$item['id']]['width'] = $width;
      
      $settings = array();
      $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "' and (language_id = '0' or language_id = '" . $page_params['language_id'] . "') and 	visibility = '0'");
      while ($set = tep_db_fetch_array($settings_query)) {
        $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
      }
      $settings[0]['pdf'] = 1;

      $width2 = $width - $settings[0]['padding_left'] * $ds -
                        $settings[0]['padding_right'] * $ds -
                        $settings[0]['border_left_width'] * $ds -
                        $settings[0]['border_right_width'] * $ds;

      if ($item['widget_name'] == 'BlockBox' || $item['widget_name'] == 'email\BlockBox'){

        $w = $this->widthByType($settings[0]['block_type'], $width2);
        if ($w['1']) $this->BlockSizes('block-' . $item['id'], $page_params, $w['1'], $pdf_params);
        if ($w['2']) $this->BlockSizes('block-' . $item['id'] . '-2', $page_params, $w['2'], $pdf_params);
        if ($w['3']) $this->BlockSizes('block-' . $item['id'] . '-3', $page_params, $w['3'], $pdf_params);
        if ($w['4']) $this->BlockSizes('block-' . $item['id'] . '-4', $page_params, $w['4'], $pdf_params);
        if ($w['5']) $this->BlockSizes('block-' . $item['id'] . '-5', $page_params, $w['5'], $pdf_params);

      } elseif($item['widget_name'] == 'invoice\Container'){

        $this->BlockSizes('block-' . $item['id'], $page_params, $width2, $pdf_params);

      } elseif ($item['widget_name'] == 'Tabs'){

        for($i = 1; $i < 11; $i++) {
          $this->BlockSizes('block-' . $item['id'] . '-' . $i, $page_params, $width2, $pdf_params);
        }

      } else {


        $widget_array['settings'] = $settings;
        $widget_array['params'] = $page_params;

        $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];
        $widget = $widget_name::widget($widget_array);

        $widget = preg_replace('/[ ]+/', ' ', $widget);
        $widget = str_replace('<br> ', '<br>', $widget);


        $pdf2 = clone $this;
        $pdf2->AddPage();
        $pdf2->Set_FontSize($settings[0]['font_size'], $name);
        $pdf2->Set_FontBold($settings[0]['font_weight'], $name);
        $pdf2->writeHTMLCell($width2, 0, 0, 0, $widget, 1, 1);
        $height = $pdf2->GetY() + $settings[0]['padding_top'] * $ds +
                                  $settings[0]['padding_bottom'] * $ds +
                                  $settings[0]['border_top_width'] * $ds +
                                  $settings[0]['border_bottom_width'] * $ds;
        $pdf2->deletePage($pdf2->getPage());

        $this->sizes[$item['id']]['height'] = $height;

        /*if ($item['widget_name'] == 'Text'){
          echo '<pre>';
          var_dump($settings);
          echo '</pre>';
        }*/

        $this->setTopHeight($height, $name);
      }
    }

  }

  public function widthByType($block_type, $width){
    $w['1'] = $w['2'] = $w['3'] = $w['4'] = $w['5'] = 0;

    switch ($block_type){
      case '1':  $w['1'] = $width;                                 break;
      case '2':  $w['1'] = $w['2'] = $width/2;                     break;
      case '3':  $w['1'] = $w['2'] = $w['3'] = round($width/3, 4); break;
      case '4':  $w['1'] = round(($width/3)*2, 4);
                 $w['2'] = round($width/3, 4);                     break;
      case '5':  $w['1'] = round($width/3, 4);
                 $w['2'] = round(($width/3)*2, 4);                 break;
      case '6':  $w['1'] = $width/4;
                 $w['2'] = ($width/4)*3;                           break;
      case '7':  $w['1'] = ($width/4)*3;
                 $w['2'] = $width/4;                               break;
      case '8':  $w['1'] = $w['3'] = $width/4;
                 $w['2'] = $width/2;                               break;
      case '9':  $w['1'] = $width/5;
                 $w['2'] = ($width/5)*4;                           break;
      case '10': $w['1'] = ($width/5)*4;
                 $w['2'] = $width/5;                               break;
      case '11': $w['1'] = ($width/5)*2;
                 $w['2'] = ($width/5)*3;                           break;
      case '12': $w['1'] = ($width/5)*3;
                 $w['2'] = ($width/5)*2;                           break;
      case '13': $w['1'] = $w['3'] = $width/5;
                 $w['2'] = ($width/5)*3;                           break;
      case '14': $w['1'] = $w['2'] = $w['3'] = $w['4'] = $width/4; break;
      case '15': $w['1'] =  $w['2'] = $w['3'] = $w['4'] = $w['5'] = $width/5; break;
    }
    return $w;
  }

  public function setTopHeight($height, $name, $n=0){

    if ((!$n && substr($name, 0, 6) == 'block-') || ($n && strpos($name, $n . '1block') === 0)){

      $e = explode('-', $name);
      $id = $e[1];
      if ($this->sizes[$name]['height']) {
        $this->sizes[$name]['height'] += $height;
      } else {
        $padding_top = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='padding_top' and visibility = '0'"));
        $padding_bottom = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='padding_bottom' and visibility = '0'"));
        $border_top_width = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='border_top_width' and visibility = '0'"));
        $border_bottom_width = tep_db_fetch_array(tep_db_query("
            select setting_value 
            from " . TABLE_DESIGN_BOXES_SETTINGS . " 
            where box_id = '" . $id . "' and setting_name='border_bottom_width' and visibility = '0'"));

        $p = $padding_top['setting_value'] +
          $padding_bottom['setting_value'] +
          $border_top_width['setting_value'] +
          $border_bottom_width['setting_value'];
        $p = $this->ds * $p;

        $height += $p;
        $this->sizes[$name]['height'] = $height;
      }


      //$items_query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
      //$this->setTopHeight($height, $items_query['block_name']);



      //$items_query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
      //$this->setTopHeight($height, $items_query['block_name']);
    }

  }

  public function setChooseHeight($n=0){
    $continue = false;
    foreach ($this->sizes as $key => $item) {
      if ((!$n && substr($key, 0, 6) == 'block-') || ($n && strpos($key, $n . '1block') === 0)){
        $e = explode('-', $key);
        $id = $e[1];

        if (!$this->sizes[$id]['height'] || $item['height'] > $this->sizes[$id]['height']) {
          $this->sizes[$id]['height'] = $item['height'];

            $this->sizes[($n+1) . '2block-' . $id]['height'] = $item['height'];


            $continue = true;
        }
      }
    }
    if ($continue){
      $this->setPlusHeight($n+1);
    }
  }
  public function setPlusHeight($n=1){
    $continue = false;
    foreach ($this->sizes as $key => $item) {
      if (strpos($key, $n . '2block') === 0){
        $e = explode('-', $key);
        $id = $e[1];

        $items_query = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));

        if (substr($items_query['block_name'], 0, 6) == 'block-') {
          $e2 = explode('-', $items_query['block_name']);
          $id2 = $e2[1];
          $name = ($n + 1) . '1block-' . $id2 . ($e2[2] ? '-' . $e2[2] : '');
          $this->setTopHeight($item['height'], $name, $n + 1);
          $continue = true;
        }
      }
    }
    if ($continue){
      $n++;
      $this->setChooseHeight($n);
    }
  }

  public function setAllHeight_Bak(){
    foreach ($this->sizes as $key => $item) {
      if (substr($key, 0, 6) == 'block-'){
        $e = explode('-', $key);
        $id = $e[1];

        if (!$this->sizes[$id]['height'] || $item['height'] > $this->sizes[$id]['height']) {
          $this->sizes[$id]['height'] = $item['height'];
        }
      }
    }
  }

  public function Set_FontSize($setting, $name){
    $style = 'font_size';
    if ($setting){
      $this->setFontSize($setting * 0.8);
    } else {
      if (substr($name, 0, 6) == 'block-'){
        $e = explode('-', $name);
        $id = $e[1];
        $items_query = tep_db_fetch_array(tep_db_query("select b.block_name, bs.setting_value from " . TABLE_DESIGN_BOXES . " b, " . TABLE_DESIGN_BOXES_SETTINGS . " bs where b.id = '" . $id . "' and b.id = bs.box_id and bs.setting_name='" . $style . "' and bs.visibility = '0'"));
        if ($items_query['setting_value']) {
          $this->Set_FontSize($items_query['setting_value'], $items_query['block_name']);
        } else {
          $block = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
          $this->Set_FontSize(0, $block['block_name']);
        }
      }
    }
  }

  public function Set_FontBold($setting, $name){
    $style = 'font_weight';
    if ($setting == 'bold'){
      $this->SetFont('', 'B');
    } elseif ($setting == 'normal'){
      $this->SetFont('', '');
    } else {
      $this->SetFont('', '');
      if (substr($name, 0, 6) == 'block-'){
        $e = explode('-', $name);
        $id = $e[1];
        $items_query = tep_db_fetch_array(tep_db_query("select b.block_name, bs.setting_value from " . TABLE_DESIGN_BOXES . " b, " . TABLE_DESIGN_BOXES_SETTINGS . " bs where b.id = '" . $id . "' and b.id = bs.box_id and bs.setting_name='" . $style . "' and bs.visibility = '0'"));
        if ($items_query['setting_value']) {
          $this->Set_FontBold($items_query['setting_value'], $items_query['block_name']);
        } else {
          $block = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
          $this->Set_FontBold(0, $block['block_name']);
        }
      }
    }
  }

  public function Set_FontColor($setting, $name){
    $style = 'color';
    if ($setting){
      list($r, $g, $b) = sscanf($setting, "#%02x%02x%02x");
      $this->SetTextColor($r, $g, $b);
    } else {
      if (substr($name, 0, 6) == 'block-'){
        $e = explode('-', $name);
        $id = $e[1];
        $items_query = tep_db_fetch_array(tep_db_query("select b.block_name, bs.setting_value from " . TABLE_DESIGN_BOXES . " b, " . TABLE_DESIGN_BOXES_SETTINGS . " bs where b.id = '" . $id . "' and b.id = bs.box_id and bs.setting_name='" . $style . "' and bs.visibility = '0'"));
        if ($items_query['setting_value']) {
          $this->Set_FontColor($items_query['setting_value'], $items_query['block_name']);
        } else {
          $block = tep_db_fetch_array(tep_db_query("select block_name from " . TABLE_DESIGN_BOXES . " where id = '" . $id . "'"));
          $this->Set_FontColor(0, $block['block_name']);
        }
      }
    }
  }

  public function BlockCreate($name, $page_params, $position, $pdf_params) {

    $ds = $pdf_params['dimension_scale'];

    $items_query = tep_db_query("select id, widget_name, widget_params from " . TABLE_DESIGN_BOXES . " where block_name = '" . $name . "' and theme_name = '" . $page_params['theme_name'] . "' order by sort_order");

    while ($item = tep_db_fetch_array($items_query)) {

      $width = $this->sizes[$item['id']]['width'];
      $height = $this->sizes[$item['id']]['height'];
      if ($position['top'] + $height > $pdf_params['height'] - $pdf_params['pdf_margin_bottom'] * $ds){
        $position['top'] = $pdf_params['pdf_margin_top'] * $ds;
        $this->AddPage();
      }

      $settings = array();
      $settings_query = tep_db_query("select * from " . TABLE_DESIGN_BOXES_SETTINGS . " where box_id = '" . (int)$item['id'] . "' and (language_id = '0' or language_id = '" . $page_params['language_id'] . "') and 	visibility = '0'");
      while ($set = tep_db_fetch_array($settings_query)) {
        $settings[$set['language_id']][$set['setting_name']] = $set['setting_value'];
      }
      $settings[0]['pdf'] = 1;


      if ($settings[0]['background_color']){
        list($r, $g, $b) = sscanf($settings[0]['background_color'], "#%02x%02x%02x");
        $this->SetFillColorArray(array($r, $g, $b));
        $this->writeHTMLCell($width, $height, $position['left'], $position['top'], ' ', 0, 1, 1);
      }

      if ($settings[0]['border_top_width']){
        list($r, $g, $b) = sscanf($settings[0]['border_top_color'], "#%02x%02x%02x");
        $this->Line(
          $position['left'] - $ds/10 - $ds/10,
          $position['top'] + ($settings[0]['border_top_width'] * $ds)/2 - $ds/10,
          $position['left'] + $width + $ds/10,
          $position['top'] + ($settings[0]['border_top_width'] * $ds)/2 - $ds/10,
          array('width' => $settings[0]['border_top_width'] * $ds, 'color' => array($r, $g, $b))
        );
      }
      if ($settings[0]['border_left_width']){
        list($r, $g, $b) = sscanf($settings[0]['border_left_color'], "#%02x%02x%02x");
        $this->Line(
          $position['left'] + ($settings[0]['border_left_width'] * $ds)/2 - $ds/10,
          $position['top'] - $ds/10,
          $position['left'] + ($settings[0]['border_left_width'] * $ds)/2 - $ds/10,
          $position['top'] + $height + $ds/10,
          array('width' => $settings[0]['border_left_width'] * $ds, 'color' => array($r, $g, $b))
        );
      }
      if ($settings[0]['border_right_width']){
        list($r, $g, $b) = sscanf($settings[0]['border_right_color'], "#%02x%02x%02x");
        $this->Line(
          $position['left'] + $width - ($settings[0]['border_right_width'] * $ds)/2 + $ds/10,
          $position['top'] - $ds/10,
          $position['left'] + $width - ($settings[0]['border_right_width'] * $ds)/2 + $ds/10,
          $position['top'] + $height + $ds/10,
          array('width' => $settings[0]['border_right_width'] * $ds, 'color' => array($r, $g, $b))
        );
      }
      if ($settings[0]['border_bottom_width']){
        list($r, $g, $b) = sscanf($settings[0]['border_bottom_color'], "#%02x%02x%02x");
        $this->Line(
          $position['left'] - $ds/10,
          $position['top'] + $height - ($settings[0]['border_bottom_width'] * $ds)/2 + $ds/10,
          $position['left'] + $width + $ds/10,
          $position['top'] + $height -($settings[0]['border_bottom_width'] * $ds)/2 + $ds/10,
          array('width' => $settings[0]['border_bottom_width'] * $ds, 'color' => array($r, $g, $b))
        );
      }

      $width = $width - $settings[0]['padding_left'] * $ds - $settings[0]['padding_right'] * $ds -
              $settings[0]['border_left_width'] * $ds - $settings[0]['border_right_width'] * $ds;

      $p['left'] = $p2['left'] = $position['left'] + $settings[0]['padding_left'] * $ds + $settings[0]['border_left_width'] * $ds;
      $p['top'] = $p2['top'] = $position['top'] + $settings[0]['padding_top'] * $ds + $settings[0]['border_top_width'] * $ds;

      if ($item['widget_name'] == 'BlockBox' || $item['widget_name'] == 'email\BlockBox'){

        $w = $this->widthByType($settings[0]['block_type'], $width);
        if ($w['1']) $this->BlockCreate('block-' . $item['id'], $page_params, $p, $pdf_params);
        $p['left'] = $p['left'] + $w['1'];
        if ($w['2']) $this->BlockCreate('block-' . $item['id'] . '-2', $page_params, $p, $pdf_params);
        $p['left'] = $p['left'] + $w['2'];
        if ($w['3']) $this->BlockCreate('block-' . $item['id'] . '-3', $page_params, $p, $pdf_params);
        $p['left'] = $p['left'] + $w['3'];
        if ($w['4']) $this->BlockCreate('block-' . $item['id'] . '-4', $page_params, $p, $pdf_params);
        $p['left'] = $p['left'] + $w['4'];
        if ($w['5']) $this->BlockCreate('block-' . $item['id'] . '-5', $page_params, $p, $pdf_params);

      } elseif($item['widget_name'] == 'invoice\Container'){

        $this->BlockCreate('block-' . $item['id'], $page_params, $p, $pdf_params);

      } elseif ($item['widget_name'] == 'Tabs'){

        for($i = 1; $i < 11; $i++) {

        }

      } else {


        $widget_array['settings'] = $settings;
        $widget_array['params'] = $page_params;

        $widget_name = 'frontend\design\boxes\\' . $item['widget_name'];
        $widget = $widget_name::widget($widget_array);

        $widget = preg_replace('/[ ]+/', ' ', $widget);
        $widget = str_replace('<br> ', '<br>', $widget);




        //$this->Line($this->GetX(), $this->GetY(), $this->GetX() + $width, $this->GetY(),  array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 255)));
        $this->Set_FontSize($settings[0]['font_size'], $name);
        $this->Set_FontBold($settings[0]['font_weight'], $name);
        $this->Set_FontColor($settings[0]['color'], $name);
        $htm = '<div style="';
        if ($settings[0]['text_align']){
          $htm .= 'text-align:' . $settings[0]['text_align'] . ';';
        }
        $htm .= '">' . $widget . '<div>';
        $this->writeHTMLCell($width, $height, $p2['left'], $p2['top'], $htm, 0, 1);


        $this->sizes[$item['id']]['height'] = $height;

        $this->setTopHeight($height, $name);
      }



      $position['top'] = $position['top'] + $height;
    }
  }

  public function Block($name, $page_params, $pdf_params) {
    $ds = $pdf_params['dimension_scale'];
    $this->ds = $ds;

    $width = '210';
    $height = '297';
    if     ($page_params['sheet_format'] == 'A0' && $page_params['sheet_format'] == 'P') {
      $width = '841';
      $height = '1189';
    }
    elseif ($page_params['sheet_format'] == 'A0' && $page_params['sheet_format'] == 'L') {
      $width = '1189';
      $height = '841';
    }
    elseif ($page_params['sheet_format'] == 'A1' && $page_params['sheet_format'] == 'P') {
      $width = '594';
      $height = '841';
    }
    elseif ($page_params['sheet_format'] == 'A1' && $page_params['sheet_format'] == 'L') {
      $width = '841';
      $height = '594';
    }
    elseif ($page_params['sheet_format'] == 'A2' && $page_params['sheet_format'] == 'P') {
      $width = '420';
      $height = '594';
    }
    elseif ($page_params['sheet_format'] == 'A2' && $page_params['sheet_format'] == 'L') {
      $width = '594';
      $height = '420';
    }
    elseif ($page_params['sheet_format'] == 'A3' && $page_params['sheet_format'] == 'P') {
      $width = '297';
      $height = '420';
    }
    elseif ($page_params['sheet_format'] == 'A3' && $page_params['sheet_format'] == 'L') {
      $width = '420';
      $height = '297';
    }
    elseif ($page_params['sheet_format'] == 'A4' && $page_params['sheet_format'] == 'P') {
      $width = '210';
      $height = '297';
    }
    elseif ($page_params['sheet_format'] == 'A4' && $page_params['sheet_format'] == 'L') {
      $width = '297';
      $height = '210';
    }
    elseif ($page_params['sheet_format'] == 'A5' && $page_params['sheet_format'] == 'P') {
      $width = '148';
      $height = '210';
    }
    elseif ($page_params['sheet_format'] == 'A5' && $page_params['sheet_format'] == 'L') {
      $width = '210';
      $height = '148';
    }
    elseif ($page_params['sheet_format'] == 'A6' && $page_params['sheet_format'] == 'P') {
      $width = '105';
      $height = '148';
    }
    elseif ($page_params['sheet_format'] == 'A6' && $page_params['sheet_format'] == 'L') {
      $width = '148';
      $height = '105';
    }

    $this->sizes = array();
    $pdf_params['height'] = $height;
    $width = $width - $pdf_params['pdf_margin_left'] * $ds - $pdf_params['pdf_margin_right'] * $ds;
    $this->BlockSizes($name, $page_params, $width, $pdf_params);

    $this->setChooseHeight();




    $position = [
      'top' => $pdf_params['pdf_margin_top'] * $ds,
      'left' => $pdf_params['pdf_margin_left'] * $ds
    ];
    $this->BlockCreate($name, $page_params, $position, $pdf_params);
  }

}



class PDFBlock extends Widget
{

  public $pages;
  public $params;

  public function init()
  {
    parent::init();
  }
  

  public function run()
  {
    $theme = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES));

    $default = [
      'theme_name' => $theme['theme_name'],
      'document_name' => 'document',
      'sheet_format' => 'A4',
      'orientation' => 'P',
      'title' => 'document',
      'subject' => 'document',
      'keywords' => '',
      'pdf_margin_top' => 25,
      'pdf_margin_left' => 20,
      'pdf_margin_right' => 20,
      'pdf_margin_bottom' => 25,
      'dimension_scale' => 0.3,
    ];

    $params = array_merge($default, $this->params);

    $ds = $params['dimension_scale'];

    $pdf = new PDFBox('P', 'mm', $params['sheet_format'], true, 'UTF-8', false);
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Holbi');
    $pdf->SetTitle($params['title']);
    $pdf->SetSubject($params['subject']);
    $pdf->SetKeywords($params['keywords']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins($params['pdf_margin_left'] * $ds, $params['pdf_margin_top'] * $ds, $params['pdf_margin_right'] * $ds);
    $pdf->SetAutoPageBreak(TRUE, $params['pdf_margin_bottom'] * $ds);

    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    if (is_array($this->pages)){
      foreach ($this->pages as $page){

        $items_query = tep_db_query("select id, widget_name, widget_params from " . (\frontend\design\Info::isAdmin() ? TABLE_DESIGN_BOXES_TMP : TABLE_DESIGN_BOXES) . " where block_name = '" . $page['name'] . "' and theme_name = '" . $params['theme_name'] . "' order by sort_order");

        $count = tep_db_num_rows($items_query);
        if ($count > 0){


          $pdf->AddPage();


          if ($page['theme_name']){
            $theme_name =  $page['theme_name'];
          } elseif ($params['theme_name']){
            $theme_name =  $params['theme_name'];
          } else {
            $theme = tep_db_fetch_array(tep_db_query("select theme_name from " . TABLE_THEMES));
            $theme_name =  $theme['theme_name'];
          }
          $pdf->Block($page['name'], array_merge($page['params'], array('theme_name' => $theme_name)), $params);
          
          
          
        }
      }
    }
    $pdf->Output($this->params['document_name'], 'I');

    die();
  }




}