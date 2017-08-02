/**
 * Created by manytostao on 25/05/15.
 */
'use strict';

/*
 * show.bs.modal
 * shown.bs.modal
 * hide.bs.modal
 * hidden.bs.modal
 *
 * */

var NomencladorDatatables = function () {

    var _initNomenclatorsListDatatable = function (tipoNom) {
        var table = $('#nomenclatorsListDatatable');
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
            "ajax": Routing.generate('nomenclador_ajax_listado', {tipoNom: tipoNom}),
            "drawCallback": $nomencladorTable.postDraw,
            "columnDefs": [{
                "visible": false,
                "targets": [0]
            }, {
                "class": "nom-description",
                "targets": [1]
            }, {
                "class": "column-center nom-active",
                "targets": [2]
            }],
            "order": [
                [1, 'asc']
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
                    "mColumns": [1]
                }, {
                    "sExtends": "xls",
                    "sButtonText": "Excel",
                    "mColumns": [1]
                }, {
                    "sExtends": "print",
                    "sButtonText": "Imprimir",
                    "sInfo": 'Presione "CTRL+P" para imprimir o "ESC" para regresar',
                    "sMessage": "Generado por CUBiM",
                    "mColumns": [1]
                }]
            }
        });
        var nCloneTh = document.createElement('th');
        nCloneTh.innerHTML = "<strong>Operaciones</strong>";
        nCloneTh.style.width = "100px";
        nCloneTh.style.textAlign = "center";

        var tableWrapper = $('#nomenclatorsListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown

        table.find('thead tr').each(function () {
            this.insertBefore(nCloneTh, this.childNodes[5]);
        });
    };

    var $nomencladorTable = {};

    $nomencladorTable.postDraw = function () {
        var $tr = $('#nomenclatorsListDatatable').find('tbody tr');
        $tr.each(function () {
            var aData = $('#nomenclatorsListDatatable').DataTable().row(this).data();
            if (aData != null) {
                var $column = $('<td></td>');

                var $subcontainer1;
                var $subcontainer2;
                if (aData != null && aData[2] != true) {
                    $column.width(147);
                    $subcontainer1 = $('<div class="col-md-4"></div>');
                    $subcontainer2 = $('<div class="col-md-4"></div>');
                }
                else {
                    $column.width(86);
                    $subcontainer1 = $('<div class="col-md-6"></div>');
                    $subcontainer2 = $('<div class="col-md-6"></div>');
                }
                //Botones
                var $row = $('<div class="row"></div>');
                var $container = $('<div class="col-md-12"></div>');
                var $btnEditar = $('<a class="edit" title="Editar" href="#">');
                var $innerDiv1 = $('<div class="btn blue-hoki btn-small"></div>');
                $innerDiv1.append($('<i class="fa fa-edit"></i>'));
                $btnEditar.append($innerDiv1);
                $btnEditar.attr('data-id', aData != null ? aData[0] : null);
                $btnEditar.attr('data-active', aData != null ? aData[2] : null);
                var $parent = $(this).closest('tr');
                var $text = $parent.find('.nom-description');
                $btnEditar.attr('data-name', $text.html());
                $subcontainer1.append($btnEditar);

                var $btnEliminar = $('<a title="Eliminar" href="#delete" data-toggle="modal"></a>');
                var $innerDiv2 = $('<div class="btn blue-hoki btn-small"></div>');
                $innerDiv2.append($('<i class="fa fa-times"></i>'));
                $btnEliminar.append($innerDiv2);
                $btnEliminar.attr('data-toggle', 'modal');
                $btnEliminar.attr('data-target', '#confirm');
                $btnEliminar.attr('data-id', aData != null ? aData[0] : null);
                $subcontainer2.append($btnEliminar);

                $container.append($subcontainer1).append($subcontainer2);

                if (aData != null && aData[2] == false) {
                    var $subcontainer3 = $('<div class="col-md-4"></div>');
                    var $btnActivar = $('<a title="Activar" href="#activate" data-toggle="modal"></a>');
                    var $innerDiv3 = $('<div class="btn blue-hoki btn-small"></div>');
                    $innerDiv3.append($('<i class="fa fa-check-square-o"></i>'));
                    $btnActivar.append($innerDiv3);
                    $btnActivar.attr('data-toggle', 'modal');
                    $btnActivar.attr('data-target', '#confirm');
                    $btnActivar.attr('data-id', aData != null ? aData[0] : null);
                    $subcontainer3.append($btnActivar);
                    $container.append($subcontainer3)
                }
                $row.append($container);
                $column.append($row);
                $(this).append($column);

                //Eventos
                //Editar Nomenclador
                $btnEditar.click(function () {
                    var $modalView = $('#add');
                    $modalView.modal();
                    $modalView.find('.modal-title').text('Editar Valor');
                    $modalView.find('#formNomenclador_id').val($(this).data('id'));
                    $modalView.find('#formNomenclador_descripcion').val($(this).data('name'));
                    $modalView.find('#formNomenclador_tipoForm').val(0);
                    if ($(this).data('active') == true) {
                        $('#formNomenclador_activo').prop('checked', true);
                        $('#formNomenclador_activo').parent().addClass('checked');
                    }
                    else {
                        $('#formNomenclador_activo').prop('checked', false);
                        $('#formNomenclador_activo').parent().removeClass('checked');
                    }
                    $modalView.modal('show');
                });

                //Eliminar Nomenclador
                $btnEliminar.click(function () {
                    var id = $(this).attr('data-id');
                    $('#eliminar').attr('href', Routing.generate('nomenclador_eliminar', {id: id}));
                    var $modalView = $('#delete');
                    $modalView.modal();
                });

                //Activar Nomenclador
                if (typeof $btnActivar != 'undefined') {
                    $btnActivar.click(function () {
                        var id = $(this).attr('data-id');
                        $('#activar').attr('href', Routing.generate('nomenclador_activar', {id: id}));
                        var $modalView = $('#activate');
                        $modalView.modal();
                    });
                }

                if (aData != null && aData[2] == true)
                    aData[2] = '<i class="fa fa-check fa-success"></i>';
                else if (aData != null)
                    aData[2] = '<i class="fa fa-ban fa-danger"></i>';
                $('#nomenclatorsListDatatable').DataTable().row(this).data(aData);
            }
        });

    };

    return {
        initNomenclatorsListDatatable: function (tipoNom) {
            _initNomenclatorsListDatatable(tipoNom)
        }
    }
}();