# EzFile - Easy and Simple Filemanager

EzFile is a free, nonprofit PHP library designed to simplify file and directory manipulation in web development. With a focus on ease of use, it provides efficient tools for common tasks like creating, renaming, moving, copying, deleting, uploading, downloading and manipulating files and folders. The EzFile accelerates web development, making it easier to work with files and directories and enhancing productivity.

## Requirements
| Tool | Version       | Requirement |
|------|---------------|-------------|
| PHP  | 7.0 or Higher | Required    |

## Installation

Install EzFile using Composer:

```bash
composer require amplie-solucoes/ezfile
```

## Note
If you need to work with handling files that will be large in size, make sure to change the following parameters in your php.ini file:
- memory_limit
- upload_max_filesize
- post_max_size

## How to Use

The objective of EzFile is to be a simple and easily accessible library, therefore, all the code was designed to be executed statically, facilitating the work and manipulation of the code.

Additionally, it's crucial to note that each of the functions listed below includes a parameter named "force." This parameter serves the purpose of enabling the manipulation of files located beyond the designated main path. Please refer to the examples provided for clarity:

----
##### Exists Function
```php
//Validate if a Directory or File exists
EzFile::exists('your_path');

//Using 'force' paramn to validate outsite main path
EzFile::exists('your_path', true);

/*
====== [ Function Return ] ===== 
EXISTS: true
NOT EXISTS: false
*/
```
----
##### Create Function
```php
//Create a Directory or File
EzFile::create('your_path');

//Create a Directory or File replacing special chars and set all to lowercase
EzFile::create('your_path', true);

//Using 'force' paramn to create outsite main path
EzFile::create('your_path', false, true);

/*
====== [ Function Return ] ===== 
SUCCESS: true
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Rename Function
```php
//Rename a Directory or File
EzFile::rename('current_path', 'renamed_path');

//Rename a Directory or File replacing special chars and set all to lowercase
EzFile::rename('current_path', 'renamed_path', true);

//Using 'force' paramn to rename outsite main path
EzFile::rename('current_path', 'renamed_path', false, true);

/*
====== [ Function Return ] ===== 
SUCCESS: true
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Move Function
```php
//Move a Directory or File
EzFile::move('current_path', 'move_path');

//Using 'force' paramn to move outsite main path
EzFile::move('current_path', 'move_path', true);

/*
====== [ Function Return ] ===== 
SUCCESS: true
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Copy Function
```php
//Copy a Directory (and all contents inside) or File
EzFile::copy('current_path', 'copy_path');

//Using 'force' paramn to copy outsite main path
EzFile::copy('current_path', 'copy_path', true);

/*
====== [ Function Return ] ===== 
SUCCESS: true
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Change Path Permissions Function
```php
//Permission code set in a Directory or File
EzFile::changePermissions('your_path', 0777);
EzFile::changePermissions('your_path', 0666);
EzFile::changePermissions('your_path', 0700);
//... and other codes that you need

//Using 'force' paramn to change permissions outsite main path
EzFile::changePermissions('your_path', 0777, true);

/*
====== [ Function Return ] ===== 
SUCCESS: true
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Pathinfo Function
```php
//Info of the Directory or File
EzFile::pathInfo('your_path');

//Using 'force' paramn to get pathinfo outsite main path
EzFile::pathInfo('your_path', true);

/*
====== [ Function Return ] ===== 
SUCCESS: [array_with_all_informations_that_you_need]
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### List Function
```php
//List a Directory
EzFile::list('your_path');

//Using 'force' paramn to list outsite main path
EzFile::list('your_path', true);

/*
====== [ Function Return ] ===== 
SUCCESS: [array_with_all_informations_that_you_need]
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Zip Folder Function
```php
//Zip a Directory with all contents inside
EzFile::zip('your_folder_path', 'zip_path');

//Using 'force' paramn to zip outsite main path
EzFile::zip('your_folder_path', 'zip_path', true);

/*
====== [ Function Return ] ===== 
SUCCESS: true
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Delete Function
```php
//Delete a Directory (and all contents inside) or File
EzFile::delete('your_path');

//Using 'force' paramn to delete outsite main path
EzFile::delete('your_path', true);

/*
====== [ Function Return ] ===== 
SUCCESS: true
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Upload Function
```php
//Example Data
$your_files_in_array = [
    [/*File/Dir 1*/],
    [/*File/Dir 2*/],
    //...
    //...
    //...
]
//Upload a Directory (and all contents inside) or File(s)
EzFile::upload('upload_path', $your_files_in_array);

