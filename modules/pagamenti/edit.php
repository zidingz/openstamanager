<?php

?><form action="" method="post" id="edit-form">
	<input type="hidden" name="backto" value="record-edit">
	<input type="hidden" name="op" value="update">

	<!-- DATI -->
	<div class="card card-primary">
		<div class="card-header">
			<h3 class="card-title"><?php echo tr('Dati'); ?></h3>
		</div>

		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					{[ "type": "text", "label": "<?php echo tr('Descrizione'); ?>", "name": "descrizione", "value": "$descrizione$", "required": 1 ]}
                </div>

                <div class="col-md-4">
					{[ "type": "select", "label": "<?php echo tr('Codice Modalità (Fatturazione Elettronica)'); ?>", "name": "codice_modalita_pagamento_fe", "value": "$codice_modalita_pagamento_fe$", "values": "query=SELECT codice as id, CONCAT(codice, ' - ', descrizione) AS descrizione FROM fe_modalita_pagamento", "required": 1 ]}
				</div>

				<div class="col-md-2">
					{[ "type": "checkbox", "label": "<?php echo tr('Pagamento di tipo Ri.Ba.'); ?>", "name": "riba", "value": "$riba$", "help": "<?php echo tr('Abilitando questa impostazione, nelle fatture verrà visualizzata la banca del cliente'); ?>" ]}
				</div>
            </div>

			<div class="row">
                <div class="col-md-6">
					{[ "type": "select", "label": "<?php echo tr('Conto predefinito per le vendite'); ?>", "name": "idconto_vendite", "value": "$idconto_vendite$", "ajax-source": "conti"  ]}
                </div>

                <div class="col-md-6">
					{[ "type": "select", "label": "<?php echo tr('Conto predefinito per gli acquisti'); ?>", "name": "idconto_acquisti", "value": "$idconto_acquisti$", "ajax-source": "conti" ]}
				</div>
			</div>
		</div>
	</div>

	<div class="card card-primary">
		<div class="card-header">
			<h3 class="card-title"><?php echo tr('Rate'); ?></h3>
		</div>

		<div class="card-body">
			<div class="data">
<?php
$values = '';
for ($i = 1; $i <= 31; ++$i) {
    $values .= '\"'.$i.'\": \"'.$i.'\"';
    if ($i != 31) {
        $values .= ',';
    }
}

