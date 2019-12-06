<?php

use Modules\Aggiornamenti\Aggiornamento;

try {
    $update = new Aggiornamento();

    include $module->filepath('view.php');

    return;
} catch (InvalidArgumentException $e) {
}

// Personalizzazioni di codice
$custom = custom();
$tables = customTables();
if (!empty($custom) || !empty($tables)) {
    echo '
	<div class="card card-outline card-warning">
		<div class="card-header">
			<h3 class="card-title"><span class="tip" title="'.tr('Elenco delle personalizzazioni rilevabili dal gestionale').'.">
				<i class="fa fa-edit"></i> '.tr('Personalizzazioni').'
			</span></h3>
		</div>
		<div class="card-body">';

    if (!empty($custom)) {
        echo '
			<table class="table table-hover table-striped">
				<tr>
					<th width="10%">'.tr('Percorso').'</th>
					<th width="15%">'.tr('Cartella personalizzata').'</th>
					<th width="15%">'.tr('Database personalizzato').'</th>
				</tr>';

        foreach ($custom as $element) {
            echo '
				<tr>
					<td>'.$element['path'].'</td>
					<td>'.($element['directory'] ? 'Si' : 'No').'</td>
					<td>'.($element['database'] ? 'Si' : 'No').'</td>
				</tr>';
        }

        echo '
			</table>

			<p><strong>'.tr("Si sconsiglia l'aggiornamento senza il supporto dell'assistenza ufficiale").'.</strong></p>';
    } else {
        echo '
			<p>'.tr('Non ci sono strutture personalizzate').'.</p>';
    }

    if (!empty($tables)) {
        echo '
			<div class="alert alert-warning">
				<i class="fa fa-warning"></i>
				<b>Attenzione!</b> Ci sono delle tabelle non previste nella versione standard del gestionale: '.implode(', ', $tables).'.
			</div>';
    }

    echo '
		</div>
	</div>';
}

// Aggiornamenti
if (setting('Attiva aggiornamenti')) {
    $alerts = [];

    if (!extension_loaded('zip')) {
        $alerts[tr('Estensione ZIP')] = tr('da abilitare');
    }

    $upload_max_filesize = ini_get('upload_max_filesize');
    $upload_max_filesize = str_replace(['k', 'M'], ['000', '000000'], $upload_max_filesize);
    // Dimensione minima: 32MB
    if ($upload_max_filesize < 32000000) {
        $alerts['upload_max_filesize'] = '32MB';
    }

    $post_max_size = ini_get('post_max_size');
    $post_max_size = str_replace(['k', 'M'], ['000', '000000'], $post_max_size);
    // Dimensione minima: 32MB
    if ($post_max_size < 32000000) {
        $alerts['post_max_size'] = '32MB';
    }

    if (!empty($alerts)) {
        echo '
<div class="alert alert-warning">
    <p>'.tr('Devi modificare il seguenti parametri del file di configurazione PHP (_FILE_) per poter caricare gli aggiornamenti', [
        '_FILE_' => '<b>php.ini</b>',
    ]).':<ul>';
        foreach ($alerts as $key => $value) {
            echo '
        <li><b>'.$key.'</b> = '.$value.'</li>';
        }
        echo '
    </ul></p>
</div>';
    }

    echo '
<script>
function update() {
    if ($("#blob").val()) {
        swal({
            title: "'.tr('Avviare la procedura?').'",
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "'.tr('Sì').'"
        }).then(function (result) {
            $("#update").submit();
        })
    } else {
        swal({
            title: "'.tr('Selezionare un file!').'",
            type: "error",
        })
    }
}

function search(button) {
    buttonLoading(button);

    $.ajax({
        url: globals.rootdir + "/actions.php",
        type: "post",
        data: {
            id_module: globals.id_module,
            op: "check",
        },
        success: function(data){
            $("#update-search").addClass("d-none");

            if (data == "none" || data == "" ) {
                $("#update-none").removeClass("d-none");
            } else {
                $("#update-version").text(data);
                $("#update-download").removeClass("d-none");
            }
        }
    });
}

function download(button) {
    buttonLoading(button);
    $("#main_loading").show();

    $.ajax({
        url: globals.rootdir + "/actions.php",
        type: "post",
        data: {
            id_module: globals.id_module,
            op: "download",
        },
        success: function(){
            window.location.reload();
        }
    });
}
</script>';

    echo '
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    '.tr('Carica un aggiornamento').' <span class="tip" title="'.tr('Form di caricamento aggiornamenti del gestionale e innesti di moduli e plugin').'."><i class="fa fa-question-circle-o"></i></span>
                </h3>
            </div>
            <div class="card-body">
                <form action="'.pathFor('module', ['module_id' => $id_module]).'" method="post" enctype="multipart/form-data" id="update">
                    <input type="hidden" name="op" value="upload">
                    <input type="hidden" name="backto" value="record-list">

			        {[ "type": "file", "name": "blob", "required": 1, "accept": ".zip" ]}

                    <button type="button" class="btn btn-primary float-right" onclick="update()">
                        <i class="fa fa-upload"></i> '.tr('Carica').'
                    </button>
                </form>
            </div>
        </div>
    </div>';

    echo '

    <div class="col-md-4">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">
                    '.tr('Ricerca aggiornamenti').' <span class="tip" title="'.tr('Controllo automatico della presenza di aggiornamenti per il gestionale').'."><i class="fa fa-question-circle-o"></i></span>
                </h3>
            </div>
            <div class="card-body">';

    if (extension_loaded('curl')) {
        echo '
            <div id="update-search">
                <button type="button" class="btn btn-info btn-block" onclick="search(this)">
                    <i class="fa fa-search"></i> '.tr('Ricerca').'
                </button>
            </div>

            <div id="update-download" class="d-none">
                <p>'.tr("E' stato individuato un nuovo aggiornamento").': <b id="update-version"></b>.</p>
                <p>'.tr('Scaricalo manualmente (_LINK_) oppure in automatico', [
                    '_LINK_' => "<a href='https://github.com/devcode-it/openstamanager/releases'>https://github.com/devcode-it/openstamanager/releases</a>",
                ]).':</p>

                <button type="button" class="btn btn-success btn-block" onclick="download(this)">
                    <i class="fa fa-download"></i> '.tr('Scarica').'
                </button>
            </div>

            <div id="update-none" class="d-none">
                <p>'.tr('Nessun aggiornamento presente').'.</p>
            </div>';
    } else {
        echo'
        <button type="button" class="btn btn-warning btn-block disabled" >
            <i class="fa fa-warning"></i> '.tr('Estensione curl non supportata').'.
        </button>';
    }

    echo '
            </div>
        </div>
    </div>
</div>';
}

// Requisiti
echo '
<hr>
<div>
    <h3>'.tr('Requisiti').'</h3>';

include DOCROOT.'/include/init/requirements.php';

echo '

</div>';
