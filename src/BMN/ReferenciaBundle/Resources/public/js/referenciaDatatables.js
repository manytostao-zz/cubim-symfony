/**
 * Created by osmany.torres on 2/29/2016.
 */

var ReferenciaDatatables = function () {
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
                "url": Routing.generate('usuario_ajax_listado', {'from': 'referencia'}),
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
                                modulo: 'referencia',
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

    var _initQuestionsListDatatable = function () {
        var table = $('#questionsListDatatable');
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
                "url": Routing.generate('referencia_ajax_listado'),
                "type": "POST"
            },
            "drawCallback": $questionsListDatatable.postDraw,
            "columnDefs": [{
                "visible": false,
                "targets": [0, 2, 4, 8, 9]
            }, {
                "orderable": false,
                "targets": [6]
            }, {
                "class": "column-center",
                "targets": [9]
            }
            ],
            "order": [
                [10, 'desc']
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
                "oPaginate": {sFirst: "Primero", sLast: "Último", sNext: "Siguiente", sPrevious: "Anterior"},
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
        var tableWrapper = $('#questionsListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        var tableColumnToggler = $('#questionsListDatatable_column_toggler');

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

        /* handle show/hide columns*/
        $('input[type="checkbox"]', tableColumnToggler).change(function () {
            /* Get the DataTables object again - this is not a recreation, just a get of the object */
            var iCol = parseInt($(this).attr("data-column"));
            oTable.fnSetColumnVis(iCol, ($(this).attr("checked") == "checked"));

            var nCloneTh = document.createElement('th');
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
        nCloneTh.style.width = '95px';
        nCloneTh.innerHTML = "<strong>Operaciones</strong>";
        nCloneTh.style.textAlign = "center";

        table.find('thead tr').each(function () {
            this.insertBefore(nCloneTh, this.childNodes[this.childNodes.length]);
        });
    };

    var $questionsListDatatable = {};

    $questionsListDatatable.postDraw = function () {
        var $tr = $('#questionsListDatatable').find('tbody tr');
        $tr.each(function () {
            var aData = $('#questionsListDatatable').dataTable().fnGetData(this);
            if (aData != null) {
                var $column = $('<td align="center"></td>');

                $column.width(95);
                var $row = $('<div style="width: 95px"></div>');

                //Botones
                var $btnEditar = $('<a title="Editar Solicitud" href="#request"></a>');
                var $innerDiv1 = $('<div class="btn blue-hoki btn-small"></div>');
                $innerDiv1.append($('<i class="fa fa-edit"></i>'));
                $btnEditar.append($innerDiv1);
                $btnEditar.attr('data-id', aData != null ? aData[0] : null);
                $btnEditar.attr('data-usuario', aData != null && aData[1] != null ? aData[1].split("/")[5] : null);
                $btnEditar.attr('data-pregunta', aData != null ? aData[3] : null);
                $btnEditar.attr('data-viaSolicitud', aData != null ? aData[4] : null);
                $btnEditar.attr('data-respuesta', aData != null ? aData[5] : null);
                $btnEditar.attr('data-tipoRespuesta', aData != null ? aData[6] : null);
                $btnEditar.attr('data-fuentesInfo', aData != null ? aData[7] : null);
                $btnEditar.attr('data-adjunto', aData != null ? aData[8] : null);
                $btnEditar.attr('data-desiderata', aData != null ? aData[9] : null);
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

                $row.append($btnEditar).append($btnEliminar);
                $column.append($row);
                $(this).append($column);

                //Eventos
                //Editar Solicitud
                $btnEditar.click(function () {
                    $('.delete_fuentesInfo').click();
                    var $collectionHolder = $('ul.fuentesInfo');
                    $collectionHolder.data('index', 1);
                    $('#formReferencia_id').val($(this).attr('data-id'));
                    $('#formReferencia_pregunta').val($(this).attr('data-pregunta'));
                    $('#formReferencia_respuesta').val($(this).attr('data-respuesta'));
                    $('#formReferencia_usuario').val($(this).attr('data-usuario'));

                    var elem = $('#formReferencia_desiderata');
                    if ($(this).attr('data-desiderata') == "Sí") {
                        $(elem).prop("checked", true);
                        $(elem).parent().addClass("checked");
                    }
                    else {
                        $(elem).prop("checked", false);
                        $(elem).parent().removeClass("checked");
                    }

                    var tipoRespuesta = $(this).attr('data-tipoRespuesta').split(',');
                    for (var j = 0; j < document.getElementsByName('formReferencia[tipoRespuesta][]').length; j++) {
                        var elem = $('#formReferencia_tipoRespuesta_' + j);
                        $(elem).prop("checked", false);
                        $(elem).parent().removeClass("checked");
                        for (var i = 0; i < tipoRespuesta.length; i++) {
                            if ($(elem).parent().parent().parent().text().trim() == tipoRespuesta[i].trim()) {
                                $(elem).prop("checked", true);
                                $(elem).parent().addClass("checked");
                                break;
                            }
                        }

                    }

                    var viaSolicitud = $(this).attr('data-viaSolicitud');
                    for (var li = 0; li < document.getElementById("formReferencia_viaSolicitud").childNodes.length; li = li + 1) {
                        var opt = document.getElementById("formReferencia_viaSolicitud").childNodes[li];
                        if (opt.firstChild != null && opt.firstChild.data == viaSolicitud.trim()) {
                            opt.selected = true;
                        }
                    }

                    var fuentes = $(this).attr('data-fuentesInfo').split(',');
                    if (fuentes.length > 0 && fuentes[0] != "")
                        for (var i = 1; i < fuentes.length + 1; i++) {
                            $('.add_fuentesInfo_link').click();
                            var id = "formReferencia_fuentesInfo_" + i + "_id";
                            for (var li = 0; li < document.getElementById(id).childNodes.length; li = li + 1) {
                                var opt = document.getElementById(id).childNodes[li];
                                if (opt.firstChild != null && opt.firstChild.data == fuentes[i - 1].trim()) {
                                    opt.selected = true;
                                }
                            }
                            $('#' + "formReferencia_fuentesInfo_" + i + "_id").select2({
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
                        document.forms[1].submit();
                    });

                    Global.initSelects();
                    var $modalView = $('#pregunta');
                    $modalView.modal();

                });
                //Eliminar Bibliografía
                $btnEliminar.click(function () {
                    var id = $(this).attr('data-id');
                    $('#eliminar').attr('href', Routing.generate('referencia_eliminar_solicitud', {id: id}));
                    var $modalView = $('#delete');
                    $modalView.modal();
                });

                if (aData[9] == "Sí")
                    aData[9] = '<i class="fa fa-check fa-success"></i>';
                else
                    aData[9] = '<i class="fa fa-ban fa-danger"></i>';
            }
            $('#questionsListDatatable').DataTable().row(this).data(aData);
        });
    };

    return {
        initUsersListDatatable: function () {
            _initUsersListDatatable()
        },

        initQuestionsListDatatable: function () {
            _initQuestionsListDatatable()
        }
    }
}();