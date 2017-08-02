/**
 * Created by osmany.torres on 12/10/15.
 */
var LecturaDatatables = function () {
    var _initUsersListDatatable = function () {
        var table = $('#usersListDatatable');
        /* Table tools samples: https://www.datatables.net/release-datatables/extras/TableTools/ */

        /* Set tabletools buttons and button container */

        $.extend(true, $.fn.DataTable.TableTools.classes, {
            "container": "btn-group tabletools-dropdown-on-portlet",
            "buttons": {
                "normal": "btn btn-sm default",
                "disabled": "btn btn-sm default disabled"
            }
        });

        var oTable = table.DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": Routing.generate('usuario_ajax_listado', {'from': 'lectura'}),
                "type": "POST"
            },
            "drawCallback": function (settings) {
                table.find('tbody tr').each(function () {
                    var aData = oTable.row(this).data();
                    if (aData != undefined)
                        if (aData[22] == true) {
                            aData[22] = '<i class="fa fa-check fa-success"></i>';
                        } else {
                            aData[22] = '<i class="fa fa-ban fa-danger"></i>';
                        }
                    if (aData != null) {
                        var nCloneTd = document.createElement('td');
                        nCloneTd.style.textAlign = "center";
                        nCloneTd.innerHTML = '' +
                            '<a title="Detalles" href="' + Routing.generate('usuario_detalles', {
                                id: aData[0],
                                modulo: 'lectura'
                            }) + '">' +
                            '<div class="btn blue-hoki btn-small">' +
                            '<i class="fa fa-search"></i>' +
                            '</div>' +
                            '</a>';
                        this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[this.childNodes.length]);
                    }
                    oTable.row(this).data(aData);
                });
            },

            "columnDefs": [{
                "visible": false,
                "targets": [0, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22]
            }, {
                "class": "column-right",
                "targets": [5]
            }, {
                "class": "column-center",
                "targets": [3, 4, 9, 22, 23]
            }, {
                "searchable": false,
                "targets": [0, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22]
            }
            ],
            "order": [
                [2, 'asc']
            ],
            "lengthMenu": [
                [5, 10, 15, 20, -1],
                [5, 10, 15, 20, "Todos"] // change per page values here
            ],
