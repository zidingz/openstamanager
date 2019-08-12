<?php

use Modules\Contratti\Contratto;

$documento = Contratto::find($id_record);

$id_cliente = $documento['idanagrafica'];
$id_sede = $documento['idsede'];
