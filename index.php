<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.n.c.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
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
