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

class Messages extends \yii\base\Object {

    public $job_id;
    public $output = 'www';
    
    public function __construct($config = array()) {
        parent::__construct($config);
        $this->setEpFileId( $this->job_id );
    }
    
    public function setEpFileId($id)
    {
        $this->job_id = $id;
        if ( $this->job_id ) {
            tep_db_query("DELETE FROM ".TABLE_EP_LOG_MESSAGES." WHERE job_id='".(int)$this->job_id."'");
        }
    }
    
    public function info($text){
        if ( $this->output=='www' ) {
            echo '<script>window.parent.uploader(\'message\', '.json_encode($text).')</script>';
            echo str_repeat(' ',2048); echo "\n";
            ob_flush();
            flush();
        }elseif( $this->output=='console' ){
            echo "$text\n";
        }
        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_LOG_MESSAGES, array(
                'job_id' => $this->job_id,
                'message_time' => 'now()',
                'message_text' => $text,
            ));
        }
    }

    public function progress($percentDone, $timeString='')
    {
        if ( $this->output=='www' ) {
            if( empty($timeString) ) {
                echo '<script>window.parent.uploader(\'progress\', ' . json_encode(round($percentDone)) . ')</script>';
            }else{
                echo '<script>window.parent.uploader(\'progress\', ' . json_encode(round($percentDone)) . ', '.json_encode($timeString).')</script>';
            }
            echo str_repeat(' ',2048); echo "\n";
            ob_flush();
            flush();
        }elseif($this->output=='console'){
            if( empty($timeString) ) {
                echo " =>".round($percentDone). "%\n";
            }else{
                echo " =>".round($percentDone). "% {$timeString}\n";
            }
        }
        if ( $this->job_id ) {
            tep_db_perform(TABLE_EP_JOB,array(
                'process_progress' => round($percentDone),
                'job_state' => round($percentDone)==100?Job::STATE_PROCESSED:Job::PROCESS_STATE_IN_PROGRESS,
            ), 'update', "job_id='".$this->job_id."'");
        }
    }

    public function command($command)
    {
        if ( $this->output=='www' ) {
            echo '<script>window.parent.uploader(\''.$command.'\')</script>';
            echo str_repeat(' ',2048); echo "\n";
            ob_flush();
            flush();
        }
    }
}