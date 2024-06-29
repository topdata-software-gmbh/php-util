<?php


namespace TopdataSoftwareGmbH\Util;

use App\Entity\Tenant\ListImportsV2\V2Column\FieldColumn\V2ColumnFieldImported;
use SqlFormatter;

/**
 * 07/2017
 */
class UtilDebug
{
    const EMOTICON_DUMP_PRE      = '⬇️';
    const EMOTICON_DUMP_DIE_PRE  = '💥⬇️';
    const EMOTICON_DUMP_POST     = '🙄';
    const EMOTICON_DUMP_DIE_POST = '💥🙄';

    //    const EMOTICON_DUMP          = '🙄';
//    const EMOTICON_DUMP_DIE      = '💥🙄';
//    const EMOTICON_DUMP_DIE = '💥🙄🔥';


    private static $timeProfilers = [];


    /**
     * uses jdorn/sql-formatter
     * @param string $sql
     * @return String formatted query (html or ansi)
     */
    static function getSqlFormatted(string $sql)
    {
        return SqlFormatter::format($sql);
    }


    /**
     * dump
     */
    public static function d()
    {
        foreach (func_get_args() as $arg) {
            dump($arg);
        }
        self::_echoCaller(self::EMOTICON_DUMP_POST);
    }


    /**
     * dump + die
     */
    public static function dd()/*: never*/
    {
        foreach (func_get_args() as $arg) {
            dump($arg);
        }

        self::_echoCaller(self::EMOTICON_DUMP_DIE_POST);
        die();
    }

    /**
     * dc is alias for dumpClass
     *
     * 05/2023 created
     *
     * @param $object1 , object2, ...
     * @return void
     */
    public static function dc(): void
    {
        foreach (func_get_args() as $arg) {
            dump(UtilReflection::getClassInheritance($arg));
        }
        self::_echoCaller(self::EMOTICON_DUMP_POST);
    }

    /**
     * dcd is alias for dumpClass + die
     *
     * 05/2023 created
     *
     * @param $object1 , object2, ...
     * @return never-return
     */
    public static function dcd()/*: never*/
    {
        foreach (func_get_args() as $arg) {
            dump(UtilReflection::getClassInheritance($arg));
        }

        self::_echoCaller(self::EMOTICON_DUMP_DIE_POST);
        die();
    }


    /**
     * former DumpSql()
     */
    public static function ds(string $sql)
    {
        echo SqlFormatter::format($sql);
        self::_echoCaller(self::EMOTICON_DUMP_POST);
    }

    /**
     * former DumpSqlDie()
     */
    public static function dsd(string $sql)
    {
        echo SqlFormatter::format($sql);
        self::_echoCaller(self::EMOTICON_DUMP_DIE_POST);
        die();
    }


    /**
     * @param string $id
     */
    public static function startTimeProfiling(string $id = 'default'): void
    {
        self::$timeProfilers[$id] = microtime(true);
    }

    /**
     * @param string $id
     * @return float seconds
     */
    public static function stopTimeProfiling(string $id = 'default'): float
    {
        $length = microtime(true) - self::$timeProfilers[$id];
        self::$timeProfilers[$id] = null;

        return $length;
    }

    /**
     * calls UtilSymfony::toArray to each parameter before dumping it
     *
     * @param 08/2018
     */
    public static function d2($mainCriteriaSet)
    {
        $ddSource = debug_backtrace()[0];
        echo $ddSource['file'] . ':' . $ddSource['line'] . UtilTextOutput::getNewline();
        foreach (func_get_args() as $arg) {
            dump(UtilSymfony::toArray($arg));
        }
    }

    /**
     * dumpMemory
     *
     * 04/2023 created
     *
     */
    public static function dm(): void
    {
        self::_echoCaller(bWithNewline: false);
        echo "    " .
            UtilFormatter::formatBytes(memory_get_usage(true)) . ' / ' .
            UtilFormatter::formatBytes(memory_get_peak_usage(true)) . ' / ' .
            ini_get('memory_limit') . UtilTextOutput::getNewline();
    }

    /**
     * 05/2023 created to avoid code duplication
     *
     * @return void
     */
    private static function _echoCaller(?string $prefix = null, bool $bWithNewline = true): void
    {
        $ddSource = debug_backtrace()[1];
        if ($prefix) {
            echo $prefix . UtilTextOutput::escapeStringForHtml(' >>>> ');
        }
        echo basename($ddSource['file']) . ':' . $ddSource['line'];
        if ($bWithNewline) {
            echo UtilTextOutput::getNewline();
        }
    }

    /**
     * 02/2024 created (cm)
     */
    public static function dumpDebugBacktrace()
    {
        $backtraces = debug_backtrace();
        foreach ($backtraces as $trace) {
            echo "    " . basename($trace['file']) . ':' . $trace['line'] . UtilTextOutput::getNewline();
        }
        self::_echoCaller(self::EMOTICON_DUMP_POST);
    }


    /**
     * 05/2023 created to avoid code duplication
     *
     * @return void
     */
    private static function _getCaller()
    {
        $ddSource = debug_backtrace()[1];
        return basename($ddSource['file']) . ':' . $ddSource['line'] . UtilTextOutput::getNewline();
    }




    /**
     * gron must be installed
     *
     * @see https://github.com/tomnomnom/gron?tab=readme-ov-file#installation
     *
     * 06/2024 created
     * 06/2024 mode from UtilJson to UtilDebug
     */
    public static function gron(mixed $data): string
    {
        $pathJsonFile = '/tmp/gron-' . uniqid() . '.json';
        UtilJson::saveJsonFile($pathJsonFile, $data);
        // run gron on the file
        $cmd = 'gron ' . $pathJsonFile;
        $output = shell_exec($cmd);
        // cleanup
        unlink($pathJsonFile);

        return $output;
    }


    /**
     * tree - Converts a data structure into an ASCII tree
     *
     * recursive function
     *
     * 06/2024 created
     *
     * @param array|object $data The data structure to convert.
     * @param string $prefix Used for formatting the tree (internal use).
     * @return string The ASCII representation of the tree.
     */
    public static function tree(array|object $data, string $prefix = ''): string
    {
        $output = '';
        $isLast = function ($key, $array) {
            return $key === array_key_last($array);
        };

        foreach ($data as $key => $value) {
            $output .= $prefix;
            if ($isLast($key, $data)) {
                $output .= '└── ';
                $newPrefix = $prefix . '    ';
            } else {
                $output .= '├── ';
                $newPrefix = $prefix . '│   ';
            }
            if (is_array($value) || is_object($value)) {
                $output .= "$key\n";
                $output .= self::tree($value, $newPrefix);
            } else {
                $output .= "$key: $value\n";
            }
        }

        return $output;    }


}
