<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP\Provider;


use backend\models\EP\JobFile;
use backend\models\EP\Messages;
use backend\models\EP\Providers;
use backend\models\EP\Transform;
use common\classes\Images as CommonImages;

class ImagesZip extends Images
{

    protected $imagesProvider;
    protected $imagesWriter;
    protected $imagesWriterDone = false;

    public function __construct()
    {
        parent::__construct();
        $this->imagesProvider = new Images();
    }

    public function setExtractDir($directory)
    {
        if ( is_dir($directory) ) {
            $this->setImagesDirectory($directory.'images/');
            $this->import_folder = $directory;
        }
    }

    public function setImagesDirectory($imagesFolder)
    {
        //$this->import_folder = $imagesFolder;
        $this->imagesProvider->setImagesDirectory($imagesFolder);
    }

    public function prepareExport($useColumns, $filter)
    {
        parent::prepareExport($useColumns, $filter);
        $this->imagesWriter = \Yii::createObject([
            'class' => 'backend\\models\\EP\\Writer\\'.'CSV',
            'filename' => tempnam(sys_get_temp_dir(), 'ep_zip_csv_write'),
        ]);
        $this->imagesWriter->setColumns($this->wColumns);
        $this->imagesWriterDone = false;
/*
        $messages = new Messages();
        $exportJob = new JobFile();
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
*/
        $this->imagesProvider;
    }


    public function exportRow()
    {
        //$this->data = tep_db_fetch_array($this->export_query);
        $parentData = parent::exportRow();
        if ( !is_array($this->data) ) {
            if ( $this->imagesWriterDone == false ){
                $this->imagesWriter->close();
                $this->imagesWriterDone = true;
                return [[
                    'filename' => $this->imagesWriter->filename,
                    'localname' => 'products_images.csv',
                ]];
            }
            //@unlink($this->imagesWriter->filename);
            return $this->data;
        }

        $filesAdd = [];
        $productsId = (int)$this->data['products_id'];
        $imageId = (int)$this->data['products_images_id'];
        $get_images_r = tep_db_query(
            "SELECT products_images_id, language_id, orig_file_name, hash_file_name, file_name ".
            "FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." ".
            "WHERE products_images_id='".$imageId."' ".
            " AND hash_file_name!='' ".
            "ORDER BY language_id"
        );
        if ( tep_db_num_rows($get_images_r)>0 ) {
            while( $get_image = tep_db_fetch_array($get_images_r) ) {
                $fsImageName = CommonImages::getFSCatalogImagesPath().'products' . DIRECTORY_SEPARATOR. $productsId . DIRECTORY_SEPARATOR . $imageId . DIRECTORY_SEPARATOR.$get_image['hash_file_name'];
                if ( is_file($fsImageName) ) {
                    $checkUniq = tep_db_fetch_array(tep_db_query(
                        "SELECT COUNT(*) AS c ".
                        "FROM ".TABLE_PRODUCTS_IMAGES_DESCRIPTION." ".
                        "WHERE orig_file_name='".tep_db_input($get_image['orig_file_name'])."'"
                    ));
                    if ( $checkUniq['c']>1 ) {
                        $languageCode = ($get_image['language_id']>0?\common\classes\language::get_code($get_image['language_id']):'main');
                        $newImageName = $imageId.'_'.$languageCode.'/'.$get_image['orig_file_name'];
                        if ( array_key_exists('orig_file_name_'.$languageCode, $parentData) ) {
                            $parentData['orig_file_name_'.$languageCode] = $newImageName;
                        }
                        $get_image['orig_file_name'] = $newImageName;
                    }
                    $filesAdd[] = [
                        'filename' => $fsImageName,
                        'localname' => 'images/'.$get_image['orig_file_name'],
                    ];
                }
            }
        }

        if ( $parentData ) {
            $this->imagesWriter->write($parentData);
        }


        return $filesAdd;
        
    }

    public function importRow($data, Messages $message)
    {
        $filename = $this->import_folder.'products_images.csv';
        $readerClass = 'CSV';
        $reader = \Yii::createObject([
            'class' => 'backend\\models\\EP\\Reader\\' . $readerClass,
            'filename' => $filename,
            //'input_encoding' => 'ISO-8859-15',
        ]);
        $transform = new Transform();
        $transform->setProviderColumns($this->imagesProvider->getColumns());
/*        if (isset($this->job_configure['remap_columns']) && is_array($this->job_configure['remap_columns'])) {
            $transform->setTransformMap($this->job_configure['remap_columns']);
        }*/

        while($data = $reader->read()){
            $data = $transform->transform($data);
            $this->imagesProvider->importRow($data, $message);
        }
        $this->imagesProvider->postProcess($message);

        return false;
    }

}