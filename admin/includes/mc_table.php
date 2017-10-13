<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

define('FPDF_FONTPATH', DIR_WS_INCLUDES . 'pdf/font/');

require(DIR_WS_INCLUDES .'pdf/fpdf.php');
require(DIR_WS_INCLUDES . 'pdf/pdf.php');

class PDF_MC_Table extends PDF
{
var $widths;
var $aligns;

function SetWidths($w)
{
    //Set the array of column widths
    $this->widths=$w;
}

function SetAligns($a)
{
    //Set the array of column alignments
    $this->aligns=$a;
}

function Row($data, $border = true, $exclude = '', $weight = '', $font = '', $fill = 0, $merge = false)
{
    //Calculate the height of the row
    $nb=0;
    for($i=0;$i<count($data);$i++)
        $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
    $h=5*$nb;
    //Issue a page break first if needed
    $this->CheckPageBreak($h);
    //Draw the cells of the row
    for($i=0;$i<count($data);$i++)
    {
        if ($merge){
          $w = array_sum($this->widths);
        } else{
          $w=$this->widths[$i];
        }
        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        //Save the current position
        $x=$this->GetX();
        $y=$this->GetY();
        //Draw the border
        if ($border){
          if (is_array($exclude)){
            if (!in_array($i, $exclude)){
              $this->Rect($x,$y,$w,$h);
            }
          }else{
            $this->Rect($x,$y,$w,$h);
          }
        }
          
        //Print the text
        if (is_array($weight)){
          if (!empty($weight[$i])){
            $this->SetFont(($font!=''?$font:'Times'), $weight[$i], 8);

            $this->MultiCell($w,5,$data[$i],0,$a,$fill);
          }else{
            $this->SetFont(($font!=''?$font:'Times'), '', 8);
            $this->MultiCell($w,5,$data[$i],0,$a,$fill);
          }
        }else{
          $this->SetFont(($font!=''?$font:'Times'), '', 10);
          $this->MultiCell($w,5,$data[$i],0,$a,$fill);
        }
        //Put the position to the right of the cell
        $this->SetXY($x+$w,$y);
    }
    //Go to the next line
    $this->Ln($h);
}

function CheckPageBreak($h)
{
    //If the height h would cause an overflow, add a new page immediately
    if($this->GetY()+$h>$this->PageBreakTrigger)
        $this->AddPage($this->CurOrientation);
}

function PrintSlip($order_id, $is_neighbour = 0) {
 global $orders;
 
//  $check = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $order_id . "' and products_quantity_recieved > 0 and printed_quantity < products_quantity_recieved");

  $check = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $order_id . "'");
  if (tep_db_num_rows($check)) {
    $this->AddPage();
    $order = new order($order_id);
    $this->SetWidths(array(50, 220));
  //  $this->SetFont('Arial','',30);
  
   	$this->SetFont('Arial','',10); 
    	
    $text = explode("<br>", 'Freepost RRBT-YRZU-HRTC <br>' . $order_id . '<br>'.str_replace("\n",'<br>',STORE_NAME_ADDRESS));
  $col = sizeof($text);
  for ($i=0; $i<$col ;$i++){
	  $this->Text(15, 28+(5*$i),$text[$i]);
  }    	
       
    $text = explode("\n",\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', "\n"));
    $col = sizeof($text);
    for ($i=0; $i<$col ;$i++){
  	  $this->Text(130, 28+(5*$i),$text[$i]);
    }
     
  $shipping_method = $order->info['shipping_method'];
  if (substr($shipping_method, -1, 1) == ')') {
    // remove bracketed description
	$bracket_start = strpos($shipping_method, '(');
    $shipping_method = substr($shipping_method, 0, $bracket_start-1);
  }

  $this->Text(100, 70, $order_id . ' ' . $shipping_method);
  
  $this->SetFont('helvetica','I',8); 
  $this->Text(70, 75, 'If undelivered, return to:');
  $this->Text(70, 78, " STW Online Ltd, Watford Business Centre, Park House, 15/23 Greenhill Crescent, Watford, WD18 8PH");
  
  
  $this->SetFont('helvetica','B',16); 
  $this->Text(60, 88, 'BedroomPleasures Packing Slip');
  
  
  $this->Ln(85);
  $this->SetWidths(array(50, 120));
  //$this->Cell(-1);
  $this->Row(array('Tel:',  $order->customer['telephone']), false );
  $this->Row(array('Email:',  $order->customer['email_address']), false );
  $this->Row(array('Order Number:',  $order_id), false );
  $this->Row(array('Payment Method:',  $order->info['payment_method']), false );

  $this->Ln(3);
  $this->SetWidths(array(20, 120, 20));
  $this->SetFillColor(102,102,102);
  $this->Cell(20,5,'',1,0,'',1);
  $this->Cell(120,5,'Products',1,0,'',1);
  $this->Cell(20,5,'',1,0,'',1);
  $this->Ln();
  //$this->Row(array('', 'Products', ''));
  
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      $product_check = tep_db_fetch_array(tep_db_query("select products_quantity_recieved, printed_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $order_id . "' and orders_products_id = '" . $order->products[$i]['orders_products_id'] . "'"));

      /*
      echo '      <tr class="dataTableRow">' . "\n" .

           '        <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           */
      if ($product_check['products_quantity_recieved'] == 0 ) {
        $qty = 'Out of stock';
      } elseif ($product_check['printed_quantity'] == 0) {
        $qty = min($product_check['products_quantity_recieved'], $order->products[$i]['qty']);
        tep_db_query("update " . TABLE_ORDERS_PRODUCTS . " set printed_quantity = printed_quantity + " . $qty . " where orders_products_id = '" . $order->products[$i]['orders_products_id'] . "'");
      } elseif ($product_check['products_quantity_recieved'] == $product_check['printed_quantity'] && $product_check['products_quantity_recieved'] < $order->products[$i]['qty']) {
        $qty = 'Out of stock';
      } elseif ($product_check['products_quantity_recieved'] == $product_check['printed_quantity'] && $product_check['products_quantity_recieved'] == $order->products[$i]['qty']) {
        $qty = 'Already sent';
      } else {
        $qty = $product_check['products_quantity_recieved'] - $product_check['printed_quantity']; 
        tep_db_query("update " . TABLE_ORDERS_PRODUCTS . " set printed_quantity = printed_quantity + " . $qty . " where orders_products_id = '" . $order->products[$i]['orders_products_id'] . "'");
      }

      $name = $order->products[$i]['name'];



      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {

        for ($j=0, $k=sizeof($order->products[$i]['attributes']); $j<$k; $j++) {

          $name .= "\n" . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
        }
      }
      
      $model_data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . $order->products[$i]['id'] . "'"));
      $model = $order->products[$i]['model'];
      if ($model_data['products_model'] != '') {
        $model = $model_data['products_model'];
      }
      $this->Row(array($qty, $name, $model));


    }
    if($is_neighbour == 1) {
    $this->SetFont('Arial','',8); 
    $this->Text(20, 210,'If I am not in please leave with a neighbour.');
    }
    $this->SetFont('helvetica','B',16); 
    $this->Text(65, 230, 'Thank you for shopping at:');
    $this->Image(DIR_FS_ADMIN . 'images/logo.gif',50, 235, 106, 22);
    $this->SetFont('Arial','',8); 
    $this->Text(70, 280, 'Company No: 4964325 | VAT No: 829 9884 50');
    $this->Ln();
  }
  
}

