<?php
/**
 * This file is part of Loaded Commerce.
 *
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace backend\models\EP\Reader;


class ZIP implements ReaderInterface
{
    public $filename;

    protected $file_handle;

    public function readColumns()
    {
        return [];
    }

    public function read()
    {
        return false;
    }

    public function currentPosition()
    {
        // TODO: Implement currentPosition() method.
    }

    public function setDataPosition($position)
    {
        // TODO: Implement setDataPosition() method.
    }

    public function getProgress()
    {
        // TODO: Implement getProgress() method.
    }


}