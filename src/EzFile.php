<?php
namespace AmplieSolucoes\EzFile;

class EzFile{
    /*
    |--------------------------------------------------------------------------
    | Const data Variables
    |--------------------------------------------------------------------------
    */
    const UNIT_BYTES = "B";
    const UNIT_KILOBYTES = "KB";
    const UNIT_MEGABYTES = "MB";
    const UNIT_GIGABYTES = "GB";
    const UNIT_TERABYTES = "TB";
    const UNIT_PETABYTES = "PB";
    const UNIT_EXABYTES = "EB";
    const UNIT_ZETTABYTES = "ZB";
    const UNIT_YOTTABYTES = "YB";

    /*
    |--------------------------------------------------------------------------
    | Callable Functions
    |--------------------------------------------------------------------------
    */
    /**
     * Check if a file or directory exists.
     *
     * @param string $path The path of the file or directory to check.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the file or directory exists. If the path is not valid and $force is false, returns false.
     * If $force is true and the path is not valid, returns an array with the 'error' key containing an error message.
     */
    public static function exists($path, $force = false){
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        return !isset($pathInfo['extension']) ? is_dir($path) : is_file($path);
    }

    /**
     * List all files and directories within a directory.
     *
     * @param string $path The path of the directory to list.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return array|mixed Returns an array containing the paths of all files and directories within the specified directory.
     * If the specified path is not valid and $force is false, returns an array with an error message.
     * If the specified path is a file, returns an array with an error message.
     */
    public static function list($path, $force = false){
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;
        if(isset($pathInfo['extension'])) return self::returnErrors("The path sent is a file '".$path."'.");

        $allFiles = array();

        if (self::exists($path) && $handle = opendir($path)) {
            while (false !== ($folder = readdir($handle))) {
                if ($folder != '.' && $folder != '..') {
                    $listing = $path . '/' . $folder;
                    if (self::exists($listing)) {
                        $allFiles[] = $listing;
                    }
                }
            }
            closedir($handle);
        }

        return $allFiles;
    }

    /**
     * Create a file or directory.
     *
     * @param string $path The path of the file or directory to create.
     * @param bool $replaceSpecialCharacters (optional) Indicates whether to replace special characters in the path. Default is false.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the file or directory was created successfully.
     * If the path is not valid and $force is false, returns an array with an error message.
     * If $replaceSpecialCharacters is true, special characters in the path will be replaced.
     * If the path points to a directory, it is created. If it points to a file, the file is created and its permissions are set to 0777.
     * Returns an array with an error message if the file could not be created.
     */
    public static function create($path, $replaceSpecialCharacters = false, $force = false){
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;
        if($replaceSpecialCharacters) $path = self::sanitizeFile($path);

        if(!isset($pathInfo['extension'])){
            return self::directoryCreate($path, $force);
        } else {
            self::directoryCreate($pathInfo['dirname'], $force);
            if (!self::exists($path, $force)) {
                if ($file = fopen($path, 'w')) {
                    fclose($file);
                    chmod($path, 0777);
                    return true;
                } else {
                    return self::returnErrors("Cound not create the file '".$path."'.");
                }
            }
        }
        return true;
    }

    /**
     * Write content to a file.
     *
     * @param string $path The path to the file where the content will be written.
     * @param string $content The content to write to the file.
     * @param bool $replaceContent (optional) Indicates whether to replace existing content. Default is true.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the content was written successfully.
     * If the path is not valid and $force is false, returns an array with an error message.
     * If the specified path points to a directory, returns an array with an error message.
     * If the file could not be written, returns an array with an error message.
     * If $replaceContent is false and the file already has content, a new line is inserted before writing.
     */
    public static function write($path, $content, $replaceContent = true, $force = false) {
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;
        if(!isset($pathInfo['extension'])) return self::returnErrors("The path sent is not a file '".$path."'.");

        self::directoryCreate($pathInfo['dirname'], $force);

        if($file = fopen($path, ($replaceContent ? 'w' : 'a'))){
            if(!$replaceContent && filesize($path) > 0) fwrite($file, PHP_EOL);
            fwrite($file, $content);
            fclose($file);
            return true;
        } else {
            return self::returnErrors("Could not write to the file '".$path."'.");
        }
    }

