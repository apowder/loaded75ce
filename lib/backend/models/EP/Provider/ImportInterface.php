<?php
/**
 * Created by PhpStorm.
 * User: tkach
 * Date: 03/05/17
 * Time: 19:48
 */

namespace backend\models\EP\Provider;


use backend\models\EP\Messages;

interface ImportInterface
{

    public function importRow($data, Messages $message);

    public function postProcess(Messages $message);
}