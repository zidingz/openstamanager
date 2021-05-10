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

/**
 *
 */
function startHooks() {
    $.ajax({
        url: globals.urls.hooks.list,
        type: 'GET',
        dataType: 'JSON',
        success: function (hooks) {
            $("#hooks-header").text(globals.translations.hooksExecuting);
            $("#hooks-number").text(hooks.length);

            if (hooks.length == 0) {
                $("#hooks-loading").hide();
                $("#hooks-number").text(0);
                $("#hooks-header").text(globals.translations.hookNone);
            }

            hooks.forEach(function (item, index) {
                renderHook(item, {
                    show: true,
                    message: globals.translations.hookExecuting.replace('_NAME_', item.name)
                });

                startHook(item, true);
            });
        },
    });
}

/**
 * Richiama l'hook per l'esecuzione.
 *
 * @param hook
 * @param init
 */
function startHook(hook, init) {
    $.ajax({
        url: globals.urls.hooks.lock
            .replace('|id|', hook.id),
        type: 'GET',
        dataType: 'JSON',
        success: function (token) {
            if (init) {
                hookCount("#hooks-counter");

                updateHook(hook);
            }

            if (token) {
                executeHook(hook, token);
            } else {
                let timeout = 10;

                setTimeout(function () {
                    startHook(hook);
                }, timeout * 1000);
            }
        },
    });
}

/**
 * Richiama l'hook per l'esecuzione.
 *
 * @param hook
 * @param token
 */
function executeHook(hook, token) {
    $.ajax({
        url: globals.urls.hooks.execute
            .replace('|id|', hook.id)
            .replace('|token|', token),
        type: 'GET',
        dataType: 'JSON',
        success: function (result) {
            updateHook(hook);

            let timeout;
            if (result.execute) {
                startHook(hook);
            } else {
                timeout = 30;

                setTimeout(function () {
                    startHook(hook);
                }, timeout * 1000);
            }
        },
    });
}

/**
 * Aggiorna le informazioni dell'hook.
 *
 * @param hook
 */
function updateHook(hook) {
    $.ajax({
        url: globals.urls.hooks.response
            .replace('|id|', hook.id),
        type: 'GET',
        dataType: 'JSON',
        success: function (result) {
            renderHook(hook, result);

            // Rimozione eventuale della rotella di caricamento
            let counter = $("#hooks-counter").text();
            let number = $("#hooks > li").length;

            if (number == 0) {
                $("#hooks-notified").html('<i class="fa fa-check" aria-hidden="true"></i>');
                $("#hooks-label").removeClass();
                $("#hooks-label").addClass('label').addClass('label-success');
            } else {
                $("#hooks-notified").text(number);
                $("#hooks-label").removeClass();
                $("#hooks-label").addClass('label').addClass('label-danger');
            }

            if (counter == $("#hooks-number").text()) {
                $("#hooks-loading").hide();

                let hookMessage;
                if (number > 1) {
                    hookMessage = globals.translations.hookMultiple.replace('_NUM_', number);
                } else if (number == 1) {
                    hookMessage = globals.translations.hookSingle;
                } else {
                    hookMessage = globals.translations.hookNone;
                }

                $("#hooks-header").text(hookMessage);
            }
        },
    });
}

/**
 * Aggiunta dell'hook al numero totale.
 */
function hookCount(id, value) {
    value = value ? value : 1;

    let element = $(id);
    let number = parseInt(element.text());
    number = isNaN(number) ? 0 : number;

    number += value;
    element.text(number);

    return number;
}

/**
 * Genera l'HTML per la visualizzazione degli hook.
 *
 * @param hook
 * @param result
 */
function renderHook(hook, result) {
    if (result.length == 0) return;

    let element_id = "hook-" + hook.id;

    // Inizializzazione
    let element = $("#" + element_id);
    if (element.length == 0) {
        $("#hooks").append('<li class="hook-element" id="' + element_id + '"></li>');

        element = $("#" + element_id);
    }

    // Rimozione
    if (!result.show) {
        element.remove();

        return;
    }

    // Contenuto
    let content = '<a href="' + (result.link ? result.link : "#") + '"><i class="' + result.icon + '"></i><span class="small"> ' + result.message + '</span>';

    if (result.progress) {
        let current = result.progress.current;
        let total = result.progress.total;
        let percentage = current / total * 100;
        percentage = isNaN(percentage) ? 100 : percentage;

        percentage = Math.round(percentage * 100) / 100;

        content += '<div class="progress" style="margin-bottom: 0px;"><div class="progress-bar" role="progressbar" aria-valuenow="' + percentage + '" aria-valuemin="0" aria-valuemax="100" style="width:' + percentage + '%">' + percentage + '% (' + current + '/' + total + ')</div></div>';
    }

    content += '</a>';

    element.html(content);
}