    /**
     * Read the content of a file.
     *
     * @param string $path The path to the file to read.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return string|array Returns the content of the file if it exists.
     * If the path is not valid and $force is false, returns an array with an error message.
     * If the specified path points to a directory, returns an array with an error message.
     * If the file could not be read or does not exist, returns an array with an error message.
     */
    public static function read($path, $force = false) {
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;
        if(!isset($pathInfo['extension'])) return self::returnErrors("The path sent is not a file '".$path."'.");

        return is_file($path) ? file_get_contents($path) : self::returnErrors("File '".$path."' does not exist.");
    }

    /**
     * Delete a file or directory.
     *
     * @param string $path The path of the file or directory to delete.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the file or directory was deleted successfully.
     * If the path is not valid and $force is false, returns an array with an error message.
     * If the specified path points to a directory, it is deleted recursively.
     * If the specified path points to a file, it is deleted.
     * Returns an array with an error message if the file or directory could not be deleted or if it does not exist.
     */
    public static function delete($path, $force = false){
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        if(!isset($pathInfo['extension'])){
            return self::directoryDelete($path, $force);
        } else {
            if (self::exists($path, $force)) {
                return unlink($path) ? true : self::returnErrors("Cound not delete the file '".$path."'.");
            } else {
                return self::returnErrors("The file '".$path."' does not exist.");
            }
        }
    }

    /**
     * Rename a file or directory.
     *
     * @param string $currentPathName The current path or name of the file or directory to be renamed.
     * @param string $newName The new name for the file or directory.
     * @param bool $replaceSpecialCharacters (optional) Indicates whether to replace special characters in the new name. Default is false.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the file or directory was renamed successfully.
     * If the current path is not valid and $force is false, returns an array with an error message.
     * If $replaceSpecialCharacters is true, special characters in the new name will be replaced.
     * If the current path points to a directory, it is renamed to the new name.
     * If the current path points to a file, it is renamed to the new name.
     * Returns an array with an error message if the file or directory could not be renamed or if it does not exist.
     */
    public static function rename($currentPathName, $newName, $replaceSpecialCharacters = false, $force = false) {
        $pathInfo = self::validatePathInfo($currentPathName, $force);
        if(isset($pathInfo['error'])) return $pathInfo;
        if($replaceSpecialCharacters) $newName = self::sanitizeFile($newName);

        if(!isset($pathInfo['extension'])){
            if (self::exists($currentPathName, $force)) {
                return rename($currentPathName, $newName)
                    ? true
                    : self::returnErrors("Cound not rename the folder '".$currentPathName."' to '".$currentPathName."'.");
            } else {
                return self::returnErrors("The directory '".$currentPathName."' does not exist.");
            }
        } else {
            if (self::exists($currentPathName, $force)) {
                return rename($currentPathName, $newName)
                    ? true
                    : self::returnErrors("Cound not rename the file '".$currentPathName."' to '".$currentPathName."'.");
            } else {
                return self::returnErrors("The file '".$currentPathName."' does not exist.");
            }
        }
    }

