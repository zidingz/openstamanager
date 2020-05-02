import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import $ from "jquery";

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
    $.get(globals.dashboard.load_url, {op: 'load_intreventi', mese: mese}, function (data) {
    }).done(function (data) {
        $('#interventi-pianificare').html(data);
        $('#external-events .fc-event').each(function () {
            $(this).draggable({
                zIndex: 999,
                revert: true,
                revertDuration: 0
            });
        });

    });
}

$(document).ready(function () {
    // Aggiornamento contatore iniziale
    aggiorna_contatore("#dashboard_stati");

    // Selezione di uno stato
    $('.dashboard_stato').click(function (event) {
        var id = $(this).val();

        session_set_array('dashboard,idstatiintervento', id).then(function () {
            aggiorna_contatore("#dashboard_stati");
            $('#calendar').fullCalendar('refetchEvents');
        });
    });

    // Selezione di tutti gli stati
    $('#seleziona_stati').click(function (event) {
        $(this).parent().parent().find('input:not(:checked)').each(function () {
            $(this).click();
        });
    });

    // Deselezione di tutti gli stati
    $('#deseleziona_stati').click(function (event) {
        $(this).parent().parent().find('input:checked').each(function () {
            $(this).click();
        });
    });

    // Caricamento interventi da pianificare
    load_interventi_da_pianificare();

    $('#select-interventi-pianificare').change(function () {
        var mese = $(this).val();
        load_interventi_da_pianificare(mese);
    });

    $('#selectalltipi').click(function (event) {

        $(this).parent().parent().find('li input[type=checkbox]').each(function (i) { // loop through each checkbox
            this.checked = true;
            $.when(session_set_array('dashboard,idtipiintervento', this.value, 0)).promise().then(function () {
                $('#calendar').fullCalendar('refetchEvents');
            });
            i++;
            update_counter('idtipi_count', i);

        });

    });

    $('#selectalltecnici').click(function (event) {

        $(this).parent().parent().find('li input[type=checkbox]').each(function (i) { // loop through each checkbox
            this.checked = true;
            $.when(session_set_array('dashboard,idtecnici', this.value, 0)).promise().then(function () {
                $('#calendar').fullCalendar('refetchEvents');
            });
            i++;
            update_counter('idtecnici_count', i);
        });

    });

    $('#selectallzone').click(function (event) {

        $(this).parent().parent().find('li input[type=checkbox]').each(function (i) { // loop through each checkbox
            this.checked = true;
            $.when(session_set_array('dashboard,idzone', this.value, 0)).promise().then(function () {
                $('#calendar').fullCalendar('refetchEvents');
            });

            i++
            update_counter('idzone_count', i);
        });

    });

    // Comandi deseleziona tutti
    $('#deselectallstati').click(function (event) {

        $(this).parent().parent().find('li input[type=checkbox]').each(function () { // loop through each checkbox
            this.checked = false;
            $.when(session_set_array('dashboard,idstatiintervento', this.value, 1)).promise().then(function () {
                $('#calendar').fullCalendar('refetchEvents');
            });

            update_counter('idstati_count', 0);

        });

    });

    $('#deselectalltipi').click(function (event) {

        $(this).parent().parent().find('li input[type=checkbox]').each(function () { // loop through each checkbox
            this.checked = false;
            $.when(session_set_array('dashboard,idtipiintervento', this.value, 1)).promise().then(function () {
                $('#calendar').fullCalendar('refetchEvents');
            });


            update_counter('idtipi_count', 0);

        });

    });

    $('#deselectalltecnici').click(function (event) {

        $(this).parent().parent().find('li input[type=checkbox]').each(function () { // loop through each checkbox
            this.checked = false;
            $.when(session_set_array('dashboard,idtecnici', this.value, 1)).promise().then(function () {
                $('#calendar').fullCalendar('refetchEvents');
            });

            update_counter('idtecnici_count', 0);

        });

    });

    $('#deselectallzone').click(function (event) {

        $(this).parent().parent().find('li input[type=checkbox]').each(function () { // loop through each checkbox
            this.checked = false;
            $.when(session_set_array('dashboard,idzone', this.value, 1)).promise().then(function () {
                $('#calendar').fullCalendar('refetchEvents');
            });

            update_counter('idzone_count', 0);

        });

    });

    // Creazione del calendario
    create_calendar();

    // Data di default
    $('.fc-prev-button, .fc-next-button, .fc-today-button').click(function () {
        var date_start = $('#calendar').fullCalendar('getView').start.format('YYYY-MM-DD');
        date_start = moment(date_start);

        if (globals.dashboard.style === 'month') {
            if (date_start.date() > 1) {
                date_start = moment(date_start).add(1, 'M').startOf('month');
            }
        }

        date_start = date_start.format('YYYY-MM-DD');
        setCookie('calendar_date_start', date_start, 365);
    });

    var calendar_date_start = getCookie('calendar_date_start');
    if (calendar_date_start !== '')
        $('#calendar').fullCalendar('gotoDate', calendar_date_start);

});

