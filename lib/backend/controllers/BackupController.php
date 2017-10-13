<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\controllers;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;

class BackupController extends Sceleton {

    public $acl = ['TEXT_SETTINGS', 'BOX_HEADING_TOOLS', 'BOX_TOOLS_BACKUP'];
    
	public $dir_ok = false;
	public $contents = array();
	public $dir = false;
	public $exec_gzip_available = false;
	public $exec_zip_available = false;
	 
        private function tep_remove($source) {
        global $messageStack, $tep_remove_error;

        if (isset($tep_remove_error))
            $tep_remove_error = false;

        if (is_dir($source)) {
            $dir = dir($source);
            while ($file = $dir->read()) {
                if (($file != '.') && ($file != '..')) {
                    if (is_writeable($source . '/' . $file)) {
                        $this->tep_remove($source . '/' . $file);
                    } else {
                        $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source . '/' . $file), 'error');
                        $tep_remove_error = true;
                    }
                }
            }
            $dir->close();

            if (is_writeable($source)) {
                rmdir($source);
            } else {
                $messageStack->add(sprintf(ERROR_DIRECTORY_NOT_REMOVEABLE, $source), 'error');
                $tep_remove_error = true;
            }
        } else {
            if (is_writeable($source)) {
                unlink($source);
            } else {
                $messageStack->add(sprintf(ERROR_FILE_NOT_REMOVEABLE, $source), 'error');
                $tep_remove_error = true;
            }
        }
    }

    public function __construct($id, $module=null){
            global $messageStack;
            global $languages_id, $language;

            \common\helpers\Translation::init('admin/backup');
		
		if($this->is_exec_available())
		  {
			exec(LOCAL_EXE_GZIP, $output, $return_var);
			if (!$return_var)
			  $this->exec_gzip_available = true; 
		}
			 
		  
		  if($this->is_exec_available())
		  {
			exec(LOCAL_EXE_ZIP, $output, $return_var);
			if (!$return_var)
			  $this->exec_zip_available = true; 
		  }
		
	    if (is_dir(DIR_FS_BACKUP)) {
			if (is_writeable(DIR_FS_BACKUP)) {
			  $this->dir_ok = true;
			  $this->dir = dir(DIR_FS_BACKUP);
			} else {
			  $messageStack->add(ERROR_BACKUP_DIRECTORY_NOT_WRITEABLE, 'error');
			}
		} else {
			$messageStack->add(ERROR_BACKUP_DIRECTORY_DOES_NOT_EXIST, 'error');
		}
		parent::__construct($id, $module);
	}
	
    public function actionIndex() {
        global $languages_id, $language, $messageStack;

        $this->selectedMenu = array('settings', 'tools', 'backup');
        $this->navigation[] = array('link' => Yii::$app->urlManager->createUrl('backup/index'), 'title' => HEADING_TITLE);
		if ( /*($action != 'backup') && */($this->dir) ) {
			$this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('backup/backup').'" class="create_item backup"><i class="icon-file-text"></i>' . IMAGE_BACKUP . '</a>';	
		}
		
		if ( /*($action != 'restorelocal') &&*/ $this->dir ) {
			$this->topButtons[] = '<a href="'.Yii::$app->urlManager->createUrl('backup/restorelocal').'" class="create_item restore"><i class="icon-file-text"></i>' . IMAGE_RESTORE . '</a>';
		}
			
        $this->view->headingTitle = HEADING_TITLE;

        $this->view->backupTable = array(
                array(
                    'title'         => TABLE_HEADING_TITLE,
                    'not_important' => 0
                ),
                array(
                    'title'         => TABLE_HEADING_FILE_DATE,
                    'not_important' => 0
                ),
                array(
                    'title'         => TABLE_HEADING_FILE_SIZE,
                    'not_important' => 0
                ),				
        );
		
		if ($messageStack->size > 0) {
			$this->view->errorMessage = $messageStack->output(true);
			$this->view->errorMessageType = $messageStack->messageType;
		}
	   
		$params = array('backupPath' => TEXT_BACKUP_DIRECTORY . ' ' . DIR_FS_BACKUP);
		if (defined('DB_LAST_RESTORE')) {
			$params['forget'] = TEXT_LAST_RESTORATION . ' ' . DB_LAST_RESTORE . ' <a href="' . tep_href_link(FILENAME_BACKUP.'/forget','') . '">' . TEXT_FORGET . '</a>';
		}		
		
        return $this->render('index', $params);
    }
	
	public function getContents(){
		
		if ($this->dir_ok == true && $this->dir) {
			
			while ($file = $this->dir->read()) {
			  if (!is_dir(DIR_FS_BACKUP . $file) && ($file != '.htaccess')) {
				$this->contents[] = $file;
			  }
			}
			sort($this->contents);	
			$this->dir->close();		
		}
	}
	
	public function getCurrentBackup($entry){

			$file_array['file'] = $entry;
			$file_array['date'] = date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry));
			$file_array['size'] = number_format(filesize(DIR_FS_BACKUP . $entry)) . ' bytes';
			switch (substr($entry, -3)) {
			  case 'zip': $file_array['compression'] = 'ZIP'; break;
			  case '.gz': $file_array['compression'] = 'GZIP'; break;
			  default: $file_array['compression'] = TEXT_NO_EXTENSION; break;
			}
