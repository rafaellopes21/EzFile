<?php
namespace AmplieSolucoes\EzFile;

class EzFile{
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
    public static function exists($path, $force = false){
        $pathInfo = self::validatePathInfo($path, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        return !isset($pathInfo['extension']) ? is_dir($path) : is_file($path);
    }

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

    public static function upload($uploadPath, $files, $renameTo = false, $acceptOnly = [], $force = false){
        $pathInfo = self::validatePathInfo($uploadPath, $force);
        if(isset($pathInfo['error'])) return $pathInfo;

        if(!self::exists($uploadPath)) self::create($uploadPath, true, $force);

        $filesUploaded = ['success' => [], 'fail' => [], 'denied' => []];
        $counter = 0;
        foreach ($files as $archive) {
            if(isset($archive['name'])){
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

                $path = $zipTo.$downloadName;
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

    public static function changePermissions($path, $permissionsCode, $force = false) {
        if (self::exists($path, $force)) {
            return chmod($path, $permissionsCode)
                ? true
                : self::returnErrors("Could not update the path '".$path."' with the permissions '".$permissionsCode."'.");
        } else {
            return self::returnErrors("The specified path '".$path."' does not exist to update the permissions to '".$permissionsCode."'.");
        }
    }

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
    private static function directoryCreate($path, $force = false){
        if (!self::exists($path, $force)) {
            if (!mkdir($path, 0777, true)) {
                return self::returnErrors("Cound not create the directory '".$path."'.");
            }
        }
        return true;
    }

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

    private static function directorySize($path, $force = false){
        $size = 0;
        foreach(glob($path.'/*') as $file){
            self::exists($file, $force) && $size += filesize($file);
            self::exists($file, $force) && $size += self::directorySize($file, $force);
        }
        return $size;
    }

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

    private static function validatePathInfo($path, $force = false){
        if((strpos($path, "..") !== false) && !$force) return self::returnErrors("Access denied to manipulate directories levels in '".$path."' without force parameter.");
        return pathinfo($path) ?? self::returnErrors("Invalid pathinfo for '".$path."'.");
    }

    private static function sanitizeFile($fileName) {
        $fileName = str_replace("/", "___divider_replacer___", strtolower($fileName));
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
        $fileName = str_replace("___divider_replacer___", "/", $fileName);
        return $fileName;
    }

    private static function returnErrors($message){
        return ['error' => true, 'message' => $message];
    }
}