<?php

include_once __DIR__.'/../../core.php';

use Prints\Template;

if (isset($id_record)) {
    $print = Template::find($id_record);
    $record = $print->toArray();
}
