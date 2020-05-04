import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin, { Draggable } from '@fullcalendar/interaction';
import allLocales from '@fullcalendar/core/locales-all';
import $ from 'jquery';
import moment from 'moment';
import 'tooltipster';

import { session_set_array, openModal } from '../../../resources/assets/js/functions/functions';

function aggiorna_contatore(counter_id) {
    var counter = $(counter_id);

    var dropdown = counter.find(".dropdown-menu");
    var selected = dropdown.find('input:checked').length;
    var total = dropdown.find('input').length;

    counter.find(".selected_counter").html(selected);
    counter.find(".total_counter").html(total);

    var object = counter.find(".counter_object");

    if (total === 0) {
        object.addClass('btn-primary disabled');
        return;
    } else {
        object.removeClass('btn-primary disabled');
    }

    if (selected === total) {
        object.removeClass('btn-warning btn-danger').addClass('btn-success');
    } else if (selected === 0) {
        object.removeClass('btn-warning btn-success').addClass('btn-danger');
    } else {
        object.removeClass('btn-success btn-danger').addClass('btn-warning');
    }
}

function load_interventi_da_pianificare(mese) {
    if (mese === undefined) {
        // Seleziono il mese corrente per gli interventi da pianificare
        var date = new Date();
        date.setDate(date.getDate());

        //Note: January is 0, February is 1, and so on.
        mese = ('0' + (date.getMonth() + 1)).slice(-2) + date.getFullYear();

        $('#select-interventi-pianificare option[value=' + mese + ']').attr('selected', 'selected').trigger('change');
    }

    $('#interventi-pianificare').html('<center><br><br><i class=\'fa fa-refresh fa-spin fa-2x fa-fw\'></i></center>');
    $.get(globals.dashboard.load_url, {
        op: 'load_intreventi',
        mese: mese
    }).done(function (data) {
        $('#interventi-pianificare').html(data);

        $('#external-events .fc-event').each(function () {
            new Draggable(this, {
                zIndex: 999,
                revert: true,
                revertDuration: 0,
                eventData: {
                    title: $.trim($(this).text()),
                    stick: false
                }
            });
        });
    });
}

$(document).ready(function () {
    // Aggiornamento contatori iniziale
    aggiorna_contatore("#dashboard_stati");
    aggiorna_contatore("#dashboard_tipi");
    aggiorna_contatore("#dashboard_tecnici");
    aggiorna_contatore("#dashboard_zone");

    // Selezione di uno stato intervento
    $('.dashboard_stato').click(function (event) {
        var id = $(this).val();

        session_set_array('dashboard,idstatiintervento', id).then(function () {
            aggiorna_contatore("#dashboard_stati");
            globals.dashboard.calendar.refetchEvents();
        });
    });

    // Selezione di un tipo intervento
    $('.dashboard_tipo').click(function (event) {
        var id = $(this).val();

        session_set_array('dashboard,idtipiintervento', id).then(function () {
            aggiorna_contatore("#dashboard_tipi");
            globals.dashboard.calendar.refetchEvents();
        });
    });

    // Selezione di un tecnico
    $('.dashboard_tecnico').click(function (event) {
        var id = $(this).val();

        session_set_array('dashboard,idtecnici', id).then(function () {
            aggiorna_contatore("#dashboard_tecnici");
            globals.dashboard.calendar.refetchEvents();
        });
    });

    // Selezione di una zona
    $('.dashboard_zona').click(function (event) {
        var id = $(this).val();

        session_set_array('dashboard,idzone', id).then(function () {
            aggiorna_contatore("#dashboard_zone");
            globals.dashboard.calendar.refetchEvents();
        });
    });

    // Selezione di tutti gli elementi
    $('.seleziona_tutto').click(function () {
        $(this).closest("ul").find('input:not(:checked)').each(function () {
            $(this).click();
        });
    });

    // Deselezione di tutti gli elementi
    $('.deseleziona_tutto').click(function () {
        $(this).closest("ul").find('input:checked').each(function () {
            $(this).click();
        });
    });

    $('#select-interventi-pianificare').change(function () {
        var mese = $(this).val();
        load_interventi_da_pianificare(mese);
    });

    // Caricamento interventi da pianificare
    load_interventi_da_pianificare();

    // Creazione del calendario
    create_calendar();
});

