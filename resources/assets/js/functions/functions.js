import $ from 'jquery';
import 'parsleyjs';
import 'jquery-form';
import 'toastr';
import Swal from 'sweetalert2';
import moment from 'moment';
import { dateFormatMoment } from './dates.js';

// Modal
export function launch_modal(title, href, init_modal, id) {
    openModal(title, href, id ? id : '#bs-popup');
}

// Modal
export function openModal(title, href, generate_id) {
    // Fix - Select2 does not function properly when I use it inside a Bootstrap modal.
    $.fn.modal.Constructor.prototype.enforceFocus = function () {
    };

    // Generazione dinamica modal
    var id;
    if (generate_id == undefined) {
        do {
            id = '#bs-popup-' + Math.floor(Math.random() * 100);
        } while ($(id).length != 0);
    } else {
        id = generate_id;
    }

    if ($(id).length == 0) {
        $('#modals').append('<div class="modal fade" id="' + id.replace("#", "") + '" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="true"></div>');
    }

    $(id).on('hidden.bs.modal', function () {
        if ($('.modal-backdrop').length < 1) {
            $(this).html('');
            $(this).data('modal', null);
        }
    });

    var content = '<div class="modal-dialog modal-xl">\
    <div class="modal-content">\
        <div class="modal-header bg-light-blue">\
            <h4 class="modal-title">\
                <i class="fa fa-pencil"></i> ' + title + '\
            </h4>\
            <button type="button" class="close" data-dismiss="modal">\
                <span aria-hidden="true">&times;</span><span class="sr-only">' + globals.translations.close + '</span>\
            </button>\
        </div>\
        <div class="modal-body">|data|</div>\
    </div>\
</div>';

    // Lettura contenuto div
    if (href.substr(0, 1) == '#') {
        var data = $(href).html();

        $(id).html(content.replace("|data|", data));
        $(id).modal('show');
    } else {
        var url = href + (href.indexOf('?') !== -1 ? '&' : '?') + 'modal=1';

        $.get(url, function (data, response) {
            if (response == 'success') {
                $(id).html(content.replace("|data|", data));
                $(id).modal('show');
            }
        });
    }
}

export function openLink(event, link) {
    if (event.ctrlKey) {
        window.open(link);
    } else {
        location.href = link;
    }
}

/**
 * Funzione per far scrollare la pagina fino a un offset
 * @param integer offset
 */
export function scrollToOffset(offset) {
    $('html,body').animate({
        scrollTop: offset
    }, 'slow');
}

/**
 * Ritorna un array associativo con i parametri passati via GET
 */
export function getUrlVars() {
    var pairs = window.location.search.slice(1).split('&');

    var result = {};
    pairs.forEach(function (pair) {
        pair = pair.split('=');
        result[pair[0]] = decodeURIComponent(pair[1] || '');
    });

    return JSON.parse(JSON.stringify(result));
}

// Data e ora (orologio)
export function clock() {
    $('#datetime').html(moment().format(dateFormatMoment(globals.timestamp_format)));
    setTimeout(clock, 10 * 1000);
}

/**
 * Funzione per impostare un valore ad un array in $_SESSION
 */
export function session_set_array(session_array, value, inversed) {
    if (inversed === undefined) {
        inversed = 1;
    }

    return $.get(globals.ajax_array_set_url + "?&session=" + session_array + "&value=" + value + "&inversed=" + inversed);
}

/**
 * Funzione per impostare un valore ad una sessione
 */
export function session_set(session_array, value, clear, reload) {
    if (clear == undefined) {
        clear = 1;
    }

    if (reload == undefined) {
        reload = 0;
    }

    return $.get(globals.ajax_set_url + "?session=" + session_array + "&value=" + value + "&clear=" + clear, function (data, status) {
        if (reload == 1)
            location.reload();
    });
}

export function session_keep_alive() {
    $.get(globals.rootdir + '/core.php');
}