//Uploading and renaming (the lib will interate automatically as new_name_1... new_name_2....)
EzFile::upload('upload_path', $your_files_in_array, 'new_name');

//Uploading accept only files with
EzFile::upload('upload_path', $your_files_in_array, false, ['txt', 'png', 'json', /* ... */]);

//Using 'force' paramn to Upload outsite main path
EzFile::upload('upload_path', $your_files_in_array, false, [], true);

/*
====== [ Function Return ] ===== 
SUCCESS: ['success' => [], 'fail' => [], 'denied' => []]
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Download Function
```php
//Download a Directory (and all contents inside) or File
EzFile::download('your_path');

//Using 'force' paramn to download outsite main path
EzFile::download('your_path', true);

/*
====== [ Function Return ] ===== 
SUCCESS: The user will receive the download item
ERROR: ['error' => true, 'message' => 'error_message']
*/
```
----
##### Computational Units Const
```php
// Get size unit constants
EzFile::UNIT_BYTES;      //Return (string): "B"
EzFile::UNIT_KILOBYTES;  //Return (string): "KB"
EzFile::UNIT_MEGABYTES;  //Return (string): "MB"
EzFile::UNIT_GIGABYTES;  //Return (string): "GB"
EzFile::UNIT_TERABYTES;  //Return (string): "TB"
EzFile::UNIT_PETABYTES;  //Return (string): "PB"
EzFile::UNIT_EXABYTES;   //Return (string): "EB"
EzFile::UNIT_ZETTABYTES; //Return (string): "ZB"
EzFile::UNIT_YOTTABYTES; //Return (string): "YB"
```
----
##### Units Format Function
```php
// Format the value for human reading

//Formatting bytes Value
EzFile::sizeUnitFormatter(100); //Return (string): 100 B

//Formatting by setting computational unit
EzFile::sizeUnitFormatter(5, EzFile::UNIT_GIGABYTES);   //Return (string): 5 GB
EzFile::sizeUnitFormatter(500, EzFile::UNIT_GIGABYTES); //Return (string): 500 GB
EzFile::sizeUnitFormatter(1, EzFile::UNIT_TERABYTES);   //Return (string): 1 TB

//Formatting by setting computational unit with data in byte number
EzFile::sizeUnitFormatter(1, EzFile::UNIT_TERABYTES, true); //Return (string): 1099511627776 B

/*
====== [ Function Return ] ===== 
SUCCESS: Return a string value
ERROR: ['error' => true, 'message' => 'error_message']
*/
```

## Example of Code
See below a simple example of how to work with some functions
```php

//Example Creating a file/Directory
$ezFile = EzFile::create('your_path');
if(isset($ezFile['error'])){
    // Ops, errors found... put your code logic here with message $ezFile['message']
} else {
    // It Worked
}

//Example renaming a file/Directory
$ezFile = EzFile::rename('current_path', 'renamed_path');
if(isset($ezFile['error'])){
    // Ops, errors found... put your code logic here with message $ezFile['message']
} else {
    // It Worked
}

//Example getting the pathinfo from file/Directory
$ezFile = EzFile::pathInfo('your_path');
if(isset($ezFile['error'])){
    // Ops, errors found... put your code logic here with message $ezFile['message']
} else {
    // It Worked, get all data from the array $ezFile
}

//Example uploading file(s)/Directory(ies)
$ezFile = EzFile::upload('upload_path', $your_files_in_array);
if(isset($ezFile['error'])){
    // Ops, errors found... put your code logic here with message $ezFile['message']
} else {
    // It Worked, get all data from the array $ezFile
}
```
----
## * Important *

If "*path*" paramn send has errors, all the functions above will return an array:
```php
['error' => true, 'message' => 'error_message']
```

## Tests
- All the tests can be found in *EzFile/tests/EzFileTest.php*
- Run the tests by typing in console
```bash
./vendor/bin/phpunit tests/ --colors=always
```

## Donate
If you find this project helpful and valuable, please consider supporting me. Your contribution help me to maintain and improve this library for everyone use with no cost. Every little bit helps!

[Donate Here](https://nubank.com.br/pagar/5th42/1efRKgR2V8)

Thank you for your generosity and support! 🙏
____
This README provides an overview of the EzFile library, its features, and how to use it. Adjustments can be made as needed to fit your specific project.
