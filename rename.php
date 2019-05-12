<?php

$src = '/store/MigrationTmp/DVD-CD/Library/Медиа курсы/';

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src));

function transliterate($textcyr) {
    $cyr = array(
    'ж',  'ч',  'щ',   'ш',  'ю',  'а', 'б', 'в', 'г', 'д', 'е', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ъ', 'ь', 'я', 'ы', '№',    'ё', 'Ё',
    'Ж',  'Ч',  'Щ',   'Ш',  'Ю',  'А', 'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ъ', 'Ь', 'Я', 'Ы');
    $lat = array(
    'zh', 'ch', 'sht', 'sh', 'yu', 'a', 'b', 'v', 'g', 'd', 'e', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', '', '', 'ya','u', 'nom.', 'e', 'E',
    'Zh', 'Ch', 'Sht', 'Sh', 'Yu', 'A', 'B', 'V', 'G', 'D', 'E', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'c', '', '', 'Ya','U');
    return str_replace($cyr, $lat, $textcyr);
}

// Rename first only files

foreach ($rii as $file) {

    if ($file->isDir()) continue;

    $origPathname = $file->getPathname();

    $origFilename = basename($origPathname);
    $origDirname = dirname($origPathname);

    $newFilename = transliterate($origFilename);

    if ($origFilename == $newFilename) continue;

    $newFilename = preg_replace('~^(.{1,64}).*(\..{2,4})$~u', '$1$2',$newFilename);
    $newFilename = preg_replace('~\s+(\..{2,4})$~u', '$1',$newFilename);
    $newFilename = str_replace([' ',','], '-', $newFilename);
    $newFilename = str_replace('.-', '-', $newFilename);
    $newFilename = preg_replace('~-{2,}~u', '-',$newFilename);
    $newFilename = preg_replace('~[^-a-z0-9_.,]~i', '', $newFilename);


    $newPathname = $origDirname.'/'.$newFilename;

    echo 'Rename: '.$origPathname.PHP_EOL;
    echo '- To: '.$newPathname.PHP_EOL;
    echo PHP_EOL;

    rename($origPathname, $newPathname);

}

// Rename only dirs

$dirsToRename = []; // first of all collect all to reverse it later to rename them from sub-folders to parent

foreach ($rii as $file) {

    if (!$file->isDir()) continue;

    $origPathname = $file->getPathname();

    if ( basename($origPathname) == '..') continue;

    $dirpathToRename = rtrim($origPathname,'/.');

    $parentDirname = dirname($dirpathToRename);

    $dirnameToRename = basename($dirpathToRename);

    $newDirnameToRename = transliterate($dirnameToRename);

    if ($dirnameToRename == $newDirnameToRename) continue;

    $newDirnameToRename = mb_substr($newDirnameToRename, 0, 96, 'utf-8');
    $newDirnameToRename = str_replace([' ',','], '-', $newDirnameToRename);
    $newDirnameToRename = str_replace('.-', '-', $newDirnameToRename);
    $newDirnameToRename = preg_replace('~-{2,}~u', '-',$newDirnameToRename);
    $newDirnameToRename = preg_replace('~[^-a-z0-9_.,]~i', '', $newDirnameToRename);
    $newDirnameToRename = rtrim($newDirnameToRename, '-');

    $dirsToRename[] = [
        'from' => $parentDirname.'/'.$dirnameToRename,
        'to' => $parentDirname.'/'.$newDirnameToRename,
    ];
}

if ($dirsToRename) {
    $dirsToRename = array_reverse($dirsToRename);
    foreach($dirsToRename as $item) {
        echo 'Rename: '.$item['from'].PHP_EOL;
        echo '- To: '.$item['to'].PHP_EOL;
        echo PHP_EOL;

        rename($item['from'], $item['to']);
    }
}
