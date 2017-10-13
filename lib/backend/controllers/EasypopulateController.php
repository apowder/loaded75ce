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
use yii\helpers\FileHelper;
use backend\models\EP;
use yii\i18n\Formatter;


class EasypopulateController extends Sceleton
{

    public $acl = ['BOX_HEADING_CATALOG', 'BOX_CATALOG_EASYPOPULATE'];

    public $import_folder = 'import';

    private $messageStack;

    /**
     *
     * @var EP/Directory 
     */
    public $currentDirectory;
    public $selectedRootDirectoryId;

    public function __construct($id, $module = null){
        parent::__construct($id, $module);

        $request_directory = Yii::$app->request->post('directory_id');
        if ( empty($request_directory) ){
            $request_directory = Yii::$app->request->get('directory_id',1);
        }
        foreach( EP\Directory::getAll() as $Directory ) {
            if ( empty($this->currentDirectory) ) {
                $this->currentDirectory = $Directory;
                $this->selectedRootDirectoryId = $Directory->directory_id;
            }
            if ( $request_directory==$Directory->directory_id ) {
                $this->currentDirectory = $Directory;
                $this->selectedRootDirectoryId = $Directory->directory_id;
                break;
            }
        }
        if ( $this->currentDirectory->parent_id ) {
            $walkDirectory = $this->currentDirectory;
            while ( $walkDirectory = $walkDirectory->getParent()) {
                $this->selectedRootDirectoryId = $walkDirectory->directory_id;
            }
        }
    }


