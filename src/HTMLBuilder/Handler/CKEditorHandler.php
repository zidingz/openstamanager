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
        $id = prepareToField($values['id']);

        // Generazione del codice HTML
        return '
    <textarea |attr|>|value|</textarea>
    <script src="'.ROOTDIR.'/assets/js/ckeditor/ckeditor.js"></script>
    <script>
        var editors = editors ? editors : {};
        
        ClassicEditor
            .create(document.querySelector("#'.$id.'"), {
                toolbar: globals.ckeditorToolbar,
                defaultLanguage: globals.locale,
            })
            .then(editor => {
                editors["'.$id.'"] = editor;
            })
            .catch(error => {
                console.error( error );
            });
    </script>';
    }
}