function create_calendar() {
    $('#external-events .fc-event').each(function () {
        // store data so the calendar knows to render an event upon drop
        $(this).data('event', {
            title: $.trim($(this).text()), // use the element's text as the event title
            stick: false // maintain when user navigates (see docs on the renderEvent method)
        });

        // make the event draggable using jQuery UI
        $(this).draggable({
            zIndex: 999,
            revert: true,     // will cause the event to go back to its
            revertDuration: 0  //  original position after the drag
        });

    });

    var calendarElement = document.getElementById('calendar');

    var calendar = new Calendar(calendarElement, {
        plugins: [dayGridPlugin, timeGridPlugin],
        locale: globals.locale,
        hiddenDays: globals.dashboard.show_sunday ? [] : [0],
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
        },
        timeFormat: 'H:mm',
        slotLabelFormat: "H:mm",
        slotDuration: '00:15:00',
        defaultView: globals.dashboard.style,
        minTime: globals.dashboard.start_time,
        maxTime: globals.dashboard.end_time,
        lazyFetching: true,
        selectHelper: true,
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
        drop: function (date, jsEvent, ui, resourceId) {
            var data = moment(date).format("YYYY-MM-DD");
            var ora_dal = moment(date).format("HH:mm");
            var ora_al = moment(date).add(1, 'hours').format("HH:mm");

            var ref = $(this).data('ref');
            var name;
            if (ref == 'ordine') {
                name = 'idordineservizio';
            } else if (ref == 'promemoria') {
                name = 'idcontratto_riga';
            } else {
                name = 'id_intervento';
            }

            openModal(globals.dashboard.drop.title, globals.dashboard.drop.url + '?&data=' + data + '&orario_inizio=' + ora_dal + '&orario_fine=' + ora_al + '&ref=dashboard&idcontratto=' + $(this).data('idcontratto') + '&' + name + '=' + $(this).data('id'));

            $(this).remove();

            $('#bs-popup').on('hidden.bs.modal', function () {
                $('#calendar').fullCalendar('refetchEvents');
            });
        },

        selectable: globals.dashboard.write_permission,
        select: function (start, end, allDay) {
            var data = moment(start).format("YYYY-MM-DD");
            var ora_dal = moment(start).format("HH:mm");
            var ora_al = moment(end).format("HH:mm");

            openModal(globals.dashboard.select.title, globals.dashboard.select.url + '?ref=dashboard&data=' + data + '&orario_inizio=' + ora_dal + '&orario_fine=' + ora_al);

            $('#calendar').fullCalendar('unselect');
        },

        editable: globals.dashboard.write_permission,
        eventDrop: function (event, dayDelta, minuteDelta, revertFunc) {
            $.post(globals.dashboard.load_url, {
                op: 'update_intervento',
                id: event.id,
                idintervento: event.idintervento,
                timeStart: moment(event.start).format("YYYY-MM-DD HH:mm"),
                timeEnd: moment(event.end).format("YYYY-MM-DD HH:mm")
            }, function (data, response) {
                if (response == "success") {
                    data = $.trim(data);
                    if (data != "ok") {
                        alert(data);
                        $('#calendar').fullCalendar('refetchEvents');
                        revertFunc();
                    } else {
                        return false;
                    }
                }
            });
        },
        eventResize: function (event, dayDelta, minuteDelta, revertFunc) {
            $.post(globals.dashboard.load_url, {
                op: 'update_intervento',
                id: event.id,
                idintervento: event.idintervento,
                timeStart: moment(event.start).format("YYYY-MM-DD HH:mm"),
                timeEnd: moment(event.end).format("YYYY-MM-DD HH:mm")
            }, function (data, response) {
                if (response == "success") {
                    data = $.trim(data);
                    if (data != "ok") {
                        alert(data);
                        $('#calendar').fullCalendar('refetchEvents');
                        revertFunc();
                    } else {
                        return false;
                    }
                }
            });
        },

        eventAfterRender: function (event, element) {
            element.find('.fc-title').html(event.title);
            element.data('idintervento', event.idintervento);

            if (globals.dashboard.tooltip) {
                element.mouseover(function () {
                    if (!element.hasClass('tooltipstered')) {
                        $(this).data('idintervento', event.idintervento);

                        $.post(globals.dashboard.load_url, {
                            op: 'get_more_info',
                            id: $(this).data('idintervento'),
                        }, function (data, response) {
                            if (response == "success") {
                                data = $.trim(data);
                                if (data != "ok") {
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
                                } else {
                                    return false;
                                }

                                $('#calendar').fullCalendar('option', 'contentHeight', 'auto');
                            }
                        });
                    }
                });
            }
        },
        events: {
            url: globals.dashboard.load_url,
            type: 'POST',
            data: {
                op: 'get_current_month',
            },
            error: function () {
                alert(globals.dashboard.errordas);
            }
        }
    });

    calendar.render();
}
