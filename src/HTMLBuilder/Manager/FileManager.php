<?php

namespace HTMLBuilder\Manager;

use Models\Upload;

/**
 * Gestione allegati.
 *
 * @since 2.3
 */
class FileManager implements ManagerInterface
{
    /**
     * Gestione "filelist_and_upload".
     * Esempio: {( "name": "filelist_and_upload", "id_module": "2", "id_record": "1", "readonly": "false" )}.
     *
     * @param array $options
     *
     * @return string
     */
    public function manage($options)
    {
        $options['readonly'] = !empty($options['readonly']) ? true : false;
        $options['showpanel'] = isset($options['showpanel']) ? $options['showpanel'] : true;
        $options['label'] = isset($options['label']) ? $options['label'] : tr('Nuovo allegato').':';

        // ID del form
        $attachment_id = 'attachments_'.$options['id_module'];

        $dbo = database();

        // Codice HTML
        $result = '
<div id="'.$attachment_id.'" >';

        if (!empty($options['showpanel'])) {
            $result .= '
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">'.tr('Allegati').'</h3>
        </div>
        <div class="card-body">
            <div id="loading_'.$attachment_id.'" class="text-center d-none" style="position:relative;top:100px;z-index:2;opacity:0.5;"  ><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i><span class="sr-only">'.tr('Caricamento...').'</span></div>';
        }

        $count = 0;

        $where = '`id_module` = '.prepare($options['id_module']);

        // Categorie
        $categories = $dbo->fetchArray('SELECT DISTINCT(BINARY `category`) AS `category` FROM `zz_files` WHERE '.$where.' ORDER BY `category`');
        foreach ($categories as $category) {
            $category = $category['category'];

            $rs = $dbo->fetchArray('SELECT * FROM `zz_files` WHERE BINARY `category`'.(!empty($category) ? '= '.prepare($category) : 'IS NULL').' AND `id_record` = '.prepare($options['id_record']).' AND '.$where);

            if (!empty($rs)) {
                $result .= '
<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title">'.(!empty($category) ? $category : tr('Generale')).'</h3>

        {[ "type": "text", "class": "hide category-name", "value": "'.$category.'" ]}

        <div class="card-tools float-right">';

                if (!empty($category)) {
                    $result .= '
            <button type="button" class="btn btn-box-tool category-save d-none">
                <i class="fa fa-check"></i>
            </button>

            <button type="button" class="btn btn-box-tool category-edit">
                <i class="fa fa-edit"></i>
            </button>';
                }

                $result .= '
            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                <i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body no-padding table-responsive">
    <table class="table table-striped table-condensed ">
	  <thead>
        <tr>
            <th scope="col" >'.tr('Nome').'</th>
            <th scope="col" width="15%" >'.tr('Data').'</th>
            <th scope="col" width="10%" class="text-center">#</th>
        </tr>
	  </thead>
	  <tbody>';

                foreach ($rs as $r) {
                    $file = Upload::find($r['id']);

                    $result .= '
        <tr>
            <td align="left">';

                    if ($file->user && $file->user->photo) {
                        $result .= '
                <img class="attachment-img tip" src="'.$file->user->photo.'" title="'.$file->user->nome_completo.'">';
                    } else {
                        $result .= '

                <i class="fa fa-user-circle-o attachment-img tip" title="'.tr('OpenSTAManager').'"></i>';
                    }

                    $result .= '

                <a href="'.ROOTDIR.'/view.php?file_id='.$r['id'].'" target="_blank">
                    <i class="fa fa-external-link"></i> '.$r['name'].'
                </a>

                <small> ('.$file->extension.')'.((!empty($file->size)) ? ' ('.\Util\FileSystem::formatBytes($file->size).')' : '').'</small>'.'
            </td>

            <td>'.\Translator::timestampToLocale($r['created_at']).'</td>

            <td class="text-center">
                <a class="btn btn-sm btn-primary" href="'.ROOTDIR.'/actions.php?id_module='.$options['id_module'].'&op=download_file&id='.$r['id'].'&filename='.$r['filename'].'" target="_blank">
                    <i class="fa fa-download"></i>
                </a>';

                    // Anteprime supportate dal browser
                    if ($file->hasPreview()) {
                        $result .= '
                <button class="btn btn-sm btn-info" data-target="#bs-popup2" type="button" data-title="'.prepareToField($r['name']).' <small style=\'color:white\'><i>('.$r['filename'].')</i></small>" data-href="'.ROOTDIR.'/view.php?file_id='.$r['id'].'">
                    <i class="fa fa-eye"></i>
                </button>';
                    } else {
                        $result .= '
                <button class="btn btn-sm btn-secondary disabled" title="'.tr('Anteprima file non disponibile').'" disabled>
                    <i class="fa fa-eye"></i>
                </button>';
                    }

                    if (!$options['readonly']) {
                        $result .= '
                <a class="btn btn-sm btn-danger ask" data-backto="record-edit" data-msg="'.tr('Vuoi eliminare questo file?').'" data-op="unlink_file" data-filename="'.$r['filename'].'" data-id_record="'.$r['id_record'].'" data-before="show_'.$attachment_id.'" data-callback="reload_'.$attachment_id.'">
                    <i class="fa fa-trash"></i>
                </a>';
                    }

                    $result .= '
            </td>
        </tr>';

                    ++$count;
                }

                $result .= '
      </tbody>
	</table>
    </div>
</div>

        <div class="clearfix"></div>
        <br>';
            }
        }

        // Form per l'upload di un nuovo file
        if (!$options['readonly']) {
            $result .= '
    <b>'.$options['label'].'</b>
    <div id="upload-form" class="row">
        <div class="col-md-4">
            {[ "type": "text", "placeholder": "'.tr('Nome').'", "name": "nome_allegato", "class": "unblockable" ]}
        </div>

        <div class="col-md-3">
            {[ "type": "text", "placeholder": "'.tr('Categoria').'", "name": "categoria", "class": "unblockable" ]}
        </div>

        <div class="col-md-3">
            {[ "type": "file", "placeholder": "'.tr('File').'", "name": "blob", "class": "unblockable" ]}
        </div>

		<div class="col-md-2 text-right">
			<button type="button" class="btn btn-success" id="upload">
				<i class="fa fa-upload"></i> '.tr('Carica').'
			</button>
		</div>
    </div>';
        }
        // In caso di readonly, se non è stato caricato nessun allegato mostro almeno box informativo
        elseif ($count == 0) {
            $result .= '
        <div class="alert alert-info" style="margin-bottom:0px;" >
            <i class="fa fa-info-circle"></i>
            '.tr('Nessun allegato è stato caricato').'.
        </div>';
        }

        if (!empty($options['showpanel'])) {
            $result .= '
    </div>
</div>
</div>';
        }

        $source = array_clean(array_column($categories, 'category'));

        $result .= '
<script>$(document).ready(init)</script>

<script>
$(document).ready(function() {
    // Modifica categoria
    $("#'.$attachment_id.' .category-edit").click(function() {
        var nome = $(this).parent().parent().find(".card-title");
        var save_button = $(this).parent().find(".category-save");
        var input = $(this).parent().parent().find(".category-name");

        nome.hide();
        $(this).hide();

        input.removeClass("d-none");
        save_button.removeClass("d-none");
    });

    $("#'.$attachment_id.' .category-save").click(function() {
        var nome = $(this).parent().parent().find(".card-title");
        var input = $(this).parent().parent().find(".category-name");

        show_'.$attachment_id.'();

        $.ajax({
            url: globals.rootdir + "/actions.php",
            cache: false,
            type: "POST",
            data: {
                id_module: "'.$options['id_module'].'",
                id_record: "'.$options['id_record'].'",
                op: "upload_category",
                category: nome.text(),
                name: input.val(),
            },
            success: function(data) {
                reload_'.$attachment_id.'();
            },
            error: function(data) {
                reload_'.$attachment_id.'();
            }
        });
    });

    // Autocompletamento nome
    $("#'.$attachment_id.' #blob").change(function(){
        var nome = $("#'.$attachment_id.' #nome_allegato");

        if (!nome.val()) {
            var fullPath = $(this).val();

            var startIndex = Math.max(fullPath.lastIndexOf("\\\\"), fullPath.lastIndexOf("/")) + 1;
            var filename = fullPath.substring(startIndex);

            nome.val(filename);
        }
    });

    // Autocompletamento categoria
    $("#'.$attachment_id.' #categoria").autocomplete({
        source: '.json_encode($source).',
        minLength: 0
    }).focus(function() {
        $(this).autocomplete("search", $(this).val())
    });

    var data = {
        op: "link_file",
        id_module: "'.$options['id_module'].'",
        id_record: "'.$options['id_record'].'",
    };

    // Upload
    $("#'.$attachment_id.' #upload").click(function(){
        $form = $("#'.$attachment_id.' #upload-form");

        if($form.find("input[name=nome_allegato]").val() == "" || $form.find("input[name=blob]").val() == "") {
            swal({
                type: "error",
                title: "'.tr('Errore').'",
                text:  "'.tr('Alcuni campi obbligatori non sono stati compilati correttamente.').'",
            });

            return;
        }

        $form.ajaxSubmit({
            url: globals.rootdir + "/actions.php",
            data: data,
            type: "post",
            uploadProgress: function(event, position, total, percentComplete) {
                $("#'.$attachment_id.' #upload").prop("disabled", true).html(percentComplete + "%").removeClass("btn-success").addClass("btn-info");
            },
            success: function(data){
                reload_'.$attachment_id.'();
            },
            error: function(xhr, error, thrown) {
                ajaxError(xhr, error, thrown);
            }
        });
    });
});

function show_'.$attachment_id.'() {
    $("#loading_'.$attachment_id.'").removeClass("d-none");
}

function reload_'.$attachment_id.'() {
    $("#'.$attachment_id.'").load(globals.rootdir + "/ajax.php?op=list_attachments&id_module='.$options['id_module'].'&id_record='.$options['id_record'].'", function() {
        $("#loading_'.$attachment_id.'").addClass("d-none");
    });
}
</script>';

        return $result;
    }
}