export function setContrast(backgroundcolor) {
    var rgb = [];
    var bg = String(backgroundcolor);

    // ex. backgroundcolor = #ffc400
    rgb[0] = bg.substr(1, 2);
    rgb[1] = bg.substr(2, 2);
    rgb[2] = bg.substr(5, 2);

    var R1 = parseInt(rgb[0], 16);
    var G1 = parseInt(rgb[1], 16);
    var B1 = parseInt(rgb[2], 16);

    var R2 = 255;
    var G2 = 255;
    var B2 = 255;

    var L1 = 0.2126 * Math.pow(R1 / 255, 2.2) + 0.7152 * Math.pow(G1 / 255, 2.2) + 0.0722 * Math.pow(B1 / 255, 2.2);
    var L2 = 0.2126 * Math.pow(R2 / 255, 2.2) + 0.7152 * Math.pow(G2 / 255, 2.2) + 0.0722 * Math.pow(B2 / 255, 2.2);

    if (L1 > L2) {
        var lum = (L1 + 0.05) / (L2 + 0.05);
    } else {
        var lum = (L2 + 0.05) / (L1 + 0.05);
    }

    if (lum >= 9) {
        return "#ffffff";
    } else {
        return "#000000";
    }
}

export function message(element) {
    data = $.extend({}, $(element).data());

    var title = globals.translations.deleteTitle;
    if (data["title"] != undefined) title = data["title"];

    var msg = globals.translations.deleteMessage;
    if (data["msg"] != undefined) msg = data["msg"];

    var button = globals.translations.delete;
    if (data["button"] != undefined) button = data["button"];

    var btn_class = "btn btn-lg btn-danger";
    if (data["class"] != undefined) btn_class = data["class"];

    Swal.fire({
        title: title,
        html: '<div id="swal-form" data-parsley-validate>' + msg + '</div>',
        type: "warning",
        showCancelButton: true,
        confirmButtonText: button,
        confirmButtonClass: btn_class,
        onOpen: function () {
            start_superselect();
            start_inputmask();
        },
        preConfirm: function () {
            $form = $('#swal-form');
            $form.find(':input').each(function () {
                data[$(this).attr('name')] = $(this).val();
            });

            if ($form.parsley().validate()) {
                return new Promise(function (resolve) {
                    resolve();
                });
            } else {
                $('.swal2-buttonswrapper button').each(function () {
                    $(this).prop('disabled', false);
                });
            }
        }
    }).then(
        function () {
            if (data["op"] == undefined) data["op"] = "delete";

            var href = window.location.href.split("#")[0];
            if (data["href"] != undefined) {
                href = data["href"];
                delete data.href;
            }

            var hash = window.location.href.split("#")[1];
            if (hash) {
                data["hash"] = hash;
            }

            method = "post";
            if (data["method"] != undefined) {
                if (data["method"] == "post" || data["method"] == "get") {
                    method = data["method"];
                }
                delete data.method;
            }

            blank = data.blank != undefined && data.blank;
            delete data.blank;

            if (data.callback) {
                $.ajax({
                    type: method,
                    crossDomain: true,
                    url: href,
                    data: data,
                    beforeSend: function (response) {
                        var before = window[data.before];

                        if (typeof before === 'function') {
                            before(response);
                        }
                    },
                    success: function (response) {
                        var callback = window[data.callback];

                        if (typeof callback === 'function') {
                            callback(response);
                        }
                    },
                    error: function (xhr, ajaxOptions, error) {
                        Swal.fire({
                            title: globals.translations.errorTitle,
                            html: globals.translations.errorMessage,
                            type: "error",
                        })
                    },
                });
            } else {
                redirect(href, data, method, blank);
            }
        },
        function (dismiss) {
        }
    );
}

export function redirect(href, data, method, blank) {
    method = method ? method : "get";
    blank = blank ? blank : false;

    if (method == "post") {
        var text = '<form action="' + href + window.location.hash + '" method="post"' + (blank ? ' target="_blank"' : '') + '>';

        for (var name in data) {
            text += '<input type="hidden" name="' + name + '" value="' + data[name] + '"/>';
        }

        text += '</form>';

        var form = $(text);
        $('body').append(form);

        form.submit();
    } else {
        var values = [];

        for (var name in data) {
            values.push(name + '=' + data[name]);
        }

        var link = href + (href.indexOf('?') !== -1 ? '&' : '?') + values.join('&') + window.location.hash;

        if (!blank) {
            location.href = link;
        } else {
            window.open(link);
        }
    }
}

