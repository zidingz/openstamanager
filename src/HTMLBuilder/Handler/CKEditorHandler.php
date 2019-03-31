<?php

namespace HTMLBuilder\Handler;

/**
 *  Gestione dell'input di tipo "ckeditor".
 *
 * @since 2.4.2
 */
class CKEditorHandler implements HandlerInterface
{
    public function handle(&$values, &$extras)
    {
        // Generazione del codice HTML
        return '
    <textarea |attr|>|value|</textarea>
    <script src="'.ROOTDIR.'/assets/js/ckeditor/ckeditor.js"></script>
    <script>
        ClassicEditor
        .create(document.querySelector("#'.prepareToField($values['id']).'"), {
            toolbar: globals.ckeditorToolbar,
            defaultLanguage: globals.locale,
        })
        .then(editor => {
            console.log( editor );
        })
        .catch(error => {
            console.error( error );
        });
    </script>';
    }
}
