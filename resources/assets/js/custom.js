$(document).ready(function () {
    // Fix per il menu principale
    $('.sidebar-menu').tree({
        followLink: true,
    });

    // Pulsante per il ritorno a inizio pagina
    var slideToTop = $("<div />");
    slideToTop.html('<i class="fa fa-chevron-up"></i>');
    slideToTop.css({
        position: 'fixed',
        bottom: '20px',
        right: '25px',
        width: '40px',
        height: '40px',
        color: '#eee',
        'font-size': '',
        'line-height': '40px',
        'text-align': 'center',
        'background-color': 'rgba(255, 78, 0)',
        'box-shadow': '0 0 10px rgba(0, 0, 0, 0.05)',
        cursor: 'pointer',
        'z-index': '99999',
        opacity: '.7',
        'display': 'none'
    });

    slideToTop.on('mouseenter', function () {
        $(this).css('opacity', '1');
    });

    slideToTop.on('mouseout', function () {
        $(this).css('opacity', '.7');
    });

    $('.wrapper').append(slideToTop);
    $(window).scroll(function () {
        if ($(window).scrollTop() >= 150) {
            if (!$(slideToTop).is(':visible')) {
                $(slideToTop).fadeIn(500);
            }
        } else {
            $(slideToTop).fadeOut(500);
        }
    });

    $(slideToTop).click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    });
    
    $(".sidebar-toggle").click(function(){
        setTimeout(function(){
            window.dispatchEvent(new Event('resize'));
        }, 350);
    });
    
    // Forza l'evento "blur" nei campi di testo per formattare i numeri con
    // jquery inputmask prima del submit
    setTimeout( function(){
        $('form').on('submit', function(){
            $('input').trigger('blur');
        });
    }, 1000 );

    // Fix multi-modal
    $(document).on('hidden.bs.modal', '.modal', function () {
        $('.modal:visible').length && $(document.body).addClass('modal-open');
    });

    $(document).ready(function () {
        // Imposta la lingua per la gestione automatica delle date dei diversi plugin
        moment.locale(globals.locale);
        globals.timestampFormat = moment.localeData().longDateFormat('L') + ' ' + moment.localeData().longDateFormat('LT');

        // Imposta lo standard per la conversione dei numeri
        numeral.register('locale', 'it', {
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
        numeral.locale('it');
        numeral.defaultFormat('0,0.' + ('0').repeat(globals.cifre_decimali));

        // Orologio
        clock();

        // Richiamo alla generazione di Datatables
        start_datatables();

        // Calendario principale
        ranges = {};
        ranges[globals.translations.today] = [moment(), moment()];
        ranges[globals.translations.firstThreemester] = [moment("01", "MM"), moment("03", "MM").endOf('month')];
        ranges[globals.translations.secondThreemester] = [moment("04", "MM"), moment("06", "MM").endOf('month')];
        ranges[globals.translations.thirdThreemester] = [moment("07", "MM"), moment("09", "MM").endOf('month')];
        ranges[globals.translations.fourthThreemester] = [moment("10", "MM"), moment("12", "MM").endOf('month')];
        ranges[globals.translations.firstSemester] = [moment("01", "MM"), moment("06", "MM").endOf('month')];
        ranges[globals.translations.secondSemester] = [moment("06", "MM"), moment("12", "MM").endOf('month')];
        ranges[globals.translations.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
        ranges[globals.translations.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];
        ranges[globals.translations.thisYear] = [moment().startOf('year'), moment().endOf('year')];
        ranges[globals.translations.lastYear] = [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')];

        // Calendario principale
        $('#daterange').daterangepicker({
                locale: {
                    customRangeLabel: globals.translations.custom,
                    applyLabel: globals.translations.apply,
                    cancelLabel: globals.translations.cancel,
                    fromLabel: globals.translations.from,
                    toLabel: globals.translations.to,
                },
                ranges: ranges,
                startDate: globals.start_date,
                endDate: globals.end_date,
                applyClass: 'btn btn-success btn-sm',
                cancelClass: 'btn btn-danger btn-sm',
                linkedCalendars: false
            },
            function (start, end) {
                // Esegue il submit del periodo selezionato e ricarica la pagina
                $.get(globals.rootdir + '/core.php?period_start=' + start.format('YYYY-MM-DD') + '&period_end=' + end.format('YYYY-MM-DD'), function (data) {
                    location.reload();
                });
            }
        );

        // Pulsante per visualizzare/ nascondere la password
        $(".input-group-addon").on('click', function () {
            if ($(this).parent().find("i").hasClass('fa-eye')) {
                $("#password").attr("type", "text");
                $(this).parent().find("i").removeClass('fa-eye').addClass('fa-eye-slash');
                $(this).parent().find("i").attr('title', 'Nascondi password');
            } else if ($(this).parent().find("i").hasClass('fa-eye-slash')) {
                $("#password").attr("type", "password");
                $(this).parent().find("i").removeClass('fa-eye-slash').addClass('fa-eye');
                $(this).parent().find("i").attr('title', 'Visualizza password');
            }
        });

        // Messaggi automatici di eliminazione
        $(document).on('click', '.ask', function () {
            message(this);
        });

        // Pulsanti di Datatables
        $(".btn-csv").click(function (e) {
            var table = $(document).find("#" + $(this).closest("[data-target]").data("target")).DataTable();

            table.buttons(0).trigger();
        });

        $(".btn-excel").click(function (e) {
            var table = $(document).find("#" + $(this).closest("[data-target]").data("target")).DataTable();

            table.buttons(3).trigger();
        });

        $(".btn-pdf").click(function (e) {
            var table = $(document).find("#" + $(this).closest("[data-target]").data("target")).DataTable();

            table.buttons(4).trigger();
        });

        $(".btn-copy").click(function (e) {
            var table = $(document).find("#" + $(this).closest("[data-target]").data("target")).DataTable();

            table.buttons(1).trigger();
        });

        $(".btn-print").click(function (e) {
            var table = $(document).find("#" + $(this).closest("[data-target]").data("target")).DataTable();

            table.buttons(2).trigger();
        });

        $(".btn-select-all").click(function () {
            var table = $(document).find("#" + $(this).parent().parent().parent().data("target")).DataTable();
            $("#main_loading").show();

            table.clear().draw();

            table.page.len(-1).draw();
        });

        $(".btn-select-none").click(function () {
            var table = $(document).find("#" + $(this).parent().parent().parent().data("target")).DataTable();

            table.rows().deselect();

            table.page.len(200);
        });

        $(".bulk-action").click(function () {
            var table = $(document).find("#" + $(this).parent().parent().parent().parent().data("target"));

            if (table.data('selected')) {
                $(this).attr("data-id_records", table.data('selected'));
                $(this).data("id_records", table.data('selected'));

                message(this);

                $(this).attr("data-id_records", "");
                $(this).data("id_records", "");
            } else {
                swal(globals.translations.waiting, globals.translations.waiting_msg, "error");
            }
        });

        // Sidebar
        $('.sidebar-menu > li.treeview i.fa-angle-left').click(function (e) {
            e.preventDefault();
            $(this).find('ul').stop().slideDown();
        });

        $('.sidebar-menu > li.treeview i.fa-angle-down').click(function (e) {
            e.preventDefault();
            $(this).find('ul').stop().slideUp();
        });

        $menulist = $('.treeview-menu > li.active');
        for (i = 0; i < $menulist.length; i++) {
            $list = $($menulist[i]);
            $list.parent().show().parent().addClass('active');
            $list.parent().parent().find('i.fa-angle-left').removeClass('fa-angle-left').addClass('fa-angle-down');
        }

        // Menu ordinabile
        $(".sidebar-menu").sortable({
            cursor: 'move',

            stop: function (event, ui) {
                var order = $(this).sortable('toArray').toString();

                $.post(globals.rootdir + "/actions.php?id_module=" + globals.aggiornamenti_id, {
                    op: 'sortmodules',
                    ids: order
                });
            }
        });

        if (isMobile()) {
            $(".sidebar-menu").sortable("disable");
        }

        // Tabs
        $('.nav-tabs').tabs();

        // Entra nel tab indicato al caricamento della pagina
        var hash = window.location.hash ? window.location.hash : getUrlVars().hash;
        if (hash && hash != '#tab_0') {
            $('ul.nav-tabs a[href="' + hash + '"]').tab('show');
        }

        // Nel caso la navigazione sia da mobile, disabilito il ritorno al punto precedente
        if (!isMobile()) {
            // Salvo lo scroll per riportare qui l'utente al reload
            $(window).on('scroll', function () {
                if (sessionStorage != undefined) {
                    sessionStorage.setItem('scrollTop_' + globals.id_module + '_' + globals.id_record, $(document).scrollTop());
                }
            });

            // Riporto l'utente allo scroll precedente
            if (sessionStorage['scrollTop_' + globals.id_module + '_' + globals.id_record] != undefined) {
                setTimeout(function () {
                    scrollToAndFocus(sessionStorage['scrollTop_' + globals.id_module + '_' + globals.id_record]);
                }, 1);
            }
        }

        $('.nav-tabs a').click(function (e) {
            $(this).tab('show');

            var scrollmem = $('body').scrollTop() || $('html').scrollTop();

            window.location.hash = this.hash;

            $('html,body').scrollTop(scrollmem);
        });

        // Fix per la visualizzazione di Datatables all'interno dei tab Bootstrap
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();

            $($.fn.dataTable.tables(true)).DataTable().scroller.measure();
        });

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

        $('.widget').mouseover(function (e) {
            e.preventDefault();
            start_widgets($("#widget-top, #widget-right"));
        });

        $('#supersearch').keyup(function () {
            $(document).ajaxStop();

            if ($(this).val() == '') {
                $(this).removeClass('wait');
            } else {
                $(this).addClass('wait');
            }
        });

        $.widget("custom.supersearch", $.ui.autocomplete, {
            _create: function () {
                this._super();
                this.widget().menu("option", "items", "> :not(.ui-autocomplete-category)");
            },
            _renderMenu: function (ul, items) {
                if (items[0].value == undefined) {
                    $('#supersearch').removeClass('wait');
                    ul.html('');
                } else {
                    var that = this,
                        currentCategory = "";

                    ul.addClass('ui-autocomplete-scrollable');
                    ul.css('z-index', '999');

                    $.each(items, function (index, item) {

                        if (item.category != currentCategory) {
                            ul.append("<li class='ui-autocomplete-category'>" + item.category + "</li>");
                            currentCategory = item.category;
                        }

                        that._renderItemData(ul, item);
                    });
                }
            },
            _renderItem: function (ul, item) {
                return $("<li>")
                    .append("<a href='" + item.link + "' title='Clicca per aprire'><b>" + item.value + "</b><br/>" + item.label + "</a>")
                    .appendTo(ul);
            }
        });

        // Configurazione supersearch
        var $super = $('#supersearch').supersearch({
            minLength: 3,
            select: function (event, ui) {
                location.href = ui.item.link;
            },
            source: function (request, response) {
                $.ajax({
                    url: globals.rootdir + '/ajax_search.php',
                    dataType: "json",
                    data: {
                        term: request.term
                    },

                    complete: function (jqXHR) {
                        $('#supersearch').removeClass('wait');
                    },

                    success: function (data) {
                        if (data == null) {
                            response($.map(['a'], function (item) {
                                return false;
                            }));
                        } else {
                            response($.map(data, function (item) {
                                labels = (item.labels).toString();
                                labels = labels.replace('<br/>,', '<br/>');

                                return {
                                    label: labels,
                                    category: item.category,
                                    link: item.link,
                                    value: item.title
                                }
                            }));
                        }
                    }
                });
            }
        });
    });

});