/*
		  if (isset($buInfo) && is_object($buInfo) && ($entry == $buInfo->file)) {
			echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
			$onclick_link = 'file=' . $buInfo->file . '&action=restore';
		  } else {
			echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
			$onclick_link = 'file=' . $entry;
		  }		
*/
		return new \objectInfo($file_array);
	}
	
	public function actionList(){
		
	  $responseList = array();	
		
	  if ($this->dir_ok == true) {
		
		$this->getContents();

		for ($i=0, $n=sizeof($this->contents); $i<$n; $i++) {
		  $entry = $this->contents[$i];

		  $check = 0;

		  
		  $responseList[] = array(Html::a(tep_image(DIR_WS_ICONS . 'file_download.gif', ICON_FILE_DOWNLOAD), tep_href_link(FILENAME_BACKUP. '/download', tep_session_name() . '=' . tep_session_id() . '&file=' . $entry)) . $entry.
								'<input class="cell_identify" type="hidden" value="' . $entry . '">',
								  date(PHP_DATE_TIME_FORMAT, filemtime(DIR_FS_BACKUP . $entry)),
								  number_format(filesize(DIR_FS_BACKUP . $entry)) . 'bytes'
								);
		}
		
	  }
	  
        $response = array(
            'draw'            => $draw,
            'recordsTotal'    => sizeof($this->contents),
            'recordsFiltered' => sizeof($this->contents),
            'data'            => $responseList
        );
        echo json_encode( $response );	  
		
	}
	
	public function actionBackup(){
	  $heading = array();
	  $contents = array();		
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_BACKUP . '</b>');

      $contents = array('form' => tep_draw_form('backup', FILENAME_BACKUP. '/backupnow' , tep_session_name() . '=' . tep_session_id()));
      $contents[] = array('text' => TEXT_INFO_NEW_BACKUP);

      $contents[] = array('text' => '<br>' . tep_draw_radio_field('compress', 'no', true) . ' ' . TEXT_INFO_USE_NO_COMPRESSION);
      if ($this->exec_gzip_available || extension_loaded('zlib')) $contents[] = array('text' => '<br>' . tep_draw_radio_field('compress', 'gzip') . ' ' . TEXT_INFO_USE_GZIP);
      if ($this->exec_zip_available || extension_loaded('zip')) $contents[] = array('text' => tep_draw_radio_field('compress', 'zip') . ' ' . TEXT_INFO_USE_ZIP);

      if ($this->dir_ok == true) {
        $contents[] = array('text' => '<br>' . tep_draw_checkbox_field('download', 'yes') . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      } else {
        $contents[] = array('text' => '<br>' . tep_draw_radio_field('download', 'yes', true) . ' ' . TEXT_INFO_DOWNLOAD_ONLY . '*<br><br>*' . TEXT_INFO_BEST_THROUGH_HTTPS);
      }

	  $contents[] = array('align' => 'left', 'text' => '<input type="submit" class="btn btn-primary" value="' . IMAGE_BACKUP . '">&nbsp;' .
															 '<button class="btn btn-cancel"  onClick="return resetStatement();">' . IMAGE_CANCEL . '</button>'
		
						);
      $box = new \box;
      echo $box->infoBox($heading, $contents);	  
	}
	
	public function actionBackupnow(){
		global $messageStack;
        set_time_limit(0);
        $backup_file = 'db_' . DB_DATABASE . '-' . date('YmdHis') . '.sql';
        
        exec('mysqldump -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p' . DB_SERVER_PASSWORD . ' ' . DB_DATABASE . ' > ' . DIR_FS_BACKUP . $backup_file);

        if (isset($_POST['download']) && ($_POST['download'] == 'yes')) {
          switch ($_POST['compress']) {
            case 'gzip':
              if($this->exec_gzip_available == true)
              {
                exec(LOCAL_EXE_GZIP . ' ' . DIR_FS_BACKUP . $backup_file);
                $backup_file .= '.gz';
              }
              elseif(extension_loaded('zlib'))
              {
                $this->gzCompressFile(DIR_FS_BACKUP . $backup_file);
                unlink(DIR_FS_BACKUP . $backup_file);
                $backup_file .= '.gz';
              }
              break;
            case 'zip':
              if($this->exec_zip_available == true)
              {
                exec(LOCAL_EXE_ZIP . ' -j ' . DIR_FS_BACKUP . $backup_file . '.zip ' . DIR_FS_BACKUP . $backup_file);
                unlink(DIR_FS_BACKUP . $backup_file);
                $backup_file .= '.zip';
              }
              elseif(extension_loaded('zip'))
              {
                $zip = new \ZipArchive();
                if ($zip->open(DIR_FS_BACKUP . $backup_file . '.zip') === TRUE)
                {
                  $zip->addFile(DIR_FS_BACKUP . $backup_file, $backup_file);
                  $zip->close();
                  unlink(DIR_FS_BACKUP . $backup_file);
                  $backup_file .= '.zip';
                }
              }
          }
          header('Cache-Control: none');
          header('Pragma: none');
          header('Content-type: application/x-octet-stream');
          header('Content-disposition: attachment; filename=' . $backup_file);

          readfile(DIR_FS_BACKUP . $backup_file);
          unlink(DIR_FS_BACKUP . $backup_file);

          exit;
        } else {
          switch ($_POST['compress']) {
            case 'gzip':
              if($this->exec_gzip_available == true)
              {
                exec(LOCAL_EXE_GZIP . ' ' . DIR_FS_BACKUP . $backup_file);
              }
              elseif(extension_loaded('zlib'))
              { 
                $this->gzCompressFile(DIR_FS_BACKUP . $backup_file);
                unlink(DIR_FS_BACKUP . $backup_file);
              }  
              break;
            case 'zip':
              if($this->exec_zip_available == true)
              {
                exec(LOCAL_EXE_ZIP . ' -j ' . DIR_FS_BACKUP . $backup_file . '.zip ' . DIR_FS_BACKUP . $backup_file);
                unlink(DIR_FS_BACKUP . $backup_file);
              }
              elseif(extension_loaded('zip'))
              {
                $zip = new \ZipArchive();
                if ($zip->open(DIR_FS_BACKUP . $backup_file . '.zip',\ZIPARCHIVE::CREATE) === TRUE)
                {
                  $zip->addFile(DIR_FS_BACKUP . $backup_file, $backup_file);
                  $zip->close();
                  unlink(DIR_FS_BACKUP . $backup_file);
                }
              }
          }

          $messageStack->add_session(SUCCESS_DATABASE_SAVED, 'success');
        }

        return $this->redirect(Url::toRoute('backup/'));
	}
	
	public function actionDownload(){
		global $messageStack;
        $extension = substr($_GET['file'], -3);

        if ( ($extension == 'zip') || ($extension == '.gz') || ($extension == 'sql') ) {
          if ($fp = fopen(DIR_FS_BACKUP . $_GET['file'], 'rb')) {
            $buffer = fread($fp, filesize(DIR_FS_BACKUP . $_GET['file']));
            fclose($fp);

            header('Cache-Control: none');
            header('Pragma: none');
            header('Content-type: application/x-octet-stream');
            header('Content-disposition: attachment; filename=' . $_GET['file']);

            echo $buffer;

            exit;
          }
        } else {
          $messageStack->add(ERROR_DOWNLOAD_LINK_NOT_ACCEPTABLE, 'error');
        }
		exit();
	}
	
	public function actionRestore(){
	  
	  $file = Yii::$app->request->get('file');

	  $heading = array();
	  $contents = array();	
	  if ($file){
		  $buInfo = $this->getCurrentBackup($file);
		  $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

		  $contents[] = array('text' => \common\helpers\Output::break_string(sprintf(TEXT_INFO_RESTORE, DIR_FS_BACKUP . (($buInfo->compression != TEXT_NO_EXTENSION) ? substr($buInfo->file, 0, strrpos($buInfo->file, '.')) : $buInfo->file), ($buInfo->compression != TEXT_NO_EXTENSION) ? TEXT_INFO_UNPACK : ''), 35, ' '));
		  
		  $contents[] = array('align' => 'center', 'text' => '<br><a href="' . tep_href_link(FILENAME_BACKUP . '/restorenow', 'file=' . $buInfo->file.'&action=restorenow') . '" class="btn btn-primary">' . IMAGE_RESTORE . '</a>&nbsp;<button class="btn btn-cancel" onClick="return resetStatement()">' . IMAGE_CANCEL . '</button>');
		  
	  }

		$box = new \box;
		echo $box->infoBox($heading, $contents);
	
	}
	
	public function actionView(){
	  $heading = array();
	  $contents = array();
	  $file = Yii::$app->request->get('file');		
	  
	  if ($file){
		    $buInfo = $this->getCurrentBackup($file);
		  
			$heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

			$contents[] = array('align' => 'center', 'text' => '<button class="btn btn-primary" onclick="actionFile(\'' . $buInfo->file . '\', \'restore\');">' . IMAGE_RESTORE . '</button> <button class="btn btn-delete" onclick="actionFile(\'' . $buInfo->file . '\', \'delete\');">' . IMAGE_DELETE . '</button>');
			$contents[] = array('text' => '<br>' . TEXT_INFO_DATE . ' ' . $buInfo->date);
			$contents[] = array('text' => TEXT_INFO_SIZE . ' ' . $buInfo->size);
			$contents[] = array('text' => '<br>' . TEXT_INFO_COMPRESSION . ' ' . $buInfo->compression);
      }		

		$box = new \box;
		echo $box->infoBox($heading, $contents);	  
	}
	
	public function actionDelete(){
	  $heading = array();
	  $contents = array();
	  $file = Yii::$app->request->get('file');		
	  
	  if ($file){
		    $buInfo = $this->getCurrentBackup($file);		
 		    $heading[] = array('text' => '<b>' . $buInfo->date . '</b>');

			$contents = array('form' => tep_draw_form('delete', FILENAME_BACKUP . '/deleteconfirm', 'file=' . $buInfo->file));
			$contents[] = array('text' => TEXT_DELETE_INTRO);
			$contents[] = array('text' => '<br><b>' . $buInfo->file . '</b>');
			$contents[] = array('align' => 'center', 'text' => '<br><input type="submit" class="btn btn-delete" value="' . IMAGE_DELETE . '"> <button class="btn btn-cancel" onclick="return resetStatement()">' . IMAGE_CANCEL . '</button>');
      }		

		$box = new \box;
		echo $box->infoBox($heading, $contents);		
	}
	
	public function actionDeleteconfirm() {
        global $messageStack, $tep_remove_error;
        if (strstr($_GET['file'], '..')) {
            return $this->redirect(Url::toRoute('backup/'));
        }
        $this->tep_remove(DIR_FS_BACKUP . '/' . $_GET['file']);

        if (!$tep_remove_error) {
            $messageStack->add_session(SUCCESS_BACKUP_DELETED, 'success');
        }
        return $this->redirect(Url::toRoute('backup/'));
    }

    public function actionRestorenow(){
		global $messageStack;
        set_time_limit(0);
		
		$action = Yii::$app->request->get('action','');

        if ($action == 'restorenow') {
          $read_from =  Yii::$app->request->get('file','');

          if (file_exists(DIR_FS_BACKUP . $read_from)) {
            $restore_file = DIR_FS_BACKUP . $read_from;
            $extension = substr($read_from, -3);

            if ( ($extension == 'sql') || ($extension == '.gz') || ($extension == 'zip') ) {
              switch ($extension) {
                case 'sql':
                  $restore_from = $restore_file;
                  $remove_raw = false;
                  break;
                case '.gz':
                  $restore_from = substr($restore_file, 0, -3);
	                if($this->exec_gzip_available == true)
                  {
                    exec(LOCAL_EXE_GUNZIP . ' ' . $restore_file . ' -c > ' . $restore_from);
                    $remove_raw = true;
                  }
                  elseif(extension_loaded('zlib'))
                  {
                    $this->gzUnCompressFile($restore_file,$restore_from);
                    $remove_raw = true;
                  }
                  break;
                case 'zip':
                  $restore_from = substr($restore_file, 0, -4);
	              if($this->exec_zip_available == true)
                  {
                    exec(LOCAL_EXE_UNZIP . ' ' . $restore_file . ' -d ' . DIR_FS_BACKUP);
                    $remove_raw = true;
                  }
                  elseif(extension_loaded('zip'))
                  {
                    $zip = new \ZipArchive();
                    if ($zip->open($restore_file) === TRUE)
                    {
                      $zip->extractTo(DIR_FS_BACKUP);
                      $zip->close();
                      $remove_raw = true;
                    }
                  }
                  
              }

              if (isset($restore_from) && file_exists($restore_from) && (filesize($restore_from) > 15000)) {
                  
                exec('mysql -h' . DB_SERVER . ' -u' . DB_SERVER_USERNAME . ' -p' . DB_SERVER_PASSWORD . ' ' . DB_DATABASE . ' < ' . $restore_from);
                  
                tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
                tep_db_query("insert into " . TABLE_CONFIGURATION . " values ('', 'Last Database Restore', 'DB_LAST_RESTORE', '" . tep_db_input($read_from) . "', 'Last database restore file', '6', '', '', now(), '', '')");

                if (isset($remove_raw) && ($remove_raw == true)) {
                  unlink($restore_from);
                }

                $messageStack->add_session(SUCCESS_DATABASE_RESTORED, 'success');
                
                return $this->redirect(Url::toRoute('backup/'));
                
                //$fd = fopen($restore_from, 'rb');
                //$restore_query = fread($fd, filesize($restore_from));
                //fclose($fd);
              }
            }
          }
        } elseif ($action == 'restorelocalnow') {
          $sql_file = new \upload('sql_file');
          if ($sql_file->parse() == true) {
            $restore_query = fread(fopen($sql_file->tmp_filename, 'r'), filesize($sql_file->tmp_filename));
            $read_from = $sql_file->filename;
          }
        }

        if (isset($restore_query)) {
          $sql_array = array();
          $sql_length = strlen($restore_query);
          $pos = strpos($restore_query, ';');
          for ($i=$pos; $i<$sql_length; $i++) {
            if ($restore_query[0] == '#') {
              $restore_query = ltrim(substr($restore_query, strpos($restore_query, "\n")));
              $sql_length = strlen($restore_query);
              $i = strpos($restore_query, ';')-1;
              continue;
            }
            if ($restore_query[($i+1)] == "\n") {
              for ($j=($i+2); $j<$sql_length; $j++) {
                if (trim($restore_query[$j]) != '') {
                  $next = substr($restore_query, $j, 6);
                  if ($next[0] == '#') {
// find out where the break position is so we can remove this line (#comment line)
                    for ($k=$j; $k<$sql_length; $k++) {
                      if ($restore_query[$k] == "\n") break;
                    }
                    $query = substr($restore_query, 0, $i+1);
                    $restore_query = substr($restore_query, $k);
// join the query before the comment appeared, with the rest of the dump
                    $restore_query = $query . $restore_query;
                    $sql_length = strlen($restore_query);
                    $i = strpos($restore_query, ';')-1;
                    continue 2;
                  }
                  break;
                }
              }
              if ($next == '') { // get the last insert query
                $next = 'insert';
              }
              if ( (preg_match('/create/i', $next)) || (preg_match('/insert/i', $next)) || (preg_match('/drop t/i', $next)) ) {
                $next = '';
                $sql_array[] = substr($restore_query, 0, $i);
                $restore_query = ltrim(substr($restore_query, $i+1));
                $sql_length = strlen($restore_query);
                $i = strpos($restore_query, ';')-1;
              }
            }
          }

          tep_db_query("drop table if exists address_book, address_format, banners, banners_history, categories, categories_description, configuration, configuration_group, counter, counter_history, countries, currencies, customers, customers_basket, customers_basket_attributes, customers_info, languages, manufacturers, manufacturers_info, orders, orders_products, orders_status, orders_status_history, orders_products_attributes, orders_products_download, products, products_attributes, products_attributes_download, prodcts_description, products_options, products_options_values, products_options_values_to_products_options, products_to_categories, reviews, reviews_description, sessions, specials, tax_class, tax_rates, geo_zones, whos_online, zones, zones_to_geo_zones");

          for ($i=0, $n=sizeof($sql_array); $i<$n; $i++) {
            tep_db_query($sql_array[$i]);
          }

          tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");
          tep_db_query("insert into " . TABLE_CONFIGURATION . " values ('', 'Last Database Restore', 'DB_LAST_RESTORE', '" . tep_db_input($read_from) . "', 'Last database restore file', '6', '', '', now(), '', '')");

          if (isset($remove_raw) && ($remove_raw == true)) {
            unlink($restore_from);
          }

          $messageStack->add_session(SUCCESS_DATABASE_RESTORED, 'success');
        }

        return $this->redirect(Url::toRoute('backup/'));
		
	}
	
	public function actionForget(){
		global $messageStack;
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key = 'DB_LAST_RESTORE'");

        $messageStack->add_session(SUCCESS_LAST_RESTORE_CLEARED, 'success');

        return $this->redirect(Url::toRoute('backup/'));
		
	}
	
	public function actionRestorelocal(){
		
	  $heading = array();
	  $contents = array();		
	  
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_RESTORE_LOCAL . '</b>');

      $contents = array('form' => tep_draw_form('restore', FILENAME_BACKUP. '/restorenow', 'action=restorelocalnow', 'post', 'enctype="multipart/form-data"'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL . '<br><br>' . TEXT_INFO_BEST_THROUGH_HTTPS);
      $contents[] = array('text' => '<br>' . tep_draw_file_field('sql_file'));
      $contents[] = array('text' => TEXT_INFO_RESTORE_LOCAL_RAW_FILE);
      $contents[] = array('align' => 'center', 'text' => '<br><input type="submit" value="' . IMAGE_RESTORE . '" class="btn btn-primary">&nbsp;<button class="btn btn-cancel" onclick="return resetStatement()">' . IMAGE_CANCEL . '</button>');

      $box = new \box;
      echo $box->infoBox($heading, $contents);		
	}
	
	function is_exec_available() {
	  $available = true;
	  if (ini_get('safe_mode'))
	  {
		$available = false;
	  } 
	  else
	  {
		$d = ini_get('disable_functions');
		$s = ini_get('suhosin.executor.func.blacklist');
		if ("$d$s")
		{
		  $array = preg_split('/,\s*/', "$d,$s");
		  if (in_array('exec', $array))
		  {
			$available = false;
		  }
		}
	  }
	  return $available;
	}

	function gzCompressFile($source, $level = 9){ 
		$dest = $source . '.gz'; 
		$mode = 'wb' . $level; 
		$error = false; 
		if ($fp_out = gzopen($dest, $mode)) { 
			if ($fp_in = fopen($source,'rb')) { 
				while (!feof($fp_in)) 
					gzwrite($fp_out, fread($fp_in, 4096)); 
				fclose($fp_in); 
			} else {
				$error = true; 
			}
			gzclose($fp_out); 
		} else {
			$error = true; 
		}
		if ($error)
			return false; 
		else
			return $dest; 
	}
	function gzUnCompressFile($srcName, $dstName) {
		$sfp = gzopen($srcName, "rb");
		$fp = fopen($dstName, "w");

		while (!gzeof($sfp)) {
			$string = gzread($sfp, 4096);
			fwrite($fp, $string, strlen($string));
		}
		gzclose($sfp);
		fclose($fp);
	}	

}