    /**
     * Copy a file or directory to a new location.
     *
     * @param string $copyFrom The path of the file or directory to be copied.
     * @param string $copyTo The path of the new location where the file or directory will be copied to.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the file or directory was copied successfully.
     * If the source path or destination path is not valid and $force is false, returns an array with an error message.
     * If the source path points to a directory, it is recursively copied to the destination path.
     * If the source path points to a file, it is copied to the destination path.
     * Returns an array with an error message if the file or directory could not be copied or if it does not exist.
     */
    public static function copy($copyFrom, $copyTo, $force = false) {
        $pathInfo = self::validatePathInfo($copyFrom, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        $newPathInfo = self::validatePathInfo($copyTo, $force);
        if(isset($newPathInfo['error'])) return $newPathInfo;

        if(!isset($pathInfo['extension'])){
            return self::directoryCopy($copyFrom, $copyTo, $force);
        } else {
            if (self::exists($copyFrom, $force)) {
                if(!is_dir($newPathInfo['dirname'])){ $created = self::directoryCreate($newPathInfo['dirname'], $force); }
                if(isset($created['error'])) return $created;

                return copy($copyFrom, $copyTo)
                    ? true
                    : self::returnErrors("Cound not copy the file '".$copyFrom."' to '".$copyTo."'.");
            } else {
                return self::returnErrors("The file '".$copyFrom."' does not exist to copy.");
            }
        }
    }

    /**
     * Move a file or directory to a new location.
     *
     * @param string $moveFrom The path of the file or directory to be moved.
     * @param string $moveTo The path of the new location where the file or directory will be moved to.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the file or directory was moved successfully.
     * If the source path or destination path is not valid and $force is false, returns an array with an error message.
     * If the source path points to a directory, it is recursively moved to the destination path.
     * If the source path points to a file, it is moved to the destination path.
     * Returns an array with an error message if the file or directory could not be moved or if it does not exist.
     */
    public static function move($moveFrom, $moveTo, $force = false){
        $pathInfo = self::validatePathInfo($moveFrom, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        $newPathInfo = self::validatePathInfo($moveTo, $force);
        if(isset($newPathInfo['error'])) return $newPathInfo;

        if(!isset($pathInfo['extension'])){
            return self::directoryMove($moveFrom, $moveTo, $force);
        } else {
            if (self::exists($moveFrom, $force)) {
                if(!is_dir($newPathInfo['dirname'])){ $dirExists = self::directoryCreate($newPathInfo['dirname'], $force); }
                if(isset($dirExists['error'])) return $dirExists;

                return rename($moveFrom, $moveTo)
                    ? true
                    : self::returnErrors("Cound not move the file from '".$moveFrom."' to '".$moveTo."'.");
            } else {
                return self::returnErrors("The file '".$moveFrom."' does not exist to move to '".$moveTo."'.");
            }
        }
    }

    /**
     * Upload one or multiple files to a specified location.
     *
     * @param string $uploadPath The path where the files will be uploaded.
     * @param array $files An array containing the file information. Each file should be represented by an array with keys: 'name', 'full_path', 'type', 'tmp_name', and 'error'.
     * @param bool|string $renameTo (optional) Indicates whether to rename uploaded files. If set to a string, files will be renamed with the specified prefix. Default is false.
     * @param array $acceptOnly (optional) An array containing the allowed file extensions. If specified, only files with these extensions will be uploaded. Default is an empty array.
     * @param bool $force (optional) Indicates whether to force path validation and creation if the upload path does not exist. Default is false.
     * @return array An array containing information about the uploaded files. It has three keys: 'success' for successfully uploaded files, 'fail' for files that failed to upload, and 'denied' for files that were denied due to their extension.
     */
    public static function upload($uploadPath, $files, $renameTo = false, $acceptOnly = [], $force = false){
        $pathInfo = self::validatePathInfo($uploadPath, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        if(!self::exists($uploadPath)) self::create($uploadPath, true, $force);

        $filesUploaded = ['success' => [], 'fail' => [], 'denied' => []];
        $counter = 0;
        foreach ($files as $archive) {
            if(isset($archive['name'])){
                if(gettype($archive['name']) === "string"){
                    $archive = [
                        'name' => [$archive['name']],
                        'full_path' => [$archive['full_path']],
                        'type' => [$archive['type']],
                        'tmp_name' => [$archive['tmp_name']],
                        'error' => [$archive['error']],
                    ];
                }
                foreach ($archive['name'] as $key => $val){
                    $file = [
                        'name' => $archive['name'][$key],
                        'full_path' => $archive['full_path'][$key],
                        'type' => $archive['type'][$key],
                        'tmp_name' => $archive['tmp_name'][$key],
                        'error' => $archive['error'][$key],
                    ];

                    $uploadInto = $uploadPath;
                    if((strpos($file['full_path'], "/") !== false)){
                        $checkDir = str_replace("/".$file['name'], "", $file['full_path']);
                        $uploadInto = $uploadPath."/".$checkDir;
                        $created = self::directoryCreate($uploadInto, $force);
                        if(isset($created['error'])) return $created;
                    }

                    if ($file['error'] !== UPLOAD_ERR_OK){
                        $filesUploaded['fail'][] = "Error to upload the file '$val'";
                        continue;
                    }

                    if (!empty($acceptOnly) && !in_array(pathinfo($val)['extension'], $acceptOnly)) {
                        $filesUploaded['denied'][] = "The file '$val' can not be uploaded because the extension '".pathinfo($val)['extension']."' is not acceptable.";
                        continue;
                    }

                    $fileName = $renameTo ? $renameTo.'_'.$counter++.'.'.pathinfo($val)['extension'] : $file['name'];

                    $destination = rtrim($uploadInto, '/') . '/' . $fileName;
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $filesUploaded['success'][] = $destination;
                    }
                }
            }
        }
        return $filesUploaded;
    }

    /**
     * Download a file from the server.
     *
     * @param string $path The path of the file to be downloaded.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return void This function outputs the file to the browser for download. It does not return any value.
     * If the specified path is not valid and $force is false, an error message is echoed.
     * If the specified path points to a directory, it is zipped before being downloaded.
     */
    public static function download($path, $force = false) {
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        if (self::exists($path)) {

            $deleteAfterDownload = false;
            $downloadName = $pathInfo['basename'];

            if(!isset($pathInfo['extension'])){
                $downloadName .= ".zip";
                $zipTo = ((strpos($path, "/") !== false) && explode("/", $path) > 1) ? $path."/../" : $path;
                self::zip($path, $zipTo, true);

                $path = $zipTo."\\".$downloadName;
                $deleteAfterDownload = true;
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $downloadName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            ob_clean();
            flush();
            readfile($path);

            if($deleteAfterDownload)
                unlink($path);
        } else {
            echo "Error to download the item '".$path."'.";
        }
        exit;
    }

    /**
     * Create a zip archive from a directory.
     *
     * @param string $path The path of the directory to be zipped.
     * @param string $pathToZip The path where the zip archive will be created.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the directory was successfully zipped.
     * If the specified path is not valid and $force is false, returns an array with an error message.
     * If the specified path points to a file, returns an array with an error message.
     * If the zip archive is created successfully, it is saved at the specified location.
     * Returns an array with an error message if the directory could not be zipped.
     */
    public static function zip($path, $pathToZip, $force = false){
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;
        if(isset($pathInfo['extension'])) return self::returnErrors("The path '".$path."' is a file and can not be converted to zip.");

        $zip = new \ZipArchive();
        if ($zip->open($pathToZip."/".$pathInfo['basename'].".zip", \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $relativePath = str_replace($path, '', $file->getPath())."\\".$file->getFileName();
                    $zip->addFile($file->getRealPath(), substr($relativePath, 1));
                }
            }
            $zip->close();
            return true;
        } else {
            return self::returnErrors("Error to convert the path '".$path."' to zip file.");
        }
    }

    /**
     * Unzips a ZIP file to a specified directory.
     *
     * @param string $zipPath The path of the ZIP file to unzip.
     * @param string $pathToExtract The directory where the ZIP file will be extracted.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the ZIP file was successfully extracted.
     * If the ZIP file path is not valid and $force is false, returns an array with an error message.
     * If the ZIP file path is not a valid ZIP file, returns an array with an error message.
     * If the destination path is not valid and $force is false, returns an array with an error message.
     * If the destination path is a file, returns an array with an error message.
     * If the destination directory does not exist and cannot be created, returns an array with an error message.
     * Returns an array with an error message if the ZIP file could not be extracted.
     */
    public static function unzip($zipPath, $pathToExtract, $force = false) {
        $pathInfo = self::validatePathInfo($zipPath, $force);
        if (isset($pathInfo['error'])) return $pathInfo;
        if (!isset($pathInfo['extension'])) return self::returnErrors("The zip file '$zipPath' is not a compact file and cannot be used as a extraction file.");
        if (strtolower($pathInfo['extension']) != "zip") return self::returnErrors("The file '$zipPath' is not a ZIP file, and cannot be extracted.");

        $pathInfoDest = self::validatePathInfo($pathToExtract, $force);
        if (isset($pathInfoDest['error'])) return $pathInfoDest;
        if (isset($pathInfoDest['extension'])) return self::returnErrors("The destination path '$pathToExtract' is a file and cannot be used as the extraction destination.");

        if(!is_dir($pathInfoDest['dirname'])){ $dirExists = self::directoryCreate($pathInfoDest['dirname'], $force); }
        if(isset($dirExists['error'])) return $dirExists;

        $zip = new \ZipArchive();

        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($pathToExtract);
            $zip->close();
            return true;
        } else {
            return self::returnErrors("Failed to unzip the file '$zipPath'.");
        }
    }

    /**
     * Change the permissions of a file or directory.
     *
     * @param string $path The path of the file or directory whose permissions will be changed.
     * @param int $permissionsCode The new permissions code to be applied. Refer to PHP's chmod() function for possible values.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the permissions were successfully changed.
     * If the specified path does not exist and $force is false, returns an array with an error message.
     * If the permissions were successfully changed, returns true.
     * Returns an array with an error message if the permissions could not be changed.
     */
    public static function changePermissions($path, $permissionsCode, $force = false) {
        if (self::exists($path, $force)) {
            return chmod($path, $permissionsCode)
                ? true
                : self::returnErrors("Could not update the path '".$path."' with the permissions '".$permissionsCode."'.");
        } else {
            return self::returnErrors("The specified path '".$path."' does not exist to update the permissions to '".$permissionsCode."'.");
        }
    }

    /**
     * Get information about a file or directory.
     *
     * @param string $path The path of the file or directory to get information about.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return array Returns an array containing information about the specified file or directory.
     * If the specified path is not valid and $force is false, returns an array with an error message.
     * If the specified file does not exist, returns an array with an error message.
     * The returned array contains the following keys:
     * - 'basename': The base name of the file or directory.
     * - 'dirname': The directory name of the file or directory.
     * - 'extension': The file extension (if applicable).
     * - 'size_raw': The size of the file or directory in bytes.
     * - 'size_unit': The unit of the file or directory size (e.g., KB, MB).
     * - 'size_formated': The formatted size of the file or directory (e.g., '2.5 MB').
     * - 'created_at': The creation date and time of the file or directory in 'Y-m-d H:i:s' format, or null if unavailable.
     * - 'modified_at': The last modification date and time of the file or directory in 'Y-m-d H:i:s' format.
     */
    public static function pathInfo($path, $force = false){
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;
        if(!self::exists($path)) return self::returnErrors("The specified file '".$path."' does not exist.");

        $bytes = !isset($pathInfo['extension']) ? self::directorySize($path, $force) : filesize($path);

        $sizeUnitFormatter = self::sizeUnitFormatter($bytes);
        $pathInfo['size_raw'] = $bytes;
        $pathInfo['size_unit'] = explode(" ", $sizeUnitFormatter)[1];
        $pathInfo['size_formated'] = $sizeUnitFormatter;
        $pathInfo['created_at'] = (filectime($path) === false) ? null : date('Y-m-d H:i:s', filectime($path));
        $pathInfo['modified_at'] = date('Y-m-d H:i:s', filemtime($path));

        return $pathInfo;
    }

    /**
     * Format the size of a file or directory into human-readable format.
     *
     * @param int $size The size of the file or directory.
     * @param string $unitType (optional) The type of unit to be used for formatting. Default is bytes.
     * @param bool $rawSize (optional) Indicates whether to return the raw size without formatting. Default is false.
     * @return string Returns the size of the file or directory formatted into a human-readable format.
     * If $rawSize is true, returns the raw size with the unit specified in $unitType.
     * The formatted size includes a numerical value and a unit (e.g., '2.5 MB').
     */
    public static function sizeUnitFormatter($size, $unitType = self::UNIT_BYTES, $rawSize = false){
        $unitIndex = 0;
        $size = self::byteCast($size, $unitType);

        if($rawSize){
            return $size.' '.self::UNIT_BYTES;
        } else {
            while($size >= 1024) {
                $size /= 1024;
                $unitIndex++;
            }
        }

        $unit = [self::UNIT_BYTES, self::UNIT_KILOBYTES, self::UNIT_MEGABYTES, self::UNIT_GIGABYTES, self::UNIT_TERABYTES, self::UNIT_PETABYTES, self::UNIT_EXABYTES, self::UNIT_ZETTABYTES, self::UNIT_YOTTABYTES][$unitIndex];
        return round($size, 2).' '.$unit;
    }

    /*
    |--------------------------------------------------------------------------
    | Private Functions
    |--------------------------------------------------------------------------
    */

    /**
     * Create a directory if it does not already exist.
     *
     * @param string $path The path of the directory to be created.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the directory was successfully created or already exists.
     * If the specified path is not valid and $force is false, returns an array with an error message.
     * If the directory could not be created, returns an array with an error message.
     */
    private static function directoryCreate($path, $force = false){
        if (!self::exists($path, $force)) {
            if (!mkdir($path, 0777, true)) {
                return self::returnErrors("Cound not create the directory '".$path."'.");
            }
        }
        return true;
    }

    /**
     * Recursively copy a directory and its contents to a new location.
     *
     * @param string $copyFrom The path of the directory to be copied.
     * @param string $copyTo The destination path where the directory will be copied.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the directory and its contents were successfully copied.
     * If the specified source directory does not exist, returns an array with an error message.
     * If any error occurs during the copy process, returns an array with an error message.
     */
    private static function directoryCopy($copyFrom, $copyTo, $force = false){
        if (is_dir($copyFrom)) {
            $created = self::directoryCreate($copyFrom, $force);
            if(isset($created['error'])) return $created;

            if ($handle = opendir($copyFrom)) {
                while (false !== ($folder = readdir($handle))) {
                    if ($folder != '.' && $folder != '..') {
                        $currentPath = $copyFrom . '/' . $folder;
                        $newPath = $copyTo . '/' . $folder;

                        if (is_dir($currentPath)) {
                            $copied = self::directoryCopy($currentPath, $newPath, $force);
                        } else {
                            $copied = self::copy($currentPath, $newPath, $force);
                        }
                        if(isset($copied['error'])) return $copied;
                    }
                }
                closedir($handle);
                return true;
            } else {
                return self::returnErrors("Cound not open the directory '".$copyFrom."'.");
            }
        } else {
            return self::returnErrors("The directory '".$copyFrom."' does not exist.");
        }
    }

    /**
     * Recursively move a directory and its contents to a new location.
     *
     * @param string $moveFrom The path of the directory to be moved.
     * @param string $moveTo The destination path where the directory will be moved.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the directory and its contents were successfully moved.
     * If the specified source directory does not exist, returns an array with an error message.
     * If any error occurs during the move process, returns an array with an error message.
     */
    private static function directoryMove($moveFrom, $moveTo, $force = false) {
        $pathInfo = self::validatePathInfo($moveFrom, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        $newPathInfo = self::validatePathInfo($moveTo, $force);
        if(isset($newPathInfo['error'])) return $newPathInfo;

        if (self::exists($moveFrom, $force)) {
            if (!is_dir($moveTo)) {
                $dirExists = self::directoryCreate($moveTo, $force);
                if(isset($dirExists['error'])) return $dirExists;
            }

            if($moveFrom == $moveTo){ return true; }

            if (is_dir($moveFrom)) {
                if ($handle = opendir($moveFrom)) {
                    while (false !== ($folder = readdir($handle))) {
                        if ($folder != '.' && $folder != '..') {
                            $currentPath = $moveFrom . '/' . $folder;
                            $newPath = $moveTo . '/' . $folder;

                            if (is_dir($currentPath)) {
                                $moved = self::directoryMove($currentPath, $newPath, $force);
                            } else {
                                $moved = self::move($currentPath, $newPath, $force);
                            }
                            if (isset($moved['error'])) return $moved;
                        }
                    }
                    closedir($handle);

                    return rmdir($moveFrom)
                        ? true
                        : self::returnErrors("Cound not move the directory '".$moveFrom."'.");
                } else {
                    return self::returnErrors("Cound not open the directory '".$moveFrom."'.");
                }
            } else {
                self::move($moveFrom, $moveTo, $force);
            }
        } else {
            return self::returnErrors("The directory '".$moveFrom."' does not exist.");
        }
    }

    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $path The path of the directory to be deleted.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return bool|array Returns true if the directory and its contents were successfully deleted.
     * If the specified directory does not exist, returns an array with an error message.
     * If any error occurs during the deletion process, returns an array with an error message.
     */
    private static function directoryDelete($path, $force = false){
        if (self::exists($path, $force)) {
            if (is_dir($path)) {
                if ($handle = opendir($path)) {
                    while (false !== ($folder = readdir($handle))) {
                        if ($folder != '.' && $folder != '..') {
                            $fullPath = $path . '/' . $folder;

                            if (self::exists($fullPath, $force)) {
                                self::directoryDelete($fullPath, $force);
                            } else {
                                unlink($fullPath);
                            }
                        }
                    }
                    closedir($handle);

                    return rmdir($path) ? true : self::returnErrors("Could not delete the folder '".$path."'.");
                } else {
                    return self::returnErrors("Could not open the folder '".$path."'.");
                }
            } else {
                return unlink($path) ? true : self::returnErrors("Could not delete the file '".$path."'.");
            }
        } else {
            return self::returnErrors("The '".$path."' does not exist.");
        }
    }

    /**
     * Calculate the total size of a directory and its contents recursively.
     *
     * @param string $path The path of the directory for which to calculate the size.
     * @param bool $force (optional) Indicates whether to force path validation. Default is false.
     * @return int The total size of the directory and its contents in bytes.
     */
    private static function directorySize($path, $force = false){
        $size = 0;
        foreach(glob($path.'/*') as $file){
            self::exists($file, $force) && $size += filesize($file);
            self::exists($file, $force) && $size += self::directorySize($file, $force);
        }
        return $size;
    }

    /**
     * Convert the size from one unit to another unit.
     *
     * @param int $size The size to be converted.
     * @param string $unitType The unit type to convert the size to. Supported units are: bytes, kilobytes, megabytes, gigabytes, terabytes, petabytes, exabytes, zettabytes, yottabytes.
     * @return int The converted size in the specified unit.
     */
    private static function byteCast($size, $unitType){
        $unitSize = 0;
        switch (strtoupper($unitType)){
            case self::UNIT_BYTES: $unitSize = 1; break;
            case self::UNIT_KILOBYTES: $unitSize = pow(1024, 1); break; // 1 KB = 1024 bytes
            case self::UNIT_MEGABYTES: $unitSize = pow(1024, 2); break; // 1 MB = 1024 KB
            case self::UNIT_GIGABYTES: $unitSize = pow(1024, 3); break; // 1 GB = 1024 MB
            case self::UNIT_TERABYTES: $unitSize = pow(1024, 4); break; // 1 TB = 1024 GB
            case self::UNIT_PETABYTES: $unitSize = pow(1024, 5); break; // 1 PB = 1024 TB
            case self::UNIT_EXABYTES: $unitSize = pow(1024, 6); break; // 1 EB = 1024 PB
            case self::UNIT_ZETTABYTES: $unitSize = pow(1024, 7); break; // 1 ZB = 1024 EB
            case self::UNIT_YOTTABYTES: $unitSize = pow(1024, 8); break; // 1 YB = 1024 ZB
            default: $unitSize = 1; break;
        }
        return $unitSize * $size;
    }

    /**
     * Validate the path information and check for any security risks.
     *
     * @param string $path The path for which to validate the information.
     * @param bool $force (optional) Indicates whether to force path validation, allowing manipulation of directory levels. Default is false.
     * @return array|string Returns an array containing the path information if it's valid.
     * If the path contains security risks (e.g., directory traversal), returns an error message.
     */
    private static function validatePathInfo($path, $force = false){
        if((strpos($path, "..") !== false) && !$force) return self::returnErrors("Access denied to manipulate directories levels in '".$path."' without force parameter.");
        return pathinfo($path) ?? self::returnErrors("Invalid pathinfo for '".$path."'.");
    }

    /**
     * Sanitize a file name by replacing special characters and removing accents.
     *
     * @param string $fileName The file name to be sanitized.
     * @return string The sanitized file name.
     */
    private static function sanitizeFile($fileName) {
        $dirname = "";
        $fileName = str_replace("/", "\\", strtolower($fileName));
        if(strpos($fileName, "..") !== false){
            $dirname = str_replace("/", "\\", dirname($fileName)."/");
            $exploded = explode($dirname, $fileName);
            $fileName = isset($exploded[1]) ? $exploded[1] : $fileName;
        }

        $fileName = str_replace("\\", "___divider_replacer___", $fileName);
        $fileName = preg_replace('/[áàãâä]/ui', 'a', $fileName);
        $fileName = preg_replace('/[éèêë]/ui', 'e', $fileName);
        $fileName = preg_replace('/[íìîï]/ui', 'i', $fileName);
        $fileName = preg_replace('/[óòõôö]/ui', 'o', $fileName);
        $fileName = preg_replace('/[úùûü]/ui', 'u', $fileName);
        $fileName = preg_replace('/[ç]/ui', 'c', $fileName);
        $fileName = preg_replace('/[ñ]/ui', 'n', $fileName);
        $fileName = preg_replace('/[ýỳŷÿ]/ui', 'y', $fileName);
        $fileName = preg_replace('/[đ]/ui', 'd', $fileName);
        $fileName = preg_replace('/[š]/ui', 's', $fileName);
        $fileName = preg_replace('/[ž]/ui', 'z', $fileName);
        $fileName = preg_replace('/[^a-zA-Z0-9\s]/', '_', $fileName);
        $fileName = str_replace("___divider_replacer___", "\\", $fileName);

        return $dirname.$fileName;
    }

    /**
     * Return an error message in a standardized format.
     *
     * @param string $message The error message to be returned.
     * @return array Returns an array containing the error message.
     *
     * Note: This function is used internally to return error messages in a standardized format.
     * It is called within almost all other functions mentioned above to handle error messages.
     */
    private static function returnErrors($message){
        return ['error' => true, 'message' => $message];
    }
}