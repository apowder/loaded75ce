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

class Job extends \yii\base\Object
{
    public $job_id;
    public $directory_id;
    public $file_name;
    public $file_name_internal;
    public $file_time;
    public $file_size = false; // for check upload complete -- auto import
    public $direction = 'import';
    public $run_frequency = -1;
    public $run_time = '00:00';
    public $last_cron_run;
    public $process_progress = 0;
    public $job_provider;
    public $job_state;
    public $job_configure;

    const STATE_UPLOAD_IN_PROGRESS = 'upload';
    const STATE_UPLOADED = 'uploaded';
    const STATE_NOT_CONFIGURED = 'not_configured';
    const STATE_CONFIGURED = 'configured';
    const STATE_PROCESSED = 'processed';

    const PROCESS_STATE_PENDING = 'pending';
    const PROCESS_STATE_CONFIGURED = 'configured';
    const PROCESS_STATE_IN_PROGRESS = 'in_progress';
    const PROCESS_STATE_COMPLETE = 'complete';

    /**
     * 
     * @param type $id
     * @return boolean|\self
     */
    static public function loadById($id)
    {
        $job_lookup_r = tep_db_query("SELECT * FROM " . TABLE_EP_JOB . " WHERE job_id='" . (int)$id . "' ");
        if (tep_db_num_rows($job_lookup_r) > 0) {
            $job_record = tep_db_fetch_array($job_lookup_r);
            if (!empty($job_record['job_configure'])) {
                $job_record['job_configure'] = json_decode($job_record['job_configure'], true);
            }
            if (!is_array($job_record['job_configure'])) $job_record['job_configure'] = array();
            if ($job_record['direction']=='datasource') {
                return new JobDatasource($job_record);
            }elseif ($job_record['direction']=='import_zip'){
                return new JobZipFile($job_record);
            } else {
                return new JobFile($job_record);
            }
            //return new self($job_record);
        }
        return false;
    }
    
    public function delete()
    {
        $filename = $this->getFileSystemName();
        if ( is_file($filename) ){
            @unlink($filename);
        }
tep_db_query("DELETE FROM " . TABLE_EP_JOB . " WHERE job_id='" . $this->job_id . "'");
tep_db_query("DELETE FROM " . TABLE_EP_LOG_MESSAGES . " WHERE job_id='" . $this->job_id . "'");
        return true;
    }
    
    /**
     * @return Directory
     */
    public function getDirectory()
    {
        return Directory::findById($this->directory_id);
    }

    public function getFileSystemName()
    {
        if ( strpos($this->file_name, 'php://')===0 ) return $this->file_name;
        $directory = $this->getDirectory();
        $ep_files_dir = $directory->filesRoot();
        return $ep_files_dir.(empty($this->file_name_internal)?$this->file_name:$this->file_name_internal);
    }

    public function getFullFilename()
    {
        if ( strpos($this->file_name, 'php://')===0 ) return $this->file_name;
        $directory = $this->getDirectory();
        $ep_files_dir = $directory->filesRoot();
        return $ep_files_dir.$this->file_name;
    }
    
    public function getFileInfo()
    {
        $filename = $this->getFileSystemName();
        return [
            'pathFilename' => $this->getFullFilename(),
            'fileSystemName' => $filename,
            'filename' => $this->file_name,
            'fileSize' => is_file($filename)?filesize($filename):false,
            'fileTime' => is_file($filename)?filemtime($filename):0,
        ];
    }
   
    public function checkRequirements()
    {
       
    }

    public function canRemove()
    {
        return true;
    }

    public function canSetupRunFrequency()
    {
        if ($this->job_state == self::STATE_UPLOAD_IN_PROGRESS) return false;
        $directory = $this->getDirectory();
        return $directory->cron_enabled && (in_array($directory->directory_type, ['import','export','datasource']) );
    }

    public function canRun()
    {
        $directory = $this->getDirectory();
        if ($directory->cron_enabled) {
            return false;
        }
        return !($this->job_state == self::STATE_NOT_CONFIGURED || $directory->directory_type != 'import');
    }

    public function canConfigureExport()
    {
        $directory = $this->getDirectory();
        return $directory->directory_type == 'export' && in_array($this->job_state, [self::STATE_CONFIGURED, self::STATE_NOT_CONFIGURED, self::STATE_PROCESSED]);
    }

