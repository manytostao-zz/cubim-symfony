/**
 * Created by manytostao on 28/05/15.
 */
'use strict';

/*
 * show.bs.modal
 * shown.bs.modal
 * hide.bs.modal
 * hidden.bs.modal
 *
 * */
var BibliographyDatatables = function () {
    var _initAutoServiceUsersListDatatable = function () {
        var table = $('#autoServiceUsersListDatatable');

        var oTable = table.dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": Routing.generate('usuario_ajax_listado', {'from': 'autoservicio'}),
                "type": "POST"
            },
            "drawCallback": function (settings) {
                table.find('tbody tr').each(function () {
                    var aData = oTable.fnGetData(this);
                    if (aData != null) {
                        var nCloneTd = document.createElement('td');
                        nCloneTd.style.textAlign = "center";
                        nCloneTd.innerHTML = '' +
                            '<a title="Realizar solicitud" href="' + Routing.generate('bibliografia_editar_solicitud', {
                                id: "null",
                                userId: aData[0]
                            }) + '">' +
                            '<div class="btn blue-hoki btn-small">' +
                            '<i class="fa fa-bookmark-o"></i>' +
                            '</div>' +
                            '</a>';
                        this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[this.childNodes.length]);
                    }
                });
            },
            "columnDefs": [{
                "visible": false,
                "targets": [0, 3]
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
//                "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable
            "pagingType": "full_numbers"
        });

        var tableWrapper = $('#autoServiceUsersListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        var tableColumnToggler = $('#autoServiceUsersListDatatable_column_toggler');

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

        /* handle show/hide columns*/
        $('input[type="checkbox"]', tableColumnToggler).change(function () {
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            oTable.fnSetColumnVis(iCol, ($(this).attr("checked") == "checked"));

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
                "url": Routing.generate('usuario_ajax_listado', {'from': 'bibliografia'}),
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
                                modulo: 'bibliografia',
                                id: aData[0]
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

    var _initRequestsListDatatable = function (from) {
        var table = $('#requestsListDatatable');
        /* Table tools samples: https://www.datatables.net/release-datatables/extras/TableTools/ */

        /* Set tabletools buttons and button container */

        $.extend(true, $.fn.DataTable.TableTools.classes, {
            "container": "btn-group tabletools-dropdown-on-portlet-refe",
            "buttons": {
                "normal": "btn btn-sm default",
                "disabled": "btn btn-sm default disabled"
            }
        });

        var url;
        if (from != null)
            url = Routing.generate('bibliografia_ajax_listado', {from: from});
        else
            url = Routing.generate('bibliografia_ajax_listado');
        var oTable = table.dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": url,
                "type": "POST"
            },
            "drawCallback": $requestsListDatatable.postDraw,
            "columnDefs": [{
                "visible": false,
                "targets": [0, 2, 4]
            }, {
                "orderable": false,
                "targets": [7, 8]
            }, {
                "class": "column-right",
                "targets": [5, 9]
            }
            ],
            "order": [
                [1, 'desc']
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
                    "mColumns": [1, 2, 3, 4, 5, 6, 7]
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
        $requestsListDatatable.prePostConfigure(table, oTable);
    };

    var $requestsListDatatable = {};

    $requestsListDatatable.postDraw = function () {
        var $tr = $('#requestsListDatatable').find('tbody tr');
        $tr.each(function () {
            var aData = $('#requestsListDatatable').dataTable().fnGetData(this);
            if (aData != null) {
                var $column = $('<td align="center"></td>');

                $column.width(142);
                var $row = $('<div style="width: 142px"></div>');

                //Botones
                var $btnEditar = $('<a title="Editar Solicitud" href="#request"></a>');
                var $innerDiv1 = $('<div class="btn blue-hoki btn-small"></div>');
                $innerDiv1.append($('<i class="fa fa-edit"></i>'));
                $btnEditar.append($innerDiv1);
                $btnEditar.attr('data-id', aData != null ? aData[0] : null);
                $btnEditar.attr('data-active', aData != null ? aData[2] : null);
                $btnEditar.attr('data-usuario', aData != null ? aData[1].split("/")[5] : null);
                $btnEditar.attr('data-tema', aData != null ? aData[3] : null);
                $btnEditar.attr('data-motivo', aData != null ? aData[4] : null);
                $btnEditar.attr('data-annos', aData != null ? aData[5] : null);
                $btnEditar.attr('data-estilo', aData != null ? aData[6] : null);
                $btnEditar.attr('data-idiomas', aData != null ? aData[7] : null);
                $btnEditar.attr('data-tiposDoc', aData != null ? aData[8] : null);
                var $parent = $(this).closest('tr');
                var $text = $parent.find('.nom-description');
                $btnEditar.attr('data-name', $text.html());

                var $btnEliminar = $('<a title="Eliminar" href="#delete" data-toggle="modal"></a>');
                var $innerDiv2 = $('<div class="btn blue-hoki btn-small"></div>');
                $innerDiv2.append($('<i class="fa fa-times"></i>'));
                $btnEliminar.append($innerDiv2);
                $btnEliminar.attr('data-toggle', 'modal');
                $btnEliminar.attr('data-target', '#confirm');
                $btnEliminar.attr('data-id', aData != null ? aData[0] : null);

                var $btnResponder = $('<a title="Responder" href="#answer" data-toggle="modal"></a>');
                var $innerDiv3 = $('<div class="btn blue-hoki btn-small"></div>');
                $innerDiv3.append($('<i class="fa fa-reply"></i>'));
                $btnResponder.append($innerDiv3);
                $btnResponder.attr('data-toggle', 'modal');
                $btnResponder.attr('data-target', '#confirm');
                $btnResponder.attr('data-id', aData != null ? aData[0] : null);

                $row.append($btnEditar).append($btnEliminar).append($btnResponder);
                $column.append($row);
                $(this).append($column);

                var nCloneTd = document.createElement('td');
                nCloneTd.innerHTML = '<span class="row-details row-details-close"></span>';

                this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[0]);

                //Eventos
                //Editar Solicitud
                $btnEditar.click(function () {
                    $('#formBibliografia_id').val($(this).attr('data-id'));
                    $('#formBibliografia_usuario').val($(this).attr('data-usuario'));
                    $('#formBibliografia_tema').val($(this).attr('data-tema'));
                    $('#formBibliografia_fechaDesde').val($(this).attr('data-annos').substr(0, 4));
                    $('#formBibliografia_fechaDesde').datepicker({
                        rtl: Metronic.isRTL(),
                        orientation: "left",
                        autoclose: true,
                        startView: 2,
                        minViewMode: 2,
                        format: 'yyyy',
                        clearBtn: true
                    });
                    $('#formBibliografia_fechaHasta').val($(this).attr('data-annos').substr(5, 8));
                    $('#formBibliografia_fechaHasta').datepicker({
                        rtl: Metronic.isRTL(),
                        orientation: "left",
                        autoclose: true,
                        startView: 2,
                        minViewMode: 2,
                        format: 'yyyy',
                        clearBtn: true
                    });

                    var idiomas = $(this).attr('data-idiomas').split(',');
                    for (var j = 0; j < document.getElementsByName('formBibliografia[idiomas][]').length; j++) {
                        var elem = $('#formBibliografia_idiomas_' + j);
                        $(elem).prop("checked", false);
                        $(elem).parent().removeClass("checked");
                        for (var i = 0; i < idiomas.length; i++) {
                            if ($(elem).parent().parent().parent().text().trim() == idiomas[i].trim()) {
                                $(elem).prop("checked", true);
                                $(elem).parent().addClass("checked");
                                break;
                            }
                        }

                    }

                    var tiposDoc = $(this).attr('data-tiposDoc').split(',');
                    for (var j = 0; j < document.getElementsByName('formBibliografia[tiposDocs][]').length; j++) {
                        var elem = $('#formBibliografia_tiposDocs_' + j);
                        $(elem).prop("checked", false);
                        $(elem).parent().removeClass("checked");
                        for (var i = 0; i < tiposDoc.length; i++) {
                            if ($(elem).parent().parent().parent().text().trim() == tiposDoc[i].trim()) {
                                $(elem).prop("checked", true);
                                $(elem).parent().addClass("checked");
                                break;
                            }
                        }

                    }

                    var estilo = $(this).attr('data-estilo');
                    for (var li = 0; li < document.getElementById("formBibliografia_estilo").childNodes.length; li = li + 1) {
                        var opt = document.getElementById("formBibliografia_estilo").childNodes[li];
                        if (opt.firstChild != null && opt.firstChild.data == estilo.trim()) {
                            opt.selected = true;
                        }
                    }

                    var motivo = $(this).attr('data-motivo');
                    for (var li = 0; li < document.getElementById("formBibliografia_motivo").childNodes.length; li = li + 1) {
                        var opt = document.getElementById("formBibliografia_motivo").childNodes[li];
                        if (opt.firstChild != null && opt.firstChild.data == motivo.trim()) {
                            opt.selected = true;
                        }
                    }

                    Global.initSelects();
                    var $modalView = $('#request');
                    $modalView.modal();

                });
                //Eliminar Bibliografía
                $btnEliminar.click(function () {
                    var id = $(this).attr('data-id');
                    $('#eliminar').attr('href', Routing.generate('bibliografia_eliminar_solicitud', {id: id}));
                    var $modalView = $('#delete');
                    $modalView.modal();
                });

                //Responder Bibliografía
                $btnResponder.click(function () {
                    $('.delete_fuentesInfo').click();
                    var $collectionHolder = $('ul.fuentesInfo');
                    $collectionHolder.data('index', 1);
                    $('#formBiblioRespuesta_bibliografia').val(aData[0]);
                    $('#formBiblioRespuesta_id').val("");
                    $('#formBiblioRespuesta_descriptores').val("");
                    $('#formBiblioRespuesta_citasRelevantes').val("");
                    $('#formBiblioRespuesta_citasPertinentes').val("");
                    $('#formBiblioRespuesta_citas').val("");
                    $('#formBiblioRespuesta_observaciones').val("");
                    var $modalView = $('#answer');
                    $modalView.modal();
                });
            }
        });
    };

    $requestsListDatatable.prePostConfigure = function (table, oTable) {
        var tableWrapper = $('#requestsListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        var tableColumnToggler = $('#requestsListDatatable_column_toggler');

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

        /* handle show/hide columns*/
        $('input[type="checkbox"]', tableColumnToggler).change(function () {
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            oTable.fnSetColumnVis(iCol, ($(this).attr("checked") == "checked"));

            var nCloneTh = document.createElement('th');
            nCloneTh.className = "table-checkbox";

            table.find('thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[0]);
            });

            nCloneTh = document.createElement('th');
            nCloneTh.style.width = '95px';
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
        nCloneTh.className = "table-checkbox";

        table.find('thead tr').each(function () {
            this.insertBefore(nCloneTh, this.childNodes[0]);
        });


        nCloneTh = document.createElement('th');
        nCloneTh.align = "center";
        nCloneTh.style.width = '95px';
        nCloneTh.innerHTML = "<strong>Operaciones</strong>";
        nCloneTh.style.textAlign = "center";

        table.find('thead tr').each(function () {
            this.insertBefore(nCloneTh, this.childNodes[this.childNodes.length]);
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
                    url: Routing.generate('bibliografia_respuestas', {'id': param}),
                    contentType: 'application/json; charset=utf-8',
                    success: function (data) {
                        var childTable = $('<table></table>');
                        childTable.addClass('table table-striped table-bordered table-hover dataTable no-footer respuesta');
                        var childTHead = $('<thead></thead>');
                        var childTRTHead = $('<tr></tr>');
                        childTRTHead.append($('<th><strong>Descriptores</strong></th>'));
                        childTRTHead.append($('<th><strong>Respondido por</strong></th>'));
                        childTRTHead.append($('<th><strong>Fuentes de Informaci&oacute;n</strong></th>'));
                        childTRTHead.append($('<th><strong>Citas Relevantes</strong></th>'));
                        childTRTHead.append($('<th><strong>Citas Pertinentes</strong></th>'));
                        childTRTHead.append($('<th><strong>Fecha Respuesta</strong></th>'));
                        childTRTHead.append($('<th align="center" style="text-align: center"><strong>Operaciones</strong></th>'));
                        childTHead.append(childTRTHead);
                        childTable.append(childTHead);
                        var childTBody = $('<tbody></tbody>');
                        if (data.length > 2) {
                            var formattedData = JSON.parse(data);
                            for (var i = 0; i < formattedData.data.length; i++) {
                                var childTRTBody = $('<tr></tr>');
                                childTRTBody.append($('<td>' + formattedData.data[i].descriptores + '</td>'));
                                childTRTBody.append($('<td>' + formattedData.data[i].respondidoPor + '</td>'));
                                childTRTBody.append($('<td>' + formattedData.data[i].fuentesInfo + '</td>'));
                                childTRTBody.append($('<td>' + formattedData.data[i].citasRelevantes + '</td>'));
                                childTRTBody.append($('<td>' + formattedData.data[i].citasPertinentes + '</td>'));
                                childTRTBody.append($('<td>' + formattedData.data[i].fechaRespuesta + '</td>'));

                                var $column = $('<td align="center"></td>');

                                $column.width(142);
                                var $row = $('<div style="width: 142px"></div>');

                                //Botones

                                var $btnEditar = $('<a title="Editar Respuesta" href="#answer"></a>');
                                var $innerDiv1 = $('<div class="btn blue-hoki btn-small"></div>');
                                $innerDiv1.append($('<i class="fa fa-edit"></i>'));
                                $btnEditar.append($innerDiv1);
                                $btnEditar.attr('data-bibliografia', formattedData.data[i].bibliografia);
                                $btnEditar.attr('data-descriptores', formattedData.data[i].descriptores);
                                $btnEditar.attr('data-fuentesInfo', formattedData.data[i].fuentesInfo);
                                $btnEditar.attr('data-citasRelevantes', formattedData.data[i].citasRelevantes);
                                $btnEditar.attr('data-citasPertinentes', formattedData.data[i].citasPertinentes);
                                $btnEditar.attr('data-citas', formattedData.data[i].citas);
                                $btnEditar.attr('data-observaciones', formattedData.data[i].observaciones);
                                $btnEditar.attr('data-id', formattedData.data[i].id != null ? formattedData.data[i].id : null);

                                var $btnCitas = $('<a title="Ver Citas" href="#citas"></a>');
                                var $innerDiv2 = $('<div class="btn blue-hoki btn-small"></div>');
                                $innerDiv2.append($('<i class="fa fa-list"></i>'));
                                $btnCitas.append($innerDiv2);
                                $btnCitas.attr('data-citas', formattedData.data[i].citas);
                                $btnCitas.attr('data-observaciones', formattedData.data[i].observaciones);
                                $btnCitas.attr('data-id', formattedData.data[i].id != null ? formattedData.data[i].id : null);

                                var $btnModelo = $('<a title="Exportar Modelo" href="' + Routing.generate('bibliografia_exportar_modelo', {id: formattedData.data[i].id}) + '"></a>');
                                var $innerDiv3 = $('<div class="btn blue-hoki btn-small"></div>');
                                $innerDiv3.append($('<i class="fa fa-file-pdf-o"></i>'));
                                $btnModelo.append($innerDiv3);
                                $btnModelo.attr('data-id', formattedData.data[i].id != null ? formattedData.data[i].id : null);

                                $row.append($btnEditar).append($btnCitas).append($btnModelo);
                                $column.append($row);
                                childTRTBody.append($column);
                                childTBody.append(childTRTBody);

                                $btnCitas.click(function () {
                                    var id = $(this).attr('data-id');
                                    $('#memoCitas').val($(this).attr('data-citas'));
                                    $('#memoObs').val($(this).attr('data-observaciones'));
                                    var numCitas = 0;
                                    var citas = $(this).attr('data-citas').split("\n");
                                    for (var i = 0; i < citas.length; i++)
                                        if (citas[i].trim() != "")
                                            numCitas += 1;
                                    $('#numCitas').text(numCitas);
                                    var $modalView = $('#citas');
                                    $modalView.modal();
                                });

                                //Responder Bibliografía
                                $btnEditar.click(function () {
                                    $('.delete_fuentesInfo').click();
                                    var $collectionHolder = $('ul.fuentesInfo');
                                    $collectionHolder.data('index', 1);
                                    $('#formBiblioRespuesta_bibliografia').val($(this).attr('data-bibliografia'));
                                    $('#formBiblioRespuesta_id').val($(this).attr('data-id'));
                                    $('#formBiblioRespuesta_descriptores').val($(this).attr('data-descriptores'));
                                    $('#formBiblioRespuesta_citasRelevantes').val($(this).attr('data-citasRelevantes'));
                                    $('#formBiblioRespuesta_citasPertinentes').val($(this).attr('data-citasPertinentes'));
                                    $('#formBiblioRespuesta_citas').val($(this).attr('data-citas'));
                                    $('#formBiblioRespuesta_observaciones').val($(this).attr('data-observaciones'));
                                    var fuentes = $(this).attr('data-fuentesInfo').split(',');
                                    var $modalView = $('#answer');
                                    $modalView.modal();
                                    for (var i = 1; i < fuentes.length + 1; i++) {
                                        $('.add_fuentesInfo_link').click();
                                        var id = "formBiblioRespuesta_fuentesInfo_" + i + "_id";
                                        for (var li = 0; li < document.getElementById(id).childNodes.length; li = li + 1) {
                                            var opt = document.getElementById(id).childNodes[li];
                                            if (opt.firstChild != null && opt.firstChild.data == fuentes[i - 1].trim()) {
                                                opt.selected = true;
                                            }
                                        }
                                        $('#' + "formBiblioRespuesta_fuentesInfo_" + i + "_id").select2({
                                            allowClear: true,
                                            class_name: 'form-control'
                                        });
                                    }

                                    $('#cancelButton').click(function () {
                                        $('.delete_fuentesInfo').click();
                                        var $collectionHolder = $('ul.fuentesInfo');
                                        $collectionHolder.data('index', 1);
                                    });

                                    $('#submitButton').click(function () {
                                        document.forms[3].submit();
                                    });

                                });
                            }
                            childTable.append(childTBody);
                            var newTR = $('<tr class="childTable"></tr>');

                            var vCols = table.find('th').length;

                            var newTD = $('<td colspan="' + vCols + '" style="background-color: #dfdfdf"></td>');
                            newTD.append($('<strong>N&uacute;mero de respuestas: </strong> ' + i + '<br />'));
                            newTD.append(childTable);
                            newTR.append(newTD);
                            if (aData != undefined) {
                                $(nTr).after(newTR);
                            } else {
                                $(nTr).after('<tr><td colspan="2">' + childTable + "</td></tr>");
                            }
                            $('.respuesta').dataTable().fnDestroy();
                            $('.respuesta').dataTable({
                                destroy: true,
                                paging: false,
                                searching: false,
                                "columnDefs": [{
                                    "orderable": false,
                                    "targets": [6]
                                }
                                ],
                                "order": [
                                    [1, 'desc']
                                ],
                                "language": {
                                    "sZeroRecords": "",
                                    "sEmptyTable": "",
                                    "sInfo": ""
                                }
                            });

                        }

                    }
                });
            }
        });
    };

    return {
        initAutoServiceUsersListDatatable: function () {
            _initAutoServiceUsersListDatatable()
        },

        initUsersListDatatable: function () {
            _initUsersListDatatable()
        },

        initRequestsListDatatable: function (from) {
            _initRequestsListDatatable(from)
        }
    }
}();