$results = $dbo->fetchArray('SELECT * FROM `co_pagamenti` WHERE descrizione='.prepare($record['descrizione']).' ORDER BY `num_giorni` ASC');
$cont = 1;
foreach ($results as $result) {
    echo '
				<div class="card card-outline card-success">
					<div class="card-header">
						<h3 class="card-title">'.tr('Rata _NUMBER_', [
                            '_NUMBER_' => $cont,
                        ]).'</h3>
						<a class="btn btn-danger float-right" onclick="';
    echo "if(confirm('".tr('Eliminare questo elemento?')."')){ location.href='".ROOTDIR.'/editor.php?id_module='.$id_module.'&id_record='.$id_record.'&op=delete_rata&id='.$result['id']."'; }";
    echo '"><i class="fa fa-trash"></i> '.tr('Elimina').'</a>
					</div>
					<div class="card-body">
						<input type="hidden" value="'.$result['id'].'" name="id[]">

						<div class="row">
							<div class="col-md-6">
								{[ "type": "number", "label": "'.tr('Percentuale').'", "name": "percentuale[]", "value": "'.$result['prc'].'", "icon-after": "<i class=\"fa fa-percent\"></i>" ]}
							</div>

							<div class="col-md-6">
								{[ "type": "select", "label": "'.tr('Scadenza').'", "name": "scadenza[]", "values": "list=\"1\":\"'.tr('Data fatturazione').'\",\"2\":\"'.tr('Data fatturazione fine mese').'\",\"3\":\"'.tr('Data fatturazione giorno fisso').'\",\"4\":\"'.tr('Data fatturazione fine mese (giorno fisso)').'\"", "value": "';

    if ($result['giorno'] == 0) {
        $select = 1;
    } elseif ($result['giorno'] == -1) {
        $select = 2;
    } elseif ($result['giorno'] < -1) {
        $select = 4;
    } elseif ($result['giorno'] > 0) {
        $select = 3;
    }
    echo $select;
    echo '" ]}
							</div>
                        </div>

                        <div class="row">
							<div class="col-md-6">
								{[ "type": "select", "label": "'.tr('Giorno').'", "name": "giorno[]", "values": "list='.$values.'", "value": "';
    if ($result['giorno'] != 0 && $result['giorno'] != -1) {
        echo ($result['giorno'] < -1) ? -$result['giorno'] - 1 : $result['giorno'];
    }
    echo '", "extra": "';
    if ($result['giorno'] == 0 || $result['giorno'] == -1) {
        echo ' disabled';
    }
    echo '" ]}
							</div>

							<div class="col-md-6">
								{[ "type": "number", "label": "'.tr('Distanza in giorni').'", "name": "distanza[]", "decimals": "0", "value": "'.$result['num_giorni'].'" ]}
							</div>
						</div>
					</div>
				</div>';
    ++$cont;
}
?>
			</div>
			<div class="float-right">
				<button type="button" class="btn btn-info" id="add"><i class="fa fa-plus"></i> <?php echo tr('Aggiungi'); ?></button>
				<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> <?php echo tr('Salva'); ?></button>
			</div>
		</div>
	</div>

</form>

<div class="card card-outline card-warning card-solid text-center d-none" id="wait">
	<div class="card-header">
		<h3 class="card-title"><i class="fa fa-warning"></i> <?php echo tr('Attenzione!'); ?></h3>
	</div>
	<div class="card-body">
		<p><?php echo tr('Prima di poter continuare con il salvataggio è necessario che i valori percentuali raggiungano in totale il 100%'); ?>.</p>
	</div>
</div>

<a href="#" class="btn btn-danger ask" data-backto="record-list">
    <i class="fa fa-trash"></i> <?php echo tr('Elimina'); ?>
</a>
<?php
echo '
<form class="d-none" id="template">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">'.tr('Nuova rata').'</h3>
        </div>
        <div class="card-body">
            <input type="hidden" value="" name="id[]">

            <div class="row">
                <div class="col-md-6">
                    {[ "type": "number", "label": "'.tr('Percentuale').'", "name": "percentuale[]", "icon-after": "<i class=\"fa fa-percent\"></i>" ]}
                </div>

                <div class="col-md-6">
                    {[ "type": "select", "label": "'.tr('Scadenza').'", "name": "scadenza[]", "values": "list=\"1\":\"'.tr('Data fatturazione').'\",\"2\":\"'.tr('Data fatturazione fine mese').'\",\"3\":\"'.tr('Data fatturazione giorno fisso').'\",\"4\":\"'.tr('Data fatturazione fine mese (giorno fisso)').'\"", "value": 1 ]}
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    {[ "type": "select", "label": "'.tr('Giorno').'", "name": "giorno[]", "values": "list='.$values.'" ]}
                </div>

                <div class="col-md-6">
                    {[ "type": "number", "label": "'.tr('Distanza in giorni').'", "name": "distanza[]", "decimals": "0" ]}
                </div>
            </div>
        </div>
    </div>
</form>';

?>

<script>
$(document).ready(function(){
	$(document).on('click', '#add', function(){
        cleanup_inputs();

	    $(this).parent().parent().find('.data').append($('#template').html());

        restart_inputs();
	});

	$(document).on('change', '[id*=scadenza]', function(){
        if($(this).val() == 1 || $(this).val() == 2){
            $(this).parentsUntil('.box').find('[id*=giorno]').prop('disabled', true);
        }else{
            $(this).parentsUntil('.box').find('[id*=giorno]').prop('disabled', false);
        }
    });

	$(document).on('change', '[id*=percentuale]', function(){
		$('button[type=submit]').prop( 'disabled', false ).removeClass('disabled');
	});

	$('#edit-form').submit( function(event) {
		var tot = 0;

		$(this).find('[id*=percentuale]').each(function(){
            prc = $(this).val().toEnglish();
            prc = !isNaN(prc) ? prc : 0;

			tot += prc;
		});

		if( tot != 100) {
			$('#wait').removeClass("d-none");
			event.preventDefault();
		} else {
			$('#wait').addClass("d-none");
			$(this).unbind('submit').submit();
		}
	});
});
</script>
