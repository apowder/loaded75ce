<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP;

use Yii;
use backend\models\EP\Providers;
use yii\helpers\FileHelper;

class JobZipFile extends JobFile
{

    public function delete()
    {
        $file = $this->getFileSystemName();
        $extractDir = dirname($file).'/'.pathinfo($this->file_name,PATHINFO_FILENAME).'/';
        Directory::findById($this->directory_id);
        FileHelper::removeDirectory($extractDir);

        return parent::delete();
    }

    public function canConfigureExport()
    {
        return false;
    }

    public function canConfigureImport()
    {
        return false;
    }

    public function tryAutoConfigure()
    {

        if ( $this->job_state != self::STATE_CONFIGURED ){
            $this->job_state = self::STATE_CONFIGURED;
            tep_db_query(
                "UPDATE " . TABLE_EP_JOB . " " .
                "SET job_state='" . tep_db_input($this->job_state) . "' " .
                "WHERE job_id='" . $this->job_id . "' "
            );
        }
    }

    public function run(Messages $messages)
    {

        $this->runZip($messages);

    }

    public function runZip(Messages $messages)
    {
        $file = $this->getFileSystemName();
        $extractDir = dirname($file).'/'.pathinfo($this->file_name,PATHINFO_FILENAME).'/';

        FileHelper::createDirectory($extractDir,0777);

        $zip = new \ZipArchive();
        $zip->open($file);

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $stream = $zip->getStream($filename);
            $extractFilename = $extractDir.$filename;
            if ( !is_dir(dirname($extractFilename)) ) {
                FileHelper::createDirectory(dirname($extractFilename),0777, true);
            }

            $writeStream = fopen($extractFilename,'wb');
            while( $data = fread($stream,16*1024) ) {
                fwrite($writeStream, $data);
            }
            fclose($stream);
            fclose($writeStream);
            chmod($extractFilename, 0666);
        }

        $zip->close();

        if ( $this->job_provider!='' && $this->job_provider!='auto' && $this->getDirectory()->cron_enabled ) {
            $providers = new \backend\models\EP\Providers();
            $providerObj = $providers->getProviderInstance($this->job_provider);

            if ( is_object($providerObj) ) {

                if ( method_exists($providerObj,'setExtractDir') ) {
                    $providerObj->setExtractDir($extractDir);
                }
                $messages->command('start_import');
                while ($providerObj->importRow([true], $messages)){

                }
                $messages->progress(100);

                $providerObj->postProcess($messages);

                FileHelper::removeDirectory($extractDir);

                $this->moveToProcessed();

                return;
            }
        }

        $this->getDirectory()->synchronizeDirectories(false);

        Directory::getAll(true);
    }

}