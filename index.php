<?php
/**
 * Questo file gestisce un redirect automatico alla cartella public/ per la gestione di tutte le richieste al software.
 */

$docroot = __DIR__;

// Individuazione del prefisso dell'URL
$script = $_SERVER['REQUEST_URI'];
$needle = '/'.basename($docroot).'/';
$pos = strrpos($script, $needle);
if ($pos !== false) {
    $prefix = substr($script, 0, $pos).$needle;
    $suffix = substr($script, $pos + strlen($needle));
} else {
    $prefix = '/';
    $suffix = '';
}
$prefix = rtrim($prefix, '/');
$prefix = str_replace('%2F', '/', rawurlencode($prefix));
$suffix = str_replace('%2F', '/', rawurlencode($suffix));

// Indirizzo di redirect
$url = 'http://'.$_SERVER['HTTP_HOST'].$prefix.'/public/'.$suffix;
$url = str_replace('index.php', '', $url);

// Redirect permanente (301)
header('HTTP/1.1 301 Moved Permanently');
header('Location: '.$url);
exit();