function PrintInvoice($order_id) {
 global $orders, $currencies;
  
  $check = tep_db_query("select * from " . TABLE_ORDERS . " where orders_id = '" . $order_id . "'");
  if (tep_db_num_rows($check)) {
    $this->AddPage();
    $order = new order($order_id);
    $this->SetWidths(array(50, 220));
  //  $this->SetFont('Arial','',30);
    
   	$this->SetFont('Helvetica','',10); 
    $this->Image(DIR_FS_ADMIN . 'images/print_logo.png',65, 10, 80, 17);    
    $text = explode("<br>", str_replace("\n",'<br>',STORE_NAME_ADDRESS));
    $col = sizeof($text);
    for ($i=0; $i<$col ;$i++){
      $this->Text(105 - ($this->GetStringWidth($text[$i]) / 2), 282+(3*$i),$text[$i]);
    } 
    $this->Text(105 - ($this->GetStringWidth('Tel: 0800 068 0533') / 2), 240+10*$col,'Tel: 0800 068 0533');
    $this->Line(10,68,100,68); 
    $this->Line(110,68,200,68); 
//echo"<pre>";print_r($order);
    $text = explode("\n",\common\helpers\Address::address_format($order->customer['format_id'], $order->customer, 1, ' ', "\n"));
    $col = sizeof($text);
    $this->Text(10, 65, 'SOLD TO:');
   	$this->SetFont('Helvetica','',10); 
   
    for ($i=0; $i<$col ;$i++){
  	  $this->Text(10, 75+(5*$i),$text[$i]);
    }


    $this->SetFont('Helvetica','',10); 
    $this->Text(110, 65, 'SHIP TO:');  
   	$this->SetFont('Helvetica','',10);    
    $text = explode("\n",\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', "\n"));
    $col = sizeof($text);
    for ($i=0; $i<$col ;$i++){
  	  $this->Text(110, 75+(5*$i),$text[$i]);
      $next = 75+(5*$i);
    }
    $next += 10;
    $this->SetFont('Helvetica','',20); 
    $this->Text(105 - ($this->GetStringWidth('Order Number: '.$order_id) / 2), 40, 'Order Number: '.$order_id);
		$this->SetFont('Helvetica','',10);
    $this->Text(105 - ($this->GetStringWidth('Date: '.\common\helpers\Date::datetime_short($order->info['date_purchased'])) / 2), 45, 'Date: '.\common\helpers\Date::datetime_short($order->info['date_purchased']));
    $this->Line(10, $next+10,200,$next+10); 
    $this->Ln($next+10);

    $this->SetWidths(array(70, 20, 15, 20, 20, 20, 20),true);
    $this->SetFillColor(70, 20, 20);
    
    $this->SetAligns(array('L','L','J','R','R','R','R'));  
    $this->SetX(10);
		$this->SetFont('Helvetica','',18);
    $this->Row(array('Products:', 'Model', 'Tax', 'Price (ex)', 'Price (inc)', 'Total (ex)', 'Total (inc)'),false);
    $this->Ln(-5);
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      $this->SetX(10);
      $this->Row(array($order->products[$i]['qty'].' x '.$order->products[$i]['name'], $order->products[$i]['model'], \common\helpers\Tax::display_tax_value($order->products[$i]['tax']).'%', 
                       utf8_decode($currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value'])), 
                       utf8_decode($currencies->format(\common\helpers\Tax::add_tax($order->products[$i]['final_price'],$order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value'])), 
                       utf8_decode($currencies->format($order->products[$i]['final_price']*$order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value'])), 
                       utf8_decode($currencies->format(\common\helpers\Tax::add_tax($order->products[$i]['final_price']*$order->products[$i]['qty'],$order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']))
                       ),false);
     
    }
    $this->Ln();
    $this->SetWidths(array(100, 20));
    $this->SetAligns(array('R','R'));    
    for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
      $this->SetX(80);
      $this->Row(array(strip_tags($order->totals[$i]['title']), utf8_decode(strip_tags($order->totals[$i]['text']))), false);
    }
    $this->Line(10, 235,200,235); 
    $this->SetFont('Helvetica','B',16); 
    $this->Text(105 - ($this->GetStringWidth('Thank you for shopping at:') / 2), 245, 'Thank you for shopping at:');
    $this->Image(DIR_FS_ADMIN . 'images/print_logo.png',79, 250, 50, 11);
    $this->SetFont('Arial','',11); 
    $this->Text(70, 275, 'Company No: 4964325 | VAT No: 829 9884 50');
    $this->Ln();
  }
  
}

function PrintSlipNew($order_id, $is_neighbour = 0) {
 global $orders;
 
  $check = tep_db_query("select * from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $order_id . "'");
  if (tep_db_num_rows($check)) {
    $this->AddPage();
    
//    $this->AddFont('arial');
    
    $order = new order($order_id);
    $this->SetWidths(array(50, 220));
    $this->SetFont('arial','',30);
    
    $this->SetTextColor(114,114,114);
   	$this->SetFont('arial','',10); 
 
	  $this->Text(10, 22, 'STW Online Ltd,#'.$order_id);
    $this->Text(10, 27, 'Watford Business Centre, Park House');
    $this->Text(10, 32, '15/23 Greenhill Crescent');
    $this->Text(10, 37, 'Watford, Herts');
    $this->Text(10, 42, 'WD18 8PH');

    $text = explode("\n",\common\helpers\Address::address_format($order->delivery['format_id'], $order->delivery, 1, ' ', "\n"));
    $col = sizeof($text);
    for ($i=0; $i<$col ;$i++){
  	  $this->Text(110, 17+(5*$i),$text[$i]);
    }
       
    $this->Image(DIR_FS_ADMIN . 'images/2nd-ppi-pack.jpg',160, 15, 40, 17);
    
  $this->SetTextColor(24,24,24);
  $shipping_method = $order->info['shipping_method'];
 
  if (substr($shipping_method, -1, 1) == ')') {
    // remove bracketed description
    $bracket_start = strpos($shipping_method, '(');
    $shipping_method = substr($shipping_method, 0, $bracket_start-1);
    $text = explode("<br>", str_replace("-",'<br>',$order_id . ' ' . $shipping_method));
    $col = sizeof($text);
    for ($i=0; $i<$col ;$i++){
      $this->Text(110, 80+(5*$i),trim($text[$i]));
    } 
  }
  
  //$this->Text(110, 80, ); 
  
  $this->SetFont('helvetica','I',9); 
  $this->Text(100, 90, 'If undelivered, return to:');
  $this->Text(100, 93, 'STW Online Ltd, Watford Business Centre, Park House');
  $this->Text(100, 96, '15/23 Greenhill Crescent, Watford, Herts, WD18 8PH');
  
  
  $this->SetFont('helvetica','B',16); 
  $this->Text(60, 125, 'Packing Slip');
  
  $this->Line(10, 150,200,150); 
  
  $this->Ln(142);
  $this->SetWidths(array(50, 120));
  //$this->Cell(-1);
  $this->SetFont('helvetica','',10); 
  $this->Row(array('Tel:',  $order->customer['telephone']), false ,'','','arial');
  $this->Row(array('Email:',  $order->customer['email_address']), false ,'','','arial');
  $this->Row(array('Order Number:',  $order_id), false ,'','','arial');
  $this->Row(array('Payment Method:',  $order->info['payment_method']), false ,'','','arial');

  $this->Ln(5);
  $this->SetWidths(array(150, 40));
  $this->SetFont('helvetica','B',15);
  //$this->SetFillColor(10,10,10);
  
  //$this->SetTextColor(255,255,255);
  $this->Row(array('Products', ''), false ,'','','arial', 0);
  //$this->SetFillColor(204,204,204);
  $this->SetTextColor(5,5,5);
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      $product_check = tep_db_fetch_array(tep_db_query("select products_quantity_recieved, printed_quantity from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . $order_id . "' and orders_products_id = '" . $order->products[$i]['orders_products_id'] . "'"));
      $qty = $order->products[$i]['qty'];
      $name = $order->products[$i]['name'];
      
      $model_data = tep_db_fetch_array(tep_db_query("select * from " . TABLE_PRODUCTS . " where products_id = '" . $order->products[$i]['id'] . "'"));
      $model = $order->products[$i]['model'];
      if ($model_data['products_model'] != '') {
        $model = $model_data['products_model'].($order->products[$i]['is_special'] ? '     Sale Item' : '');
        
      }
      //$this->SetX(15);
      $this->Row(array('      '.$qty.' x '.$name, $model),false,'','','arial',0);


      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        $this->SetFont('Arial','',8); 
        for ($j=0, $k=sizeof($order->products[$i]['attributes']); $j<$k; $j++) {

          $name = "                  " . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          $this->Ln(-1);
          //$this->SetX(25);
          $this->Row(array($name, ''),false,'',array('I',''),'',0);
        }
      }
      



    }
    
    if($is_neighbour == 1) {
		$this->SetFont('Arial','',10); 
    $this->Line(10, 195+($n*4),200,195+($n*4)); 
		$this->Text(20, 200+($n*4),'If I am not in please leave with a neighbour.');
    $this->Line(10, 205+($n*4),200,205+($n*4)); 
		}
    $this->SetFont('helvetica','B',16); 
    $this->Text(65, 230+($n*4), 'Thank you for shopping at:');
    $this->Image(DIR_FS_ADMIN . 'images/logo.gif',50, 235+($n*4), 106, 22);
    $this->SetFont('helvetica','B',8); 
    $this->Text(70, 260+($n*4), 'Company No: 4964325 | VAT No: 829 9884 50');
    $this->Ln();
  }
  
}


function PrintOrder($order_id){
  Global $currencies;
  $this->AddPage();
//  $order = new order($order_id);
  
  $this->Image(DIR_FS_ADMIN . 'images/logo_invoice.jpg',10, 10, 100, 90);
  $this->Image(DIR_FS_ADMIN . 'images/invoice_title.jpg',115, 15, 70, 7);
 
  $this->SetWidths(array(50, 220));
//  $this->SetFont('Arial','',30);
//  $this->Text(20,20, $order_id);
//  $this->SetFont('Arial','',12);
  
  $supplier = tep_db_fetch_array(tep_db_query("select s.*, sr.* from  " . TABLE_SUPPLIERS . " s, " . TABLE_SUPPLIERS_REQUESTS . " sr where sr.supplier_request_id = '" . $order_id . "' and s.supplier_id = sr.supplier_id "));
//  $this->Text(20, 42, \common\helpers\Date::date_short($supplier['date_added']));

//$this->Ln(20);
$this->SetFont('helvetica','B',14);

$this->Text(115, 40, 'Purchase Order Number');
$this->Text(160, 50, 'Date');

	$this->SetFont('Arial','',12); 	
	$this->SetFillColor(255,255,255);
  $this->SetTextColor(0);
  $this->SetDrawColor(0,0,0);
  $this->SetLineWidth(.2);
  $this->SetFont('helvetica','B');
  
  #666666
  //Header
//  $this->Ln(50);
// 	$this->Cell(-1);
//  $this->Cell(75,5,'',10,0,'',1);

//  $this->Cell(40);
  $this->Ln(25);
  $this->Cell(165);
  $this->Cell(25,5,$order_id,1,0,'',1); 
 
//  $this->Cell(-1);
  $this->Ln(10);
  $this->Cell(165);
  $this->Cell(25,5,\common\helpers\Date::date_short($supplier['date_added']),1,0,'',1); 
  

//  $this->Text(20, 48, 'Payment: ' . $order->info['payment_method']);
  /*if ($order->info['cc_number'] != ''){
    $this->Text(20, 54, 'CC number: ' . str_repeat('x', 12) . substr($order->info['cc_number'], 12, 4));
  }
  if ($order->info['cc_cvn'] != ''){
    $this->Text(20, 58, 'CC cvn: ' .  $order->info['cc_cvn']);
  }
  if ($order->info['cc_expires'] != ''){
    $this->Text(20, 62, 'CC expires: ' .  $order->info['cc_expires']);
  }  
  if ($order->info['cc_validfrom'] != ''){
    $this->Text(20, 66, 'CC valid from: ' .  $order->info['cc_validfrom']);
  }  
  if ($order->info['cc_issuenumber'] != ''){
    $this->Text(20, 70, 'CC issue number: ' .  $order->info['cc_issuenumber']);
  } */ 
  
  $this->SetFont('Arial','',16);
  
//  $this->Image(DIR_FS_CATALOG . '/images/fax.jpg',100, 15, 75, 30);
//  $this->Image(DIR_FS_CATALOG . '/images/fax.jpg',100, 208, 72, 30);
  
  
  $this->SetFont('Arial','',10);  
/*
  $text = explode("\n",STORE_NAME_ADDRESS);
  $col = sizeof($text);
  for ($i=0; $i<$col ;$i++){
	  $this->Text(150, 28+(5*$i),$text[$i]);
  }
  //$this->Text(150, 28+(5*$i),'VAT:' . TAX_NUMBER);

*/
	$this->SetFont('Arial','',12); 	
	$this->SetFillColor(255,255,255);
  $this->SetTextColor(0);
  $this->SetDrawColor(0,0,0);
  $this->SetLineWidth(.2);
  $this->SetFont('helvetica','B');
  
  #666666
  //Header
  $this->Ln(15);
 	$this->Cell(-1);
//  $this->Cell(75,5,'',10,0,'',1);

//  $this->Cell(40);
  $this->Cell(105);
  $this->Cell(85,5,'Supplier',1,0,'',1); 
  
      
  $this->Ln();

  //$this->Cell(15);
  $this->SetFont('','');    
  $this->SetFillColor(255,255,255);
  $this->SetTextColor(0);
  $this->SetDrawColor(102,102,102);
  //Header
  $this->Cell(-1);
  
//  $this->Cell(75,45,'',1,0,'');
//  $this->Cell(40);	  
  $this->Cell(105);	  
  $this->Cell(85,45,'',1,0,'');
  $this->Ln();
	$this->SetFont('Arial','',10); 
	
	

  $text = explode("\n",$supplier['supplier_address']);
  $text[] = $supplier['supplier_phone'];
  $col = sizeof($text);
  for ($i=0; $i<$col ;$i++){
	  $this->Text(120, 70+(5*$i),$text[$i]);
  }  
/*     
  $text = explode("\n",STORE_NAME_ADDRESS);
  $col = sizeof($text);
  for ($i=0; $i<$col ;$i++){
	  $this->Text(130, 85+(5*$i),$text[$i]);
  }
*/  
  $this->Ln(5);
  $this->Cell(-1);
  $this->SetWidths(array(85, 85, 20));
  $this->SetAligns(array('L', 'L', 'R'));
  	/*$this->SetFont('Arial','B',8); 	
	$this->SetFillColor(10,23,203);    
    $this->SetDrawColor(10,23,203);*/
    //$this->SetLineWidth(array(.2,.2,.2));
  //$this->SetTextColor(255);
  
  $this->Row(array('Product Code', 'Item', 'Quantity'), true, '', array('B', 'B', 'B'),'helvetica' ,12);
  
  $products_query = tep_db_query("select * from  " . TABLE_SUPPLIER_REQUESTS_PRODUCTS . " where supplier_requests_id = '" . $order_id . "'");
  while ($products = tep_db_fetch_array($products_query)) {
    $this->Cell(-1);
    $this->Row(array($products['products_name'], $products['products_model'], $products['products_quantity']) );
  }
    


  $this->Ln();
}

function NbLines($w,$txt)
{
    //Computes the number of lines a MultiCell of width w will take
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $nl=1;
    while($i<$nb)
    {
        $c=$s[$i];
        if($c=="\n")
        {
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
            continue;
        }
        if($c==' ')
            $sep=$i;
        $l+=$cw[$c];
        if($l>$wmax)
        {
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
            }
            else
                $i=$sep+1;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
        }
        else
            $i++;
    }
    return $nl;
}

function BulkPurchaseOrder() {
  
  $query="select sr.supplier_request_id, sr.date_added, srp.products_id, srp.supplier_requests_products_id, srp.products_name, srp.products_model, s.supplier_name, s.supplier_address, s.supplier_phone, SUM( op.products_quantity_recieved ) as qty, op.orders_products_id from " . TABLE_SUPPLIERS_REQUESTS . " sr join ". TABLE_SUPPLIER_REQUESTS_PRODUCTS ." srp on sr.supplier_request_id = srp.supplier_requests_id join " . TABLE_SUPPLIERS ." s on s.supplier_id = sr.supplier_id join " . TABLE_ORDERS_PRODUCTS ." op on concat(',',srp.orders_products_ids,',') like concat('%,',op.orders_products_id,',%') where srp.products_quantity_recieved = srp.products_quantity and op.orders_products_status=3 and date(sr.date_recieved) between date('2012-06-01') and curdate() group by sr.supplier_request_id, srp.products_id  and sr.date_recieved between '2012-06-01 00:00:00' and now() order by sr.supplier_request_id";
  $sup_query = tep_db_query($query);
  if(tep_db_num_rows($sup_query)){
    Global $currencies;
	global $login_id;
    $current = '';
    while($orders = tep_db_fetch_array($sup_query)){
	  tep_db_query("update ". TABLE_SUPPLIER_REQUESTS_PRODUCTS." set is_printed = '1' where supplier_requests_products_id = '". $orders['supplier_requests_products_id'] ."'");
	  tep_db_query("insert into " . TABLE_SUPPLIERS_REQUESTS_PRODUCTS_HISTORY . " set supplier_request_id = '" . $orders['supplier_request_id'] . "', products_id = '" . $orders['products_id'] . "', date_added = now(), update_qty = '" . $orders['qty'] . "', orders_products_id = '" . $orders['orders_products_id'] . "', comments = 'Purchase Order`s Position Printed', admin_id = '" . (int)$login_id . "'");
	  if($current !== $orders['supplier_request_id']){
	    $this->AddPage();
//  $order = new order($order_id);
  
        $this->Image(DIR_FS_ADMIN . 'images/logo_invoice1.jpg',10, 10, 100, 35);
        $this->Image(DIR_FS_ADMIN . 'images/logo_address_ico.jpg',43, 47, 5, 5);
        $this->SetFont('helvetica','',12);
        $text = explode("<br>", str_replace("\n",'<br>',STORE_NAME_ADDRESS));
        $col = sizeof($text);
        for ($i=0; $i<$col ;$i++){
          $this->Text(50, 51+(6*$i),$text[$i]);
        } 
        $this->Image(DIR_FS_ADMIN . 'images/logo_phone_ico.jpg',43, 87, 6, 5);
        $this->Text(50, 91, "0800 068 0533");
        
        $this->Image(DIR_FS_ADMIN . 'images/logo_fax_ico.jpg',43, 94, 6, 5);
        $this->Text(50, 98, "01923 249 227");

        $this->Image(DIR_FS_ADMIN . 'images/logo_email_ico.jpg',43, 101, 6, 5);
        $this->Text(50, 105, STORE_OWNER_EMAIL_ADDRESS);

        $this->Image(DIR_FS_ADMIN . 'images/logo_web_ico.jpg',43, 108, 6, 5);
        $this->Text(50, 112, "www.bedroompleasures.co.uk");
        
        $this->Image(DIR_FS_ADMIN . 'images/invoice_title.jpg',115, 15, 70, 7);
 
        $this->SetWidths(array(50, 220));
        $this->SetFont('helvetica','B',14);
        $this->Text(115, 40, 'Purchase Order Number');
        $this->Text(160, 50, 'Date');

	    $this->SetFont('helvetica','',12); 	
	    $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.2);
        $this->SetFont('helvetica','B');
  
        $this->Ln(28);
        $this->Cell(165);
        $this->Cell(25,5,$orders['supplier_request_id'],1,0,'',1); 
 
        $this->Ln(10);
        $this->Cell(165);
        $this->Cell(25,5,\common\helpers\Date::date_short($orders['date_added']),1,0,'',1); 
 	    $this->SetFont('helvetica','',16);
  
        $this->SetFont('helvetica','',10);  

	    $this->SetFont('helvetica','',12); 	
	    $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.2);
        $this->SetFont('helvetica','B');
  
        #666666
        //Header
        $this->Ln(15);
 	    $this->Cell(-1);
        //  $this->Cell(75,5,'',10,0,'',1);

//  $this->Cell(40);
        $this->Cell(105);
        $this->Cell(85,5,'Supplier',1,0,'',1); 
       
        $this->Ln();
       //$this->Cell(15);
        //$this->SetFont('','');    
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(102,102,102);
        //Header
        $this->Cell(-1);
  
        $this->Cell(105);	  
        $this->Cell(85,45,'',1,0,'');
        $this->Ln();
	    $this->SetFont('helvetica','',10); 
	
        $text = explode("\n",$orders['supplier_address']);
        $text[] = $orders['supplier_phone'];
        $col = sizeof($text);
        for ($i=0; $i<$col ;$i++){
	       $this->Text(120, 73+(5*$i),$text[$i]);
        }  
        $this->Ln(5);
        $this->Cell(-1);
        $this->SetWidths(array(85, 85, 20));
        $this->SetAligns(array('L', 'L', 'R'));
  
        $this->Row(array('Product Code', 'Item', 'Quantity'), true, '', array('B', 'B', 'B'));
	  }
      
      $this->Cell(-1);
      $this->Row(array($orders['products_name'], $orders['products_model'], $orders['qty']) );
      
//      $this->Ln();
	  
	  $current = $orders['supplier_request_id'];
  
	}
	
  }
}

function BulkPurchaseOrderBC($list) {
  
  $query = "select sr.supplier_request_id, sr.date_added, srp.products_id, srp.supplier_requests_products_id, srp.products_name, srp.products_model, s.supplier_name, s.supplier_address, s.supplier_phone, SUM( op.products_quantity_recieved ) as qty, op.orders_products_id from " . TABLE_SUPPLIERS_REQUESTS . " sr join ". TABLE_SUPPLIER_REQUESTS_PRODUCTS ." srp on sr.supplier_request_id = srp.supplier_requests_id join " . TABLE_SUPPLIERS ." s on s.supplier_id = sr.supplier_id join " . TABLE_ORDERS_PRODUCTS ." op on concat(',',srp.orders_products_ids,',') like concat('%,',op.orders_products_id,',%') where srp.products_quantity_recieved = srp.products_quantity and op.orders_products_status=3 and sr.supplier_request_id in(".implode(",", $list).") /*and srp.is_printed = 0*/ group by sr.supplier_request_id, srp.products_id  order by sr.supplier_request_id";
  $sup_query = tep_db_query($query);
  if(tep_db_num_rows($sup_query)){
    Global $currencies;
	global $login_id;
    $current = '';
    while($orders = tep_db_fetch_array($sup_query)){
	  tep_db_query("update ". TABLE_SUPPLIER_REQUESTS_PRODUCTS." set is_printed = '1' where supplier_requests_products_id = '". $orders['supplier_requests_products_id'] ."'");
	  tep_db_query("insert into " . TABLE_SUPPLIERS_REQUESTS_PRODUCTS_HISTORY . " set supplier_request_id = '" . $orders['supplier_request_id'] . "', products_id = '" . $orders['products_id'] . "', date_added = now(), update_qty = '" . $orders['qty'] . "', orders_products_id = '" . $orders['orders_products_id'] . "', comments = 'Purchase Order`s Position Printed', admin_id = '" . (int)$login_id . "'");
	  if($current !== $orders['supplier_request_id']){
	    $this->AddPage();
//  $order = new order($order_id);
  
        $this->Image(DIR_FS_ADMIN . 'images/logo_invoice1.jpg',10, 10, 100, 35);
        $this->Image(DIR_FS_ADMIN . 'images/logo_address_ico.jpg',43, 47, 5, 5);
        $this->SetFont('helvetica','',12);
        $text = explode("<br>", str_replace("\n",'<br>',STORE_NAME_ADDRESS));
        $col = sizeof($text);
        for ($i=0; $i<$col ;$i++){
          $this->Text(50, 51+(6*$i),$text[$i]);
        } 
        $this->Image(DIR_FS_ADMIN . 'images/logo_phone_ico.jpg',43, 85, 6, 5);
        $this->Text(50, 89, "0800 068 0533");
        
        $this->Image(DIR_FS_ADMIN . 'images/logo_fax_ico.jpg',43, 92, 6, 5);
        $this->Text(50, 96, "01923 249 227");

        $this->Image(DIR_FS_ADMIN . 'images/logo_email_ico.jpg',43, 99, 6, 5);
        $this->Text(50, 103, STORE_OWNER_EMAIL_ADDRESS);

        $this->Image(DIR_FS_ADMIN . 'images/logo_web_ico.jpg',43, 106, 6, 5);
        $this->Text(50, 110, "www.bedroompleasures.co.uk");
        
        $this->Image(DIR_FS_ADMIN . 'images/invoice_title.jpg',115, 15, 70, 7);
 
        $this->SetWidths(array(50, 220));
        $this->SetFont('helvetica','B',14);
        $this->Text(115, 40, 'Purchase Order Number');
        $this->Text(160, 50, 'Date');

	    $this->SetFont('helvetica','',12); 	
	    $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.2);
        $this->SetFont('helvetica','B');
  
        $this->Ln(25);
        $this->Cell(165);
        $this->Cell(25,5,$orders['supplier_request_id'],1,0,'',1); 
 
        $this->Ln(10);
        $this->Cell(165);
        $this->Cell(25,5,\common\helpers\Date::date_short($orders['date_added']),1,0,'',1); 
 	    $this->SetFont('helvetica','',16);
  
        $this->SetFont('helvetica','',10);  

	    $this->SetFont('helvetica','',12); 	
	    $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.2);
        $this->SetFont('helvetica','B');
  
        #666666
        //Header
        $this->Ln(15);
 	    $this->Cell(-1);
        //  $this->Cell(75,5,'',10,0,'',1);

//  $this->Cell(40);
        $this->Cell(105);
        $this->Cell(85,5,'Supplier',1,0,'',1); 
       
        $this->Ln();
       //$this->Cell(15);
        //$this->SetFont('','');    
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(102,102,102);
        //Header
        $this->Cell(-1);
  
        $this->Cell(105);	  
        $this->Cell(85,45,'',1,0,'');
        $this->Ln();
	    $this->SetFont('helvetica','',10); 
	
        $text = explode("\n",$orders['supplier_address']);
        $text[] = $orders['supplier_phone'];
        $col = sizeof($text);
        for ($i=0; $i<$col ;$i++){
	       $this->Text(120, 70+(5*$i),$text[$i]);
        }  
        $this->Ln(5);
        $this->Cell(-1);
        $this->SetWidths(array(85, 85, 20));
        $this->SetAligns(array('L', 'L', 'R'));
  
        $this->Row(array('Product Code', 'Item', 'Quantity'), true, '', array('B', 'B', 'B'));
	  }
      
      $this->Cell(-1);
      $this->Row(array($orders['products_name'], $orders['products_model'], $orders['qty']) );
      
//      $this->Ln();
	  
	  $current = $orders['supplier_request_id'];
  
	}
	
  } else {
    	    $this->AddPage();
          $this->Text(10, 10, "There are no purchase orders for your request");
  }
}

function PrintNewsletterStat($head, $data){
 $img =  DIR_FS_ADMIN.'tmp/dategramm.png';
 $e = @getImageSize($img);
 $w = $e[0]/3;

 $this->Open();
 if ($w > 190){
  $orient = 'l';
  $mw = $w;
  if ($w > 280) $mw = 280;
 } else{
  $orient = 'p';
  $mw = 190;
 }

 $this->AddPage($orient);

 $this->Image(DIR_FS_ADMIN . 'images/logo.jpg',10, 10, 80, 17);    
 $this->SetFont('Arial','',10); 
 $this->Text(110, 20, 'Customers Newsletters Statistic Report');
 $this->Text(110, 25, date('jS F Y'));

 $this->Image($img,10,40, $mw,100,'PNG');
 $this->SetFont('Arial','',10); 
 $this->Ln(140);
 //$this->Cell(-1);
 $cn = sizeof($head);
 $a = array(20);
 $b = array('C');
 for($i=1;$i<$cn;$i++){
  $a[] = $mw/$cn;
  $b[] = 'C';
 }
 $this->SetWidths($a);
 $this->SetAligns($b); 
 $this->Row($head, true, '', $mw,'',0);
 //$this->Cell(-1);
 $this->Row($data, true, '', $mw,'',0);
 //$this->Text(10, 120, $head);
 $this->Ln();
}

function PrintRepeatStat($head, $data, $data2){
 $img =  DIR_FS_ADMIN.'tmp/repeatc_bars.png';
 $e = @getImageSize($img);
 $w = $e[0]/3;

 $this->Open();
 if ($w > 190){
  $orient = 'l';
  $mw = $w;
  if ($w > 280) $mw = 280;
 } else{
  $orient = 'p';
  $mw = 190;
 }

 $this->AddPage($orient);
 $this->Image(DIR_FS_ADMIN . 'images/logo.jpg',10, 10, 80, 17);    
 $this->SetFont('Arial','',10); 
 $this->Text(110, 20, 'Repeat Customer Statictics Report');
 $this->Text(110, 25, date('jS F Y'));
 $this->Image($img,10,30, $mw,120,'PNG');
 
 $this->Ln(145);
// $this->Cell(-1);
 $cn = sizeof($head);
 $a = array(20);
 $b = array('C');
 for($i=1;$i<$cn;$i++){
  $a[] = $mw/$cn;
  $b[] = 'C';
 }
 $this->SetWidths($a);
 $this->SetAligns($b);
 $this->SetFillColor(0,0,0);
 $this->SetTextColor(255,255,255);
 $this->SetDrawColor(102,102,102);
 $this->Row($head, false, '', $mw,'',1);
 //$this->Cell(-1);
 $this->SetFillColor(239,239,239);
 $this->SetTextColor(0);

 $this->Row($data, true, '', $mw,'',1);
 //$this->Cell(-1);
 $this->Row($data2, true, '', $mw,'',1); 
 //$this->Text(10, 120, $head);
 $this->AddPage($orient);
 $img =  DIR_FS_ADMIN.'tmp/repeatc_pie.png';
 $this->Image($img,10,10, $mw,120,'PNG');
}

function PrintNewRepeatStat($head, $data){
 $img =  DIR_FS_ADMIN.'tmp/nrepeat_pie.png';
 $e = @getImageSize($img);
 $w = $e[0]/3;

 $this->Open();
 $orient = 'p';
 $mw = 190;
 
 $this->AddPage($orient);

 $this->Image(DIR_FS_ADMIN . 'images/logo.jpg',10, 10, 80, 17);    
 
 $this->SetFont('Helvetica','B',10); 
 $this->Text(10, 40, 'New Purchases / Repeat Purchases Report');
 $this->Text(10, 50, 'Percentage of Purchases (New/Repeat)');
 $this->Text(165, 50, date('jS F Y'));
 $this->SetFont('Arial','',10); 
 $this->Image($img,10,60, $mw,95,'PNG');
 
 $this->Ln(160);
 //$this->Cell(-1);
 $cn = sizeof($data[0]);
 $a = array(60,40,45);
 $b = array('L');
 for($i=1;$i<$cn;$i++){
  $a[] = $mw/$cn;
  $b[] = 'C';
 }
 $this->SetWidths($a);
 $this->SetAligns($b); 
// $this->Row($head, true, '', $mw);
 //$this->Cell(-1);
 $this->SetFillColor(0,0,0);
 $this->SetTextColor(255,255,255);
 $this->SetDrawColor(102,102,102);
 for($i=0;$i<sizeof($data);$i++){
  if ($i > 0) {
    $this->SetFillColor(239,239,239);
    $this->SetTextColor(0);
  }
  $this->Row($data[$i], false, '', $mw,'',1);
 }
 
}

function PrintStatSold($head, $data){
 $img =  DIR_FS_ADMIN.'tmp/stats_sold.png';
 $e = @getImageSize($img);
 $w = $e[0]/3;

 $this->Open();
 $orient = 'p';
 $mw = 190;

 $this->AddPage($orient);

 $this->Image(DIR_FS_ADMIN . 'images/logo.jpg',110, 10, 80, 17);    
 $this->SetFont('Arial','',10); 
 $pos = 0;
 for($i = 0; $i<count($head);$i++){ 
  $this->Text(10,20+(5*$i), utf8_decode($head[$i]));
  $pos = 20+(5*$i);
 }
 $pos +=10;
 //$this->Text(110, 20, 'Repeat Customer Statictics Report');
 //$this->Text(110, 25, date('jS F Y'));
 $this->Image($img,10,$pos, $mw,100,'PNG');
 $pos += 92;
 //$this->Ln($pos);
 //$this->Text(110, $pos, date('jS F Y'));
 $j = $i = 0;
 $this->Ln($pos);
 $this->SetWidths(array(20,20,60,10,20,20,20,20,20));   
 foreach($data as $title => $data2){
  $this->Ln(0);
  $this->Row(array($title), false, '', $mw,'',0, true);
  for($j = 0;$j<count($data2);$j++){
    foreach($data2[$j] as $k=>$it){
      $data2[$j][$k] = utf8_decode($it);
    }
    $this->Row($data2[$j], true, '', $mw,'',0);
  }
  $this->Row(array(''), false, '', $mw,'',0);
  $i++;
 }
/* 
 
 $cn = sizeof($head);
 $a = array(20);
 $b = array('C');
 for($i=1;$i<$cn;$i++){
  $a[] = $mw/$cn;
  $b[] = 'C';
 }
 $this->SetWidths($a);
 $this->SetAligns($b);
 $this->SetFillColor(0,0,0);
 $this->SetTextColor(255,255,255);
 $this->SetDrawColor(102,102,102);
 $this->Row($head, false, '', $mw,'',1);
 //$this->Cell(-1);
 $this->SetFillColor(239,239,239);
 $this->SetTextColor(0);

 $this->Row($data, true, '', $mw,'',1);
 $this->Row($data2, true, '', $mw,'',1); 
 */
}
}