    public function canConfigureImport()
    {
        $directory = $this->getDirectory();
        return $directory->directory_type == 'import' && in_array($this->job_state, [self::STATE_CONFIGURED, self::STATE_NOT_CONFIGURED, self::STATE_UPLOADED, self::STATE_PROCESSED]);
    }

    public function watchFileChanges()
    {
        clearstatcache();

        $fileInfo = $this->getFileInfo();
        $touchData = [
            'file_time' => $fileInfo['fileTime'],
            'file_size' => $fileInfo['fileSize'],
        ];
        if ( $touchData['file_size']==$this->file_size /*&& $touchData['file_time']==$this->file_time*/ ) {
            if ( $this->job_state==self::STATE_UPLOAD_IN_PROGRESS ) {
                $touchData['job_state'] = self::STATE_UPLOADED;

                if ( empty($this->file_name_internal) ) {
                    $file_name_internal = md5(time() . '' . $this->file_name);
                    $directory = $this->getDirectory();
                    if (rename($directory->filesRoot() . $this->file_name, $directory->filesRoot() . $file_name_internal)) {
                        $touchData['file_name_internal'] = $file_name_internal;
                    }
                }
            }
        }else {
            $touchData['job_state'] = self::STATE_UPLOAD_IN_PROGRESS;
        }

        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_JOB, $touchData, 'update', "job_id='" . $this->job_id . "'");
        }
        foreach($touchData as $key=>$val) {
            $this->{$key} = $val;
        }
    }

    public function checkUploadFinish()
    {
        clearstatcache();

        $fileInfo = $this->getFileInfo();
        $touchData = [
            'file_time' => $fileInfo['fileTime'],
            'file_size' => $fileInfo['fileSize'],
        ];
        if ( $touchData['file_size']==$this->file_size /*&& $touchData['file_time']==$this->file_time*/ ) {
            $touchData['job_state'] = self::STATE_UPLOADED;

            $file_name_internal = md5(time().''.$this->file_name);
            $directory = $this->getDirectory();
            if ( rename( $directory->filesRoot().$this->file_name, $directory->filesRoot().$file_name_internal ) ) {
                $touchData['file_name_internal'] = $file_name_internal;
            }
        }
        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_JOB, $touchData, 'update', "job_id='" . $this->job_id . "'");
        }
        foreach($touchData as $key=>$val) {
            $this->{$key} = $val;
        }

        return $this->job_state==self::STATE_UPLOADED;
    }

    public function tryAutoConfigure()
    {
        if ( empty($this->job_type) || $this->job_type=='auto' ) {

            $providers = new Providers();

            $reader = new Reader\CSV([
                'filename' => $this->getFileSystemName(),
            ]);
            $fileColumns = $reader->readColumns();
            $possibleProviders = $providers->bestMatch($fileColumns);
            reset($possibleProviders);
            if (current($possibleProviders) == 1) {
                $fileProvider = current(array_keys($possibleProviders));
                $this->job_state = self::STATE_CONFIGURED;
                $this->job_type = $fileProvider;
                if ($this->job_id) {
                    tep_db_query(
                        "UPDATE " . TABLE_EP_JOB . " " .
                        "SET job_state='" . tep_db_input($this->job_state) . "', job_type='" . tep_db_input($fileProvider) . "' " .
                        "WHERE job_id='" . $this->job_id . "' "
                    );
                }
            }
        }else{
            if ( $this->job_state != self::STATE_CONFIGURED ){
                $this->job_state = self::STATE_CONFIGURED;
                tep_db_query(
                    "UPDATE " . TABLE_EP_JOB . " " .
                    "SET job_state='" . tep_db_input($this->job_state) . "' " .
                    "WHERE job_id='" . $this->job_id . "' "
                );
            }
        }
    }
   
    public function run(Messages $messages)
    {

        if ( $this->direction=='import' ) {
            $this->runImport($messages);
        } elseif ( $this->direction=='export' ) {
            $this->runExport($messages);
        }
    }

    public function runExport($messages)
    {
        $selected_columns = false;
        $filter = [];
        if ( isset($this->job_configure['export']) && is_array($this->job_configure['export']) ) {
            if ( isset($this->job_configure['export']['columns']) ) {
                $selected_columns = $this->job_configure['export']['columns'];
            }
            if ( isset($this->job_configure['export']['filter']) && is_array($this->job_configure['export']['filter']) ) {
                $filter = $this->job_configure['export']['filter'];
            }
        }

        $writer = Yii::createObject([
            'class' => 'backend\\models\\EP\\Writer\\'.$this->job_configure['export']['format'],
            'filename' => $this->getFileSystemName(),
        ]);

//        $writer = new EP\Writer\CSV([
//            'filename' => 'php://output',
//            //'output_encoding' => 'UTF-8',
//        ]);

        $providers = new Providers();
        $exportProviderObj = $providers->getProviderInstance($this->job_type);
        if ( !is_object($exportProviderObj) ) {
            die;
        }
        $exportColumns = $exportProviderObj->getColumns();

        if ( is_array($selected_columns) && count($selected_columns)>0 ) {
            $_selected = array();
            foreach($selected_columns as $selected_column){
                if ( !isset($exportColumns[$selected_column]) ) continue;
                $_selected[$selected_column] = $exportColumns[$selected_column];
            }
            $exportColumns = $_selected;
        }

        $writer->setColumns($exportColumns);

        $exportProviderObj->prepareExport(array_keys($exportColumns), $filter);
        while($providerData = $exportProviderObj->exportRow()){
            $writer->write($providerData);
        }
    }
    
    public function runImport(Messages $messages)
    {
        
        if( (empty($this->job_type) || $this->job_type=='auto' ) ){
            throw new Exception('Need select job type');
        }
        
        $providers = new Providers();

        $providerObj = $providers->getProviderInstance($this->job_type);

        if ( $providerObj instanceof Provider\Images ) {
            $directory = Directory::findById($this->directory_id);
            if ( is_object($directory) ) {
                $providerObj->setImagesDirectory($directory->filesRoot(Directory::TYPE_IMAGES));
            }
        }

        //$messages->setEpFileId($this->job_id);
//        $messages = new EP\Messages([
//            'job_id' => $job_record->job_id,
//        ]);
        $messages->command('start_import');
        //$dir = rtrim(\Yii::getAlias($this->ep_work_dir), '/');
        $filename = $this->getFileSystemName();
        try {
            $reader = new Reader\CSV([
                'filename' =>  $filename,
                //'input_encoding' => 'ISO-8859-15',
            ]);

            $transform = new Transform();
            $transform->setProviderColumns( $providerObj->getColumns() );
            if ( isset($this->job_configure['remap_columns']) && is_array($this->job_configure['remap_columns']) ) {
                $transform->setTransformMap($this->job_configure['remap_columns']);
            }

            $started = time();
            $progressRowInform = 100;
            $rowCounter = 0;
            while ($data = $reader->read()) {
                $data = $transform->transform($data);
                $providerObj->importRow($data, $messages);
                $rowCounter++;
                if (($rowCounter % $progressRowInform)==0) {
                    $percentProgress = $reader->getProgress();
                    $currentTime = time();
                    if ( $percentProgress==0 ) {
                        $secondsForJob = round(($currentTime - $started) * 100 / 0.0001);
                    }else{
                        $secondsForJob = round(($currentTime - $started) * 100 / $percentProgress);
                    }
                    $timeLeft = 'Time left: '.date('H:i:s',max(0,$secondsForJob - ($currentTime-$started)) );
                    if ( $currentTime!=$started ) {
                        $timeLeft .= ' ' . number_format($rowCounter / ($currentTime - $started), 1, '.', '') . ' Lines per second';
                    }

                    $messages->progress($percentProgress, $timeLeft);

                    set_time_limit(30);
                }
            }
            $messages->progress(100);

            $providerObj->postProcess($messages);

        }catch (\Exception $ex){
            //$messages->info($ex->getMessage());
            throw $ex;
        }
    }
    
    public function haveMessages()
    {
        $haveMessages = false;
        if ($this->job_id) {
            $check = tep_db_fetch_array(tep_db_query(
                "SELECT COUNT(*) AS c " .
                "FROM " . TABLE_EP_LOG_MESSAGES . " " .
                "WHERE job_id='" . $this->job_id . "'"
            ));
            $haveMessages = $check['c'] > 0;
        }
        return $haveMessages;
    }
    
    public function moveToProcessed()
    {
        $directory = $this->getDirectory();

        $processedDirectory = $directory->getProcessedDirectory();

        if ( !$processedDirectory ) return false;

        $this->job_state = self::STATE_PROCESSED;
        $this->directory_id = $processedDirectory->directory_id;

        tep_db_query(
            "UPDATE ".TABLE_EP_JOB." ".
            "SET job_state='".tep_db_input($this->job_state)."', directory_id='".intval($this->directory_id)."' ".
            "WHERE job_id='".intval($this->job_id)."'"
        );
    }

}
