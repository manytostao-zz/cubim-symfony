/**
 * Created by osmany.torres on 2/29/2016.
 */

var Calendario = function () {
    var _initCalendar = function () {
        var calendar = $('#calendar').fullCalendar({
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: {
                url: Routing.generate('otros_eventos'),
                error: function () {
                    $('#script-warning').show();
                }
            },
            loading: function (bool) {
                $('#loading').toggle(bool);
            },
            selectable: true,
            selectHelper: true,
            select: function (start, end) {
                startDate.data("DateTimePicker").date(start);
                endDate.data("DateTimePicker").date(end);
                $('#formEvent_id').val('');
                $('#formEvent_title').val('');
                $('#formEvent_description').val('');
                $('#formEvent_color').val('');
                $('#formEvent_url').val('');
                $('#formEvent_allDay').prop('checked', false);
                $('#deleteBtn').hide();
                document.getElementById("formEvent_allDay").parentNode.className = '';
                _widgetsReadOnly();
                document.getElementById('cremod').innerHTML = 'Adicionar';
                document.getElementById('submitBtn').innerHTML = 'Salvar';
                $('#createEvent').click();

            },
            eventClick: function (calEvent, jsEvent, view) {
                startDate.data("DateTimePicker").date(calEvent.start);
                endDate.data("DateTimePicker").date(calEvent.end);
                $('#formEvent_title').val(calEvent.title);
                $('#formEvent_description').val(calEvent.description);
                $('#formEvent_id').val(calEvent.id);
                $('#formEvent_color').val(calEvent.color);
                $('#formEvent_url').val(calEvent.url);
                $('#formEvent_allDay').prop('checked', calEvent.allDay);
                $('#formEvent_calendarView').val(view.name);
                var m = calendar.fullCalendar('getDate');
                $('#formEvent_currentDate').val(m.format());
                $('#deleteBtn').show();
                document.getElementById('cremod').innerHTML = 'Modificar';
                document.getElementById('submitBtn').innerHTML = 'Salvar';
                if (calEvent.allDay)
                    document.getElementById("formEvent_allDay").parentNode.className = 'checked';
                else
                    document.getElementById("formEvent_allDay").parentNode.className = '';
                _widgetsReadOnly();
                $('#createEvent').click();
                return false;
            },
            eventResize: function (event, delta, revertFunc) {
                if (!confirm('"' + event.title + '"' + " ahora termina el " + event.end.format('DD/MM/YYYY') + " a las " + event.end.format('HH:mm:ss') + ". ¿Es esto correcto?")) {
                    revertFunc();
                }
                startDate.data("DateTimePicker").date(event.start);
                endDate.data("DateTimePicker").date(event.end);
                $('#formEvent_title').val(event.title);
                $('#formEvent_description').val(event.description);
                $('#formEvent_id').val(event.id);
                $('#formEvent_color').val(event.color);
                $('#formEvent_url').val(event.url);
                _submitData();
            },
            eventDrop: function (event, delta, revertFunc) {
                if (event.end) {
                    if (!confirm('"' + event.title + '"' + " ahora comienza el " + event.start.format('DD/MM/YYYY') + " a las " + event.start.format('HH:mm:ss') + " y termina el " + event.end.format('DD/MM/YYYY') + " a las " + event.end.format('HH:mm:ss') + ". ¿Es esto correcto?")) {
                        revertFunc();
                    }
                }
                else {
                    if (event.allDay) {
                        $('#formEvent_allDay').prop('checked', true);
                        document.getElementById("formEvent_allDay").parentNode.className = 'checked';
                    }
                    else {
                        $('#formEvent_allDay').prop('checked', false);
                        document.getElementById("formEvent_allDay").parentNode.className = '';
                        event.end = moment(event.start.format('DD/MM/YYYY HH:mm:ss'), 'DD/MM/YYYY HH:mm:ss');
                        event.end.add(2, 'h')
                    }

                    if (!confirm('"' + event.title + '"' + " ahora comienza el " + event.start.format('DD/MM/YYYY') + " a las " + event.start.format('HH:mm:ss') + ". ¿Es esto correcto?")) {
                        revertFunc();
                    }
                }
                startDate.data("DateTimePicker").date(event.start);
                endDate.data("DateTimePicker").date(event.end);
                $('#formEvent_title').val(event.title);
                $('#formEvent_description').val(event.description);
                $('#formEvent_id').val(event.id);
                $('#formEvent_color').val(event.color);
                $('#formEvent_url').val(event.url);
                _widgetsReadOnly();
                _submitData();
            },
            theme: false,
            timezone: 'America/Havana',
            buttonIcons: {
                prev: 'left-single-arrow',
                next: 'right-single-arrow',
                prevYear: 'left-double-arrow',
                nextYear: 'right-double-arrow'
            }, // show the prev/next text
            editable: true,
            eventLimit: true, // allow "more" link when too many events
            lang: 'es'
        });
    };

    var _renderCalendar = function (view, defaultDate) {
        setTimeout(function () {
            $('#calendar').fullCalendar('destroy');
            _initCalendar();
            $('#calendar').fullCalendar('changeView', view.name);
            $('#calendar').fullCalendar('gotoDate', defaultDate);
        }, 1000);

    };

    var _submitData = function () {
        var view;
        view = $('#calendar').fullCalendar('getView');
        var date;
        date = $('#calendar').fullCalendar('getDate');
        $.post(Routing.generate('otros_crear_evento'), $('.modal form').serialize(), _renderCalendar(view, date)
        )
        ;
    };

    var _deleteData = function () {
        var view;
        view = $('#calendar').fullCalendar('getView');
        var date;
        date = $('#calendar').fullCalendar('getDate');
        var route = Routing.generate('otros_eliminar_evento', {'id': "PLACEHOLDER"});
        var id = $('#formEvent_id').val();
        var correctedRoute = route.replace("PLACEHOLDER", id);
        $.get(correctedRoute, _renderCalendar(view, date));
    };

    var _widgetsReadOnly = function () {
        if ($('#formEvent_allDay').attr('checked') == 'checked') {
            $('#formEvent_end').val('');
        }
        $('#formEvent_start').attr('readonly', $('#formEvent_allDay').attr('checked') == 'checked');
        $('#formEvent_end').attr('readonly', $('#formEvent_allDay').attr('checked') == 'checked');
    };

    var _initDateAndColorPickers = function () {
        startDate = $('#formEvent_start').datetimepicker({
            locale: 'es',
            showClear: true,
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-calendar-o',
                clear: 'fa fa-trash-o',
                close: 'fa fa-times'
            },
            format: 'DD/MM/YYYY HH:mm:ss'
        });
        endDate = $('#formEvent_end').datetimepicker({
            locale: 'es',
            showClear: true,
            icons: {
                time: 'fa fa-clock-o',
                date: 'fa fa-calendar',
                up: 'fa fa-chevron-up',
                down: 'fa fa-chevron-down',
                previous: 'fa fa-chevron-left',
                next: 'fa fa-chevron-right',
                today: 'fa fa-calendar-o',
                clear: 'fa fa-trash-o',
                close: 'fa fa-times'
            },
            format: 'DD/MM/YYYY HH:mm:ss'
        });
        eventColor = $('#formEvent_color').colorpicker();
    };

    return {
        initCalendar: function () {
            _initCalendar()
        },

        renderCalendar: function () {
            _renderCalendar()
        },

        submitData: function () {
            _submitData()
        },

        deleteData: function () {
            _deleteData()
        },

        widgetsReadOnly: function () {
            _widgetsReadOnly()
        },

        initDateAndColorPickers: function () {
            _initDateAndColorPickers()
        }
    }
}();