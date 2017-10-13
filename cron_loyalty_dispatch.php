<?php
include('includes/application_top.php');


if($ext = \common\helpers\Acl::checkExtension('CustomerLoyalty', 'dispatchPoints')){
    if ($ext::isEnabled()){
        $ext::dispatchPoints();
    }
} else {
    echo 'Module disabled';
}

echo '<hr>Done.';
