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
var AppUsersDatatables = function () {
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
            "ajax": Routing.generate('appusers_ajax_listado'),
            "drawCallback": function (settings) {
                table.find('tbody tr').each(function () {
                    var aData = oTable.row(this).data();
                    if (aData != null) {
                        var nCloneTd = document.createElement('td');
                        nCloneTd.style.width = "0px";
                        if (aData[5] == true) {
                            nCloneTd.innerHTML = '' +
                                '<div style="width: 147px">' +
                                '<a title="Editar" href="' + Routing.generate('appuser_edit', {id: aData[0]}) + '">' +
                                '<div class="btn blue-hoki btn-small">' +
                                '<i class="fa fa-edit"></i>' +
                                '</div>' +
                                '</a>' +
                                '<a title="Detalles" href="' + Routing.generate('appuser_details', {id: aData[0]}) + '">' +
                                '<div class="btn blue-hoki btn-small">' +
                                '<i class="fa fa-search"></i>' +
                                '</div>' +
                                '</a>' +
                                '<a title="Eliminar" href="#delete" data-toggle="modal" onclick="document.getElementById(\'eliminar\').href = \'' + Routing.generate('appuser_delete', {id: aData[0]}) + '\'">' +
                                '<div class="btn blue-hoki btn-small">' +
                                '<i class="fa fa-times"></i>' +
                                '</div>' +
                                '</a>' +
                                '</div>';
                            aData[5] = '<i class="fa fa-check fa-success"></i>';
                        } else {
                            nCloneTd.innerHTML = '' +
                                '<div style="width: 196px">' +
                                '<a title="Editar" href="' + Routing.generate('appuser_edit', {id: aData[0]}) + '">' +
                                '<div class="btn blue-hoki btn-small">' +
                                '<i class="fa fa-edit"></i>' +
                                '</div>' +
                                '</a>' +
                                '<a title="Detalles" href="' + Routing.generate('appuser_details', {id: aData[0]}) + '">' +
                                '<div class="btn blue-hoki btn-small">' +
                                '<i class="fa fa-search"></i>' +
                                '</div>' +
                                '</a>' +
                                '<a title="Eliminar" href="#delete" data-toggle="modal" onclick="document.getElementById(\'eliminar\').href = \'' + Routing.generate('appuser_delete', {id: aData[0]}) + '\'">' +
                                '<div class="btn blue-hoki btn-small">' +
                                '<i class="fa fa-times"></i>' +
                                '</div>' +
                                '</a>' +
                                '<a title="Activar" href="#activate" data-toggle="modal" onclick="document.getElementById(\'activar\').href = \'' + Routing.generate('appuser_activate', {id: aData[0]}) + '\'">' +
                                '<div class="btn blue-hoki btn-small">' +
                                '<i class="fa fa-check-square-o"></i>' +
                                '</div>' +
                                '</a>' +
                                '</div>';
                            aData[5] = '<i class="fa fa-ban fa-danger"></i>';
                        }
                        oTable.row(this).data(aData);
                        this.insertBefore(nCloneTd.cloneNode(true), this.childNodes[this.childNodes.length]);
                    }
                });
            },
            "columnDefs": [{
                "visible": false,
                "targets": [0]
            }, {
                "class": "column-center",
                "targets": [5]
            }
            ],
            "order": [
                [2, 'asc']
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
                    "sExtends": "pdf",
                    "sButtonText": "PDF",
                    "mColumns": [1, 2, 3, 4, 5, 6]
                }, {
                    "sExtends": "csv",
                    "sButtonText": "CSV",
                    "mColumns": [1, 2, 3, 4, 5, 6]
                }, {
                    "sExtends": "xls",
                    "sButtonText": "Excel",
                    "mColumns": [1, 2, 3, 4, 5, 6]
                }, {
                    "sExtends": "print",
                    "sButtonText": "Imprimir",
                    "sInfo": 'Presione "CTRL+P" para imprimir o "ESC" para regresar',
                    "sMessage": "Generado por CUBiM",
                    "mColumns": [1, 2, 3, 4, 5, 6]
                }, {
                    "sExtends": "copy",
                    "sButtonText": "Copiar",
                    "mColumns": [1, 2, 3, 4, 5, 6]
                }]
            }
        });

        var tableWrapper = $('#usersListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        var tableColumnToggler = $('#usersListDatatable_column_toggler');

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

        var nEditing = null;
        var nNew = false;
        /*
         * Insert a 'details' column to the table
         */
        var nCloneTh = document.createElement('th');
        nCloneTh.innerHTML = "<strong>Operaciones</strong>";
        nCloneTh.style.textAlign = "center";

        table.find('thead tr').each(function () {
            this.insertBefore(nCloneTh, this.childNodes[this.childNodes.length]);
        });
        /* handle show/hide columns*/
        $('input[type="checkbox"]', tableColumnToggler).change(function () {
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
            oTable.fnSetColumnVis(iCol, (bVis ? false : true));

            var nCloneTh = document.createElement('th');
            nCloneTh.innerHTML = "<strong>Operaciones</strong>";
            nCloneTh.style.textAlign = "center";

            table.find('thead tr').each(function () {
                this.insertBefore(nCloneTh, this.childNodes[this.childNodes.length]);
            });
        });
    };

    var _initTracesListDatatable = function (id) {
        var table = $('#tracesListDatatable');
        /* Table tools samples: https://www.datatables.net/release-datatables/extras/TableTools/ */

        /* Set tabletools buttons and button container */

        $.extend(true, $.fn.DataTable.TableTools.classes, {
            "container": "btn-group tabletools-dropdown-on-portlet",
            "buttons": {
                "normal": "btn btn-sm default",
                "disabled": "btn btn-sm default disabled"
            }
        });

        var oTable = table.dataTable({
            "processing": true,
            "serverSide": true,
            "ajax": Routing.generate('trazas_ajax_listado', {'id': id, 'from':'profile' }),
            "columnDefs": [{
                "class": "column-right",
                "targets": [4]
            }, {
                "visible": false,
                "targets": 3
            }
            ],
            "order": [
                [4, 'desc']
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
                "sInfoFiltered": "",
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
            "pagingType": "full_numbers",
            "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

            "tableTools": {
                "sSwfPath": "{{ asset('metronic/assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf') }}",
                "aButtons": [{
                    "sExtends": "pdf",
                    "sButtonText": "PDF",
                    "mColumns": [0, 1, 2, 3, 4, 5]
                }, {
                    "sExtends": "xls",
                    "sButtonText": "Excel",
                    "mColumns": [0, 1, 2, 3, 4, 5]
                }, {
                    "sExtends": "print",
                    "sButtonText": "Imprimir",
                    "sInfo": 'Presione "CTRL+P" para imprimir o "ESC" para regresar',
                    "sMessage": "Generado por CUBiM",
                    "mColumns": [0, 1, 2, 3, 4, 5]
                }, {
                    "sExtends": "copy",
                    "sButtonText": "Copiar",
                    "mColumns": [0, 1, 2, 3, 4, 5]
                }]
            }
        });

        var tableWrapper = $('#tracesListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        var tableColumnToggler = $('#tracesListDatatable_column_toggler');

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown
    };

    return {
        initUsersListDatatable: function () {
            _initUsersListDatatable()
        },

        initTracesListDatatable: function (id) {
            _initTracesListDatatable(id)
        }
    }
}();