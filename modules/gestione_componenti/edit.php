<?php

if (empty($id_record)) {
    echo '
    <table width="100%" class="datatables table table-striped table-hover table-condensed table-bordered">
        <thead>
            <tr>
                <th width="10%">'.tr('Numero').'</th>
                <th>'.tr('Nome del file').'</th>
            </tr>
        </thead>
        <tbody>';

    for ($c = 1; $c <= count($cmp); ++$c) {
        echo '
            <tr class="clickable" onclick="openLink(event, \''.ROOTDIR.'/editor.php?id_module='.$id_module.'&id_record='.$c.'\')">
                <td>'.$c.'</td>
                <td>'.$cmp[$c - 1][0].'</td>
			</tr>';
    }
    echo '
	    </tbody>
	</table>';
} else {
    ?>
    <form action="" method="post" id="edit-form" enctype="multipart/form-data">
        <input type="hidden" name="backto" value="record-edit">
        <input type="hidden" name="op" value="update">

        <!-- DATI ANAGRAFICI -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title"><?php echo tr('Componente'); ?></h3>
            </div>

            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        {[ "type": "text", "label": "<?php echo tr('Nome file'); ?>", "name": "nomefile", "required": 1, "value": "$nomefile$", "readonly": 1 ]}
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-12">
                        {[ "type": "textarea", "label": "<?php echo tr('Contenuto'); ?>", "name": "contenuto", "required": 1, "class": "autosize", "value": "$contenuto$" ]}
                    </div>
                </div>

            </div>
        </div>
    </form>

    <a href="#" class="btn btn-danger ask" data-backto="record-list" data-nomefile="<?php echo $record['nomefile']; ?>">
        <i class="fa fa-trash"></i> <?php echo tr('Elimina'); ?>
    </a>

<?php
}
