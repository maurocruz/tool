<?php
namespace Plinct\Tool\FileSystem;

class FileSystem {
    private static $PATHFILE;

    public static function setPathfile(string $filename): void {
        $docroot = filter_input(INPUT_SERVER,'DOCUMENT_ROOT');
        self::$PATHFILE = strpos($filename, $docroot) !== false ? $filename : $docroot . $filename;
    }


    public function moveUploadFile(string $filename, string $destination) {

    }

    public static function makeDirectory(string $directory, int $mode, bool $recursive) {
        self::setPathfile($directory);
        if (!is_dir(self::$PATHFILE)) {
            $oldumask = umask(0);
            mkdir(self::$PATHFILE, $mode, $recursive);
            umask($oldumask);
        }
    }

    public static function listDirectories(string $directory) {
        $response = false;
        $docroot = filter_input(INPUT_SERVER,'DOCUMENT_ROOT');
        if (substr($directory,-1) !== "/") $directory .= "/";
        $directoryPath =  $docroot . $directory;
        if (is_dir($directoryPath)) {
            $response[] = $directory;
            foreach (scandir($directoryPath) as $file) {
                if (!in_array($file, array(".", "..", "thumbs"))) {
                    if (is_dir($directoryPath . $file)) {
                        $subdir = self::listDirectories(str_replace($docroot,"",$directoryPath.$file));
                        if ($subdir != false) {
                            $response = array_merge($response,$subdir);
                        }
                    }
                }
            }
        }
        return $response;
    }
}