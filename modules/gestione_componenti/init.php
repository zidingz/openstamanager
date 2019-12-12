<?php

$cmp = \Util\Ini::getList(DOCROOT.'/files/my_impianti/');

if (!empty($id_record) && isset($cmp[$id_record - 1])) {
    $record['nomefile'] = $cmp[$id_record - 1][0];
    $record['contenuto'] = file_get_contents(DOCROOT.'/files/my_impianti/'.$record['nomefile']);
}
