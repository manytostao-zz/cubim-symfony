/**
 * Created by Osmany Torres Leyva on 11/02/2016.
 */

var Otros = function () {
    var _showTooltip = function (x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css({
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 15,
            border: '1px solid #333',
            padding: '4px',
            color: '#fff',
            'border-radius': '3px',
            'background-color': '#333',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    };

    var _initDashboardDaterangeLocal = function () {
        $('#dashboard-report-range').daterangepicker({
                opens: (Metronic.isRTL() ? 'right' : 'left'),
                startDate: moment().subtract('days', 29),
                endDate: moment(),
                minDate: '01/01/2012',
                maxDate: '12/31/2050',
                dateLimit: {
                    days: 365
                },
                showDropdowns: false,
                showWeekNumbers: true,
                timePicker: false,
                timePickerIncrement: 1,
                timePicker12Hour: true,
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Ayer': [moment().subtract('days', 1), moment().subtract('days', 1)],
                    'Últimos 7 días': [moment().subtract('days', 6), moment()],
                    'Últimos 30 días': [moment().subtract('days', 29), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                },
                buttonClasses: ['btn btn-sm'],
                _applyClass: ' blue',
                cancelClass: 'default',
                format: 'DD/MM/YYYY',
                separator: ' a ',
                locale: {
                    _applyLabel: 'Aplicar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Rango propio',
                    daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    firstDay: 1
                }
            },
            function (start, end) {
                $('#dashboard-report-range span').html(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            },
            function (e) {
                e.preventDefault();
                _apply();
                this.updateInputText();
                this.hide();
                this.element.trigger('_apply.daterangepicker', this);

            }
        );

        $('#dashboard-report-range span').html(moment().subtract('days', 29).format('DD/MM/YYYY') + ' - ' + moment().format('DD/MM/YYYY'));
        $('#dashboard-report-range').show();
    };

    var _apply = function () {
        Metronic.blockUI({target: $('#update'), iconOnly: true});
        window.setTimeout(function () {
            Metronic.unblockUI($('#update'));
        }, 5200);
        $.ajax({
            url: Routing.generate('otros_data'),
            type: 'POST',
            data: JSON.stringify({
                desde: document.getElementsByName('daterangepicker_start')[0].value,
                hasta: document.getElementsByName('daterangepicker_end')[0].value
            }),
            contentType: 'application/json; charset=utf-8',
            success: function (data) {
                document.getElementById('total_users').innerHTML = 'Total de usuarios que visitaron la biblioteca: <strong>' + $.parseJSON(data).total_users + '</strong>';
                var d1 = [];
                d1.push([1, $.parseJSON(data).referencia[1]]);
                d1.push([2, $.parseJSON(data).bibliografia[1]]);
                d1.push([3, $.parseJSON(data).sala_de_lectura[1]]);
                d1.push([4, $.parseJSON(data).sala_de_navegacion[1]]);
                d1.push([5, $.parseJSON(data).dsi[1]]);
                var d2 = [];
                d2.push([1, $.parseJSON(data).recepcion['referencia'][1]]);
                d2.push([2, $.parseJSON(data).recepcion['bibliografia'][1]]);
                d2.push([3, $.parseJSON(data).recepcion['sala_de_lectura'][1]]);
                d2.push([4, $.parseJSON(data).recepcion['sala_de_navegacion'][1]]);
                d2.push([5, $.parseJSON(data).recepcion['dsi'][1]]);
                d2.push([6, $.parseJSON(data).cursos[1]]);
                d2.push([7, $.parseJSON(data).conferencias[1]]);

                /********Gráfico de Barras*************/
                var tick_options = [
                    [1, "Referencia"],
                    [2, "Bibliografía"],
                    [3, "Sala de Lectura"],
                    [4, "Sala de Navegación"],
                    [5, "DSI"],
                    [6, "Cursos"],
                    [7, "Conferencias"]
                ];

                var data_options = [
                    {
                        label: "Desde el servicio",
                        data: d1,
                        bars: {
                            order: 1
                        },
                        lines: {
                            lineWidth: 1
                        },
                        shadowSize: 0
                    },
                    {
                        label: "Desde Recepción",
                        data: d2,
                        bars: {
                            order: 2
                        },
                        lines: {
                            lineWidth: 1
                        },
                        shadowSize: 0
                    }
                ];
                var options = {
                    series: {
                        bars: {
                            show: true,
                            fill: 1,
                            lineWidth: 1
                        },
                        points: {
                            show: true,
                            radius: 3,
                            lineWidth: 1
                        }
                    },
                    bars: {
                        show: true,
                        barWidth: 0.3,
                        lineWidth: 1, // in pixels
                        shadowSize: 0,
                        align: 'center'
                    },
                    xaxis: {
                        tickDecimals: 0,
                        ticks: tick_options
                    },
                    yaxis: {
                        tickDecimals: 0
                    },
                    grid: {
                        hoverable: true,
                        clickable: true,
                        tickColor: "#eee",
                        borderColor: "#eee",
                        borderWidth: 1
                    },
                    colors: ["#727f36", "#335f1d"]
                };

                $.plot($("#chart_1_1"), data_options, options);

                var previousPoint = null;
                $("#chart_1_1").bind("plothover", function (event, pos, item) {
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));

                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;

                            $("#tooltip").remove();
                            var x = item.datapoint[0].valueOf(2),
                                y = item.datapoint[1].valueOf(2);

                            _showTooltip(item.pageX, item.pageY, item.series.label + ': ' + y);
                        }
                    } else {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });
                /***********************************************/

                /********Gráfico de Pastel*************/
                var pie = [];
                pie[0] = {
                    label: "Potenciales",
                    data: $.parseJSON(data).potenciales
                };
                pie[1] = {
                    label: "Temporales",
                    data: $.parseJSON(data).temporales
                };
                pie[2] = {
                    label: "Sin inscribir",
                    data: $.parseJSON(data).unclassified
                };
                $.plot($("#pie_chart_3"), pie, {
                    series: {
                        pie: {
                            show: true,
                            radius: 1,
                            label: {
                                show: true,
                                radius: 3 / 4,
                                formatter: function (label, series) {
                                    return '<div style="font-size:8pt;text-align:center;padding:2px;color:#ffedd2;">' + label + '<br/>' + series.data[0][1] + ' (' + Math.round(series.percent) + '%)</div>';
                                },
                                background: {
                                    opacity: 0.8
                                }
                            }
                        }
                    },
                    colors: ["#5bb136", "#edde34", "#3bb7f3"],
                    legend: {
                        show: false
                    }
                });
                /**************************************/
                var html = '';
                for (var i = 0; i < $.parseJSON(data).usuariosMAS.length; i++)
                    html = html + '<div class="row">' +
                        '<div class="col-md-12 user-info">' +
                        '<img class="img-responsive" src="../../../../metronic/assets/admin/layout/img/avatar.png" alt="">' +
                        '<div class="details">' +
                        '<div>' +
                        '<span><b>' + $.parseJSON(data).usuariosMAS[i].nombres + ' ' + $.parseJSON(data).usuariosMAS[i].apellidos + ' </b></span>' +
                        '<span class="label label-sm label-success label-mini">' + $.parseJSON(data).usuariosMAS[i].visitas + ' </span></div><div> &Uacute;ltima visita: ' + $.parseJSON(data).usuariosMAS[i].ultima + ' </div></div></div></div>';
                document.getElementById('MAS').innerHTML = html;

                if ($.parseJSON(data).totalCitas != undefined) {
                    $('#serv').find('li').remove();
                    var newLi = $('<li>' +
                        '<div class="col1">' +
                        '<div class="cont" style="margin-right: 0 !important;">' +
                        '<div class="cont-col1">' +
                        '<div class="label label-sm label-success">' +
                        '<i class="fa fa-2x fa-bookmark-o"></i>&nbsp;' +
                        '</div>' +
                        '</div>' +
                        '<div class="cont-col2">' +
                        '<div class="desc">' +
                        'Desde <b> Bibliografía</b>: ' +
                        '<ul id="listado_bibliografia" style="list-style: none; padding: 0">' +
                        '<li>- Se elaboraron un total de <b>' + $.parseJSON(data).totalCitas + '</b> citas bibliográficas en el período.</li>' +
                        '</ul>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</li>');
                    $('#serv').append(newLi);
                }
                if ($.parseJSON(data).navegacionMAS[0] != undefined && $.parseJSON(data).navegacionMAS[0] != '') {
                    newLi = $('<li>' +
                        '<div class="col1">' +
                        '<div class="cont" style="margin-right: 0 !important;">' +
                        '<div class="cont-col1">' +
                        '<div class="label label-sm label-success">' +
                        '<i class="fa fa-2x fa-desktop"></i>&nbsp;' +
                        '</div>' +
                        '</div>' +
                        '<div class="cont-col2">' +
                        '<div class="desc">' +
                        'Desde <b> Sala de Navegación</b>: ' +
                        '<ul id="listado_navegacion" style="list-style: none; padding: 0"></ul>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</li>');
                    $('#serv').append(newLi);
                    var navegacionMAS = $.parseJSON(data).navegacionMAS;
                    var newLi = $('<li>' +
                        '- La fuente de informaci&oacute;n m&aacute;s utilizada en el per&iacute;odo, fue <b>"' + navegacionMAS[0].descripcion + '"</b> con <b>' + navegacionMAS[0].cantidad + '</b> solicitud(es).</li>');
                    var newLi2 = $('<li>' +
                        '- Un total de <b>' + navegacionMAS[1][1] + '</b> usuarios declararon consultar solo correo.</li>');
                    $('#listado_navegacion').append(newLi);
                    $('#listado_navegacion').append(newLi2);


                }
                if ($.parseJSON(data).lecturaMAS[0] != undefined && $.parseJSON(data).lecturaMAS[0] != '') {
                    newLi = $('<li>' +
                        '<div class="col1">' +
                        '<div class="cont" style="margin-right: 0 !important;">' +
                        '<div class="cont-col1">' +
                        '<div class="label label-sm label-success">' +
                        '<i class="fa fa-2x fa-book"></i>&nbsp;' +
                        '</div>' +
                        '</div>' +
                        '<div class="cont-col2">' +
                        '<div class="desc">' +
                        'Desde <b> Sala de Lectura</b>: ' +
                        '<ul id="listado_lectura" style="list-style: none; padding: 0"></ul>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</li>');
                    $('#serv').append(newLi);
                    var lecturaMAS = $.parseJSON(data).lecturaMAS;
                    for (var j = 0; j < lecturaMAS.length; j++) {
                        if (lecturaMAS[j].tipo != undefined)
                            var newLi2 = $('<li>' +
                                '- El recurso  <b>' + lecturaMAS[j].tipo + '</b> m&aacute;s solicitado de la modalidad <b>' + lecturaMAS[j].descripcion +
                                '</b> en el per&iacute;odo, fue <b>"' + lecturaMAS[j].detalle + '"</b> con <b>' + lecturaMAS[j].cantidad + '</b> solicitud(es).</li>');
                        else if (lecturaMAS[j].cantidad > 1)
                            var newLi2 = $('<li>' +
                                '- La modalidad <b>' + lecturaMAS[j].descripcion +
                                '</b> fue empleada en <b>' + lecturaMAS[j].cantidad + '</b> ocasiones en el período.</li>');
                        else
                            var newLi2 = $('<li>' +
                                '- La modalidad <b>' + lecturaMAS[j].descripcion +
                                '</b> fue empleada en <b>' + lecturaMAS[j].cantidad + '</b> ocasión en el período.</li>');
                        $('#listado_lectura').append(newLi2);
                    }

                }
            },
            error: function () {
                //alert("error");
            }
        });

    };

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
            selectable: false,
            selectHelper: false,
            dayClick: function (date, jsEvent, view) {
                calendar.fullCalendar('changeView', 'agendaDay');
                calendar.fullCalendar('gotoDate', date);
            },
            eventRender: function (event, element) {
                var content = (event.description && '<p><b>Descripción:</b> ' + event.description + '</p>' || '') +
                    (event.allDay && '<p><b>Evento de todo el día</b></p>' ||
                    '<p><b>Comienza:</b> ' + event.start.format('DD/MM/YYYY HH:mm:ss') + '<br />' +
                    (event.end && '<p><b>Termina:</b> ' + event.end.format('DD/MM/YYYY HH:mm:ss') + '</p>' || ''));
                element.qtip({
                    content: {
                        text: content,
                        title: event.title
                    },
                    position: {
                        my: 'center left',  // Position my top left...
                        at: 'center right', // at the bottom right of...
                        target: element // my target
                    },
                    style: 'qtip-bootstrap'
                });
            },
            theme: false,
            timezone: 'America/Havana',
            buttonIcons: {
                prev: 'left-single-arrow',
                next: 'right-single-arrow',
                prevYear: 'left-double-arrow',
                nextYear: 'right-double-arrow'
            }, // show the prev/next text
            editable: false,
            eventLimit: true, // allow "more" link when too many events
            lang: 'es'
        });
    };

    var _initInteractiveChart = function () {
        $.ajax({
            url: Routing.generate('otros_interactive_chart'),
            type: 'POST',
            contentType: 'application/json; charset=utf-8',
            success: function (data) {
                data = $.parseJSON(data);
                var months = [];
                for (var i = 0; i < data['recepcion'].length; i++) {
                    switch (data['recepcion'][i][0]) {
                        case 1:
                            months.push([1, "Enero"]);
                            break;
                        case 2:
                            months.push([2, "Febrero"]);
                            break;
                        case 3:
                            months.push([3, "Marzo"]);
                            break;
                        case 4:
                            months.push([4, "Abril"]);
                            break;
                        case 5:
                            months.push([5, "Mayo"]);
                            break;
                        case 6:
                            months.push([6, "Junio"]);
                            break;
                        case 7:
                            months.push([7, "Julio"]);
                            break;
                        case 8:
                            months.push([8, "Agosto"]);
                            break;
                        case 9:
                            months.push([9, "Septiembre"]);
                            break;
                        case 10:
                            months.push([10, "Octubre"]);
                            break;
                        case 11:
                            months.push([11, "Noviembre"]);
                            break;
                        case 12:
                            months.push([12, "Diciembre"]);
                            break;
                    }
                }
                $.plot($("#chart_2"),
                    [{
                        data: data['referencia'],
                        label: "Referencia",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }, {
                        data: data['bibliografia'],
                        label: "Bibliografia",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }, {
                        data: data['sala_de_lectura'],
                        label: "Sala de Lectura",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }, {
                        data: data['sala_de_navegacion'],
                        label: "Sala de Navegación",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }, {
                        data: data['dsi'],
                        label: "DSI",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }, {
                        data: data['cursos'],
                        label: "Cursos",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }, {
                        data: data['conferencias'],
                        label: "Conferencias",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }, {
                        data: data['recepcion'],
                        label: "Total",
                        lines: {
                            lineWidth: 2
                        },
                        shadowSize: 1

                    }
                    ], {
                        series: {
                            lines: {
                                show: true,
                                lineWidth: 2,
                                fill: true,
                                fillColor: {
                                    colors: [{
                                        opacity: 0.05
                                    }, {
                                        opacity: 0.01
                                    }
                                    ]
                                }
                            },
                            points: {
                                show: true,
                                radius: 3,
                                lineWidth: 1
                            },
                            shadowSize: 2
                        },
                        grid: {
                            hoverable: true,
                            clickable: true,
                            tickColor: "#eee",
                            borderColor: "#eee",
                            borderWidth: 1
                        },
                        xaxis: {
                            ticks: months,
                            tickDecimals: 0,
                            tickColor: "#eee"
                        },
                        yaxis: {
                            ticks: 40,
                            tickDecimals: 0,
                            tickColor: "#eee"
                        }
                    });
                var previousPoint = null;
                $("#chart_2").bind("plothover", function (event, pos, item) {
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));

                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;

                            $("#tooltip").remove();
                            var x = item.datapoint[0].valueOf(2),
                                y = item.datapoint[1].valueOf(2);

                            _showTooltip(item.pageX, item.pageY, y);
                        }
                    } else {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });
            }
        })
    };

    return {
        showTooltip: function (x, y, contents) {
            _showTooltip(x, y, contents)
        },

        initDashboardDaterangeLocal: function (x, y, contents) {
            _initDashboardDaterangeLocal(x, y, contents)
        },

        apply: function (x, y, contents) {
            _apply(x, y, contents)
        },

        initCalendar: function (x, y, contents) {
            _initCalendar(x, y, contents)
        },

        initInteractiveChart: function (x, y, contents) {
            _initInteractiveChart(x, y, contents)
        }
    }
}();