/**
 *
 */
export function startHooks() {
    if (!globals.hooks){
        return;
    }

    $.ajax({
        url: globals.hooks.list,
        type: "get",
        success: function (data) {
            var hooks = JSON.parse(data);

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
 */
export function startHook(hook, init) {
    $.ajax({
        url: globals.hooks.lock.replace('|id|', hook.id),
        type: "get",
        success: function (data) {
            var token = JSON.parse(data);

            if (init) {
                hookCount("#hooks-counter");

                updateHook(hook);
            }

            if (token) {
                executeHook(hook, token);
            } else {
                var timeout = 10;

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
export function executeHook(hook, token) {
    $.ajax({
        url: globals.hooks.lock.replace('|id|', hook.id).replace('|token|', token),
        type: "get",
        success: function (data) {
            var result = JSON.parse(data);
            updateHook(hook);

            var timeout;
            if (result && result.execute) {
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
 * @param init
 */
export function updateHook(hook) {
    $.ajax({
        url: globals.hooks.response.replace('|id|', hook.id),
        type: "get",
        success: function (data) {
            var result = JSON.parse(data);
            renderHook(hook, result);

            // Rimozione eventuale della rotella di caricamento
            var counter = $("#hooks-counter").text();
            var number = $("#hooks > li").length;
            $("#hooks-notified").text(number);

            if (counter == $("#hooks-number").text()) {
                $("#hooks-loading").hide();

                var hookMessage;
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
export function hookCount(id, value) {
    value = value ? value : 1;

    var element = $(id);
    var number = parseInt(element.text());
    number = isNaN(number) ? 0 : number;

    number += value;
    element.text(number);

    return number;
}

/**
 * Genera l'HTML per la visualizzazione degli hook.
 *
 * @param element_id
 * @param result
 */
export function renderHook(hook, result) {
    if (result.length == 0) return;

    var element_id = "hook-" + hook.id;

    // Inizializzazione
    var element = $("#" + element_id);
    if (element.length == 0) {
        $("#hooks").append('<a class="dropdown-item hook-element" href="#" id="' + element_id + '"></a>');

        element = $("#" + element_id);
    }

    // Rimozione
    if (!result.show) {
        element.remove();

        return;
    }

    // Contenuto
    var content = '</i><span> ' + result.message + '</span>';

    if (result.progress) {
        var current = result.progress.current;
        var total = result.progress.total;
        var percentage = current / total * 100;
        percentage = isNaN(percentage) ? 100 : percentage;

        percentage = Math.round(percentage * 100) / 100;

        content += '<div class="progress" style="margin-bottom: 0px;"><div class="progress-bar" role="progressbar" aria-valuenow="' + percentage + '" aria-valuemin="0" aria-valuemax="100" style="width:' + percentage + '%">' + percentage + '% (' + current + '/' + total + ')</div></div>';
    }

    element.attr("href", result.link ? result.link : "#");
    element.html(content);
}
