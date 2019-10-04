<?php

?><form action="" method="post" id="edit-form">
	<input type="hidden" name="backto" value="record-edit">
	<input type="hidden" name="op" value="update">

	<!-- DATI ARTICOLO -->
	<div class="card card-primary">
		<div class="card-header">
			<h3 class="card-title"><?php echo tr('Listino'); ?></h3>
		</div>

		<div class="card-body">
			<div class="row">
				<div class="col-md-6">
					{[ "type": "text", "label": "<?php echo tr('Nome'); ?>", "name": "nome", "required": 1, "value": "$nome$" ]}
				</div>

				<div class="col-md-6">
					{[ "type": "number", "label": "<?php echo tr('Rincaro/sconto'); ?>", "name": "prc_guadagno", "required": 1, "value": "$prc_guadagno$", "icon-after": "%", "help": "<?php echo tr('Il valore positivo indica uno sconto').'. '.tr('Per applicare una percentuale di rincaro inserire un valore negativo').'.'; ?>" ]}
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					{[ "type": "textarea", "label": "<?php echo tr('Note'); ?>", "name": "note", "value": "$note$" ]}
				</div>
			</div>

		</div>
	</div>

</form>

<a href="#" class="btn btn-danger ask" data-backto="record-list">
    <i class="fa fa-trash"></i> <?php echo tr('Elimina'); ?>
</a>
