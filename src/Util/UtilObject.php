<?php

namespace TopdataSoftwareGmbH\Util;


/**
 * 11/2024 created
 */
class UtilObject
{
    /**
     * used by sw6shopimports
     *
     * 11/2024 created (Topdata)
     *
     * @param object $srcDocument
     * @param array $columnNames eg ['id', 'name']
     * @return array eg ['id' => 123, 'name' => 'foo']
     */
    public static function pick($srcDocument, array $columnNames): array
    {
        $ret = [];
        foreach ($columnNames as $key) {
            $ret[$key] = $srcDocument->$key;
        }
        return $ret;
    }

}