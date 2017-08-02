/**
 * Created by osmany.torres on 27/08/15.
 */

var Referencia = function () {
    var _initExcel = function () {
        $('#excel').on('click', function (e) {
            e.preventDefault();
            var origColumns = ['id',
                'nombres',
                'apellidos',
                'carnetId',
                'telefono',
                'email',
                'tipoUsua',
                'tipoPro',
                'institucion',
                'carnetBib',
                'especialidad',
                'profesion',
                'dedicacion',
                'categOcup',
                'categCien',
                'categInv',
                'categDoc',
                'pais',
                'cargo',
                'temaInv',
                'observaciones',
                'atendidoPor',
                'estudiante',
                'fechaIns'
            ];
            var inputs = $('#usersListDatatable_column_toggler').find('input');
            var table = $('#usersListDatatable').DataTable();
            var info = table.page.info();
            var order = table.order();
            var start = info.start;
            var end = info.end;
            var length = info.length;
            var columns = [];
            for (var i = 0; i <= inputs.length; i++)
                if ($(inputs[i]).is(':checked'))
                    columns.push($(inputs[i]).data('field'));
            $('#start').val(start);
            $('#end').val(end);
            $('#length').val(length);
            $('#columns').val(origColumns);
            $('#visColumns').val(columns);
            $('#order').val(order);
            $('#excelExport').submit();
        });
    };

    var _initSpecificSelects = function () {
        $("#form_pais").select2({
            allowClear: true,
            placeholder: 'País...',
            class_name: 'form-control'
        });
        $("#form_tipoPro").select2({
            allowClear: true,
            placeholder: 'Tipo de Profesional...',
            class_name: 'form-control'
        });
        $("#form_especialidad").select2({
            allowClear: true,
            placeholder: 'Especialidad...',
            class_name: 'form-control'
        });
        $("#form_profesion").select2({
            allowClear: true,
            placeholder: 'Profesión...',
            class_name: 'form-control'
        });
        $("#form_cargo").select2({
            allowClear: true,
            placeholder: 'Cargo...',
            class_name: 'form-control'
        });
        $("#form_dedicacion").select2({
            allowClear: true,
            placeholder: 'Dedicación...',
            class_name: 'form-control'
        });
        $("#form_categDoc").select2({
            allowClear: true,
            placeholder: 'Categoría Docente...',
            class_name: 'form-control'
        });
        $("#form_categInv").select2({
            allowClear: true,
            placeholder: 'Categoría Investigativa...',
            class_name: 'form-control'
        });
        $("#form_categCien").select2({
            allowClear: true,
            placeholder: 'Categoría Científica...',
            class_name: 'form-control'
        });
        $("#form_categOcup").select2({
            allowClear: true,
            placeholder: 'Categoría Ocupacional...',
            class_name: 'form-control'
        });
        $("#form_tipoUsua").select2({
            allowClear: true,
            placeholder: 'Tipo de Usuario...',
            class_name: 'form-control'
        });
        $("#form_atendidoPor").select2({
            allowClear: true,
            placeholder: 'Atendido Por...',
            class_name: 'form-control'
        });
    };

    var _switchSelectsByCheck = function () {
        if (document.getElementById("form_estudiante").checked) {
            $("#form_tipoPro").select2({
                allowClear: true,
                placeholder: "Carrera en Salud...",
                class_name: "form-control"
            });
            $("#form_profesion").select2({
                allowClear: true,
                placeholder: "Carrera fuera de Salud...",
                class_name: "form-control"
            });
            document.getElementById("categorias").style = "display : none";
            document.getElementById("atendidoPor").style = "display : none";
            document.getElementById("s2id_form_dedicacion").style = "display : none";
            document.getElementById("s2id_form_cargo").style = "display : none";
        } else {
            $("#form_tipoPro").select2({
                allowClear: true,
                placeholder: "Tipo de Profesional...",
                class_name: "form-control"
            });
            $("#form_profesion").select2({
                allowClear: true,
                placeholder: "Profesión...",
                class_name: "form-control"
            });
            document.getElementById("categorias").style = "display : ";
            document.getElementById("atendidoPor").style = "display : ";
            document.getElementById("s2id_form_dedicacion").style = "display : ; width: 95%";
            document.getElementById("s2id_form_cargo").style = "display : ; width: 95%";
        }
    };

    return {
        initExcel: function () {
            _initExcel()
        },

        initSpecificSelects: function () {
            _initSpecificSelects()
        },

        switchSelectsByCheck: function () {
            _switchSelectsByCheck()
        }
    }
}();