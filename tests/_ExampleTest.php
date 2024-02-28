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
}

//Verifica se arquivo/pasta existe
//EzFile::exists(self::FOLDER);

//Criar Pasta
//EzFile::create(self::FOLDER."/minha_pasta/outra");

//Criar Arquivo
//EzFile::create(self::FOLDER."/minha_pasta/outra/teste.txt");

//Renomear Pasta
//EzFile::rename(self::FOLDER."/minha_pasta", self::FOLDER."/pasta_renomeada");

//Renomear Arquivo
//EzFile::rename(self::FOLDER."/minha_pasta/renomeamos/teste.txt", self::FOLDER."/minha_pasta/renomeamos/renomeou.txt");

//Move/recorta o diretório com todos os seus conteúdos para outro lugar
//EzFile::move(self::FOLDER."/teste", self::FOLDER."/minha_pasta");

//Move/recorta o arquivo para outro lugar
//EzFile::move(self::FOLDER."/arquivo_renomeado.txt", self::FOLDER."/minha_pasta/movendo/arquivo_renomeado.txt");

//Copia o diretório com todos os seus conteúdos para outro lugar
//EzFile::copy(self::FOLDER."/minha_pasta", self::FOLDER."/pasta_copiada");

//Copia o arquivo para outro lugar
//EzFile::copy(self::FOLDER."/arquivo_renomeado.txt", self::FOLDER."/minha_pasta/arquivo_renomeado.txt");

//Altera as permissões de um arquivo/pasta
//EzFile::changePermissions(self::FOLDER."/arquivo_renomeado.txt", 0777);

//Obtém as informações de uma arquivo/pasta
//$data = EzFile::pathInfo(self::FOLDER."/finalizou.txt");

//Lista todos os arquivos de uma pasta
//EzFile::list(self::FOLDER)

//Zip de pastas com arquivos
//EzFile::zip(self::FOLDER."/sddsds", self::FOLDER);

//Faz upload de arquivos
//EzFile::upload(self::FOLDER, $_FILES, false, ['xlsx', 'png', 'txt'])

//Faz download de pastas
//EzFile::download('filemanager/mover_pasta');

//Faz download de arquivos
//EzFile::download('filemanager/mover_pasta/b.txt');

//Exclui Diretório e tudo o que tem dentro
//EzFile::delete(self::FOLDER."/minha_pasta");

//Exclui Arquivo
//EzFile::delete(self::FOLDER."/minha_pasta/renomeamos/arquivo_renomeado.txt");

//Obtém constantes de unidade de tamanho
//EzFile::UNIT_BYTES;
//EzFile::UNIT_KILOBYTES;
//EzFile::UNIT_MEGABYTES;
//EzFile::UNIT_GIGABYTES;
//EzFile::UNIT_TERABYTES;
//EzFile::UNIT_PETABYTES;
//EzFile::UNIT_EXABYTES;
//EzFile::UNIT_ZETTABYTES;
//EzFile::UNIT_YOTTABYTES;

//Formata o valor para leitura humanda
//EzFile::sizeUnitFormatter(100);// 100 B
//EzFile::sizeUnitFormatter(1, EzFile::UNIT_GIGABYTES); // 5 GB
//EzFile::sizeUnitFormatter(10, EzFile::UNIT_TERABYTES); // 10 GB
//EzFile::sizeUnitFormatter(1, EzFile::UNIT_TERABYTES, true); // 1099511627776 B