function create_calendar() {
    var calendarElement = document.getElementById('calendar');

    var calendar = new Calendar(calendarElement, {
        plugins: [interactionPlugin, dayGridPlugin, timeGridPlugin],
        locales: allLocales,
        locale: globals.locale,
        hiddenDays: globals.dashboard.show_sunday ? [] : [0],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        slotDuration: '00:15:00',
        defaultView: globals.dashboard.style,
        minTime: globals.dashboard.start_time,
        maxTime: globals.dashboard.end_time,
        lazyFetching: true,
        selectMirror: true,
        eventLimit: false, // allow "more" link when too many events
        allDaySlot: false,
        loading: function (isLoading, view) {
            if (isLoading) {
                $('#tiny-loader').fadeIn();
            } else {
                $('#tiny-loader').hide();
            }
        },

        droppable: globals.dashboard.write_permission,
        drop: function (info) {
            var date = info.date;

            var data = moment(date).format("YYYY-MM-DD");
            var ora_dal = moment(date).format("HH:mm");
            var ora_al = moment(date).add(1, 'hours').format("HH:mm");

            var ref = $(this).data('ref');
            var name;
            if (ref === 'ordine') {
                name = 'idordineservizio';
            } else if (ref === 'promemoria') {
                name = 'idcontratto_riga';
            } else {
                name = 'id_intervento';
            }

            openModal(globals.dashboard.drop.title, globals.dashboard.drop.url + '?&data=' + data + '&orario_inizio=' + ora_dal + '&orario_fine=' + ora_al + '&ref=dashboard&idcontratto=' + $(this).data('idcontratto') + '&' + name + '=' + $(this).data('id'));

            $(this).remove();

            $('#bs-popup').on('hidden.bs.modal', function () {
                globals.dashboard.calendar.refetchEvents();
            });
        },

        selectable: globals.dashboard.write_permission,
        select: function(info) {
            var start = info.start;
            var end = info.end;

            var data = moment(start).format("YYYY-MM-DD");
            var orario_inizio = moment(start).format("HH:mm");
            var orario_fine = moment(end).format("HH:mm");

            openModal(globals.dashboard.select.title, globals.dashboard.select.url + '?ref=dashboard&data=' + data + '&orario_inizio=' + orario_inizio + '&orario_fine=' + orario_fine);
        },

        editable: globals.dashboard.write_permission,
        eventDrop: function(info) {
            var event = info.event;

            $.post(globals.dashboard.load_url, {
                op: 'update_intervento',
                id: event.id,
                idintervento: event.idintervento,
                timeStart: moment(event.start).format("YYYY-MM-DD HH:mm"),
                timeEnd: moment(event.end).format("YYYY-MM-DD HH:mm")
            }, function (data, response) {
                data = $.trim(data);
                if (response !== "success" || data !== "ok") {
                    alert(data);
                    info.revert();
                }
            });
        },
        eventResize: function(info) {
            var event = info.event;

            $.post(globals.dashboard.load_url, {
                op: 'update_intervento',
                id: event.id,
                idintervento: event.idintervento,
                timeStart: moment(event.start).format("YYYY-MM-DD HH:mm"),
                timeEnd: moment(event.end).format("YYYY-MM-DD HH:mm")
            }, function (data, response) {
                data = $.trim(data);
                if (response !== "success" || data !== "ok") {
                    alert(data);
                    info.revert();
                }
            });
        },

        eventPositioned: function (info) {
            var event = info.event;
            var element = $(info.el);

            element.find('.fc-title').html(event.title);
            element.data('idintervento', event.idintervento);

            if (globals.dashboard.tooltip) {
                element.mouseover(function () {
                    if (!element.hasClass('tooltipstered')) {
                        $.post(globals.dashboard.load_url, {
                            op: 'get_more_info',
                            id: $(this).data('idintervento'),
                        }, function (data, response) {
                            data = $.trim(data);
                            if (response === "success" && data !== "ok") {
                                element.addClass('tooltipstered');

                                element.tooltipster({
                                    content: data,
                                    animation: 'grow',
                                    contentAsHTML: true,
                                    hideOnClick: true,
                                    onlyOne: true,
                                    speed: 200,
                                    delay: 100,
                                    maxWidth: 400,
                                    theme: 'tooltipster-shadow',
                                    touchDevices: true,
                                    trigger: 'hover',
                                    position: 'left'
                                });

                                $('.tooltipstered').tooltipster('hide');
                                element.tooltipster('show');
                            }
                        });
                    }
                });
            }
        },
        events: {
            url: globals.dashboard.load_url + "?op=get_current_month",
            type: 'POST',
            error: function () {
                alert(globals.dashboard.errordas);
            }
        }
    });

    calendar.render();

    globals.dashboard.calendar = calendar;
}
