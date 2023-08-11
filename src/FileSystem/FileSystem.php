<?php

declare(strict_types=1);

namespace Plinct\Tool\FileSystem;

use Directory;
use Plinct\Tool\StringTool;

class FileSystem
{
	/**
	 * @var string
	 */
  private static string $PATHFILE;
	/**
	 * @var Directory|false|null
	 */
	private ?Directory $dir = null;

	/**
	 * @param string|null $filename
	 */
	public function __construct(string $filename = null)
	{
		if ($filename && is_dir($this->pathFile($filename))) {
			$this->dir = dir($this->pathFile($filename));
		}
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	private function pathFile(string $filename): string
	{
		$docroot = filter_input(INPUT_SERVER,'DOCUMENT_ROOT');
		return strpos($filename, $docroot) !== false ? $filename : $docroot . $filename;
	}

	/**
	 * @return Directory|null
	 */
	public function getDir(): ?Directory
	{
		return $this->dir;
	}

	/**
	 * @param string $dir
	 */
	public function setDir(string $dir): void
	{
		$this->dir = is_dir($this->pathFile($dir)) ? dir($this->pathFile($dir)) : null;
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public function file_exists(string $filename): bool
	{
		return file_exists($this->pathFile($filename));
	}

	/**
	 * @param string $filename
	 * @return void
	 */
  public static function setPathfile(string $filename): void {
      $docroot = filter_input(INPUT_SERVER,'DOCUMENT_ROOT');
      self::$PATHFILE = strpos($filename, $docroot) !== false ? $filename : $docroot . $filename;
  }

	/**
	 * @param array $files
	 * @return array
	 */
  public function uploadFiles(array $files): array
  {
		$returns = [];
		foreach ($files['error'] as $key => $error) {
			$name = $files['name'][$key];
			$tmpName = $files['tmp_name'][$key];
			$type = $files['type'][$key];
			if ($error == UPLOAD_ERR_OK) {
				$destinationFile = $this->destinationFile($name, $type);
				if(move_uploaded_file($tmpName,$destinationFile)) {
					$returns[] = ['status'=>'successs','message'=>'File uploaded', 'data'=> $destinationFile];
				} else {
					$returns[] = ['status'=> 'fail','message'=>"File not Uploaded: '".$name."'"];
				}
			}
		}
		return $returns;
  }

	/**
	 * @param string $name
	 * @param string $type
	 * @return string
	 */
	private function destinationFile(string $name, string $type): string
	{
		$prefix = date("Ymd-His-");
		$extension = substr(strstr($type,"/"),1);
		$filename = pathinfo($name)['filename'];
		$newName = $prefix . substr(md5(StringTool::removeAccentsAndSpaces($filename)),0,16) . "." . $extension;
		return $this->getDir()->path . (substr($this->getDir()->path, -1) !== '/' ? DIRECTORY_SEPARATOR : null) . $newName;
	}

	/**
	 * @param string $directory
	 * @param int $mode
	 * @param bool $recursive
	 * @return void
	 */
  public static function makeDirectory(string $directory, int $mode, bool $recursive) {
      self::setPathfile($directory);
      if (!is_dir(self::$PATHFILE)) {
          $oldumask = umask(0);
          mkdir(self::$PATHFILE, $mode, $recursive);
          umask($oldumask);
      }
  }

	/**
	 * @param string $directory
	 * @return array
	 */
  public static function listDirectories(string $directory): array
  {
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
            if ($subdir) {
              $response = array_merge($response,$subdir);
            }
          }
        }
      }
    }
    return $response;
  }
}
