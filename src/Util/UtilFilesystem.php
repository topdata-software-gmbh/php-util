<?php

namespace TopdataSoftwareGmbH\Util;


# see http://docs.python.org/2/library/os.path.html for inspiration
# will not work under windows .. it assumes slash (/) as separator

class UtilFilesystem
{
    /**
     * @param string $pathFile
     * @param int $idxStart
     * @return null|string null if no free filename found (after 999999999 tries)
     *
     * 03/2022 where is this used?
     */
    public static function getNextFreeFilename(string $pathFile, int $idxStart = 1): ?string
    {
        if (!file_exists($pathFile)) {
            return $pathFile;
        }

        $ext = self::getExtension($pathFile);
        $base = self::getWithoutExtension($pathFile);
        for ($idx = $idxStart; $idx < 999999999; $idx++) {
            $newFilePath = $base . '-' . $idx . '.' . $ext;
            if (!file_exists($newFilePath)) {
                return $newFilePath;
            }
        }

        return null; // FAILED
    }


    /**
     * returns LOWERCASE extension
     *
     * @param string $filePath
     * @return string eg "png"
     */
    public static function getExtension(string $filePath)
    {
        $pos = strrpos($filePath, '.');
        if ($pos !== false) {
            return strtolower(substr($filePath, $pos + 1));
        } else { // no extension
            return '';
        }
    }

    /**
     * alias
     *
     * @param $filename
     * @return string
     */
    public static function stripExtension(string $filename): string
    {
        return self::getWithoutExtension($filename);
    }


    /**
     * 03/2022 TODO: add param for number of dots (default 1) .. to eg remove extension from file like "001.palette.json"
     * @param string $pathFile
     * @return string file path without extension eg "/tmp/somefile.txt"
     */
    public static function getWithoutExtension(string $pathFile): string
    {
        $pos = strrpos($pathFile, '.');
        if ($pos !== false) {
            return substr($pathFile, 0, $pos);
        } else { // no extension
            return $pathFile;
        }
    }


    /**
     * not recursive .. returns files and directories ... relative to $path
     * 02/2021 TODO: where is this used?
     * @param string $path
     * @return string[]
     */
    public static function scanDir(string $path): array
    {
        $ret = [];
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $ret[] = $entry;
                }
            }
            closedir($handle);
        }

        asort($ret);

        return $ret;
    }


