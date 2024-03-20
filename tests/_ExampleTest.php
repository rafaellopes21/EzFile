<?php
namespace AmplieSolucoes\EzFile;

class _ExampleTest{
    const DIRECTORY_TEST = __DIR__.'/folder_test';
    const FILE_TEST = self::DIRECTORY_TEST.'/file_test.txt';

    const DIRECTORY_TEST_FORCE = __DIR__.'/../folder_test';
    const FILE_TEST_FORCE = self::DIRECTORY_TEST_FORCE.'/file_test.txt';

    const DIRECTORY_RENAME_TEST = __DIR__.'/folder_test_rename';
    const FILE_RENAME_TEST = self::DIRECTORY_TEST.'/file_test_rename.txt';

    const DIRECTORY_TEST_COPY = __DIR__.'/folder_test_copy';
    const DIRECTORY_EXTRACTOR = __DIR__.'/extracted';
}