    public function actionIndex()
    {
        global $languages_id, $language, $messageStack;

        \common\helpers\Translation::init('admin/easypopulate');

        $this->messageStack = new \messageStack;

        $this->selectedMenu       = array( 'catalog', 'easypopulate' );
        $this->navigation[]       = array( 'link' => Yii::$app->urlManager->createUrl( 'easypopulate/index' ), 'title' => EP_HEDING_TITLE );
        if ( $this->selectedRootDirectoryId==5 && count(EP\DataSources::getAvailableList())>0 ) {
            $this->topButtons[] = '<a href="' . Yii::$app->urlManager->createUrl(['easypopulate/create-data-source']) . '" class="create_item js-create-datasource"><i class="icon-file-text"></i>' . 'Create data source' . '</a>';
        }
        $this->view->headingTitle = EP_HEDING_TITLE;

        if ( Yii::$app->request->isPost ) {
            $datasource = tep_db_prepare_input(Yii::$app->request->post('datasource'));

            foreach ( $datasource as $dsKey=>$dsSettings ) {
                if ( !class_exists('backend\\models\\EP\\Datasource\\'.$dsKey) ) continue;
                $dsSettings = call_user_func_array(
                    ['backend\\models\\EP\\Datasource\\'.$dsKey, 'beforeSettingSave'],
                    [$dsSettings]
                );

                $settings = json_encode($dsSettings);

                tep_db_query(
                    "INSERT INTO ep_datasources (code, settings) ".
                    "VALUES ".
                    " ('".tep_db_input($dsKey)."', '".tep_db_input($settings)."') ".
                    "ON DUPLICATE KEY UPDATE settings='".tep_db_input($settings)."'"
                );
            }
            $this->redirect( Yii::$app->urlManager->createUrl(['easypopulate/index']+Yii::$app->request->get()) );
        }
        $datasourceSettings = [];
        $get_ds_settings_r = tep_db_query("SELECT * FROM ep_datasources");
        if ( tep_db_num_rows($get_ds_settings_r)>0 ) {
            while( $_dsSetting = tep_db_fetch_array($get_ds_settings_r) ){
                if ( class_exists('backend\\models\\EP\\Datasource\\'.$_dsSetting['code']) ) {
                    $datasourceSettings[$_dsSetting['code']] = call_user_func_array(
                        ['backend\\models\\EP\\Datasource\\'.$_dsSetting['code'],'configureArray'],
                        [json_decode($_dsSetting['settings'], true)]
                    );
                }
            }

        }

        /*
        $warn_products   = '';
        $warn_caregories = '';
        $office_limit    = 31998;
        $tpd_r           = tep_db_query( "SELECT max(length(products_description)) as pd_len, max(length(products_head_desc_tag)) as hd_len, max(length(products_head_keywords_tag )) as hk_len FROM " . TABLE_PRODUCTS_DESCRIPTION );
        $tpd_a           = tep_db_fetch_array( $tpd_r );
        foreach( $tpd_a as $col => $max_length ) {
            if( (int) $max_length > (int) $office_limit ) $warn_products = TEXT_WARN_LONGTEXT_EDIT;
        }
        $tpd_r = tep_db_query( "SELECT max(length(categories_description)) as cd_len, max(length(categories_head_desc_tag)) as hd_len, max(length(categories_head_keywords_tag)) as hk_len FROM " . TABLE_CATEGORIES_DESCRIPTION );
        $tpd_a = tep_db_fetch_array( $tpd_r );
        foreach( $tpd_a as $col => $max_length ) {
            if( (int) $max_length > (int) $office_limit ) $warn_caregories = TEXT_WARN_LONGTEXT_EDIT;
        }
        */
        if ( !is_dir(Yii::getAlias('@ep_files')) ) {
            $messageStack->add(sprintf(ERROR_DATA_DIRECTORY_MISSING, Yii::getAlias('@ep_files')));
        }elseif( !is_writeable(Yii::getAlias('@ep_files')) ){
            $messageStack->add(sprintf(ERROR_DATA_DIRECTORY_NOT_WRITEABLE, Yii::getAlias('@ep_files')));
        }
        $this->view->importFolder = $this->currentDirectory->filesRoot(EP\Directory::TYPE_IMAGES);
        if (!file_exists($this->view->importFolder)) {
            @mkdir($this->view->importFolder, 0777, true);
        }

        \common\helpers\Translation::init('admin/categories');
        $message_stack_output = '';
        if ($messageStack->size > 0) {
          $message_stack_output = $messageStack->output();
        }

        $providers = new EP\Providers();

        $importProviders = $providers->pullDownVariants('Import', [
            'items'=>['' => TEXT_OPTION_AUTO,],
            'options' => [ 'class'=> 'form-control' ],
        ]);

        $export_options = $providers->pullDownVariants('Export',[
            'selection' => '',
            'items' => [
                '' => PULL_DOWN_DEFAULT,
            ],
            'options' => [
                'class' => 'form-control',
                'required' => "true",
                'options' => [
                ],
            ],
        ];

        foreach($providers->getAvailableProviders('Export','') as $exportProviderInfo) {
            $export_options['items'][$exportProviderInfo['key']] = $exportProviderInfo['name'];
            $options_data = [];
            if ( !isset($exportProviderInfo['exportDisableSelectFields']) || !$exportProviderInfo['exportDisableSelectFields'] ){
                $options_data['data-select-fields'] = 'true';
            }
            if ( isset( $exportProviderInfo['exportFilters'] ) && count($exportProviderInfo['exportFilters'])>0 ) {
                foreach ($exportProviderInfo['exportFilters'] as $filterCode){
                    $options_data['data-allow-select-'.$filterCode] = 'true';
                }
            }
            if ( count($options_data)>0 ) {
                $export_options['options']['options'][$exportProviderInfo['key']] = $options_data;
            }
        }

        $download_format_down_data = [
            'selection' => 'CSV',
            'items' => [
                '' => PULL_DOWN_DEFAULT,
                'CSV' => TEXT_OPTION_EXPORT_CSV,
                'ZIP' => TEXT_OPTION_EXPORT_ZIP,
            ]
        ];

        $check_dev_admin = tep_db_fetch_array(tep_db_query("SELECT COUNT(*) AS c FROM ".TABLE_ADMIN." WHERE admin_id='".(int)$_SESSION['login_id']."' AND admin_email_address LIKE '%@holbi%'"));

        $directories = [];
        foreach( EP\Directory::getAllRoots() as $Directory ) {
            if ( $Directory->parent_id!=0 ) continue;
            /**
             * @var EP\Directory $Directory
             */
            $directories[] = [
                'id' => $Directory->directory_id,
                'text' =>  $Directory->name,
                'link' => Yii::$app->urlManager->createUrl(['easypopulate/','directory_id'=>$Directory->directory_id]),
            ];
        }
        
        $order_year_start = $order_year_end = date('Y');
        $order_start_from_r = tep_db_query("SELECT MIN(YEAR(date_purchased)) AS min_year FROM ".TABLE_ORDERS);
        if ( tep_db_num_rows($order_start_from_r)>0 ) {
            $order_start_from = tep_db_fetch_array($order_start_from_r);
            $order_year_start = $order_start_from['min_year'];
        }
        $order_year_range = [];
        for( $i=$order_year_start; $i<=$order_year_end; $i++ ) {
            $order_year_range[(int)$i] = (int)$i;
        }
        $order_month_range = array_map(function($i){
            return $i==0?TEXT_ALL:sprintf('%02s',$i);
        },range(0,12));
        
        $filter_defaults = [
            'order' => [
                'date_type_range' => [
                    'value' => 'presel',
                ],
                'year' => [
                    'value' => date('Y'),
                    'items' => $order_year_range,
                ],
                'month' => [
                    'items' => $order_month_range,
                ],
                'interval' =>[
                    'value' => '',
                    'items' => [
                        '' => TEXT_ALL,
                        '1' => TEXT_TODAY,
                        'week' => TEXT_WEEK,
                        'month' => TEXT_THIS_MONTH,
                        'year' => TEXT_THIS_YEAR,
                        '3' => TEXT_LAST_THREE_DAYS,
                        '7' => TEXT_LAST_SEVEN_DAYS,
                        '14' => TEXT_LAST_FOURTEEN_DAYS,
                        '30' => TEXT_LAST_THIRTY_DAYS,
                    ],
                ],
            ]
        ];

        $view_data = array(
            'current_directory_id' => $this->currentDirectory->directory_id,
            'currentDirectory' => $this->currentDirectory,
            'selectedRootDirectoryId' => $this->selectedRootDirectoryId,
            'directories' => $directories,
            'message_stack_output' => $message_stack_output,
            'show_data_management' => $check_dev_admin['c']>0 /*&& isset($_GET['remove'])*/,
            'show_export_page' => $this->currentDirectory->directory_type == EP\Directory::TYPE_EXPORT,
            'show_import_page' => $this->currentDirectory->directory_type == EP\Directory::TYPE_IMPORT,
			'importProviders' => $importProviders,            
			'upload_options' => $upload_options,
            'export_options' => $export_options,
            'easypopulate_command_action' => tep_href_link( FILENAME_EASYPOPULATE . '/command'),
            'upload_form_action_ajax' => Yii::$app->urlManager->createUrl(['easypopulate/upload-file-ajax','directory_id'=>$this->currentDirectory->directory_id]),
            'job_list_url' => Yii::$app->urlManager->createUrl(['easypopulate/files-list']),
            'get_job_messages_popup_action' => Yii::$app->urlManager->createUrl(['easypopulate/job-log-messages','directory_id'=>$this->currentDirectory->directory_id]),
            'upload_max_part_size' => 900*1024,
            'download_format_down_data' => $download_format_down_data,
            'download_form_action' => Yii::$app->urlManager->createUrl(['easypopulate/process-export','directory_id'=>$this->currentDirectory->directory_id]),
            'get_fields_action' => tep_href_link( FILENAME_EASYPOPULATE . '/get-fields'),
            'refresh_filter_action' => tep_href_link( FILENAME_EASYPOPULATE . '/refresh-filters'),
            //'select_filter_categories' => tep_draw_pull_down_menu('filter[category_id]', \common\helpers\Categories::get_category_tree(0,'','','',false,true), 0, ''),
            'select_filter_categories_auto_complete_url' => \Yii::$app->urlManager->createUrl(['easypopulate/get-categories-list']),
            'select_filter_properties' => tep_draw_pull_down_menu('filter[properties_id]', \common\helpers\Properties::get_properties_tree(0,'','',false), 0, ''),
            'filter_defaults' => $filter_defaults,
            'js_messages' => json_encode([
                'file_changed' => TEXT_FILE_CHANGED,
                'file_upload' => TEXT_FILE_UPLOAD,
                'file_uploaded' => TEXT_FILE_UPLOADED,
            ]),
        );

        return $this->render( 'index', $view_data );
    }

    public function actionCreateDirectory()
    {
        $this->layout = false;
        
        if ( Yii::$app->request->isPost ) {
            
        }else{
            $directoryTypeVariants = [
                'import' => 'Import',
                'export' => 'Export',
            ];
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=>'Job messages',
                    'message' => $this->render('create-directory',['directoryTypeVariants'=>$directoryTypeVariants]),
                    'buttons' => [
                        'cancel' => [
                            'label' => TEXT_OK,
                            'className' => 'btn-primary',
                        ]
                    ]
                ]
            ];
        }

    }

    public function actionCreateDataSource()
    {
        $this->layout = false;

        if ( Yii::$app->request->isPost ) {
            $new_datasource = Yii::$app->request->post('new_datasource');

            EP\DataSources::add($new_datasource);

        }else{
            $availableSources = [];
            foreach (EP\DataSources::getAvailableList() as $ds){
                $availableSources[$ds['class']] = $ds['name'];
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=>'Create data source',
                    'message' => $this->render('create-data-source', ['availableSourcesVariants'=>$availableSources]),
                    'buttons' => [
                        'confirm' => [
                            'label' => TEXT_OK,
                            'className' => 'btn-primary',
                        ]
                    ]
                ]
            ];
        }
    }

    public function actionConfigureAutoDatasourceDirectory()
    {
        \common\helpers\Translation::init('admin/easypopulate');

        $this->layout = false;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;

        $directory = EP\Directory::loadById($id);
        if ( !$directory ){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Directory not found',
                ]
            ];
        }
        $dataSourceObj = EP\DataSources::getByName($directory->directory);
        if ( !$dataSourceObj ) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Datasource not found',
                ]
            ];
        }
        $dataSourceClass = substr(get_class($dataSourceObj), strrpos(get_class($dataSourceObj),'\\')+1);

        $providers = new EP\Providers();
        $providersList = $providers->pullDownVariants('Datasource',[], $dataSourceClass);

        $formatReaders = [
            'selection' => '',
            'items' => [
                //'' => PULL_DOWN_DEFAULT,
            ]
        ];

        foreach( EP\DataSources::getAvailableList() as $dataSource){
            if ( $dataSourceClass != $dataSource['class'] ) continue;
            $formatReaders['items'][$dataSource['class']] = $dataSource['name'];
        }
        $launchFrequency = [
            'selection' => '-1',
            'items' => [
                -1 => TEXT_DISABLED,
                TEXT_RUN_ONCE => [
                    1 => TEXT_IMMEDIATELY,
                    0 => TEXT_DEFINED_TIME,
                ],
                TEXT_RUN_PERIODICALLY => [
                    5 => TEXT_EVERY_5_MINUTES,
                    15 => TEXT_EVERY_15_MINUTES,
                    30 => TEXT_EVERY_30_MINUTES,
                    60 => TEXT_EVERY_HOUR,
                    1440 => TEXT_EVERY_DAY,
                ]
            ]
        ];

        if ( Yii::$app->request->isPost ) {
            $directory_config_input = tep_db_prepare_input(Yii::$app->request->post('directory_config', []));
            $directory_config = [];
            foreach($directory_config_input as $directory_file_config){
                if ( empty($directory_file_config['filename_pattern']) ) {
                    $directory_file_config['filename_pattern'] = str_replace('\\','_',$directory_file_config['job_provider']).'_'.rand(1000,9999);
                }
                $directory_file_config['run_time'] = date('H:i',strtotime('2000-01-01 '.$directory_file_config['run_time']));
                $directory_config[] = $directory_file_config;
            }
            $directory->directory_config = $directory_config;

            tep_db_query(
                "UPDATE ".TABLE_EP_DIRECTORIES." ".
                "SET directory_config='".tep_db_input(json_encode($directory_config))."' ".
                "WHERE directory_id='".(int)$id."'"
            );
            $directory->applyDirectoryConfig();

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            $directoryConfigs = [];
            foreach($directory->directory_config as $directory_config){
                $directory_config['run_time'] = date('g:i A',strtotime('2000-01-01 '.$directory_config['run_time']));
                $directoryConfigs[] = $directory_config;
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title' => TEXT_DIRECTORY_CONFIGURE,
                    'message' => $this->render('configure-auto-datasource-directory',[
                        'directoryConfigs' => $directoryConfigs,
                        'providersList' => $providersList,
                        'formatReaders' => $formatReaders,
                        'launchFrequency' => $launchFrequency,
                        'runTimeDefault' => date('g:i A',strtotime('+2 minutes')),
                    ])
                ]
            ];
        }
    }

    public function actionConfigureDatasourceSettings()
    {
        \common\helpers\Translation::init('admin/easypopulate');

        $this->layout = false;

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;
        $directory = EP\Directory::loadById($id);
        if (is_object($directory)) {
            $ds = EP\DataSources::getByName($directory->directory);
            if (is_object($ds)){
                if (Yii::$app->request->isPost){
                    $datasource = Yii::$app->request->post('datasource',[]);
                    $ds->update(isset($datasource[$ds->code])?$datasource[$ds->code]:[]);

                    Yii::$app->response->data = ['result'=>'ok'];
                    return;
                }
                Yii::$app->response->data = [
                    'dialog' => [
                        'title'=> 'Datasource "'.$directory->directory.'" configure',
                        'message' => '<form id="frmDatasourceConfig"><input type="hidden" name="by_id" value="'.$id.'">'.call_user_func_array([$this, 'render'], $ds->configureView()).'</form>',
                    ]
                ];
                return;
            }
        }
        Yii::$app->response->data = [
            'dialog' => [
                'error'=> 'true',
                'message' => 'Datasource not found',
            ]
        ];
    }

    public function actionConfigureAutoImportDirectory()
    {
        \common\helpers\Translation::init('admin/easypopulate');

        $this->layout = false;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;

        $directory = EP\Directory::loadById($id);
        if ( !$directory ){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Directory not found',
                ]
            ];
        }

        $providers = new EP\Providers();
        $providersList = [
            'selection' => '',
            'items' => [],
        ];
        foreach($providers->getAvailableProviders('Import','') as $importProviderInfo) {
            $providersList['items'][$importProviderInfo['key']] = $importProviderInfo['name'];
            if ( empty($providersList['selection']) ) $providersList['selection'] = $importProviderInfo['key'];
        }
        $formatReaders = [
            'selection' => 'CSV',
            'items' => [
                //'' => PULL_DOWN_DEFAULT,
                'CSV' => TEXT_OPTION_EXPORT_CSV,
                'ZIP' => TEXT_OPTION_EXPORT_ZIP,
            ]
        ];
        if ( class_exists('backend\models\EP\Datasource\BrightPearl') ) {
            $formatReaders['items']['BrightPearl'] = 'BrightPearl';
        }
        $launchFrequency = [
            'selection' => '-1',
            'items' => [
                -1 => TEXT_DISABLED,
                TEXT_RUN_ONCE => [
                    1 => TEXT_IMMEDIATELY,
                    0 => TEXT_DEFINED_TIME,
                ],
                TEXT_RUN_PERIODICALLY => [
                    5 => TEXT_EVERY_5_MINUTES,
                    15 => TEXT_EVERY_15_MINUTES,
                    30 => TEXT_EVERY_30_MINUTES,
                    60 => TEXT_EVERY_HOUR,
                    1440 => TEXT_EVERY_DAY,
                ]
            ]
        ];

        if ( Yii::$app->request->isPost ) {
            $directory_config_input = tep_db_prepare_input(Yii::$app->request->post('directory_config', []));
            $directory_config = [];
            foreach($directory_config_input as $directory_file_config){
                if ( empty($directory_file_config['filename_pattern']) ) continue;
                $directory_file_config['run_time'] = date('H:i',strtotime('2000-01-01 '.$directory_file_config['run_time']));
                $directory_config[] = $directory_file_config;
            }
            $directory->directory_config = $directory_config;

            tep_db_query(
                "UPDATE ".TABLE_EP_DIRECTORIES." ".
                "SET directory_config='".tep_db_input(json_encode($directory_config))."' ".
                "WHERE directory_id='".(int)$id."'"
            );
            $directory->applyDirectoryConfig();

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            $directoryConfigs = [];
            foreach($directory->directory_config as $directory_config){
                $directory_config['run_time'] = date('g:i A',strtotime('2000-01-01 '.$directory_config['run_time']));
                $directoryConfigs[] = $directory_config;
            }

            $directoryFilesSuggest = [];
            $get_files_r = tep_db_query(
                "SELECT DISTINCT file_name ".
                "FROM ".TABLE_EP_JOB." ".
                "WHERE directory_id='".$directory->directory_id."'"
            );
            if ( tep_db_num_rows($get_files_r)>0 ) {
                while( $get_file = tep_db_fetch_array($get_files_r) ){
                    $directoryFilesSuggest[$get_file['file_name']] = $get_file['file_name'];
                    $masked = preg_replace('/\d+/','*',$get_file['file_name']);
                    $directoryFilesSuggest[$masked] = $masked;
                    $masked2 = preg_replace('/\*.*\*/','*',$masked);
                    $directoryFilesSuggest[$masked2] = $masked2;
                }
            }

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title' => TEXT_DIRECTORY_CONFIGURE,
                    'message' => $this->render('configure-auto-import-directory',[
                        'directoryFilesSuggestSource' => implode(':',$directoryFilesSuggest),
                        'directoryConfigs' => $directoryConfigs,
                        'providersList' => $providersList,
                        'formatReaders' => $formatReaders,
                        'launchFrequency' => $launchFrequency,
                        'runTimeDefault' => date('g:i A',strtotime('+2 minutes')),
                    ])
                ]
            ];
        }
    }

    public function actionConfigureAutoProcessedDirectory()
    {
        \common\helpers\Translation::init('admin/easypopulate');

        $this->layout = false;

        $id = Yii::$app->request->get('by_id',0);
        $id = Yii::$app->request->post('by_id',$id);
        $id = (int)$id;

        $directory = EP\Directory::loadById($id);
        if ( !$directory ){
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=> TEXT_DIRECTORY_CONFIGURE,
                    'message' => 'Directory not found',
                ]
            ];
        }

        $cleaningTerm = [
            'selection' => '-1',
            'items' => [
                -1 => TEXT_DISABLE_REMOVAL,
                '1 day' => TEXT_KEEP_1_DAY,
                '1 week' => TEXT_KEEP_1_WEEK,
                '2 week' => TEXT_KEEP_2_WEEKS,
                '1 month' => TEXT_KEEP_1_MONTH,
            ]
        ];

        if ( Yii::$app->request->isPost ) {
            $directory->directory_config = tep_db_prepare_input(Yii::$app->request->post('directory_config', []));

            tep_db_query(
                "UPDATE ".TABLE_EP_DIRECTORIES." ".
                "SET directory_config='".tep_db_input(json_encode($directory->directory_config))."' ".
                "WHERE directory_id='".(int)$id."'"
            );
            $directory->applyDirectoryConfig();

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            $directoryConfigs = $directory->directory_config;
            if ( !isset($directoryConfigs['cleaning_term']) ) $directoryConfigs['cleaning_term'] = '-1';

            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->data = [
                'dialog' => [
                    'title'=>'Configure directory',
                    'message' => $this->render('configure-auto-processed-directory',[
                        'directoryConfigs' => $directoryConfigs,
                        'cleaningTerm' => $cleaningTerm,
                    ])
                ]
            ];
        }
    }
    
    public function actionRefreshFilters()
    {
      $this->layout = false;
      $data = array(
        //'select_filter_categories' => tep_draw_pull_down_menu('filter[category_id]', \common\helpers\Categories::get_category_tree(0,'','','',false,true), 0, ''),
        'select_filter_properties' => tep_draw_pull_down_menu('filter[properties_id]', \common\helpers\Properties::get_properties_tree(0,'','',false), 0, ''),
      );
      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      Yii::$app->response->data = $data;
    }

    public function actionGetCategoriesList()
    {
      $this->layout = false;

      $all_data = \common\helpers\Categories::get_category_tree(0,'','','',false,true);
      $data = array_filter($all_data,function($option){
        $search_term = \Yii::$app->request->get('term','');
        $search_term = tep_db_prepare_input($search_term);
        $option_value = html_entity_decode($option['text'], ENT_HTML5, 'UTF-8');
        return preg_match('/'.preg_quote($search_term,'/').'/is', $option_value) || $option_value==$search_term;
      });
      $data = array_map(function($option){
        $option['value'] = html_entity_decode($option['text'], ENT_HTML5, 'UTF-8');
        $option['text'] = $option['value'];
        return $option;
      },$data);

      Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
      Yii::$app->response->data = $data;
    }

    public function actionExportColumns()
    {
        $this->layout = false;
        $job_id = Yii::$app->request->get('by_id', 0);
        $job_id = Yii::$app->request->post('by_id', $job_id);

        if ( Yii::$app->request->isPost ) {
            $selected_columns = tep_db_prepare_input(Yii::$app->request->post( 'selected_fields', '' ));
            if ( !empty($selected_columns) ) {
                $selected_columns = explode(',',$selected_columns);
            }else{
                $selected_columns = false;
            }

            if ( $job_id ) {
                $job = EP\Job::loadById($job_id);
                if ( $job ) {
                    $job->job_configure['export']['columns'] = $selected_columns;
                    tep_db_query("UPDATE ".TABLE_EP_JOB." SET job_configure='".tep_db_input(json_encode($job->job_configure))."' WHERE job_id='".(int)$job->job_id."' ");
                }
            }
        }
        die;
    }
    
    public function actionGetFields()
    {
        $this->layout = false;

        $selected = false;
        $export_provider = tep_db_prepare_input(Yii::$app->request->post('export_provider', ''));

        $job_id = Yii::$app->request->post('by_id', 0);
        if ( $job_id ) {
            $job = EP\Job::loadById($job_id);
            if ( $job ) {
                $export_provider = $job->job_type;
                if ( isset($job->job_configure['export']['columns']) && is_array($job->job_configure['export']['columns']) ) {
                    $selected = array_flip($job->job_configure['export']['columns']);
                }
            }
        }

        $providers = new EP\Providers();

        $columns = array();

        $exportProvider = $providers->getProviderInstance($export_provider);

        if (is_object($exportProvider) && $exportProvider instanceof EP\Provider\ExportInterface){
            $columns = $exportProvider->getColumns();
        }

        if ( !is_array($selected) ) {
            $selected = array();
            $get_selected_fields_r = tep_db_query(
                "SELECT shop_field ".
                "FROM ".TABLE_EP_PROFILES." ".
                "WHERE ep_direction='export' AND ep_type='".tep_db_input($export_provider)."' "
            );
            if ( tep_db_num_rows($get_selected_fields_r)>0 ) {
                while( $_selected_field = tep_db_fetch_array($get_selected_fields_r) ){
                    if ( !isset($columns[$_selected_field['shop_field']]) ) continue;
                    $selected[$_selected_field['shop_field']] = $_selected_field['shop_field'];
                }
            }
            if ( count($selected)==0 ) {
                $selected = false;
            }
        }

        $out_columns = array();
        foreach( $columns as $key=>$column_title ) {
            $out_columns[] = array(
                'db_key' => $key,
                'selected' => (is_array($selected)?isset($selected[$key]):true),
                'title' => $column_title,
            );
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $out_columns;
    }

    function actionProcessExport()
    {
        \common\helpers\Translation::init('admin/easypopulate');

        $export_filename = tep_db_prepare_input(Yii::$app->request->post('export_filename'));
        $export_provider = tep_db_prepare_input(Yii::$app->request->post('export_provider'));

        $format = tep_db_prepare_input(Yii::$app->request->post('format'));

        $selected_columns = tep_db_prepare_input(Yii::$app->request->post( 'selected_fields', '' ));
        if ( !empty($selected_columns) ) {
            $selected_columns = explode(',',$selected_columns);
        }else{
            $selected_columns = false;
        }

        $filter = tep_db_prepare_input(Yii::$app->request->post('filter'));
        if ( !is_array($filter) ) $filter = [];
        if ( !empty($filter['order']['date_from']) ) {
            $value_time = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $filter['order']['date_from']);
            $filter['order']['date_from'] = '';
            if ( $value_time ) {
               $filter['order']['date_from'] = $value_time->format('Y-m-d');
            }
        }
        if ( !empty($filter['order']['date_to']) ) {
            $value_time = date_create_from_format(DATE_FORMAT_DATEPICKER_PHP, $filter['order']['date_to']);
            $filter['order']['date_to'] = '';
            if ( $value_time ) {
               $filter['order']['date_to'] = $value_time->format('Y-m-d');
            }
        }

        if ( $this->currentDirectory->cron_enabled && Yii::$app->request->post('new_job',0) ) {
            $error = false;
            $export_filename = ltrim(FileHelper::normalizePath('/'.$export_filename),'/');
            if ( empty($export_filename) ){
                $error = ERROR_EMPTY_FILENAME;
            }else{
                if ($this->currentDirectory->findJobByFilename($export_filename)){
                    $error = ERROR_FILENAME_NOT_UNIQUE;
                }
            }
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            if ($error){
                Yii::$app->response->data = [
                    'status' => 'error',
                    'dialog' => [
                        'title' => ICON_ERROR,
                        'message' => '<p>'.$error.'</p>',
                    ]
                ];
                return;
            }
            $new_job_data = [
                'directory_id' => $this->currentDirectory->directory_id,
                'file_name' => $export_filename,
                'direction' => $this->currentDirectory->directory_type,
                'job_provider' => $export_provider,
                'job_state' => 'configured',
                'job_configure' => json_encode([
                    'export' => [
                        'columns' => $selected_columns,
                        'filter' => $filter,
                        'format' => $format,
                    ]
                ]),
            ];
            
            tep_db_perform(TABLE_EP_JOB, $new_job_data);
                    
            Yii::$app->response->data = ['status'=>'ok'];
            return;
        }
        
        $job_id = Yii::$app->request->post('by_id',0);
        if ( $job_id ) {
            
            $messages = new \backend\models\EP\Messages([
                'job_id' => $job_id,
                'output' => 'none',
            ]);

            $job = EP\Job::loadById($job_id);

            if (true) {
                if ( $format=='ZIP' ) {
                    $mime_type = 'application/zip';
                    $extension = 'zip';
                }else{
                    $mime_type = 'application/vnd.ms-excel';
                    $extension = 'csv';
                }
                $export_provider = $job->job_provider;
                $filename  = (strpos($export_provider,'\\')===false?$export_provider:substr($export_provider,strpos($export_provider,'\\')+1) ) . '_' . strftime( '%Y%b%d_%H%I' ) . '.'.$extension;

                header('Content-Type: ' . $mime_type);
                header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');

                if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                } else {
                    header('Pragma: no-cache');
                }
            }
            $job->file_name = 'php://output';
            $job->job_configure['export'] = [
                'columns' => false,
                'filter' => [],
                'format' => $format,
            ];

            $job->run($messages);
            die;
        }

        if (true) {
            if ( $format=='ZIP' ) {
                $mime_type = 'application/zip';
                $extension = 'zip';
            }else{
                $mime_type = 'application/vnd.ms-excel';
                $extension = 'csv';
            }
            $filename  = (strpos($export_provider,'\\')===false?$export_provider:substr($export_provider,strpos($export_provider,'\\')+1) ) . '_' . strftime( '%Y%b%d_%H%I' ) . '.'.$extension;

            header('Content-Type: ' . $mime_type);
            header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
            
            if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
            } else {
                header('Pragma: no-cache');
            }
        }

        $messages = new EP\Messages();
        $exportJob = new EP\JobFile();
        $exportJob->directory_id = $this->currentDirectory->directory_id;
        $exportJob->direction = 'export';
        $exportJob->file_name = 'php://output';
        $exportJob->job_provider = $export_provider;
        $exportJob->job_configure['export'] = [
            'columns' => $selected_columns,
            'filter' => $filter,
            'format' => $format,
        ];

        $exportJob->run($messages);

        die;
    }

    public function actionUploadFileAjax()
    {
        include(DIR_WS_CLASSES.'UploadHandler.php');

        $file_type = Yii::$app->request->post('file_type','');

        $ep_files_dir = $this->currentDirectory->filesRoot(EP\Directory::TYPE_IMPORT);

        $UploadHandler = new \UploadHandler(array(
            'upload_dir' => $ep_files_dir,
            'accept_file_types' => '/.*/', //'/.+\.('.$ep_modules[$_GET['epID']]['epObj']->getAcceptedExtensions().')$/i',
            'param_name' => 'data_file',
            'access_control_allow_methods' => array('POST'),
        ));
        $response = $UploadHandler->get_response();
        if ( isset($response['data_file']) && is_array($response['data_file']) && isset($response['data_file'][0]) ) {
            if (is_object($response['data_file'][0]) && isset($response['data_file'][0]->name)) {
                if (isset($response['data_file'][0]->url)) {
                    // upload finished
                    $job_id = $this->currentDirectory->touchImportJob($response['data_file'][0]->name,'uploaded', $file_type);
                    $job = EP\Job::loadById($job_id);
                    if ( is_object($job) ) {
                        $job->tryAutoConfigure();
                    }
                }else{
                    // upload in progress
                    $this->currentDirectory->touchImportJob($response['data_file'][0]->name,'upload', $file_type);
                }
            }
        }
        die;
    }

    public function actionFilesList()
    {
        $this->layout = false;

        \common\helpers\Translation::init('admin/easypopulate');
        
        $this->currentDirectory->synchronizeFiles();

        $providers = new EP\Providers();
        
        $formatter = new Formatter();

        $listDirectoryIds = [ $this->currentDirectory->directory_id ];
        $subdirectories = $this->currentDirectory->getSubdirectories();

        foreach($subdirectories as $subdir){
            $listDirectoryIds[] = $subdir->directory_id;
        }
        $dir_files = array();
        $get_db_files_r = tep_db_query(
            "SELECT job_id ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE directory_id='".$this->currentDirectory->directory_id."' ".
            //"WHERE directory_id IN('".implode("','", $listDirectoryIds)."') ".
            "ORDER BY file_time DESC"
        );
        if ( tep_db_num_rows($get_db_files_r)>0 ) {
            while( $_db_file = tep_db_fetch_array($get_db_files_r) ){
                $dir_files[] = EP\Job::loadById($_db_file['job_id']);
            }
        }

        $directoryRoot = $this->currentDirectory->filesRoot();
        $files = array();
        // {{
        if ( $levelUpDirectory = $this->currentDirectory->getParent() ) {
            $files[] = array(
                '<div data-directory_id="'.$levelUpDirectory->directory_id.'"><span class="parent_cats"><i class="icon-circle"></i><i class="icon-circle"></i><i class="icon-circle"></i></span></div>',
                '--',
                '',
                '',
                '',
                '<div class="job-actions">'.
                ($this->currentDirectory->canConfigureDatasource()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_datasource_settings" data-type="'.$this->currentDirectory->directory_type.'" data-directory_id="'.(int)$this->currentDirectory->directory_id.'"><i class="icon-wrench"></i></a>':'').
                ($this->currentDirectory->canConfigure()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_dir" data-type="'.$this->currentDirectory->directory_type.'" data-directory_id="'.(int)$this->currentDirectory->directory_id.'"><i class="icon-cog"></i></a>':'').
                '</div>'
            );
        }
        foreach($subdirectories as $subdir){
            $files[] = array(
                '<div class="cat_name cat_name_attr" data-directory_id="'.$subdir->directory_id.'">'.$subdir->directory.'</div>',
                '--',
                '',
                '',
                '',
                '<div class="job-actions">'.
                ($subdir->canRemove()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_directory_remove('.(int)$subdir->directory_id.');"><i class="icon-trash"></i></a>':'').
                ($subdir->canConfigureDatasource()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_datasource_settings" data-type="'.$subdir->directory_type.'" data-directory_id="'.(int)$subdir->directory_id.'"><i class="icon-wrench"></i></a>':'').
                ($subdir->canConfigure()?'<a class="job-button js-action-link" href="javascript:void(0);" data-action="configure_dir" data-type="'.$subdir->directory_type.'" data-directory_id="'.(int)$subdir->directory_id.'"><i class="icon-cog"></i></a>':'').
                '</div>'
            );
        }
        // }}

        foreach ($dir_files as $job){
            /**
             * @var EP/Job $job
             */
            if ( $job instanceof EP\JobFile) {
                $file_info = $job->getFileInfo();
                $showFilename = $job->file_name;
                if (!empty($file_info['pathFilename'])) {
                    $showFilename = str_replace($directoryRoot, '', $file_info['pathFilename']);
                }

                if (!empty($file_info['fileSystemName']) && is_file($file_info['fileSystemName'])) {
                    $fileNameCell = '<div style="white-space: nowrap"><a href="' . Yii::$app->urlManager->createUrl(['easypopulate/download', 'id' => $job->job_id]) . '" target="_blank"><i class="' . ($job->direction == 'import' ? 'icon-upload' : 'icon-download fieldRequired') . '"></i></a> ' . $showFilename . '</div>';
                } else {
                    $fileNameCell = '<div style="white-space: nowrap"><i class="icon-download fieldRequired"></i> ' . $showFilename . '</div>';
                }
            }else{
                $fileNameCell = '<div style="white-space: nowrap"> ' . $job->file_name . '</div>';
                $file_info = false;
            }
            
            $file_row = array(
              $fileNameCell,
              $providers->getProviderName($job->job_provider),
              (is_array($file_info) && $file_info['fileSize']?$formatter->asShortSize($file_info['fileSize'],3):'--'),
                ($file_info['fileTime']>0?\common\helpers\Date::datetime_short(date('Y-m-d H:i:s',$file_info['fileTime'])):(
                    ($job->last_cron_run>2000?\common\helpers\Date::datetime_short($job->last_cron_run):'--')
                ))
              ,
              '<div class="job-actions">'.
              ($job->canRemove()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_file_remove('.(int)$job->job_id.');"><i class="icon-trash"></i></a>':'').
              ($job->canConfigureExport()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\'configure_export_columns\', '.(int)$job->job_id.');"><i class="icon-reorder"></i></a>':'').
              ($job->canConfigureImport()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\'configure\', '.(int)$job->job_id.');"><i class="icon-reorder"></i></a>':'').
              ($job->canSetupRunFrequency()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\'run_frequency\', '.(int)$job->job_id.');"><i class="icon-time" style="color:'.($job->run_frequency==-1?'red':'green').'"></i></a>':'').
              ($job->canRun()?'<a class="job-button" href="javascript:void(0);" onclick="return ep_command(\''.$job->direction.'\', '.(int)$job->job_id.');"><i class="icon-play"></i></a>':'').
              ($job->haveMessages()?'<a class="job-button" href="javascript:void(0);" onclick="return showJobMessages('.(int)$job->job_id.');"><i class="icon-file-text"></i></a>':'').
              '</div>'
            );
            if ($this->currentDirectory->cron_enabled && (in_array($this->currentDirectory->directory_type, ['import', 'processed', 'datasource'] )) ){
                $file_row[5] = $file_row[4];
                $file_row[4] = $job->job_state;
                if ( $job->job_state==EP\Job::PROCESS_STATE_IN_PROGRESS ) {
                    $file_row[4] .= ' '.$job->process_progress.'%';
                }
            }
            $files[] = $file_row;
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = ['data'=>$files];
    }

    public function actionDownload()
    {
        $this->layout = false;
        $job_id = Yii::$app->request->get('id',0);
        $job = EP\Job::loadById($job_id);
        if ( $job && $job instanceof EP\JobFile && is_file($job->getFileSystemName()) ){
            $filename = basename($job->file_name);
            
            $mime_type = FileHelper::getMimeTypeByExtension($job->file_name);
            if ( $mime_type=='text/plain' ) {
                $mime_type = 'application/vnd.ms-excel';
            }
            
            header('Content-Type: ' . $mime_type);
            header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Disposition: attachment; filename="' . urlencode($filename) . '"');
            
            if (preg_match('@MSIE ([0-9].[0-9]{1,2})@', $_SERVER['HTTP_USER_AGENT'], $log_version)) {
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
            } else {
                header('Pragma: no-cache');
            }
            
            readfile($job->getFileSystemName());
        }
        die;
    }

    public function actionRemoveDirectory()
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $directory_id = intval(Yii::$app->request->post('id',0));

        $directory = EP\Directory::loadById($directory_id);
        if ( $directory && $directory->delete() ) {
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            Yii::$app->response->data = ['status'=>'error'];
        }
    }

    public function actionRemoveEpFile()
    {
        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $file_id = intval(Yii::$app->request->post('id',0));

        $job = EP\Job::loadById($file_id);
        if ( $job && $job->delete() ) {
            Yii::$app->response->data = ['status'=>'ok'];
        }else{
            Yii::$app->response->data = ['status'=>'error'];
        }

    }

    public function actionCommand()
    {
        $cmd = Yii::$app->request->post('cmd','');
        if ( !empty($cmd) && method_exists($this,$cmd) ) {
            return call_user_func(array($this,$cmd));
        }
    }

    public function actionChooseProvider()
    {
        $this->layout = false;
        $id = intval(Yii::$app->request->post('id'));
        $update_type = tep_db_prepare_input(Yii::$app->request->post('file_type'));
        tep_db_query("UPDATE ".TABLE_EP_JOB." SET job_provider='".tep_db_input($update_type)."' WHERE job_id='".(int)$id."' ");
        return '';
    }

    public function actionImportConfigure()
    {

        $providers = new EP\Providers();

        // load ep_job
        $job_record = false;

        $open_full_match = false;
        $job_by_filename = Yii::$app->request->post('by_file_name','');
        if ( !empty($job_by_filename) ) {
            $job_record = $this->currentDirectory->findJobByFilename($job_by_filename);
        }else{
            $job_by_id = Yii::$app->request->post('by_id', '');
            if (!empty($job_by_id)) {
                $job_record = EP\Job::loadById($job_by_id);
                $open_full_match = true;
            }
        }

        if ( !is_object($job_record) || !$job_record->canConfigureImport() ) {
            die;
        }

        $command_params = [
            'id' => $job_record->job_id,
        ];

        if ( !empty($job_record->job_provider) && $job_record->job_provider!='auto' ) {
            $checkValid = $providers->getProviderInstance($job_record->job_provider);
            if ( !is_object($checkValid) ) {
                $job_record->job_provider = '';
            }
        }

        $fileSystemName = $job_record->getFileSystemName();
        if ( preg_match('/\.zip/i',$fileSystemName) ) {
            $reader = new EP\Reader\ZIP([
                'filename' => $fileSystemName,
            ]);
        }else {
            $reader = new EP\Reader\CSV([
                'filename' => $fileSystemName,
            ]);
        }
        $fileColumns = $reader->readColumns();

        if ( empty($job_record->job_provider) || $job_record->job_provider=='auto' ) {
            // guess
            /**
             * @var $reader EP\Reader\ReaderInterface
             */
            if ( count($fileColumns)==0 ) {
                echo '<script>window.parent.uploader(\'wrong_file_type\')</script>';
                die;
            }

            $possibleProviders = $providers->bestMatch($fileColumns);
            reset($possibleProviders);
            if ( count($possibleProviders)==0 ) {
                echo '<script>window.parent.uploader(\'need_choose_file_type\', '.json_encode($command_params).')</script>';
                die;
            }elseif ( current($possibleProviders)==1 ) {
                $fileProvider = current(array_keys($possibleProviders));
                $job_record->job_provider = $fileProvider;
                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET job_state='configured', job_provider='".tep_db_input($fileProvider)."' ".
                    "WHERE job_id='".$job_record->job_id."' "
                );
                echo '<script>window.parent.uploader(\'reload_file_list\')</script>';
            }else{
                // not sure, something match, but not 100%
                $command_params['matched_providers'] = array_keys($possibleProviders);
                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET job_provider='".tep_db_input($command_params['matched_providers'][0])."' ".
                    "WHERE job_id='".$job_record->job_id."' "
                );
                $command_params['file_columns'] = $fileColumns;
                $firstProvider = $providers->getProviderInstance($command_params['matched_providers'][0]);
                //$command_params['provider_name'] = $firstProvider;
                $command_params['provider_columns'] = array_merge(array(''),$firstProvider->getColumns());
                
                $command_params['remap_columns'] = [];
                $pMap = array_flip($firstProvider->getColumns());
                foreach( $fileColumns as $fileColumn ) {
                    $command_params['remap_columns'][$fileColumn] = isset($pMap[$fileColumn])?$pMap[$fileColumn]:'';
                }
                
                echo '<script>window.parent.uploader(/*1*/\'need_choose_import_map\','.json_encode($command_params).')</script>';
                die;
            }
        }

        $job_configure = $job_record->job_configure;

        $providerObj = $providers->getProviderInstance( $job_record->job_provider );

        if ( $providerObj->getColumnMatchScore( $fileColumns )!=1 || $open_full_match ) {
            $command_params['file_columns'] = $fileColumns;
            $command_params['provider_columns'] = array_merge(array(''=>''),$providerObj->getColumns());

            if ( isset($job_configure['remap_columns']) && is_array($job_configure['remap_columns']) ) {
                $command_params['remap_columns'] = $job_configure['remap_columns'];
            }else{
                $command_params['remap_columns'] = [];
                $pMap = array_flip($providerObj->getColumns());
                foreach( $fileColumns as $fileColumn ) {
                    $command_params['remap_columns'][$fileColumn] = isset($pMap[$fileColumn])?$pMap[$fileColumn]:'';
                }
            }

            echo '<script>window.parent.uploader(/*2*/\'need_choose_import_map\','.json_encode($command_params).')</script>';
            die;
        }

        // guess job_provider for auto
        // check columns map - init dialog for map missing
        // suggest start import
        die;
    }

    public function actionConfirmMapping()
    {
        $this->layout = false;

        $result = ['status'=>'ok'];

        $job_id = intval(Yii::$app->request->post('id', 0 ));
        $map = Yii::$app->request->post('map', array() );

        $job_record = false;
        if (!empty($job_id)) {
            $job_lookup_r = tep_db_query("SELECT * FROM " . TABLE_EP_JOB . " WHERE job_id='" . (int)$job_id . "' ");
            if (tep_db_num_rows($job_lookup_r) > 0) {
                $job_record = tep_db_fetch_array($job_lookup_r);
                if ( !empty($job_record['job_configure']) ) {
                    $job_record['job_configure'] = json_decode($job_record['job_configure'], true);
                }
                if ( !is_array($job_record['job_configure']) ) $job_record['job_configure'] = array();
            }
        }

        if ( !is_array($job_record) ) {
            $result['status'] = 'error';
            $result['message'] = 'Job not found';
        }elseif( (empty($job_record['job_provider']) || $job_record['job_provider']=='auto' ) ){
            $result['status'] = 'error';
            $result['message'] = 'Need select job type';
        }else{
            $providers = new EP\Providers();

            $provider = $providers->getProviderInstance($job_record['job_provider']);
            if ( $provider instanceof EP\Provider\ProviderAbstract)
            {
                $providerColumns = $provider->getColumns();
                $remap_columns = array_flip($providerColumns);
                if ( is_array($map) && count($map)>0 ) {
                    $__map_columns = array();
                    foreach ($map as $fileColumnName=>$importFieldName){
                        if ( isset($providerColumns[$importFieldName]) ) {
                            $__map_columns[$fileColumnName] = $importFieldName;
                        }
                    }
                    if ( count($__map_columns)>0 ) {
                        $remap_columns = $__map_columns;
                    }
                }

                $job_record['job_configure']['remap_columns'] = $remap_columns;

                tep_db_query(
                    "UPDATE ".TABLE_EP_JOB." ".
                    "SET job_state='configured', job_configure='".tep_db_input(json_encode($job_record['job_configure']))."' ".
                    "WHERE job_id='".$job_record['job_id']."' "
                );
            }else{
                $result['status'] = 'error';
                $result['message'] = 'Wrong job type';
            }
        }



        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = $result;
    }

    public function actionImport()
    {
        $this->layout = false;

        $job_id = intval(Yii::$app->request->post('by_id', 0 ));

        $job_record = EP\Job::loadById($job_id);

        if ( !is_object($job_record) ) {
            $result['status'] = 'error';
            $result['message'] = 'Job not found';
        }else{
            $messages = new EP\Messages();
            $messages->setEpFileId($job_record->job_id);

            try {
                $job_record->run($messages);
            }catch (\Exception $ex){
                $messages->info($ex->getMessage());
            }
            $messages->command('reload_file_list');
        }
        die;
    }

    public function actionJobLogMessages()
    {
        $this->layout = false;
        $id = Yii::$app->request->get('id');
        $messages = [];
        $message_string = '';
        $get_job_messages_r = tep_db_query(
            "SELECT message_text ".
            "FROM ".TABLE_EP_LOG_MESSAGES." ".
            "WHERE job_id='".(int)$id."' ".
            "ORDER BY ep_log_message_id ".
            "/*LIMIT 3000*/"
        );
        if ( tep_db_num_rows($get_job_messages_r)>0 ){
            while( $message = tep_db_fetch_array($get_job_messages_r) ) {
                $message_string .= $message['message_text'].'<br>';
                //$messages[] = $message;
            }
        }
        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return $this->render('log-messages',['messages'=>$messages,'message_string'=>$message_string]);
        /*Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        Yii::$app->response->data = [
            'dialog' => [
                'title'=>'Job messages',
                'message' => $this->render('log-messages',['messages'=>$messages,'message_string'=>$message_string]),
                'buttons' => [
                    'cancel' => [
                        'label' => TEXT_OK,
                        'className' => 'btn-primary',
                    ]
                ]
            ]
        ];*/
    }
    
    public function actionJobFrequency()
    {
        \common\helpers\Translation::init('admin/easypopulate');

        $this->layout = false;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $by_id = Yii::$app->request->get('by_id', 0);
        $by_id = Yii::$app->request->post('by_id', $by_id);
        
        $run_frequency = Yii::$app->request->post('run_frequency', -1);
        $run_time = Yii::$app->request->post('run_time', '00:00');

        if ( Yii::$app->request->isPost ) {
            $time_APM = strtotime('2000-01-01 '.$run_time);
            tep_db_query(
                "UPDATE ".TABLE_EP_JOB." ".
                "SET ".((int)$run_frequency==0?"last_cron_run=IF(run_time='".tep_db_input(date('H:i',$time_APM))."',last_cron_run,NULL), ":'').
                " run_frequency='".(int)$run_frequency."', run_time='".tep_db_input(date('H:i',$time_APM))."' ".
                "WHERE job_id='".(int)$by_id."' "
            );
            Yii::$app->response->data = ['status'=>'ok'];
            return;
        }
        
        $get_data_r = tep_db_query(
            "SELECT run_frequency, run_time ".
            "FROM ".TABLE_EP_JOB." ".
            "WHERE job_id='".(int)$by_id."' "
        );
        if ( tep_db_num_rows($get_data_r)>0 ) {
            $data = tep_db_fetch_array($get_data_r);
            $run_frequency = $data['run_frequency'];
            $run_time = $data['run_time'];
        }
        
        $runFrequencyVariants = [
            -1 => TEXT_DISABLED,
            TEXT_RUN_ONCE => [
                1 => TEXT_IMMEDIATELY,
                0 => TEXT_DEFINED_TIME,
            ],
            TEXT_RUN_PERIODICALLY => [
                5 => TEXT_EVERY_5_MINUTES,
                15 => TEXT_EVERY_15_MINUTES,
                30 => TEXT_EVERY_30_MINUTES,
                60 => TEXT_EVERY_HOUR,
                1440 => TEXT_EVERY_DAY,
            ]
        ];

        $time_APM = strtotime('2000-01-01 '.$run_time);
        
        Yii::$app->response->data = [
            'dialog' => [
                'title'=>'Job run frequency',
                'message' => $this->render('popup-job-frequency',[
                    'run_frequency' => $run_frequency,
                    'runFrequencyVariants' => $runFrequencyVariants,
                    'run_time' => date('g:i A',strtotime('2000-01-01 '.$run_time)),
                ]),
                'buttons' => [
                    'confirm' => [
                        'label' => TEXT_OK,
                        'className' => 'btn-primary',
                    ]
                ]
            ]
        ];
    }

    public function actionEmpty()
    {
        global $messageStack;
        if ($_POST['products']){
          $query = tep_db_query("select * from " . TABLE_CATEGORIES);
          while ($data = tep_db_fetch_array($query)){
            @unlink(DIR_FS_CATALOG_IMAGES . $data['categories_image']);
          }
          tep_db_query("TRUNCATE TABLE " . TABLE_CATEGORIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_CATEGORIES_DESCRIPTION);
          $products_count = tep_db_fetch_array(tep_db_query(
              "select count(*) AS c from " . TABLE_PRODUCTS.""
          ));
          $product_images_columns = [
            'products_image',
            'products_image_med',
            'products_image_lrg',
            'products_image_sm_1',
            'products_image_xl_1',
            'products_image_sm_2',
            'products_image_xl_2',
            'products_image_sm_3',
            'products_image_xl_3',
            'products_image_sm_4',
            'products_image_xl_4',
            'products_image_sm_5',
            'products_image_xl_5',
            'products_image_sm_6',
            'products_image_xl_6',
          ];

            $pages_count = ceil($products_count['c']/5000);
          for( $page=0; $page<$pages_count; $page++ ) {
              $query = tep_db_query("select * from " . TABLE_PRODUCTS . " LIMIT ".($page*5000).", 5000 ");
              while ($data = tep_db_fetch_array($query)) {
                  //@unlink(DIR_FS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $data['products_id'] . DIRECTORY_SEPARATOR);
                  FileHelper::removeDirectory(DIR_FS_CATALOG_IMAGES . 'products' . DIRECTORY_SEPARATOR . $data['products_id'] . DIRECTORY_SEPARATOR);
                  foreach( $product_images_columns as $product_images_column ) {
                      if ( !empty($data[$product_images_column]) && is_file(DIR_FS_CATALOG_IMAGES . $data[$product_images_column]) ){
                          @unlink(DIR_FS_CATALOG_IMAGES . $data[$product_images_column]);
                      }
                  }
              }
              set_time_limit(60);
          }

          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_PRICES);
          tep_db_query("TRUNCATE TABLE " . TABLE_SPECIALS);
          tep_db_query("TRUNCATE TABLE " . TABLE_SPECIALS_PRICES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_DESCRIPTION);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_XSELL);


          tep_db_query("TRUNCATE TABLE " . TABLE_CATS_PRODUCTS_XSELL);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_UPSELL);
          tep_db_query("TRUNCATE TABLE " . TABLE_CATEGORIES_UPSELL);
          tep_db_query("TRUNCATE TABLE " . TABLE_CATS_PRODUCTS_UPSELL);


          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_ATTRIBUTES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_ATTRIBUTES_PRICES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_NOTIFICATIONS);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_OPTIONS);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_OPTIONS_VALUES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS);
          tep_db_query("TRUNCATE TABLE " . TABLE_INVENTORY);
          tep_db_query("TRUNCATE TABLE " . TABLE_INVENTORY_PRICES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_TO_CATEGORIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_REVIEWS);
          tep_db_query("TRUNCATE TABLE " . TABLE_REVIEWS_DESCRIPTION);
          $query = tep_db_query("select * from " . TABLE_MANUFACTURERS);
          while ($data = tep_db_fetch_array($query)){
            @unlink(DIR_FS_CATALOG_IMAGES . $data['manufacturers_image']);
          }
          tep_db_query("TRUNCATE TABLE " . TABLE_MANUFACTURERS);
          tep_db_query("TRUNCATE TABLE " . TABLE_MANUFACTURERS_INFO);

          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_CATEGORIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_CATEGORIES_DESCRIPTION);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_TO_PROPERTIES_CATEGORIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_DESCRIPTION);
          tep_db_query("TRUNCATE TABLE " . TABLE_PROPERTIES_TO_PRODUCTS);


          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_DESCRIPTION);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_INVENTORY);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_IMAGES_ATTRIBUTES);

          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_VIDEOS);

          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_DOCUMENTS);
          tep_db_query("TRUNCATE TABLE " . TABLE_PRODUCTS_DOCUMENTS_TITLES);
          tep_db_query("TRUNCATE TABLE " . TABLE_DOCUMENT_TYPES);

          tep_db_query("TRUNCATE TABLE " . TABLE_PLATFORMS_PRODUCTS);
          tep_db_query("TRUNCATE TABLE " . TABLE_PLATFORMS_CATEGORIES);
        }

        if ($_POST['orders'] == 1){
          tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS);
          tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_PRODUCTS);
          tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES);
          tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_PRODUCTS_DOWNLOAD);
          tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_STATUS_HISTORY);
          tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_TOTAL);
          tep_db_query("TRUNCATE TABLE " . TABLE_ORDERS_HISTORY);
        }


        if ($_POST['customers'] == 1){
          tep_db_query("TRUNCATE TABLE " . TABLE_CUSTOMERS);
          tep_db_query("TRUNCATE TABLE " . TABLE_CUSTOMERS_BASKET);
          tep_db_query("TRUNCATE TABLE " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES);
          tep_db_query("TRUNCATE TABLE " . TABLE_CUSTOMERS_INFO);
          tep_db_query("TRUNCATE TABLE " . TABLE_ADDRESS_BOOK);
        }

        if( is_null( $this->messageStack ) ) {
            $this->messageStack = new \messageStack();
        }

        $messageStack = $this->messageStack;

        $messageType = 'success';
        $message = ICON_SUCCESS;
        $messageStack->add_session($message, 'success');

        return $this->redirect(Yii::$app->urlManager->createUrl('easypopulate/'));
    }

}
