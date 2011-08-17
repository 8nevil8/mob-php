<?
/**
 * FileUtils - utility class for files operation.
 *
 * @author nevil
 */
class FileUtils {
    private $errors;
    private static $ignoreFiles = array('.svn', '.git', '.', '..');
    private static $instance;

    /**
     * Returns instance.
     * @return FileUtils 
     */
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new FileUtils();
        }
        return self::$instance;
    }

    private function __construct() {
        
    }

    public function removeDir($directory) {
        return $this->removeDirectory($directory);
    }

    public function insertDir($directory) {
        if (file_exists($directory)) {
            return false;
        }
        return mkdir($directory);
    }

    public function removeFile($file, $directory) {
        $directory = rtrim($directory, '/') . '/'; //ensure that directory ends with /
        $fileName = $directory . $file;
        return unlink($fileName);
    }

    public function saveFile($directory, $field, $overwrite = true, $mode=0755) {
        $fileName = md5(microtime());
        return $this->saveFileAs($fileName . '.' . $this->getFileExtension($field), $directory, $field, $overwrite, $mode);
    }

    public function saveFileAs($filename, $directory, $field, $overwrite = true, $mode=0755) {
        if ($_FILES[$field]['size'] > 0) {
            $noerrors = true;

            // Get names
            $tempName = $_FILES[$field]['tmp_name'];
            $directory = rtrim($directory, '/') . '/'; //ensure that directory ends with /
            $all = $directory . $filename;

            // Copy to directory
            if (file_exists($all)) {
                if ($overwrite) {
                    @unlink($all) || $noerrors = false;
                    $this->errors = "Upload class saveas error: unable to overwrite " . $all . "<BR>";
                    @copy($tempName, $all) || $noerrors = false;
                    $this->errors .= "Upload class saveas error: unable to copy to " . $all . "<BR>";
                    @chmod($all, $mode) || $noerrors = false;
                    $this->errors .= "Upload class saveas error: unable to copy to" . $all . "<BR>";
                }
            } else {
                @copy($tempName, $all) || $noerrors = false;
                $this->errors = "Upload class saveas error: unable to copy to " . $all . "<BR>";
                @chmod($all, $mode) || $noerrors = false;
                $this->errors .= "Upload class saveas error: unable to change permissions for: " . $all . "<BR>";
            }
            return $noerrors ? $filename : false;
        } elseif ($_FILES[$field]['size'] == 0) {
            $this->errors = "File size is 0 bytes";
            return false;
        }
    }

    function getErrors() {
        return $this->errors;
    }

    function getFilename($field) {
        return $_FILES[$field]['name'];
    }

    function getFileMimeType($field) {
        return $_FILES[$field]['type'];
    }

    function getFileSize($field) {
        return $_FILES[$field]['size'];
    }

    function getFileExtension($field) {
        return self::getExtension($this->getFilename($field));
    }

    static function getExtension($fileName) {
        return pathinfo($fileName, PATHINFO_EXTENSION);
    }

    public static function readDir($sourceDir, $addFullPath=true, $recursive = false) {
        $result = array();
        Debug::printMessage("Start reading files operations from $sourceDir.");
        if ($recursive) {
            Debug::printMessage('Read recursively<br>');
        }
        $openedDir = opendir($sourceDir);
        if ($openedDir) {
            while (false !== ($file = readdir($openedDir))) {
                if (self::isFileValid($file)) {
                    $source = $sourceDir . $file;
                    Debug::printMessage("Reading $source<br>");
                    if (!is_dir($source)) {
                        $result[] = $addFullPath ? $source : $file;
                    } else if ($recursive) {
                        FileUtils::readDir($source, $addFullPath, $recursive);
                    }
                }
            }
            Debug::printMessage("Closing $sourceDir<br>");
            closedir($openedDir);
        } else {
            die('Dir ' . $sourceDir . ' could not be opened');
        }
        return $result;
    }

    public static function getSubdirs($sourceDir, $addFullPath=true, $recursive=false) {
        $result = array();
        Debug::printMessage("Start reading subdirs operations from $sourceDir.");
        if ($recursive) {
            Debug::printMessage('Read recursively<br>');
        }
        $openedDir = opendir($sourceDir);
        if ($openedDir) {
            while (false !== ($file = readdir($openedDir))) {
                if (self::isFileValid($file)) {
                    $source = $sourceDir . $file;
                    Debug::printMessage("Reading $source<br>");
                    if (!is_file($source)) {
                        $result[] = $addFullPath ? $source : $file;
                    } else if (is_dir($source) && $recursive) {
                        FileUtils::getSubdirs($source, $addFullPath, $recursive);
                    }
                }
            }
            Debug::printMessage("Closing $sourceDir<br>");
            closedir($openedDir);
        } else {
            die('Dir ' . $sourceDir . ' could not be opened');
        }
        return $result;
    }

    public function getSingleFile($sourceDir, $createDir=true) {
        $result = null;
        if (file_exists($sourceDir)) {
            $openedDir = opendir($sourceDir);
            if ($openedDir) {
                while (false !== ($file = readdir($openedDir))) {
                    if (!is_dir($file) && self::isFileValid($file)) {
                        $result = $file;
                        break;
                    }
                }
            }
            closedir($openedDir);
        } else {
            mkdir($sourceDir);
        }
        return $result;
    }

    static function isFileValid($file) {
        return array_search($file, self::$ignoreFiles) === false;
    }

// recursive_remove_directory( directory to delete, empty )
// expects path to directory and optional TRUE / FALSE to empty
// of course PHP has to have the rights to delete the directory
// you specify and all files and folders inside the directory
    public function removeDirectory($directory, $empty=FALSE) {
        // if the path has a slash at the end we remove it here
        if (substr($directory, -1) == '/') {
            $directory = substr($directory, 0, -1);
        }
        // if the path is not valid or is not a directory ...
        if (!file_exists($directory) || !is_dir($directory)) {
            // ... we return false and exit the function
            return FALSE;
            // ... if the path is not readable
        } elseif (!is_readable($directory)) {
            // ... we return false and exit the function
            return FALSE;
            // ... else if the path is readable
        } else {
            // we open the directory
            $handle = opendir($directory);
            // and scan through the items inside
            while (FALSE !== ($item = readdir($handle))) {
                // if the filepointer is not the current directory
                // or the parent directory
                if ($item != '.' && $item != '..') {
                    // we build the new path to delete
                    $path = $directory . '/' . $item;
                    // if the new path is a directory
                    if (is_dir($path)) {
                        // we call this function with the new path
                        $this->removeDirectory($path);
                        // if the new path is a file
                    } else {
                        // we remove the file
                        unlink($path);
                    }
                }
            }
            // close the directory
            closedir($handle);
            // if the option to empty is not set to true
            if ($empty == FALSE) {
                // try to delete the now empty directory
                if (!rmdir($directory)) {
                    // return false if not possible
                    return FALSE;
                }
            }
            // return success
            return TRUE;
        }
    }
}
?>
