<?php


namespace WhyooOs\Util;


class Util
{
    /**
     * can also handle fieldnames like inventoryItem.currentStockLevel for embedded stuff
     *
     * @param $document
     * @param $arr
     * @param $fieldNames
     */
    public static function copyDataFromArrayToDocument($document, array $arr, array $fieldNames)
    {
        foreach ($fieldNames as $attributeName) {
            $myDoc = $document;
            $myArr = $arr;

            $subfields = explode('.', $attributeName);
            $lastSubfield = array_pop($subfields);
            $setterName = 'set' . ucfirst($lastSubfield);

            foreach ($subfields as $subfield) {
                $getterName = 'get' . ucfirst($subfield);
                $myDoc = $myDoc->$getterName();

                $myArr = @$myArr[$subfield];
            }
            $myDoc->$setterName(@$myArr[$lastSubfield]);
        }

    }


    /**
     * @return bool
     */
    public static function isLive()
    {
        return preg_match('#^/home/marc/devel/#', __DIR__) == 0;
    }


    public static function createMongoId()
    {
        return new \MongoId(); // deprecated
        // LATER: return new \MongoDB\BSON\ObjectId();
    }


    /**
     * @param $str
     * @return int
     */
    public static function isMongoId($str)
    {
        return preg_match('/^[a-f\d]{24}$/i', $str);
    }

    public static function toMongoId($str)
    {
        if (self::isMongoId($str)) {
            return new \MongoId($str);
            // LATER: fix .. it is deprecated
        }
    }


    /**
     * @param  \Doctrine\Common\Collections\ArrayCollection|\Doctrine\ODM\MongoDB\Cursor|\MongoCursor|array $arr
     * @return array
     */
    public static function toArray($arr, $useKeys = true)
    {
        if (is_array($arr)) {
            return $arr;
        }
        return iterator_to_array($arr, $useKeys);
        #return $arr->toArray();
    }


    public static function waitKeypress()
    {
        echo "\npress enter\n";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        return trim($line);
    }



    public static function humanReadableSize($size)
    {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $size ? round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    }

    /**
     * @param $row
     * @param string $propertyName eg 'calculation.revenueGross'
     * @return mixed
     */
    public static function getPropertyDeep($row, $propertyName)
    {
        $parts = explode('.', $propertyName);
        foreach ($parts as $part) {
            //dump(@get_class($row));
            if (is_null($row)) {
                //dump($parts, $rowClone);
                return null;
            }
            if (is_array($row)) {
                $row = $row[$part];
            } else {
                $getterName = "get" . ucfirst($part);
                $row = $row->$getterName();
            }
        }
        //dump($row);
        return $row;
    }


    /**
     * removes namespace from FQCN of obj
     * @param $obj
     * @return string class name without namespace
     */
    public static function getClassNameShort($obj, $numBack = 1)
    {
        return self::removeNamespace(get_class($obj), $numBack);
    }


    public static function removeNamespace(string $class, $numBack = 1)
    {
        $tmp = explode('\\', $class);

        return implode(".", array_slice($tmp, count($tmp) - $numBack, $numBack));
    }


    /**
     * dump + die
     * @param $searchTerm
     */
    public static function dd()
    {
        $ddSource = debug_backtrace()[0];
        echo("{$ddSource['file']}:{$ddSource['line']}<br>\n");
        foreach (func_get_args() as $arg) {
            dump($arg);
        }
        die();
    }


    /**
     * used for calculation of PricePerPiece
     *
     * @param $number
     * @param int $precision
     * @return float|int
     */
    public static function roundUp($number, $precision = 2)
    {
        $fig = pow(10, $precision);
        return ceil($number * $fig) / $fig;
    }


    public static function roundDown($number, $precision = 2)
    {
        $fig = pow(10, $precision);
        return floor($number * $fig) / $fig;
    }


    public static function simpleLogError($string)
    {
        file_put_contents('/tmp/mcx-simple-log-error.txt', date('Y-m-d H:i') . "\t" . $string . "\n", FILE_APPEND);
    }

}