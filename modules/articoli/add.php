<?php

unset($_SESSION['superselect']['id_categoria']);

?><form action="<?php urlFor('module-add-save', [
        'module_id' => $module_id,
        'reference_id' => $reference_id,
    ]); ?>" method="post" id="add-form">
	<input type="hidden" name="op" value="add">
	<input type="hidden" name="backto" value="record-edit">

	<div class="row">
		<div class="col-md-6">
			{[ "type": "text", "label": "<?php echo tr('Inserisci il codice:'); ?>", "name": "codice", "required": 0, "value": "<?php echo htmlentities(filter('codice')) ?: ''; ?>", "help": "<?php echo tr('Se non specificato, il codice verrà calcolato automaticamente'); ?>" ]}
		</div>

		<div class="col-md-6">
			{[ "type": "text", "label": "<?php echo tr('Inserisci la descrizione:'); ?>", "name": "descrizione", "required": 1, "value": "<?php echo htmlentities(filter('descrizione')) ?: ''; ?>" ]}
		</div>

		<div class="col-md-6">
			{[ "type": "select", "label": "<?php echo tr('Inserisci la categoria:'); ?>", "name": "categoria", "required": 1, "ajax-source": "categorie", "icon-after": "add|<?php echo \Modules\Module::get('Categorie articoli')['id']; ?>" ]}
		</div>

		<div class="col-md-6">
			{[ "type": "select", "label": "<?php echo tr('Inserisci la sottocategoria:'); ?>", "name": "subcategoria", "id": "subcategoria_add", "ajax-source": "sottocategorie", "icon-after": "add|<?php echo \Modules\Module::get('Categorie articoli')['id']; ?>||hide" ]}
		</div>
	</div>

	<!-- PULSANTI -->
	<div class="row">
		<div class="col-md-12 text-right">
			<button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo tr('Aggiungi'); ?></button>
		</div>
	</div>
</form>

<script>
$(document).ready(function () {
    var sub = $('#add-form').find('#subcategoria_add');
    var original = sub.parent().find(".input-group-prepend button").data("href");

    $('#add-form').find('#categoria').change( function(){
        sub.selectInfo('id_categoria', $(this).val())

        sub.selectReset();

        if($(this).val()){
            sub.parent().find(".input-group-prepend button").removeClass("d-none");
            sub.parent().find(".input-group-prepend button").data("href", original + "&id_original="+$(this).val());
        }
        else {
            sub.parent().find(".input-group-prepend button").addClass("d-none");
        }
    });
});
</script>
