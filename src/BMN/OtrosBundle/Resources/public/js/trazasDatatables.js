/**
 * Created by osmany.torres on 2/29/2016.
 */

var TrazasDatatables = function () {

    var _initTrazasListDatatable = function () {
        var table = $('#trazasListDatatable');
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
            "ajax": Routing.generate('trazas_ajax_listado'),
            "columnDefs": [{
                "class": "column-right",
                "targets": [4]
            }],
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
            "pagingType": "full_numbers",
            "dom": "<'row' <'col-md-12'T>><'row'<'col-md-6 col-sm-12'l><'col-md-6 col-sm-12'f>r><'table-scrollable't><'row'<'col-md-5 col-sm-12'i><'col-md-7 col-sm-12'p>>", // horizobtal scrollable datatable

            "tableTools": {
                "sSwfPath": "{{ asset('metronic/assets/global/plugins/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf') }}",
                "aButtons": [{
                    "sExtends": "pdf",
                    "sButtonText": "PDF",
                    "mColumns": [0, 1, 2, 3, 4, 5]
                }, {
                    "sExtends": "csv",
                    "sButtonText": "CSV",
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

        var tableWrapper = $('#trazasListDatatable_wrapper'); // datatable creates the table wrapper by adding with id {your_table_jd}_wrapper
        var tableColumnToggler = $('#trazasListDatatable_column_toggler');

        /* modify datatable control inputs */
        tableWrapper.find('.dataTables_length select').select2(); // initialize select2 dropdown
    };

    return {
        initTrazasListDatatable: function () {
            _initTrazasListDatatable()
        }
    }
}();