/**
 * Created by osmany.torres on 27/08/15.
 */

var Usuario = function () {
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
                'activo',
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

    var _select2Initialization = function (elementId, placeholder, nomTipoId, minimumInputLength) {
        var $page = 0;
        $(elementId).select2({
            allowClear: true,
            minimumInputLength: minimumInputLength,
            quietMillis: 7200,
            placeholder: placeholder,
            class_name: 'form-control',
            ajax: {
                delay: 7200,
                dataType: 'json',
                url: Routing.generate('nomenclador_ajax_select'),
                type: 'POST',
                data: function (term, page) { // page is the one-based page number tracked by Select2
                    $page = page;
                    return {
                        q: {tipoNomId: nomTipoId, description: term, page: page, pageCount: 10}, //search term
                        page: page // page number
                    };
                },
                results: function (data, page) {
                    var more = (page * 10) < data.total_count; // whether or not there are more results available
                    $page = page;
                    // notice we return the value of more so Select2 knows if more results can be loaded
                    return {results: data.items, more: more};
                },
                escapeMarkup: function (m) {
                    return m;
                }
            },
            initSelection: function (element, callback) {
                // the input tag has a value attribute preloaded that points to a preselected repository's id
                // this function resolves that id attribute to an object that select2 can render
                // using its formatResult renderer - that way the repository name is shown preselected
                var id = $(element).val();
                if (id !== "") {
                    $.ajax({
                        url: Routing.generate('nomenclador_ajax_select'),
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            q: {tipoNomId: nomTipoId, id: id, page: $page, pageCount: 10}, //search term
                            page: $page // page number
                        }
                    }).done(function (data) {
                        callback(data.items[0]);
                    });
                }
            }
        });
    };

    var _initSpecificSelects = function () {
        /*form_tipoPro y form_profesion se inicializan en la función
         * switchSelectsByCheck porque tienen un comportamiento diferente
         * al resto de select2 y dicha función se llama tambien en el
         * OnLoad del documento HTML*/
        _select2Initialization("#form_pais", "País...", 12);
        _select2Initialization("#form_cargo", "Cargo...", 4);
        _select2Initialization("#form_dedicacion", "Dedicación...", 6);
        _select2Initialization("#form_especialidad", "Especialidad...", 2);
        _select2Initialization("#form_categCien", "Categoría Científica...", 10);
        _select2Initialization("#form_categDoc", "Categoría Docente...", 7);
        _select2Initialization("#form_categInv", "Categoría Investigativa...", 9);
        _select2Initialization("#form_categOcup", "Categoría Ocupacional...", 8);
        _select2Initialization("#form_tipoUsua", "Tipo de Usuario...", 11);
        _select2Initialization("#form_institucion", "Institución...", 5);
        $("#form_atendidoPor").select2({
            allowClear: true,
            placeholder: 'Atendido Por...',
            class_name: 'form-control'
        });
    };

    var _initDetailsSpecificSelects = function () {
        $("#form_servicio").select2({
            allowClear: true,
            class_name: 'form-control'
        });
        $("#form_pc").select2({
            allowClear: true,
            class_name: 'form-control'
        });
        $("#form_fuentesInfo").select2({
            allowClear: true,
            class_name: 'form-control'
        });
    };

    var _switchSelectsByCheck = function () {
        var $page = 0;
        if (document.getElementById("form_estudiante").checked) {
            _select2Initialization("#form_tipoPro", "Carrera en Salud...", 1);
            _select2Initialization("#form_profesion", "Carrera fuera de Salud...", 3);
            document.getElementById("categorias").style = "display : none";
            document.getElementById("atendidoPor").style = "display : none";
            document.getElementById("s2id_form_dedicacion").style = "display : none";
            document.getElementById("s2id_form_cargo").style = "display : none";
        } else {
            _select2Initialization("#form_tipoPro", "Tipo de Profesional...", 1);
            _select2Initialization("#form_profesion", "Profesión...", 3);
            document.getElementById("categorias").style = "display : ";
            document.getElementById("atendidoPor").style = "display : ";
            document.getElementById("s2id_form_dedicacion").style = "display : ; width: 95%";
            document.getElementById("s2id_form_cargo").style = "display : ; width: 95%";
        }
    };

    var _switchEditSelectsByCheck = function () {
        if (document.getElementById("form_estudiante").checked) {
            _select2Initialization("#form_tipoPro", "Carrera en Salud...", 1);
            _select2Initialization("#form_profesion", "Carrera fuera de Salud...", 3);
            document.getElementById("especialidad").style = "display : none";
            document.getElementById("cargo").style = "display : none";
            document.getElementById("dedicacion").style = "display : none";
            document.getElementById("experiencia").style = "display : none";
            $("#tipoPro").text("Carrera en Salud");
            $("#profesion").text("Carrera fuera de Salud");
        } else {
            _select2Initialization("#form_tipoPro", "Tipo de Profesional...", 1);
            _select2Initialization("#form_profesion", "Profesión...", 3);
            document.getElementById("especialidad").style = "";
            document.getElementById("cargo").style = "";
            document.getElementById("dedicacion").style = "";
            document.getElementById("experiencia").style = "";
            $("#tipoPro").text("Tipo de Profesional");
            $("#profesion").text("Profesión");
        }
    };

    var _noCIConfirm = function () {
        if ($('#form_carnetId').val() == '')
            $('#save').modal();
        else
            document.forms[0].submit();
    };

    var _refreshTable = function (tableId, urlData) {
        $.getJSON(urlData, null, function (json) {
            table = $(tableId).dataTable();
            oSettings = table.fnSettings();

            table.fnClearTable(this);

            for (var i = 0; i < json.data.length; i++) {
                table.oApi._fnAddData(oSettings, json.data[i]);
            }

            oSettings.aiDisplay = oSettings.aiDisplayMaster.slice();
            table.fnDraw();
        });
    };

    var _initFilter = function () {
        $('#filter').on('click', function (e) {
            e.preventDefault();
            var form = {};
            form['carnetId'] = $('#form_carnetId').val();
            form['telefono'] = $('#form_telefono').val();
            form['pais'] = $('#form_pais').val();
            form['tipoPro'] = $('#form_tipoPro').val();
            form['profesion'] = $('#form_profesion').val();
            form['cargo'] = $('#form_cargo').val();
            form['dedicacion'] = $('#form_dedicacion').val();
            form['categCien'] = $('#form_categCien').val();
            form['categDoc'] = $('#form_categDoc').val();
            form['categInv'] = $('#form_categInv').val();
            form['categOcup'] = $('#form_categOcup').val();
            form['atendidoPor'] = $('#form_atendidoPor').val();
            form['experiencia'] = $('#form_experiencia').val();
            form['tipoUsua'] = $('#form_tipoUsua').val();
            form['especialidad'] = $('#form_especialidad').val();
            form['fechaInsDesde'] = $('#form_fechaInsDesde').val();
            form['fechaInsHasta'] = $('#form_fechaInsHasta').val();
            form['tipoForm'] = $('#form_tipoForm').val();
            form['inside'] = $('#form_inside').is(":checked");
            form['inactivo'] = $('#form_inactivo').is(":checked");
            form['estudiante'] = $('#form_estudiante').is(":checked");
            form['currentlyInNav'] = $('#form_currentlyInNav').is(":checked");
            form['currentlyInLect'] = $('#form_currentlyInLect').is(":checked");
            form['id'] = $('#form_id').val();
            form['modulo'] = $('#form_modulo').val();
            form['_token'] = $('#form__token').val();
            form['fromAjax'] = true;
            $.ajax({
                url: Routing.generate('usuario_lista', {'modulo': $('#form_modulo').val()}),
                type: 'POST',
                data: {form: form},
                success: function (data) {
                    $('#usersListDatatable').DataTable().ajax.reload();
                }
            });
        });
    };

    var _initRefreshFilter = function () {
        $('#refreshFilter').on('click', function (e) {
            e.preventDefault();
            $.ajax({
                url: Routing.generate('limpiar_filtros'),
                type: 'POST',
                success: function (data) {
                    var form = {};
                    $('#form_carnetId').val('');
                    $('#form_telefono').val('');
                    $('#form_pais').val('');
                    $('#form_tipoPro').val('');
                    $('#form_profesion').val('');
                    $('#form_cargo').val('');
                    $('#form_dedicacion').val('');
                    $('#form_categCien').val('');
                    $('#form_categDoc').val('');
                    $('#form_categInv').val('');
                    $('#form_categOcup').val('');
                    $('#form_atendidoPor').val('');
                    $('#form_experiencia').val('');
                    $('#form_tipoUsua').val('');
                    $('#form_especialidad').val('');
                    $('#form_fechaInsDesde').val('');
                    $('#form_fechaInsHasta').val('');
                    $('#form_tipoForm').val('');
                    $('#form_id').val('');
                    $('#form_inside').prop("checked", false);
                    $('#form_inside').parent().removeClass("checked");
                    $('#form_inactivo').prop("checked", false);
                    $('#form_inactivo').parent().removeClass("checked");
                    $('#form_estudiante').prop("checked", false);
                    $('#form_estudiante').parent().removeClass("checked");
                    $('#form_currentlyInNav').prop("checked", false);
                    $('#form_currentlyInNav').parent().removeClass("checked");
                    $('#form_currentlyInLect').prop("checked", false);
                    $('#form_currentlyInLect').parent().removeClass("checked");
                    _initSpecificSelects();
                    _switchSelectsByCheck();
                    $('#usersListDatatable').DataTable().ajax.reload();
                }
            });
        });
    };

    return {
        initExcel: function () {
            _initExcel()
        },

        initSpecificSelects: function () {
            _initSpecificSelects()
        },

        initDetailsSpecificSelects: function () {
            _initDetailsSpecificSelects()
        },

        switchSelectsByCheck: function () {
            _switchSelectsByCheck()
        },

        switchEditSelectsByCheck: function () {
            _switchEditSelectsByCheck()
        },

        noCIConfirm: function () {
            _noCIConfirm()
        },

        initFilter: function () {
            _initFilter()
        },

        initRefreshFilter: function () {
            _initRefreshFilter()
        }
    }
}();