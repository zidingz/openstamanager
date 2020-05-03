import $ from 'jquery';
import 'parsleyjs';
import 'jquery-form';
import Swal from 'sweetalert2';

import '../functions/select';
import { getUrlVars, redirect, buttonLoading, buttonRestore, ajaxError } from "../functions/functions";

var flag_link = "https://lipis.github.io/flag-icon-css/flags/4x3/|flag|.svg";

$(document).ready(function() {
    $("#install").on("click", function(){
        if($(this).closest("form").parsley().validate()){
            var restore = buttonLoading("#install");
            $("#config-form").submit();

            //buttonRestore("#install", restore);
        }
    });

    $("#test").on("click", function(){
        if($(this).closest("form").parsley().validate()){
            var restore = buttonLoading("#test");
            $("#install").prop('disabled', true);

            $(this).closest("form").ajaxSubmit({
                url: globals.configuration.test_url,
                data: {
                    test: 1,
                },
                type: "post",
                success: function(data){
                    data = parseFloat(data.trim());

                    buttonRestore("#test", restore);
                    $("#install").prop('disabled', false);

                    if(data == 0){
                        Swal.fire(globals.configuration.translations.error, globals.configuration.translations.errorMessage, "error");
                    } else if(data == 1){
                        Swal.fire(globals.configuration.translations.permissions, globals.configuration.translations.permissionsMessage, "error");
                    } else {
                        Swal.fire(globals.configuration.translations.success, globals.configuration.translations.successMessage, "success");
                    }
                },
                error: function(xhr, error, thrown) {
                    ajaxError(xhr, error, thrown);
                }
            });
        }
    });

    $.ajax({
        url: flag_link.replace("|flag|", "it"),
        success: function(){
            initLanguage(true);
        },
        error: function(){
            initLanguage(false);
        },
        timeout: 500
    });
});

function languageFlag(item) {
    if (!item.id) {
        return item.text;
    }

    var element = $(item.element);
    var img = $("<img>", {
        class: "img-flag",
        width: 26,
        src: flag_link.replace("|flag|", element.data("country").toLowerCase()),
    });

    var span = $("<span>", {
        text: " " + item.text
    });
    span.prepend(img);

    return span;
}

function initLanguage() {
    $("#language").removeClass("d-none");
    $("#language-info").addClass("d-none");

    $("#language").select2({
        theme: "bootstrap",
        templateResult: languageFlag,
        templateSelection:languageFlag,
    });

    // Preselezione lingua
    if (globals.full_locale) {
        $("#language").selectSet(globals.full_locale);
    }

    $("#language").on("change", function(){
        if ($(this).val()) {
            var location = window.location;
            var url = location.protocol + "//" + location.host + "" + location.pathname;

            var parameters = getUrlVars();
            parameters.lang = $(this).val();

            redirect(url, parameters);
        }
    });
}
