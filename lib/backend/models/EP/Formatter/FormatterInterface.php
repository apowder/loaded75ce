<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP\Formatter;

interface FormatterInterface {

  public function write_array($data_array);

  public function getHeaders();
  public function setReadRemapArray($data_array);
  public function read_array();

}