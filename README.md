# EzFile - Easy and Simple Filemanager

EzFile is a PHP library that provides an easy and simple way to manage files and directories.

## Installation

Install EzFile using Composer:

```bash
composer require amplie-solucoes/ezfile
```

## Usage

See how to use the library with simple methods:
```
// Check if file/folder exists
EzFile::exists(self::FOLDER);

// Create Folder
EzFile::create(self::FOLDER."/minha_pasta/outra");

// Create File
EzFile::create(self::FOLDER."/minha_pasta/outra/teste.txt");

// Rename Folder
EzFile::rename(self::FOLDER."/minha_pasta", self::FOLDER."/pasta_renomeada");

// Rename File
EzFile::rename(self::FOLDER."/minha_pasta/renomeamos/teste.txt", self::FOLDER."/minha_pasta/renomeamos/renomeou.txt");

// Move/cut directory with all its contents to another location
EzFile::move(self::FOLDER."/teste", self::FOLDER."/minha_pasta");

// Move/cut file to another location
EzFile::move(self::FOLDER."/arquivo_renomeado.txt", self::FOLDER."/minha_pasta/movendo/arquivo_renomeado.txt");

// Copy directory with all its contents to another location
EzFile::copy(self::FOLDER."/minha_pasta", self::FOLDER."/pasta_copiada");

// Copy file to another location
EzFile::copy(self::FOLDER."/arquivo_renomeado.txt", self::FOLDER."/minha_pasta/arquivo_renomeado.txt");

// Change permissions of a file/folder
EzFile::changePermissions(self::FOLDER."/arquivo_renomeado.txt", 0777);

// Get information about a file/folder
$data = EzFile::pathInfo(self::FOLDER."/finalizou.txt");

// List all files in a folder
EzFile::list(self::FOLDER);

// Zip folders with files
EzFile::zip(self::FOLDER."/sddsds", self::FOLDER);

// Upload files
EzFile::upload(self::FOLDER, $_FILES, false, ['xlsx', 'png', 'txt']);

// Download folders
EzFile::download('filemanager/mover_pasta');

// Download files
EzFile::download('filemanager/mover_pasta/b.txt');

// Delete Directory and everything inside it
EzFile::delete(self::FOLDER."/minha_pasta");

// Delete File
EzFile::delete(self::FOLDER."/minha_pasta/renomeamos/arquivo_renomeado.txt");

// Get size unit constants
EzFile::UNIT_BYTES;
EzFile::UNIT_KILOBYTES;
EzFile::UNIT_MEGABYTES;
EzFile::UNIT_GIGABYTES;
EzFile::UNIT_TERABYTES;
EzFile::UNIT_PETABYTES;
EzFile::UNIT_EXABYTES;
EzFile::UNIT_ZETTABYTES;
EzFile::UNIT_YOTTABYTES;

// Format the value for human reading
EzFile::sizeUnitFormatter(100); // 100 B
EzFile::sizeUnitFormatter(1, EzFile::UNIT_GIGABYTES); // 5 GB
EzFile::sizeUnitFormatter(10, EzFile::UNIT_TERABYTES); // 10 GB
EzFile::sizeUnitFormatter(1, EzFile::UNIT_TERABYTES, true); // 1099511627776 B
```
____
This README provides an overview of the EzFile library, its features, and how to use it. Adjustments can be made as needed to fit your specific project.
