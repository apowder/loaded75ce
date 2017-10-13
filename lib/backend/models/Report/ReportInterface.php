<?php

namespace backend\models\Report;


interface ReportInterface {

    public function loadPurchases();
    
    public function getTableTitle();
    
    public function getRange();
    
    public function getOptions();
    
    public function getRowsCount();
    
    public function convertColumnTitle($value);
}
