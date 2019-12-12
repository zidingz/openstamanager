<?php

if (in_array($id_cliente, $tipi_anagrafica)) {
    echo '
<div class="dropdown dropleft">
    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fa fa-magic"></i> '.tr('Crea').'...
    </button>

    <div class="dropdown-menu">
        <a class="dropdown-item" data-toggle="modal" data-title="'.tr('Aggiungi intervento').'" data-href="add.php?id_module='.Modules::get('Interventi')['id'].'&idanagrafica='.$record['idanagrafica'].'">
            <i class="fa fa-wrench"></i> '.tr('Nuovo intervento').'
        </a>

        <a class="dropdown-item" data-toggle="modal" data-title="'.tr('Aggiungi preventivo').'" data-href="add.php?id_module='.Modules::get('Preventivi')['id'].'&idanagrafica='.$record['idanagrafica'].'">
            <i class="fa fa-file-text"></i> '.tr('Nuovo preventivo').'
        </a>

        <a class="dropdown-item" data-toggle="modal" data-title="'.tr('Aggiungi contratto').'" data-href="add.php?id_module='.Modules::get('Contratti')['id'].'&idanagrafica='.$record['idanagrafica'].'">
            <i class="fa fa-file-text-o"></i> '.tr('Nuovo contratto').'
        </a>

        <a class="dropdown-item" data-toggle="modal" data-title="'.tr('Aggiungi ordine').'" data-href="add.php?id_module='.Modules::get('Ordini cliente')['id'].'&idanagrafica='.$record['idanagrafica'].'">
            <i class="fa fa-file-o"></i> '.tr('Nuovo ordine').'
        </a>

		  <a class="dropdown-item" data-toggle="modal" data-title="'.tr('Aggiungi ddt').'" data-href="add.php?id_module='.Modules::get('Ddt di vendita')['id'].'&idanagrafica='.$record['idanagrafica'].'">
		    <i class="fa fa-truck"></i> '.tr('Nuovo ddt').'
        </a>

        <a class="dropdown-item" data-toggle="modal" data-title="'.tr('Aggiungi fattura').'" data-href="add.php?id_module='.Modules::get('Fatture di vendita')['id'].'&idanagrafica='.$record['idanagrafica'].'">
            <i class="fa fa-file"></i> '.tr('Nuova fattura').'
        </a>

    </div>
</div>';
}
