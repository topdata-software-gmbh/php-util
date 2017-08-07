<?php

namespace WhyooOs\Util;


/**
 * Class UtilException
 * @package WhyooOs\Util
 */
class UtilException
{


    /**
     * jTraceEx() - provide a Java style exception trace
     * @param $exception
     * @param $seen - array passed to recursive calls to accumulate trace lines already seen
     *                     leave as NULL when calling this function
     * @return string  nicely formatted exception
     */
    public static function jTraceEx(\Exception $e, $seen = null)
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) $seen = array();
        $trace = $e->getTrace();
        $prev = $e->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
        $file = $e->getFile();
        $line = $e->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace) + 1);
                break;
            }
            $result[] = sprintf(' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line);
            if (is_array($seen))
                $seen[] = "$file:$line";
            if (!count($trace))
                break;
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }

        $result = join("\n", $result);
        if ($prev) {
            $result .= "\n" . self::jTraceEx($prev, $seen);
        }

        return $result;
    }


    /**
     * used by ebaygen
     *
     * installs exception handler which writes Timestamp + jTraceEx to STDERR
     * useful to see in error logs of supervisor when the error happens
     */
    public static function installExceptionHandler()
    {
        set_exception_handler(function (\Throwable $ex) {
            fwrite(STDERR, "\n\n------------------------ " . date("Y-m-d H:i:s") . " ------------------------\n\n");
            fwrite(STDERR, self::jTraceEx($ex) . "\n\n");
        });
    }


}