/**
 * Created by osmany.torres on 27/08/15.
 */
var Lectura = function () {
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
            $('#from').val("navegacion");
            $('#excelExport').submit();
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

    var _initDetailsEmbeddedFormsFunctionalities = function () {
        var $lecturaModalidadHolder;
// setup an "add a tag" link
        var $addLecturaModalidadLink = $('#addLecturaModalidad');
        var $newLinkLi = $('<li></li>');
        jQuery(document).ready(function () {

            // Get the ul that holds the collection of tags
            $lecturaModalidadHolder = $('ul.lecturaModalidad');
            // add a delete link to all of the existing tag form li elements
            $lecturaModalidadHolder.find('li.lecturaModalidad').each(function () {
                addLecturaModalidadFormDeleteLink($(this));
            });
            // add the "add a tag" anchor and li to the tags ul
            //$lecturaModalidadHolder.append($newLinkLi);
            // count the current form inputs we have (e.g. 2), use that as the new
            // index when inserting a new item (e.g. 2)
            $lecturaModalidadHolder.data('index', $lecturaModalidadHolder.find(':input').length);
            $addLecturaModalidadLink.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // add a new tag form (see next code block)
                addLecturaModalidadForm($lecturaModalidadHolder);
            });

            $lecturaModalidadHolder.find('.addModalidadDetalle').each(function () {
                $(this).attr('id', 'addModalidadDetalle' + $lecturaModalidadHolder.data('index'));

                var $modalidadDetalleHolder = $('#modalidadDetalle');
                $modalidadDetalleHolder.attr('id', 'modalidadDetalleHolder' + $lecturaModalidadHolder.data('index'));
                $modalidadDetalleHolder.find('li').each(function () {
                    addModalidadDetalleFormDeleteLink($(this));
                });

                $lecturaModalidadHolder.data('index', $lecturaModalidadHolder.data('index') + 1);

                $(this).on('click', function (e) {
                    // prevent the link from creating a "#" on the URL
                    e.preventDefault();
                    // add a new tag form (see next code block)
                    addModalidadDetalleForm($modalidadDetalleHolder);
                });
            });
        });

        function addLecturaModalidadForm($collectionHolder) {
            // Get the data-prototype explained earlier
            var prototype = $collectionHolder.data('prototype');
            // get the new index
            var index = $collectionHolder.data('index');
            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newForm = prototype.replace(/__lecturaModalidad__/g, index);
            // increase the index with one for the next item
            $collectionHolder.data('index', index + 1);
            // Display the form in the page in an li, before the "Add a tag" link li
            var $newFormLi = $('<li class="lecturaModalidad"></li>').append(newForm);
            // add a delete link to the new form
            addLecturaModalidadFormDeleteLink($newFormLi);
            //$newLinkLi.before($newFormLi);
            $collectionHolder.append($newFormLi);


            var $modalidadDetalleHolder;
            $modalidadDetalleHolder = $('#modalidadDetalle');
            $modalidadDetalleHolder.attr('id', 'modalidadDetalleHolder' + index + 1);
            var $addModalidadDetalleLink = $('#addModalidadDetalle');
            $addModalidadDetalleLink.attr('id', 'addModalidadDetalle' + index + 1);
            $addModalidadDetalleLink = $('#addModalidadDetalle' + index + 1);

            $newLinkLi = $('<li></li>');
            $modalidadDetalleHolder.find('li').each(function () {
                addModalidadDetalleFormDeleteLink($(this));
            });

            $modalidadDetalleHolder.data('index', $modalidadDetalleHolder.find(':input').length);
            $addModalidadDetalleLink.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // add a new tag form (see next code block)
                addModalidadDetalleForm($modalidadDetalleHolder);
            });
        }

        function addLecturaModalidadFormDeleteLink($tagFormLi) {
            var $removeFormA = $('<a href="#"><button title="Eliminar modalidad" style="width: 100%" class="btn btn-default"><i class="fa fa-minus"></i> Eliminar modalidad</button></a><div class="clearfix"></div><br />');
            $tagFormLi.append($removeFormA);
            $removeFormA.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // remove the li for the tag form
                $tagFormLi.remove();
            });
        }

        function addModalidadDetalleForm($collectionHolder) {
            // Get the data-prototype explained earlier
            var prototype = $collectionHolder.data('prototype');
            // get the new index
            var index = $collectionHolder.data('index');
            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newForm = prototype.replace(/__modalidadDetalle__/g, index);
            // increase the index with one for the next item
            $collectionHolder.data('index', index + 1);
            // Display the form in the page in an li, before the "Add a tag" link li
            var $newFormLi = $('<li></li>').append(newForm);
            // add a delete link to the new form
            addModalidadDetalleFormDeleteLink($newFormLi);
            //$newLinkLi.before($newFormLi);
            $collectionHolder.append($newFormLi);
        }

        function addModalidadDetalleFormDeleteLink($tagFormLi) {
            var $removeFormA = $('<div style="float: right" class="delete_fuentesInfo col-md-2"><a href="#"><button title="Eliminar detalle" class="btn btn-default"><i class="fa fa-minus"></i></button></a></div><div class="clearfix"></div><br />');
            $tagFormLi.append($removeFormA);
            $removeFormA.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // remove the li for the tag form
                $tagFormLi.remove();
            });
        }
    };

    var _notiUserCurrentlyInLect = function (nombre, apellidos) {
        setTimeout(function () {
            var unique_id = $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'Notificaci\u00F3n de CUBiM',
                // (string | mandatory) the text inside the notification
                text: 'El usuario ' + nombre + ' ' + apellidos + ' se encuentra actualmente en Sala de Lectura.',
                // (string | optional) the image to display on the left
                image: '',
                // (bool | optional) if you want it to fade out on its own or just sit there
                sticky: true,
                // (int | optional) the time you want it to be alive for before fading out
                time: '',
                // (string | optional) the class name you want to apply to that specific message
                class_name: 'noti'
            });

            // You can have it return a unique id, this can be used to manually remove it later using
            setTimeout(function () {
                $.gritter.remove(unique_id, {
                    fade: true,
                    speed: 'slow'
                });
            }, 12000);
        }, 2000);
    };

    var _initSpecificSelects = function () {
        $("#form_pais").select2({
            allowClear: true,
            placeholder: 'Pa\u00EDs...',
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
            placeholder: 'Profesi\u00F3n...',
            class_name: 'form-control'
        });
        $("#form_cargo").select2({
            allowClear: true,
            placeholder: 'Cargo...',
            class_name: 'form-control'
        });
        $("#form_dedicacion").select2({
            allowClear: true,
            placeholder: 'Dedicaci\u00F3n...',
            class_name: 'form-control'
        });
        $("#form_categDoc").select2({
            allowClear: true,
            placeholder: 'Categor\u00EDa Docente...',
            class_name: 'form-control'
        });
        $("#form_categInv").select2({
            allowClear: true,
            placeholder: 'Categor\u00EDa Investigativa...',
            class_name: 'form-control'
        });
        $("#form_categCien").select2({
            allowClear: true,
            placeholder: 'Categor\u00EDa Cient\u00EDfica...',
            class_name: 'form-control'
        });
        $("#form_categOcup").select2({
            allowClear: true,
            placeholder: 'Categor\u00EDa Ocupacional...',
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

    return {
        initExcel: function () {
            _initExcel()
        },

        switchSelectsByCheck: function () {
            _switchSelectsByCheck()
        },

        initDetailsEmbeddedFormsFunctionalities: function () {
            _initDetailsEmbeddedFormsFunctionalities()
        },

        notiUserCurrentlyInLect: function (nombre, apellidos) {
            _notiUserCurrentlyInLect(nombre, apellidos)
        },

        initSpecificSelects: function () {
            _initSpecificSelects()
        }
    }
}();