<?php

$module = Modules::get($id_module);

if ($module['name'] == 'Ddt di vendita') {
    $dir = 'entrata';
} else {
    $dir = 'uscita';
}
unset($_SESSION['superselect']['idanagrafica']);
unset($_SESSION['superselect']['idsede_partenza']);
unset($_SESSION['superselect']['idsede_destinazione']);
$_SESSION['superselect']['idanagrafica'] = $record['idanagrafica'];
$_SESSION['superselect']['idsede_partenza'] = $record['idsede_partenza'];
$_SESSION['superselect']['idsede_destinazione'] = $record['idsede_destinazione'];

?>
<form action="" method="post" id="edit-form">
	<input type="hidden" name="backto" value="record-edit">
	<input type="hidden" name="op" value="update">
	<input type="hidden" name="id_record" value="<?php echo $id_record; ?>">

	<!-- INTESTAZIONE -->
	<div class="card card-primary">
		<div class="card-header">
			<h3 class="card-title"><?php echo tr('Intestazione'); ?></h3>
		</div>

		<div class="card-body">


			<?php
                if ($dir == 'entrata') {
                    $anagrafica = $dbo->fetchOne('SELECT an_anagrafiche.piva, an_anagrafiche.codice_fiscale, citta, indirizzo, cap, provincia FROM an_anagrafiche
                        INNER JOIN `an_sedi` ON `an_sedi`.`id`=`an_anagrafiche`.`id_sede_legale`
                        WHERE `an_anagrafiche`.idanagrafica='.prepare($record['idanagrafica']));
                    $campi_mancanti = [];

                    if ($anagrafica['piva'] == '') {
                        if ($anagrafica['codice_fiscale'] == '') {
                            array_push($campi_mancanti, 'codice fiscale');
                        }
                    }
                    if ($anagrafica['citta'] == '') {
                        array_push($campi_mancanti, 'citta');
                    }
                    if ($anagrafica['indirizzo'] == '') {
                        array_push($campi_mancanti, 'indirizzo');
                    }
                    if ($anagrafica['cap'] == '') {
                        array_push($campi_mancanti, 'C.A.P.');
                    }

                    if (sizeof($campi_mancanti) > 0) {
                        echo "<div class='alert alert-warning'><i class='fa fa-warning'></i> Prima di procedere alla stampa completa i seguenti campi dell'anagrafica:<br/><b>".implode(', ', $campi_mancanti).'</b><br/>
						'.Modules::link('Anagrafiche', $record['idanagrafica'], tr('Vai alla scheda anagrafica'), null).'</div>';
                    }
                }
            ?>


			<div class="row">
				<?php
                    if ($dir == 'uscita') {
                        echo '
							<div class="col-md-3">
								{[ "type": "span", "label": "'.tr('Numero ddt').'", "class": "text-center", "value": "$numero$" ]}
							</div>';
                    }
                ?>

				<div class="col-md-3">
					{[ "type": "text", "label": "<?php echo tr('Numero secondario'); ?>", "name": "numero_esterno", "class": "text-center", "value": "$numero_esterno$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "date", "label": "<?php echo tr('Data'); ?>", "name": "data", "required": 1, "value": "$data$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
				</div>

				<div class="col-md-3">
                    <?php
                    if (setting('Cambia automaticamente stato ddt fatturati')) {
                        if ($record['stato'] == 'Fatturato' || $record['stato'] == 'Parzialmente fatturato') {
                            ?>
                            {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "id_stato", "required": 1, "values": "query=SELECT * FROM dt_statiddt", "value": "$id_stato$", "extra": "readonly" ]}
                    <?php
                        } else {
                            ?>
                            {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "id_stato", "required": 1, "values": "query=SELECT * FROM dt_statiddt WHERE descrizione IN('Bozza', 'Evaso', 'Parzialmente evaso')", "value": "$id_stato$" ]}
                    <?php
                        }
                    } else {
                        ?>
                    {[ "type": "select", "label": "<?php echo tr('Stato'); ?>", "name": "id_stato", "required": 1, "values": "query=SELECT * FROM dt_statiddt", "value": "$id_stato$" ]}
                    <?php
                    }
                    ?>
				</div>
			</div>

                <?php
                // Conteggio numero articoli ddt in uscita
                $articolo = $dbo->fetchArray('SELECT mg_articoli.id FROM ((mg_articoli INNER JOIN dt_righe_ddt ON mg_articoli.id=dt_righe_ddt.idarticolo) INNER JOIN dt_ddt ON dt_ddt.id=dt_righe_ddt.idddt) WHERE dt_ddt.id='.prepare($id_record));
                ?>
                <div class="row">
                    <div class="col-md-3">
                        {[ "type": "select", "label": "<?php echo ($dir == 'uscita') ? tr('Fornitore') : tr('Destinatario'); ?>", "name": "idanagrafica", "required": 1, "value": "$idanagrafica$", "ajax-source": "clienti_fornitori", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                    </div>

                    <?php
                        if ($dir == 'entrata') {
                            ?>
                    <div class="col-md-3">
                        {[ "type": "select", "label": "<?php echo tr('Partenza merce'); ?>", "name": "idsede_partenza", "ajax-source": "sedi_azienda",  "value": "$idsede_partenza$", "readonly": "<?php echo ($record['flag_completato'] || sizeof($articolo)) ? 1 : 0; ?>" ]}
                    </div>
                    <div class="col-md-3">
                        {[ "type": "select", "label": "<?php echo tr('Destinazione merce'); ?>", "name": "idsede_destinazione", "ajax-source": "sedi", "ajax-info": "idanagrafica=$idanagrafica$", "value": "$idsede_destinazione$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                    </div>
                    <?php
                        } else {
                            ?>
                    <div class="col-md-3">
                        {[ "type": "select", "label": "<?php echo tr('Partenza merce'); ?>", "name": "idsede_partenza", "ajax-source": "sedi", "ajax-info": "idanagrafica=$idanagrafica$", "value": "$idsede_partenza$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                    </div>
                    <div class="col-md-3">
                        {[ "type": "select", "label": "<?php echo tr('Destinazione merce'); ?>", "name": "idsede_destinazione", "ajax-source": "sedi_azienda",  "value": "$idsede_destinazione$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
                    </div>

                    <?php
                        }
                    ?>
                </div>
            <hr>

			<div class="row">
				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Aspetto beni'); ?>", "name": "idaspettobeni", "value": "$idaspettobeni$",  "ajax-source": "aspetto-beni", "readonly": "<?php echo $record['flag_completato']; ?>", "icon-after": "add|<?php echo Modules::get('Aspetto beni')['id']; ?>" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Causale trasporto'); ?>", "name": "idcausalet",  "value": "$idcausalet$", "ajax-source": "causali", "readonly": "<?php echo $record['flag_completato']; ?>", "icon-after": "add|<?php echo Modules::get('Causali')['id']; ?>" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Porto'); ?>", "name": "idporto", "placeholder": "-", "help": "<?php echo tr('<ul><li>Franco: pagamento del trasporto a carico del mittente</li> <li>Assegnato pagamento del trasporto a carico del destinatario</li> </ul>'); ?>", "values": "query=SELECT id, descrizione FROM dt_porto ORDER BY descrizione ASC", "value": "$idporto$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "text", "label": "<?php echo tr('Num. colli'); ?>", "name": "n_colli", "value": "$n_colli$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
				</div>
			</div>

			<div class="row">
				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Pagamento'); ?>", "name": "idpagamento", "values": "query=SELECT id, descrizione FROM co_pagamenti GROUP BY descrizione ORDER BY descrizione ASC", "value": "$idpagamento$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Tipo di spedizione'); ?>", "name": "idspedizione", "placeholder": "-", "values": "query=SELECT id, descrizione FROM dt_spedizione ORDER BY descrizione ASC", "value": "$idspedizione$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
				</div>

				<div class="col-md-3">
					{[ "type": "select", "label": "<?php echo tr('Vettore'); ?>", "name": "idvettore", "ajax-source": "vettori", "value": "$idvettore$", "readonly": "<?php echo $record['flag_completato']; ?>", "disabled": <?php echo intval($record['idspedizione'] == 3); ?>, "required": <?php echo (!empty($record['idspedizione'])) ? intval($record['idspedizione'] != 3) : 0; ?>, "icon-after": "add|<?php echo Modules::get('Anagrafiche')['id']; ?>|tipoanagrafica=Vettore|<?php echo (($record['idspedizione'] != 3 and intval(!$record['flag_completato']))) ? '' : 'disabled'; ?>" ]}
				</div>


                 <script>
                    $("#idspedizione").change( function(){
                        if ($(this).val() == 3) {
                            $("#idvettore").attr("required", false);
                            $("#idvettore").attr("disabled", true);
                            $("label[for=idvettore]").text("<?php echo tr('Vettore'); ?>");
                            $("#idvettore").selectReset("- Seleziona un'opzione -");
                            $("#idvettore").next().next().find("button.bound:nth-child(1)").prop("disabled", true);
                        }else{
                            $("#idvettore").attr("required", true);
                            $("#idvettore").attr("disabled", false);
                            $("label[for=idvettore]").text("<?php echo tr('Vettore'); ?>*");
                            $("#idvettore").next().next().find("button.bound:nth-child(1)").prop("disabled", false);
                        }
                    });

                    $("#idcausalet").change( function(){
                        if ($(this).val() == 3) {
                            $("#tipo_resa").attr("disabled", false);
                        }else{
                            $("#tipo_resa").attr("disabled", true);
                        }
                    });
                </script>
			</div>

			<div class="row">
				<div class="col-md-12">
					{[ "type": "textarea", "label": "<?php echo tr('Note'); ?>", "name": "note", "value": "$note$", "readonly": "<?php echo $record['flag_completato']; ?>" ]}
				</div>
			</div>

            <div class="row">
                <div class="col-md-12">
                    {[ "type": "textarea", "label": "<?php echo tr('Note aggiuntive'); ?>", "name": "note_aggiuntive", "help": "<?php echo tr('Note interne.'); ?>", "value": "$note_aggiuntive$" ]}
                </div>
            </div>
		</div>
	</div>
</form>


<!-- RIGHE -->
<div class="card card-primary">
	<div class="card-header">
		<h3 class="card-title"><?php echo tr('Righe'); ?></h3>
	</div>

	<div class="card-body">
		<div class="float-left">
<?php

if ($record['flag_completato'] == 0) {
    // Lettura ordini
    $ordini_query = 'SELECT COUNT(*) AS tot FROM or_ordini WHERE idanagrafica='.prepare($record['idanagrafica']).' AND id_stato IN (SELECT id FROM or_statiordine WHERE descrizione IN(\'Bozza\', \'Evaso\', \'Parzialmente evaso\', \'Parzialmente fatturato\')) AND id_tipo_ordine=(SELECT id FROM or_tipiordine WHERE dir='.prepare($dir).') AND or_ordini.id IN (SELECT idordine FROM or_righe_ordini WHERE or_righe_ordini.idordine = or_ordini.id AND (qta - qta_evasa) > 0)';
    $ordini = $dbo->fetchArray($ordini_query)[0]['tot'];
    echo '
            <a class="btn btn-sm btn-primary'.(!empty($ordini) ? '' : ' disabled').'" data-href="'.$rootdir.'/modules/ddt/add_ordine.php?id_module='.$id_module.'&id_record='.$id_record.'" data-toggle="modal" data-title="Aggiungi ordine">
                <i class="fa fa-plus"></i> '.tr('Ordine').'
            </a>';

    echo '
            <a class="btn btn-sm btn-primary" data-href="'.$structure->fileurl('row-add.php').'?id_module='.$id_module.'&id_record='.$id_record.'&is_articolo" data-toggle="tooltip" data-title="'.tr('Aggiungi articolo').'">
                <i class="fa fa-plus"></i> '.tr('Articolo').'
            </a>';

    echo '
            <a class="btn btn-sm btn-primary" data-href="'.$structure->fileurl('row-add.php').'?id_module='.$id_module.'&id_record='.$id_record.'&is_riga" data-toggle="tooltip" data-title="'.tr('Aggiungi riga').'">
                <i class="fa fa-plus"></i> '.tr('Riga').'
            </a>';

    echo '
            <a class="btn btn-sm btn-primary" data-href="'.$structure->fileurl('row-add.php').'?id_module='.$id_module.'&id_record='.$id_record.'&is_descrizione" data-toggle="tooltip" data-title="'.tr('Aggiungi descrizione').'">
                <i class="fa fa-plus"></i> '.tr('Descrizione').'
            </a>';

    echo '
            <a class="btn btn-sm btn-primary" data-href="'.$structure->fileurl('row-add.php').'?id_module='.$id_module.'&id_record='.$id_record.'&is_sconto" data-toggle="tooltip" data-title="'.tr('Aggiungi sconto/maggiorazione').'">
                <i class="fa fa-plus"></i> '.tr('Sconto/maggiorazione').'
            </a>';
}
?>
		</div>
		<div class="clearfix"></div>
		<br>

		<div class="row">
			<div class="col-md-12">

<?php
include $docroot.'/modules/ddt/row-list.php';
?>
			</div>
		</div>
	</div>
</div>

{( "name": "filelist_and_upload", "id_module": "$id_module$", "id_record": "$id_record$" )}

{( "name": "log_email", "id_module": "$id_module$", "id_record": "$id_record$" )}

<script>
    var id = "<?php echo $dir == 'entrata' ? 'idsede_destinazione' : 'idsede_partenza'; ?>";

    $("#idanagrafica").change(function(){
        $("#" + id).selectInfo("idanagrafica", $(this).val()).selectReset();
	});
</script>

<?php
// Collegamenti diretti
// Fatture collegate a questo ddt
$elementi = $dbo->fetchArray('SELECT `co_documenti`.*, `co_tipidocumento`.`descrizione` AS tipo_documento, `co_tipidocumento`.`dir` FROM `co_documenti` JOIN `co_tipidocumento` ON `co_tipidocumento`.`id` = `co_documenti`.`id_tipo_documento` WHERE `co_documenti`.`id` IN (SELECT `iddocumento` FROM `co_righe_documenti` WHERE `idddt` = '.prepare($id_record).') ORDER BY `data`');

if (!empty($elementi)) {
    echo '
<div class="card card-outline card-warning collapsable collapsed-box">
    <div class="card-header with-border">
        <h3 class="card-title"><i class="fa fa-warning"></i> '.tr('Documenti collegati: _NUM_', [
            '_NUM_' => count($elementi),
        ]).'</h3>
        <div class="card-tools float-right">
            <button type="button" class="btn btn-card-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <ul>';

    foreach ($elementi as $fattura) {
        $descrizione = tr('_DOC_ num. _NUM_ del _DATE_', [
            '_DOC_' => $fattura['tipo_documento'],
            '_NUM_' => !empty($fattura['numero_esterno']) ? $fattura['numero_esterno'] : $fattura['numero'],
            '_DATE_' => Translator::dateToLocale($fattura['data']),
        ]);

        $modulo = ($fattura['dir'] == 'entrata') ? 'Fatture di vendita' : 'Fatture di acquisto';
        $id = $fattura['id'];

        echo '
            <li>'.Modules::link($modulo, $id, $descrizione).'</li>';
    }

    echo '
        </ul>
    </div>
</div>';
}

if (!empty($elementi)) {
    echo '
<div class="alert alert-error">
    '.tr('Eliminando questo documento si potrebbero verificare problemi nelle altre sezioni del gestionale').'.
</div>';
}

?>

<?php
// Eliminazione ddt solo se ho accesso alla sede aziendale
$field_name = ($dir == 'entrata') ? 'idsede_partenza' : 'idsede_destinazione';
if (in_array($record[$field_name], $user->sedi)) {
    ?>
    <a class="btn btn-danger ask" data-backto="record-list">
        <i class="fa fa-trash"></i> <?php echo tr('Elimina'); ?>
    </a>
<?php
}
?>

<script>
<?php
if ($record['flag_completato']) {
    ?>
    $('#tipo_sconto_generico').prop('disabled', true);
<?php
}
?>
</script>
