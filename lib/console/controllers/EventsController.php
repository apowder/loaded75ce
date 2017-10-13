<?php

/**
 * /usr/bin/php /home/user/public_html/site/yii.php events - > /dev/null
 */

namespace console\controllers;

use yii\console\Controller;

/**
 * Events controller
 */
class EventsController extends Controller {

    public $runNow;

    public function options($actionID)
    {
        if ( $actionID=='datasource' ) {
            return ['runNow'];
        }
        return [];
    }

   /* public function options($actionID)
    {
        return ['r'=>'run'];
    }*/

    public function bindActionParams($action, $params){
        $language_id = \common\classes\language::defaultId();
        \common\helpers\Translation::init('main', $language_id);
        return parent::bindActionParams($action, $params);
    }

    /**
     * Default cron event
     */
    public function actionIndex() {
        echo "cron service running\n";
    }

    /**
     * cron event
     */
    public function actionReset() {
        echo "cron service 2 running\n";
    }

    /**
     * cron event
     */
    public function actionGoogleAnalytics() {
        $path = dirname($_SERVER['SCRIPT_NAME']);
        $remoteFile = 'https://www.google-analytics.com/analytics.js';
        $localfile = $path . '/themes/basic/js/analytics.js';
        $response = file_get_contents($remoteFile);
        if ($response != false) {
            if (!file_exists($localfile)) {
                fopen($localfile, 'w');
            }
            if (is_writable($localfile)) {
                if ($fp = fopen($localfile, 'w')) {
                    fwrite($fp, $response);
                    fclose($fp);
                }
            }
        }
    }

    /**
     * cron event
     */
    public function actionPayments() {
        echo "cron service 4 running\n";
    }
    
    /**
     * cron EP export
     */
    public function actionExport() {
        echo "cron service Export running\n";
        
        \backend\models\EP\Cron::runExport();
        
        echo "cron service Export - done\n";
    }

    /**
     * cron EP import
     */
    public function actionImport() {
        echo "cron service Import running\n";
        
        \backend\models\EP\Cron::runImport();
        
        echo "cron service Import - done\n";
    }
    
    /**
     * cron EP import
     */
    public function actionDatasource($runNow = false) {

        echo "cron service Datasource running\n";

        \backend\models\EP\Cron::runDatasource(boolval($runNow));

        echo "cron service Datasource - done\n";
    }
    
    public function actionRestore()
    {
        global $argv;
        
        define('DIR_FS_BACKUP', \Yii::getAlias('@app') . '/../../admin/backups/');
        define('LOCAL_EXE_GUNZIP', '/bin/gunzip');
        define('LOCAL_EXE_UNZIP', '/usr/bin/unzip');
        include \Yii::getAlias('@app') . '/../../includes/local/configure.php';

        $read_from = $argv[2];

        if (file_exists(DIR_FS_BACKUP . $read_from)) {
            $restore_file = DIR_FS_BACKUP . $read_from;
            $extension = substr($read_from, -3);

            if (($extension == 'sql') || ($extension == '.gz') || ($extension == 'zip')) {
                switch ($extension) {
                    case 'sql':
                        $restore_from = $restore_file;
                        $remove_raw = false;
                        break;
                    case '.gz':
                        $restore_from = substr($restore_file, 0, -3);
                        exec(LOCAL_EXE_GUNZIP . ' ' . $restore_file . ' -c > ' . $restore_from);
                        $remove_raw = true;
                        break;
                    case 'zip':
                        $restore_from = substr($restore_file, 0, -4);
                        exec(LOCAL_EXE_UNZIP . ' ' . $restore_file . ' -d ' . DIR_FS_BACKUP);
                        $remove_raw = true;
                }

                if (isset($restore_from) && file_exists($restore_from) && (filesize($restore_from) > 15000)) {
                    exec('mysql -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p' . DB_SERVER_PASSWORD . ' ' . DB_DATABASE . ' < ' . $restore_from);
                }
            }
        }
    }

}
