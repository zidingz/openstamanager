import $ from 'jquery';
import 'admin-lte';
import 'bootstrap';
import 'moment';
import 'numeral';
import 'bootstrap-colorpicker';
import 'toastr';

import { start_datatables } from '../functions/datatables';
import { start_complete_calendar } from '../functions/dates';
import { renderMessages, clock } from '../functions/functions';
import { init } from '../functions/init';
import { startHooks } from '../functions/hooks';

// Aggiunta dell'ingranaggio all'unload della pagina
$(window).on("beforeunload", function () {
    $("#main_loading").show();
});

// Rimozione dell'ingranaggio al caricamento completo della pagina
$(window).on("load", function () {
    $("#main_loading").fadeOut();
});

// Fix multi-modal
$(document).on('hidden.bs.modal', '.modal', function () {
    $(this).remove();
    $('.modal:visible').length && $(document.body).addClass('modal-open');
});

$(document).ready(function () {
    // Impostazioni di default per gli alert
    swal.setDefaults({
        buttonsStyling: false,
        confirmButtonClass: "btn btn-lg btn-primary",
        cancelButtonClass: "btn btn-lg",
        cancelButtonText: globals.translations.cancel,
    });

    // Standard per i popup
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "12000",
        "extendedTimeOut": "8000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };

    // Imposta lo standard per la conversione dei numeri
    numeral.register('locale', 'current', {
        delimiters: {
            thousands: globals.thousands,
            decimal: globals.decimals,
        },
        abbreviations: {
            thousand: 'k',
            million: 'm',
            billion: 'b',
            trillion: 't'
        },
        currency: {
            symbol: '€'
        }
    });
    numeral.locale('current');
    numeral.defaultFormat('0,0.' + ('0').repeat(globals.cifre_decimali));

    // Messaggi per l'utente
    renderMessages();

    // Orologio
    clock();

    // Hooks
    startHooks();

    // Richiamo alla generazione di Datatables
    start_datatables();

    // Calendario principale
    start_complete_calendar("#daterange", function (start, end) {
        // Esegue il submit del periodo selezionato e ricarica la pagina
        $.get(globals.rootdir + '/core.php?period_start=' + start.format('YYYY-MM-DD') + '&period_end=' + end.format('YYYY-MM-DD'), function (data) {
            location.reload();
        });
    });

    // Messaggi automatici di eliminazione
    $(document).on('click', '.ask', function () {
        message(this);
    });

    // Forza l'evento "blur" nei campi di testo per formattare i numeri con
    // jquery inputmask prima del submit
    setTimeout(function () {
        $('form').on('submit', function () {
            $('input').trigger('blur');
        });
    }, 1000);

    // Operazioni di inizializzazione
    init();
});
