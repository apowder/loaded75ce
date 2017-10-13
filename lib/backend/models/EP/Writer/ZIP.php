<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP\Writer;


use backend\models\EP\Exception;
use common\helpers\Product;

class ZIP implements WriterInterface
{

    public $filename;

    protected $tmpfilename = false;
    /**
     * @var \ZipArchive
     */
    protected $file_handle;
    protected $_first_write = true;

    protected $columns = [];

    public function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    public function write(array $writeData)
    {
        if ( $this->_first_write ) {
            $this->_first_write = false;
            $this->file_handle = new \ZipArchive();
            if ( $this->filename=='php://output' ) {
                $this->tmpfilename = tempnam(sys_get_temp_dir(), 'ep_zip_write');
                $archiveStatus = $this->file_handle->open($this->tmpfilename, \ZipArchive::OVERWRITE);
            }else {
                $archiveStatus = $this->file_handle->open($this->filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            }
            if ( $archiveStatus!==true ) {
                throw new Exception('Create file error ['.$archiveStatus.']');
            }
        }
        foreach ($writeData as $writeFile) {
            $this->file_handle->addFile($writeFile['filename'], $writeFile['localname']);
        }
    }

    public function close()
    {
        $this->file_handle->close();
        if ( $this->filename=='php://output' ) {
            readfile($this->tmpfilename);
            unlink($this->tmpfilename);
        }
    }
}