<?php

namespace AmplieSolucoes\EzFile;

use AmplieSolucoes\EzFile\EzFile;
use PHPUnit\Framework\TestCase;

class EzFileTest extends TestCase
{
    public function testCreate(){
        /*---[ DIRECTORY ]---*/
        $path = _ExampleTest::DIRECTORY_TEST;

        //Check if exists before create
        $this->assertFalse(EzFile::exists($path));
        EzFile::create($path);
        $this->assertTrue(EzFile::exists($path));

        /*---[ FILE ]---*/
        $path = _ExampleTest::FILE_TEST;

        //Check if exists before create
        $this->assertFalse(EzFile::exists($path));
        EzFile::create($path);
        $this->assertTrue(EzFile::exists($path));
    }

    public function testExists(){
        // Directory Exists
        $path = _ExampleTest::DIRECTORY_TEST;
        $this->assertTrue(EzFile::exists($path));

        // File Exists
        $path = _ExampleTest::FILE_TEST;
        $this->assertTrue(EzFile::exists($path));

        // Directory Not Exists
        $path = _ExampleTest::DIRECTORY_TEST."/fail";
        $this->assertFalse(EzFile::exists($path));

        // File Not Exists
        $path = _ExampleTest::FILE_TEST."/fail.txt";
        $this->assertFalse(EzFile::exists($path));
    }

    public function testRename(){
        /*---[ FILE ]---*/
        $original = _ExampleTest::FILE_TEST;
        $renamed = _ExampleTest::FILE_RENAME_TEST;

        EzFile::rename($original, $renamed);
        $this->assertTrue(EzFile::exists($renamed));
        $this->assertFalse(EzFile::exists($original));

        /*---[ DIRECTORY ]---*/
        $original = _ExampleTest::DIRECTORY_TEST;
        $renamed = _ExampleTest::DIRECTORY_RENAME_TEST;

        EzFile::rename($original, $renamed);
        $this->assertTrue(EzFile::exists($renamed));
        $this->assertFalse(EzFile::exists($original));
    }

    public function testMove(){
        //Create a dir to move the case below
        $this->testCreate();

        /*---[ FILE ]---*/
        $original = _ExampleTest::DIRECTORY_RENAME_TEST."/file_test_rename.txt";
        $moved = _ExampleTest::DIRECTORY_TEST."/file_test_rename.txt";
        EzFile::move($original, $moved);
        $this->assertTrue(EzFile::exists($moved));
        $this->assertFalse(EzFile::exists($original));

        /*---[ DIRECTORY (and all inside) ]---*/
        EzFile::move(_ExampleTest::DIRECTORY_TEST, _ExampleTest::DIRECTORY_RENAME_TEST);
        $this->assertTrue(EzFile::exists(_ExampleTest::DIRECTORY_RENAME_TEST));
        $this->assertFalse(EzFile::exists(_ExampleTest::DIRECTORY_TEST));
    }

    public function testCopy(){
        /*---[ DIRECTORY (and all inside) ]---*/
        $original = _ExampleTest::DIRECTORY_RENAME_TEST;
        $copied = _ExampleTest::DIRECTORY_TEST_COPY;
        EzFile::copy($original, $copied);
        $this->assertTrue(EzFile::exists($copied));

        /*---[ FILE ]---*/
        $original = _ExampleTest::DIRECTORY_RENAME_TEST."/file_test.txt";
        $copied = _ExampleTest::DIRECTORY_TEST_COPY."/file_test_copied.txt";
        EzFile::copy($original, $copied);
        $this->assertTrue(EzFile::exists($copied));
    }

    public function testChangePermissions(){
        /*---[ FILE ]---*/
        $path = _ExampleTest::DIRECTORY_TEST_COPY."/file_test_copied.txt";
        EzFile::changePermissions($path, 0777);
        $this->assertFileIsWritable($path, 0777);
        $this->assertFileIsReadable($path, 0777);

        /*---[ DIRECTORY ]---*/
        $path = _ExampleTest::DIRECTORY_TEST_COPY;
        EzFile::changePermissions($path, 0777);
        $this->assertFileIsWritable($path, 0777);
        $this->assertFileIsReadable($path, 0777);
    }

    public function testPathInfo(){
        /*---[ DIRECTORY ]---*/
        $path = _ExampleTest::DIRECTORY_TEST_COPY;
        $pathInfo = EzFile::pathInfo($path);
        $this->assertIsArray($pathInfo);
        $this->assertArrayNotHasKey('extension', $pathInfo);

        /*---[ FILE ]---*/
        $path = _ExampleTest::DIRECTORY_TEST_COPY."/file_test_copied.txt";
        $pathInfo = EzFile::pathInfo($path);
        $this->assertIsArray($pathInfo);
        $this->assertArrayHasKey('extension', $pathInfo);
    }

    public function testList(){
        $files = EzFile::list(_ExampleTest::DIRECTORY_TEST_COPY);
        $this->assertIsArray($files);
    }

    public function testZipFolderWithForce(){
        $path = _ExampleTest::DIRECTORY_TEST_COPY;
        $zipPath = _ExampleTest::DIRECTORY_TEST_COPY."/../";

        $result = EzFile::zip($path, $zipPath, true);
        $this->assertTrue($result);
        $this->assertFileExists($zipPath);
    }

    public function testDelete(){
        $result = EzFile::delete(__DIR__."/folder_test_copy.zip");
        $this->assertTrue($result);
        $result = EzFile::delete(_ExampleTest::DIRECTORY_TEST_COPY);
        $this->assertTrue($result);
        $result = EzFile::delete(_ExampleTest::DIRECTORY_RENAME_TEST);
        $this->assertTrue($result);
    }

    public function testSizeFormatter(){
        $result1 = EzFile::sizeUnitFormatter(100);
        $this->assertEquals('100 B', $result1);

        $result2 = EzFile::sizeUnitFormatter(1, EzFile::UNIT_GIGABYTES);
        $this->assertEquals('1 GB', $result2);

        $result3 = EzFile::sizeUnitFormatter(10, EzFile::UNIT_TERABYTES);
        $this->assertEquals('10 TB', $result3);

        $result4 = EzFile::sizeUnitFormatter(1, EzFile::UNIT_TERABYTES, true);
        $this->assertEquals('1099511627776 B', $result4);
    }
}
