<?php
/**
 * Created by PhpStorm.
 * User: tkach
 * Date: 03/05/17
 * Time: 19:48
 */

namespace backend\models\EP\Provider;


interface ExportInterface
{

    public function prepareExport($useColumns, $filter);
    public function exportRow();

}