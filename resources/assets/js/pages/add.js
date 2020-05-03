import { cleanup_inputs, restart_inputs } from "../functions/input";

$(document).ready(function() {
    cleanup_inputs();

    var form = $("#custom_fields_top-add").parent().find("form").first();

    // Campi a inizio form
    form.prepend($("#custom_fields_top-add").html());

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
});