//    /**
//     * returns RELATIVE paths (relative to $pathDir)
//     *
//     * recursive .. returns files only
//     * 02/2021 UNUSED - removed
//     */
//    public static function scanDirForFilesRecursive($pathDir)
//    {
//        $filenames = [];
//        if ($handle = opendir($pathDir)) {
//            while (false !== ($entry = readdir($handle))) {
//                if ($entry != "." && $entry != "..") {
//                    $fullPath = self::joinPaths($pathDir, $entry);
//                    if (is_dir($fullPath)) {
//                        $filenames = array_merge($filenames, self::scanDirForFilesRecursive($fullPath, self::joinPaths($prefix, $entry)));
//                    } else {
//                        $filenames[] = $entry;
//                    }
//                }
//            }
//            closedir($handle);
//        }
//
//        return $filenames;
//    }



    /**
     * private recursive helper
     *
     * @return string[]
     */
    private static function _findFilesRecursive(string $pathDir, ?string $pattern, bool $bReturnRelativePaths, string $prefix)
    {
        $ret = [];
        $dir = dir($pathDir);
        while (false !== ($file = $dir->read())) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = self::joinPaths($pathDir, $file);
            if (is_dir($fullPath)) {
                $ret = array_merge($ret, self::_findFilesRecursive($fullPath, $pattern, $bReturnRelativePaths, self::joinPaths($prefix, $file)));
            }
            if (is_file($fullPath) && ($pattern===null || preg_match($pattern, $fullPath))) {
                $ret[] = $bReturnRelativePaths ? self::joinPaths($prefix, $file) : $fullPath;
            }
        }

        return $ret;
    }


    /**
     * 03/2022 TODO rename to findFilesByPattern
     *
     * scans $pathDir for files recursively
     *
     * returns full or relative paths of found files
     *
     * from smartdonation
     * buggy? not working correctly?
     * similar to python's os.walk
     *
     * 04/2018 fixed .. does only return files, no directories
     * 02/2021 renamed findFilesRecursive --> findFiles
     * 02/2021 parameter $bReturnRelativePaths added
     *
     * @param string $pathDir
     * @param string|null $pattern a regex pattern, eg '~\.png$~'
     * @param bool $bReturnRelativePaths - if true it returns the relative paths (ie the filenames if not in a subdirectory)
     * @return string[] full or relative paths of found files
     */
    public static function findFiles(string $pathDir, ?string $pattern = null, bool $bReturnRelativePaths = false)
    {
        return self::_findFilesRecursive($pathDir, $pattern, $bReturnRelativePaths, '');
    }


    /**
     * 08/2017
     * from: https://stackoverflow.com/a/2021729/2848530
     *
     * @param $filename
     * @return false|string
     */
    public static function sanitizeFilename($filename)
    {
        // Remove anything which isn't a word, whitespace, number
        // or any of the following caracters -_~,;[]().
        // If you don't need to handle multi-byte characters
        // you can use preg_replace rather than mb_ereg_replace
        // Thanks @Łukasz Rysiak!
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        // Remove any runs of periods (thanks falstro!)
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);

        return $filename;
    }

    /**
     * eg: "example.csv" --> "example.xls"
     * 09/2017
     *
     * @param string $pathCsv
     * @param string $newExtension
     * @return string
     */
    public static function replaceExtension(string $pathCsv, string $newExtension): string
    {
        return self::getWithoutExtension($pathCsv) . '.' . ltrim($newExtension, '.');
    }


    /**
     * rm -rf /path/to/dir
     *
     * @param $pathDirectory
     */
    public static function deleteDirectoryRecursive($pathDirectory)
    {
        try {
            $it = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($pathDirectory),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $file) {
                if (in_array($file->getBasename(), ['.', '..'])) {
                    continue;
                } elseif ($file->isDir()) {
                    rmdir($file->getPathname());
                } elseif ($file->isFile() || $file->isLink()) {
                    unlink($file->getPathname());
                }
            }
        } catch (\Exception $e) {
            // ...
        }
        @rmdir($pathDirectory);
    }


    /**
     * returns alphabetically sorted filtered list of files
     * used by push4
     *
     * @param string $pathDirectory
     * @param bool $bRecursive
     * @return string[]
     */
    public static function findImages(string $pathDirectory, $bRecursive = true)
    {
        $extensionsLowerCase = ['jpg', 'jpeg', 'png', 'gif'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        return self::findByExtensionsOrMimeTypes($pathDirectory, $extensionsLowerCase, $allowedMimeTypes, $bRecursive);
    }

    /**
     * used by art-experiments
     * 04/2022 TODO: remove this one (not used anymore, except by findImages)
     *
     * @param string $pathDirectory
     * @param string[] $extensionsLowerCase
     * @param string[] $allowedMimeTypes
     * @param bool $bRecursive
     * @param bool $bReturnFullPath
     * @return array|false
     */
    public static function findByExtensionsOrMimeTypes(string $pathDirectory, array $extensionsLowerCase,
                                                       array $allowedMimeTypes = null, bool $bRecursive = true, bool $bReturnFullPath = false)
    {
        if ($bRecursive) {
            $files = self::findFiles($pathDirectory, null, true);
        } else {
            $files = self::scanDir($pathDirectory);
        }

        // ---- filter
        $ret = array_filter($files, function ($filename) use ($extensionsLowerCase, $allowedMimeTypes, $pathDirectory) {
            //echo "#".self::getExtension( $filename);
            $ext = self::getExtension($filename);
            if (empty($ext)) {
                if (empty($allowedMimeTypes)) {
                    return false;
                }
                // filename has no extension .. we use mimeType
                $mime = mime_content_type($pathDirectory . '/' . $filename);
                return in_array($mime, $allowedMimeTypes);
            }

            // ---- instead of using in_array() we iterate manually to circumvent problem with extensions like "palette.json"
            foreach($extensionsLowerCase as $extension) {
                if(UtilString::endsWithCaseInsensitive($filename, $extension)) {
                    return true;
                }
            }

            return false;
        });

        // ---- sort
        asort($ret);

        if ($bReturnFullPath) {
            return array_values(array_map(function ($x) use ($pathDirectory) {
                return self::joinPaths($pathDirectory, $x);
            }, $ret));
        } else {
            return array_values($ret);
        }
    }


    /**
     * 05/2020 created
     * used by push4
     * 08/2020 used by webpack migrator
     * 04/2022 added param $bReturnFullPath
     * 04/2022 used by artgen instead of self::findByExtensionsOrMimeTypes()
     * 04/2022 braking change: $bReturnFullPath set default to true
     *
     * @param string $pathDirectory
     * @param array|string $extensionsLowerCase
     * @param bool $bRecursive
     * @param bool $bReturnFullPath
     * @return array
     */
    public static function findByExtensions(string $pathDirectory, $extensionsLowerCase, bool $bRecursive = true, $bReturnFullPath = true)
    {
        if(is_string($extensionsLowerCase)) {
            $extensionsLowerCase = [$extensionsLowerCase];
        }

        if ($bRecursive) {
            $files = self::findFiles($pathDirectory, null, true);
        } else {
            $files = self::scanDir($pathDirectory);
        }

        $ret = array_filter($files, function ($filename) use ($extensionsLowerCase) {
            // ---- instead of using in_array() we iterate manually to circumvent problem with extensions like "palette.json"
            foreach($extensionsLowerCase as $extension) {
                if(UtilString::endsWithCaseInsensitive($filename, $extension)) {
                    return true;
                }
            }
            return false;
        });

        asort($ret);

        if ($bReturnFullPath) {
            return array_values(array_map(function ($x) use ($pathDirectory) {
                return self::joinPaths($pathDirectory, $x);
            }, $ret));
        } else {
            return array_values($ret);
        }
    }


    /**
     * 03/2022 created
     *
     * @return string
     */
    public static function joinPaths(): string
    {
        $paths = [];

        foreach (func_get_args() as $arg) {
            if ($arg !== '' && $arg !== '.') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', join('/', $paths));
    }


    /**
     * not recursive
     *
     * @param string $path
     * @return array directory names relative to $path (sorted by name)
     */
    public static function findDirectories($path)
    {
        $files = self::scanDir($path);
        $dirs = array_filter($files, function ($filename) use ($path) {
            return is_dir(self::joinPaths($path, $filename));
        });
        asort($dirs);

        return array_values($dirs);
    }

    /**
     * @param string $path
     * @param int $perm
     */
    public static function mkdirIfNotExists(string $path, int $perm = 0777)
    {
        if (!is_dir($path)) {
            self::mkdir($path, $perm);
        }
    }

    /**
     * creates directory recursively with the right permissions
     *
     * @param string $path
     * @param int $perm
     * @throws \Exception
     */
    public static function mkdir(string $path, int $perm = 0777 /*, $maxLevelsDown=3*/)
    {
        $parts = explode('/', $path);
//		$offset = count($parts) - $maxLevelsDown;
//		$parts = array_slice( $parts, $offset, $maxLevelsDown);

        $currentPath = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue; // bugfix/workaround for absolute path
            }
            $currentPath .= '/' . $part;
            if (!is_dir($currentPath)) {
                if (!@mkdir($currentPath, $perm)) {
                    $error = error_get_last();
                    throw new \Exception("could not create directory $currentPath - " . $error['message']);
                }
                @chmod($currentPath, $perm);
            }
            if (!@chdir($currentPath)) {
//				return false
                throw new \Exception("could not enter $currentPath");
            }
        }
    }


    /**
     * move content of one directory to another
     *
     * @param string $pathOld
     * @param string $pathNew
     */
    public static function moveFiles(string $pathOld, string $pathNew)
    {
        foreach (scandir($pathOld) as $fname) {
            if ($fname != '.' && $fname != '..') {
                rename(self::joinPaths($pathOld, $fname), self::joinPaths($pathNew, $fname));
            }
        }
    }




