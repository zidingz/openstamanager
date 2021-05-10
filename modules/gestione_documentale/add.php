<?php
/*
 * OpenSTAManager: il software gestionale open source per l'assistenza tecnica e la fatturazione
 * Copyright (C) DevCode s.r.l.
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

include_once __DIR__.'/../../core.php';

?><form action="" method="post" id="add-form">
	<input type="hidden" name="op" value="add">
	<input type="hidden" name="backto" value="record-edit">

	<div class='row'>
		<div class="col-md-12">
			{[ "type": "text", "label": "Nome", "name": "nome", "required": 1, "value": "", "extra": "" ]}
		</div>
	</div>
	<div class='row'>
		<div class="col-md-6">
			{[ "type": "select", "label": "Categoria", "name": "idcategoria", "required": 1, "ajax-source": "categorie_documenti" , "value": "", "extra": "", "icon-after": "add|<?php echo module('Categorie documenti')['id']; ?>"  ]}
		</div>

		<div class="col-md-6">
			{[ "type": "text", "label": "Data", "name": "data", "class": "datepicker text-center", "value": "", "extra": "" ]}
		</div>
	</div>

    <!-- PULSANTI -->
	<div class="row">
		<div class="col-md-12 text-right">
			<button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> <?php echo tr('Aggiungi'); ?></button>
		</div>
	</div>
</form>