export function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

export function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

export function buttonLoading(button) {
    var $this = $(button);

    var result = [
        $this.html(),
        $this.attr("class")
    ];

    $this.html('<i class="fa fa-spinner fa-pulse fa-fw"></i> Attendere...');
    $this.addClass("btn-warning");
    $this.prop("disabled", true);

    return result;
}

export function buttonRestore(button, loadingResult) {
    var $this = $(button);

    $this.html(loadingResult[0]);

    $this.attr("class", "");
    $this.addClass(loadingResult[1]);
    $this.prop("disabled", false);
}

export function submitAjax(form, data, callback, errorCallback) {
    var valid = $(form).parsley().validate();

    if (!data) data = {};

    if (valid) {
        $("#main_loading").show();
        var url = $(form).attr('action') ? $(form).attr('action') : location.href;

        content_was_modified = false;

        // Fix per gli id di default
        data.id_module = data.id_module ? data.id_module : globals.id_module;
        data.id_record = data.id_record ? data.id_record : globals.id_record;
        data.id_plugin = data.id_plugin ? data.id_plugin : globals.id_plugin;
        data.ajax = 1;

        prepareForm(form);

        // Invio dei dati
        $(form).ajaxSubmit({
            url: url,
            data: data,
            type: "post",
            success: function (data) {
                data = data.trim();

                if (data) {
                    response = JSON.parse(data);
                    if (callback) callback(response);
                }

                $("#main_loading").fadeOut();

                renderMessages();
            },
            error: function (data) {
                $("#main_loading").fadeOut();

                toastr["error"](data);

                if (errorCallback) errorCallback(data);
            }
        });
    }

    return valid;
}

export function prepareForm(form) {
    $(form).find('input:disabled, select:disabled').prop('disabled', false);

    var hash = window.location.hash;
    if (hash) {
        var input = $('<input/>', {
            type: 'hidden',
            name: 'hash',
            value: hash,
        });

        $(form).append(input);
    }
}

export function renderMessages() {
    // Visualizzazione messaggi
    $.ajax({
        url: globals.messages_url,
        type: 'get',
        success: function (flash) {
            let messages = JSON.parse(flash);

            let info = messages.info ? messages.info : [];
            info.forEach(function (element) {
                if (element) toastr["success"](element);
            });

            let warning = messages.warning ? messages.warning : [];
            warning.forEach(function (element) {
                if (element) toastr["warning"](element);
            });

            let error = messages.error ? messages.error : [];
            error.forEach(function (element) {
                if (element) toastr["error"](element);
            });

        }
    });
}

export function removeHash() {
    history.replaceState(null, null, ' ');
}

export function replaceAll(str, find, replace) {
    return str.replace(new RegExp(find, "g"), replace);
}

export function alertPush() {
    // Messaggio di avviso salvataggio a comparsa sulla destra solo nella versione a desktop intero
    if ($(window).width() > 1023) {
        var i = 0;

        $('.alert-success.push').each(function () {
            i++;
            tops = 60 * i + 95;

            $(this).css({
                'position': 'fixed',
                'z-index': 3,
                'right': '10px',
                'top': -100,
            }).delay(1000).animate({
                'top': tops,
            }).delay(3000).animate({
                'top': -100,
            });
        });
    }

    // Nascondo la notifica se passo sopra col mouse
    $('.alert-success.push').on('mouseover', function () {
        $(this).stop().animate({
            'top': -100,
            'opacity': 0
        });
    });
}

export function ajaxError(xhr, error, thrown) {
    Swal.fire({
        title: globals.translations.errorTitle,
        html: globals.translations.errorMessage + (xhr.responseJSON ? ".<br><i>" + xhr.responseJSON.exception[0].message + "</i>" : ''),
        type: "error",
    })
}
