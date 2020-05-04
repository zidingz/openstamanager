import $ from 'jquery';
import { cleanup_inputs, restart_inputs } from "../functions/input";
import { renderMessages, submitAjax } from "../functions/functions";

function importFields(){
    // Gestione caricamento campi aggiuntivi
    cleanup_inputs();

    var top_fields = $("#custom_fields_top-add");
    var form = top_fields.parent().find("form").first();

    // Campi a inizio form
    form.prepend(top_fields.html());

    // Campi a fine form
    var last = form.find(".panel").last();

    if (!last.length) {
        last = form.find(".box").last();
    }

    if (!last.length) {
        last = form.find(".row").eq(-2);
    }

    last.after($("#custom_fields_bottom-add").html());
    restart_inputs();
}

function ajaxManager(info) {
    // Gestione invio dei dati via AJAX
    var handle_ajax = info && info.handle_ajax;
    if (handle_ajax) {
        var data = info.data;
        var form_id = info.form_id;
        var after_submit = info.after_submit;

        var form_element = $(form_id).find("form")[0];
        var submit_form = $(form_element);
        submit_form.on('submit', function(e){
            e.preventDefault();

            submitAjax(this, data, function (response) {
                after_submit(response);

                closeModal(submit_form);
            });

            return false;
        });
    }
}

function initAdd(info) {
    importFields();

    ajaxManager(info);
}

window.initAdd = initAdd;