//                "stateSave": true,
            "language": {
                "lengthMenu": " _MENU_ registros",
                "sSearch": "Buscar",
                "sZeroRecords": "No se encontraron registros",
                "sEmptyTable": "No hay registros disponibles: cambie los criterios de filtrado",
                "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "oPaginate": {
                    sFirst: "<i class='fa fa-backward' title='Primero'></i>",
                    sLast: "<i class='fa fa-forward' title='Último'></i>",
                    sNext: "<i class='fa fa-caret-right' title='Siguiente'></i>",
                    sPrevious: "<i class='fa fa-caret-left' title='Anterior'></i>"
                },
                "sPaginationType": "full_numbers",
                "sProcessing": "Procesando..."
            },
            // set the initial value
            "pageLength": 10,
            "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable
            "pagingType": "full_numbers",
            "tableTools": {
                "sSwfPath": "{{ asset('metronic/assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf') }}",
                "aButtons": [{
                    "sExtends": "print",
                    "sButtonText": "Imprimir",
                    "sInfo": 'Presione "CTRL+P" para imprimir o "ESC" para regresar',
                    "sMessage": "Generado por CUBiM",
                    "mColumns": "visible"
                }]
            }
        });

        var tableWrapper = $('#usersListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        var tableColumnToggler = $('#usersListDatatable_column_toggler');
        tableColumnToggler.css("min-height", "595px");

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown
        /* handle show/hide columns*/
        $('input[type="checkbox"]', tableColumnToggler).change(function () {
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            oTable.column(iCol).visible($(this).attr("checked") == "checked");

            var nCloneTh = document.createElement('th');
            nCloneTh.style.width = '147px';
            nCloneTh.innerHTML = "<strong>Operaciones</strong>";
            nCloneTh.style.textAlign = "center";

            table.find('thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[this.childNodes.length]);
            });
        });

        /*
         * Insert a 'details' column to the table
         */
        var nCloneTh = document.createElement('th');
        nCloneTh.style.width = '147px';
        nCloneTh.innerHTML = "<strong>Operaciones</strong>";
        nCloneTh.style.textAlign = "center";

        table.find('thead tr').each(function () {
            this.insertBefore(nCloneTh, this.childNodes[this.childNodes.length]);
        });
    };

    var _initEntriesListDatatable = function () {
        var table = $('#entriesListDatatable');
        /* Table tools samples: https://www.datatables.net/release-datatables/extras/TableTools/ */

        /* Set tabletools buttons and button container */

        $.extend(true, $.fn.DataTable.TableTools.classes, {
            "container": "btn-group tabletools-dropdown-on-portlet-refe",
            "buttons": {
                "normal": "btn btn-sm default",
                "disabled": "btn btn-sm default disabled"
            }
        });

        var oTable = table.dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": Routing.generate('lectura_ajax_listado'),
                "type": "POST"
            },
            "drawCallback": function () {
                var $tr = $('#entriesListDatatable').find('tbody tr');
                $tr.each(function () {
                    var nCloneTd = document.createElement('td');
                    nCloneTd.innerHTML = '<span class="row-details row-details-close"></span>';

                    this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);
                })
            },
            "columnDefs": [{
                "visible": false,
                "targets": [0]
            }, {
                "orderable": false,
                "targets": [0]
            },
                {"width": "40%", "targets": 1}
            ],
            "order": [
                [2, 'desc']
            ],
            "lengthMenu": [
                [5, 10, 15, 20, -1],
                [5, 10, 15, 20, "Todos"] // change per page values here
            ],
            "language": {
                "lengthMenu": " _MENU_ registros",
                "sSearch": "Buscar",
                "sZeroRecords": "No se encontraron registros",
                "sEmptyTable": "No hay registros disponibles: cambie los criterios de filtrado",
                "sInfo": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando 0 a 0 de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "oPaginate": {
                    sFirst: "Primero",
                    sLast: "Último",
                    sNext: "Siguiente",
                    sPrevious: "Anterior"
                },
                "sPaginationType": "full_numbers",
                "sProcessing": "Procesando..."
            },
            // set the initial value
            "pageLength": 10,
            "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

            "tableTools": {
                "sSwfPath": "{{ asset('metronic/assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf') }}",
                "aButtons": [{
                    "sExtends": "pdf",
                    "sButtonText": "PDF",
                    "sPdfMessage": "Listado de Entradas/Salidas",
                    "mColumns": [1, 2, 3, 4]
                }, {
                    "sExtends": "xls",
                    "sButtonText": "Excel"
                }, {
                    "sExtends": "print",
                    "sButtonText": "Imprimir",
                    "sInfo": 'Presione "CTRL+P" para imprimir o "ESC" para regresar',
                    "sMessage": "Generado por CUBiM"
                }, {
                    "sExtends": "copy",
                    "sButtonText": "Copiar"
                }]
            }
        });
        entriesListDatatable.prePostConfigure(table, oTable);
    };

    var entriesListDatatable = {};

    entriesListDatatable.prePostConfigure = function (table, oTable) {
        var tableWrapper = $('#entriesListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

        /*
         * Insert a 'details' column to the table
         */

        var nCloneTh = document.createElement('th');
        nCloneTh.className = "table-checkbox";

        table.find('thead tr').each(function () {
            this.insertBefore(nCloneTh, this.childNodes[0]);
        });

        table.on('click', ' tbody td .row-details', function () {
            var nTr = $(this).parents('tr')[0];
            var aData = oTable.fnGetData(nTr);
            var param;
            if (aData == undefined) {
                param = $($(nTr).find("td")[0]).text();
            } else {
                param = aData[0];
            }
            if ($(this).hasClass("row-details-open")) {
                /* This row is already open - close it */
                $(this).addClass("row-details-close").removeClass("row-details-open");
                if (aData != undefined && $(nTr).next().hasClass('childTable')) {
                    $(nTr).next().remove();
                }
            } else {
                /* Open this row */
                var childTable = "";
                $(this).addClass("row-details-open").removeClass("row-details-close");

                $.ajax({
                    url: Routing.generate('lectura_ajax_detalle', {'id': param}),
                    contentType: 'application/json; charset=utf-8',
                    success: function (data) {
                        var childTable = $('<table></table>');
                        childTable.addClass('table table-striped table-bordered table-hover dataTable no-footer detalle');
                        var childTHead = $('<thead></thead>');
                        var childTRTHead = $('<tr></tr>');
                        childTRTHead.append($('<th><strong>Modalidad</strong></th>'));
                        childTRTHead.append($('<th class="hidden"><strong>Agrupado por modalidades</strong></th>'));
                        childTHead.append(childTRTHead);
                        childTable.append(childTHead);
                        var childTBody = $('<tbody></tbody>');
                        if (data.length > 2) {
                            var formattedData = JSON.parse(data);
                            for (var i = 0; i < formattedData.data.length; i++) {
                                var childTRTBody = $('<tr></tr>');
                                childTRTBody.append($('<td>' + formattedData.data[i].modalidad + '</td>'));
                                if (formattedData.data[i].modalidad != "Autoestudio") {
                                    if (formattedData.data[i].modalidad != 'PC')
                                        childTRTBody.append($('<td>' + formattedData.data[i].tipo + ': ' + formattedData.data[i].detalle + ' </td>'));
                                    else
                                        childTRTBody.append($('<td> Número: ' + formattedData.data[i].detalle + ' </td>'));
                                } else {
                                    childTRTBody.append($('<td></td>'));
                                }
                                childTBody.append(childTRTBody);

                            }
                            childTable.append(childTBody);
                            var newTR = $('<tr class="childTable"></tr>');

                            var vCols = table.find('th').length;

                            var newTD = $('<td colspan="' + vCols + '" style="background-color: #dfdfdf"></td>');
                            newTD.append(childTable);
                            newTR.append(newTD);
                            if (aData != undefined) {
                                $(nTr).after(newTR);
                            } else {
                                $(nTr).after('<tr><td colspan="2">' + childTable + "</td></tr>");
                            }
                            $('.detalle').dataTable().fnDestroy();
                            var dt = $('.detalle').DataTable({
                                "drawCallback": function (settings) {
                                    var api = this.api();
                                    var rows = api.rows({page: 'current'}).nodes();
                                    var last = null;

                                    api.column(0, {page: 'current'}).data().each(function (group, i) {
                                        if (last !== group) {
                                            $(rows).eq(i).before(
                                                '<tr class="group"><td colspan="3">' + group + '</td></tr>'
                                            );

                                            last = group;
                                        }
                                    });
                                },
                                destroy: true,
                                paging: false,
                                searching: false,
                                "order": [
                                    [0, 'asc']
                                ],
                                "language": {
                                    "sZeroRecords": "",
                                    "sEmptyTable": "",
                                    "sInfo": ""
                                },
                                "columnDefs": [{
                                    "visible": false,
                                    "targets": [0]
                                }]
                            });
                            $('.detalle tbody').on('click', 'tr.group', function () {
                                var currentOrder = dt.order()[0];
                                if (currentOrder != undefined && currentOrder[0] === 0 && currentOrder[1] === 'asc') {
                                    dt.order([0, 'desc']).draw();
                                }
                                else {
                                    dt.order([0, 'asc']).draw();
                                }
                            });

                        }

                    }
                });
            }
        });
    };

    return {
        initUsersListDatatable: function () {
            _initUsersListDatatable()
        },

        initEntriesListDatatable: function () {
            _initEntriesListDatatable()
        }
    }
}();