//    # similar to python's os.walk
//    // IS BUGGY/NOT WORKING CORRECTLY
//    public function findFilesRecursive($dir = '.', $pattern = '~.*~')
//    {
//        $ret = array();
//        $prefix = $dir . '/';
//        $dir = @dir($dir);
//        if (!is_object($dir)) {
//            return [];
//        }
//        while (false !== ($file = $dir->read())) {
//            if ($file === '.' || $file === '..') {
//                continue;
//            }
//            $file = $prefix . $file;
//            if (is_dir($file)) {
//                $ret = array_merge($ret, self::findFilesRecursive($file, $pattern));
//            }
//            if (preg_match($pattern, $file)) {
//                #echo $file . "\n";
//                $ret[] = $file;
//            }
//        }
//        return $ret;
//    }


    /**
     * not recursive .. returns directories (alphabetically sorted)
     */
    public static function getDirs($path)
    {
        $dirs = [];
        if ($handle = opendir($path)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != ".." && is_dir(self::joinPaths($path, $entry))) {
                    $dirs[] = realpath(self::joinPaths($path, $entry));
                }
            }
            closedir($handle);
        }
        sort($dirs);

        return $dirs;
    }


    /**
     * returns FULL PATHES to all files (no directories, recursive)
     */
    public static function getFiles($path)
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        $files = [];
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }
            $files[] = $file->getPathname();
        }

        return $files;
    }


    /**
     * former rawDataToPhysicalFile
     *
     * moved from UtilImage to here
     * decode file and save to temporary file
     *
     * @param $base64EncodedString
     * @param $pathDestDir
     * @return string
     * @throws \Exception
     */
    public static function base64ToPhysicalFile($base64EncodedString, string $pathDestDir)
    {
        $binData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64EncodedString));

        $extension = self::guessExtensionOfRawData($binData);

        // how file will be named
        $destFilename = sprintf('%s-%s.%s', md5(microtime()), md5(rand() . 'xxx' . rand()), $extension);
        $pathDest = UtilFilesystem::joinPaths($pathDestDir, $destFilename);
        file_put_contents($pathDest, $binData);

        return $pathDest;
    }

    /**
     * FIXME: currently it onlt works for images
     * used by cloudlister
     *
     * @param $fullPathFile
     * @return string
     */
    public static function physicalFileToBase64($fullPathFile)
    {
        $type = pathinfo($fullPathFile, PATHINFO_EXTENSION);
        $data = file_get_contents($fullPathFile);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

        return $base64;
    }


    /**
     * @param string $pathDirectory
     * @param string $filename
     * @return string $newFilename the new filename test.jpg, test-1.jpg, test-2.jpg etc
     */
    public static function getUniqueFilename(string $pathDirectory, string $filename)
    {
        $newFilename = $filename;

        $path_parts = pathinfo($filename);

        $counter = 1;
        while (file_exists("$pathDirectory/$newFilename")) {
            $newFilename = $path_parts['filename'] . "-$counter." . $path_parts['extension'];
            $counter++;
        }
        return $newFilename;
    }


    /**
     * eg: removeLeadingDirectories('/tmp/aaa/bbb/ccc/ddd/eee', 2) ==> 'ddd/eee'
     *
     * @param string $path
     * @param int $numBack
     * @return string
     */
    public static function removeLeadingDirectories(string $path, int $numBack = 1, string $directorySeparator = '/'): string
    {
        $tmp = explode($directorySeparator, $path);

        return implode($directorySeparator, array_slice($tmp, count($tmp) - $numBack, $numBack));
    }


    /**
     * 09/2017
     *
     * for deleting older .csv files (scraper)
     *
     * @param $dirPath
     * @param $numDays
     */
    public static function deleteOldFiles($dirPath, $numDays)
    {
        if (file_exists($dirPath)) {
            foreach (new \DirectoryIterator($dirPath) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if (time() - $fileInfo->getCTime() >= 3600 * 24 * $numDays) {
                    // dump("unlink: " . $fileInfo->getRealPath());
                    unlink($fileInfo->getRealPath());
                }
            }
        }
    }


    /**
     * 10/2017 used by cloudlister
     * source https://stackoverflow.com/a/21409562/2848530
     *
     * @param $pathDir
     * @return int size in bytes
     */
    public static function getDirectorySize($pathDir)
    {
        $bytesTotal = 0;
        $pathDir = realpath($pathDir);
        if ($pathDir !== false && $pathDir != '' && file_exists($pathDir)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pathDir, \FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytesTotal += $object->getSize();
            }
        }

        return $bytesTotal;
    }

    /**
     * 07/2018 some hack
     * @param $binData
     * @return string
     */
    public static function guessExtensionOfRawData($binData)
    {
        // check mime type .. must be an image
        $finfo = new \finfo(FILEINFO_MIME);
        $mime = $finfo->buffer($binData);
        if (!preg_match('#.*/(\w+)#', $mime, $gr)) {
            throw new \Exception("mimetype fail $mime");
        }

        return $gr[1];
    }


    /**
     * 07/2018 used by Schlegel
     * 01/2023 used by CM, optional parameter $newExtension added
     *
     * @param string $pathFile
     * @param string $suffix
     * @param string|null $newExtension
     * @return string
     */
    public static function appendSuffix(string $pathFile, string $suffix, ?string $newExtension = null)
    {
        $base = self::getWithoutExtension($pathFile);
        $ext = $newExtension ? ltrim($newExtension, '.') : self::getExtension($pathFile);

        return $base . $suffix . '.' . $ext;
    }

    /**
     * prepends a prefix to a filename of a path
     *
     * 03/2022 created
     * (not) used in art-experiments
     *
     * @param string $pathFile
     * @param string $suffix
     * @return string
     */
    public static function prependPrefix(string $pathFile, string $suffix): string
    {
        return self::joinPaths(dirname($pathFile), $suffix.basename($pathFile));
    }



    /**
     * 03/2020
     *
     * @param string $path
     * @return string|string[]|null
     */
    public static function normalizePath(string $path)
    {
        $r = [
            '~/{2,}~'                  => '/',
            '~/(\./)+~'                => '/',
            '~([^/\.]+/(?R)*\.{2,}/)~' => '',
            '~\.\./~'                  => '',
            '~/[^/\.]+/\.\.$~'         => '', // 04/2020 added: /a/b/c/.. --> a/b
        ];
        return preg_replace(array_keys($r), array_values($r), $path);
    }

    /**
     * 04/2021 created (for art-experiments/push4)
     *
     * @param string[] $filePaths
     */
    public static function deleteManyFiles(array $filePaths)
    {
        foreach($filePaths as $fullFilePath) {
            unlink($fullFilePath);
        }
    }


}
