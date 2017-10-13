<?php
/**
 * Created by PhpStorm.
 * User: tkach
 * Date: 05/05/17
 * Time: 16:02
 */

namespace backend\models\EP;


class Transform
{
    protected $columnMap = [];
    protected $mapping = [];

    public function setProviderColumns($columns)
    {
        $this->columnMap = $columns;
        if ( count($this->mapping)==0 ) $this->mapping = array_flip($columns);
    }

    public function setTransformMap($external)
    {
        $this->mapping = $external;
    }

    public function transform($data)
    {
        if ( !is_array($data) ) return $data;

        $transformedData = [];
        foreach( $this->mapping as $file_key=>$db_key ) {
            if ( !array_key_exists($file_key, $data) ) continue;
            $transformedData[$db_key] = $data[$file_key];
        }

        return $transformedData;
    }
}