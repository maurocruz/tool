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
        self::setPathfile($directory);
        if (is_dir(self::$PATHFILE)) {
            $response[] = $directory;
            foreach (scandir(self::$PATHFILE) as $file) {
                if (!in_array($file, array(".", "..", "thumbs"))) {
                    if (is_dir(self::$PATHFILE . $file)) {
                        $subdir = self::listDirectories($directory . $file . DIRECTORY_SEPARATOR);
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