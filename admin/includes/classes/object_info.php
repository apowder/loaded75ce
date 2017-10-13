<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

  function tep_trim($string)
  {
    if (is_string($string)) {
        return trim($string);
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = tep_trim($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

  class objectInfo {

// class constructor
    function __construct($object_array, $trim = true, $call_tep_db_prepare_input = false) {
      if (is_array($object_array)){
        reset($object_array);
        while (list($key, $value) = each($object_array)) {
          if($call_tep_db_prepare_input != true)
          {
            if($trim)
                $this->$key = tep_trim($value);
            else
              $this->$key = $value;
          }
          else
          {
            $this->$key = tep_db_prepare_input($value, $trim);
          }
        }
      }
    }
  }
