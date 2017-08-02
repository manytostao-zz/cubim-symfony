/**
 * Created by osmany.torres on 27/08/15.
 */
var Bibliography = function () {
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
            var inputs = $('#usersBibliographyDatatable_column_toggler').find('input');
            var table = $('#usersBibliographyDatatable').DataTable();
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

    var _switchSelectByCheck = function () {
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

    var _initRequestsListEmbeddedFormsFunctionalities = function () {
        var $collectionHolder;
        // setup an "add a tag" link
        var $addTagLink = $('<a href="#" class="add_fuentesInfo_link"><button class="btn btn-default" onclick="Global.doThisAfterInsert()"><i class="fa fa-plus"></i>Adicionar fuente</button></a>');
        var $newLinkLi = $('<li style="float: right"></li>').append($addTagLink);
        jQuery(document).ready(function () {
            // Get the ul that holds the collection of tags
            $collectionHolder = $('ul.fuentesInfo');
            // add a delete link to all of the existing tag form li elements
            $collectionHolder.find('li').each(function () {
                addTagFormDeleteLink($(this));
            });
            // add the "add a tag" anchor and li to the tags ul
            $collectionHolder.append($newLinkLi);
            // count the current form inputs we have (e.g. 2), use that as the new
            // index when inserting a new item (e.g. 2)
            $collectionHolder.data('index', $collectionHolder.find(':input').length);
            $addTagLink.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // add a new tag form (see next code block)
                addTagForm($collectionHolder, $newLinkLi);
            });
        });

        function addTagForm($collectionHolder, $newLinkLi) {
            // Get the data-prototype explained earlier
            var prototype = $collectionHolder.data('prototype');
            // get the new index
            var index = $collectionHolder.data('index');
            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newForm = prototype.replace(/__name__/g, index);
            // increase the index with one for the next item
            $collectionHolder.data('index', index + 1);
            // Display the form in the page in an li, before the "Add a tag" link li
            var $newFormLi = $('<li style="float: right;"></li>').append(newForm);
            // add a delete link to the new form
            addTagFormDeleteLink($newFormLi);
            $newLinkLi.before($newFormLi);
        }

        function addTagFormDeleteLink($tagFormLi) {
            var $removeFormA = $('<div style="float: right" class="delete_fuentesInfo"><a href="#"><button  title="Eliminar fuente" class="btn btn-default"><i class="fa fa-minus"></i></button></a></div><div class="clearfix"></div><br />');
            $tagFormLi.append($removeFormA);
            $removeFormA.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // remove the li for the tag form
                $tagFormLi.remove();
            });
        }
    };

    var _initRequestEmbeddedFormsFunctionalitites = function () {
        var $collectionHolder;
        // setup an "add a tag" link
        var $addTagLink = $('<a href="#" class="add_fuentesInfo_link"><button class="btn btn-default" onclick="doThisAfterInsert()"><i class="fa fa-plus"></i>Adicionar fuente</button></a>');
        var $newLinkLi = $('<li style="float: right"></li>').append($addTagLink);
        jQuery(document).ready(function () {
            // Get the ul that holds the collection of tags
            $collectionHolder = $('ul.fuentesInfo');
            // add a delete link to all of the existing tag form li elements
            $collectionHolder.find('li').each(function () {
                addTagFormDeleteLink($(this));
            });
            // add the "add a tag" anchor and li to the tags ul
            $collectionHolder.append($newLinkLi);
            // count the current form inputs we have (e.g. 2), use that as the new
            // index when inserting a new item (e.g. 2)
            $collectionHolder.data('index', $collectionHolder.find(':input').length);
            $addTagLink.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // add a new tag form (see next code block)
                addTagForm($collectionHolder, $newLinkLi);
            });
        });

        function addTagForm($collectionHolder, $newLinkLi) {
            // Get the data-prototype explained earlier
            var prototype = $collectionHolder.data('prototype');
            // get the new index
            var index = $collectionHolder.data('index');
            // Replace '__name__' in the prototype's HTML to
            // instead be a number based on how many items we have
            var newForm = prototype.replace(/__name__/g, index);
            // increase the index with one for the next item
            $collectionHolder.data('index', index + 1);
            // Display the form in the page in an li, before the "Add a tag" link li
            var $newFormLi = $('<li style="float: right;"></li>').append(newForm);
            // add a delete link to the new form
            addTagFormDeleteLink($newFormLi);
            $newLinkLi.before($newFormLi);
        }

        function addTagFormDeleteLink($tagFormLi) {
            var $removeFormA = $('<div style="float: right"><a href="#"><button title="Eliminar fuente" class="btn btn-default"><i class="fa fa-minus"></i></button></a></div><div class="clearfix"></div><br />');
            $tagFormLi.append($removeFormA);
            $removeFormA.on('click', function (e) {
                // prevent the link from creating a "#" on the URL
                e.preventDefault();
                // remove the li for the tag form
                $tagFormLi.remove();
            });
        }
    };

    var _initRequestDatePickers = function () {
        if (jQuery().datepicker) {
            $('.date-picker').datepicker({
                rtl: Metronic.isRTL(),
                orientation: "left",
                autoclose: true,
                startView: 2,
                minViewMode: 2,
                format: 'yyyy',
                clearBtn: true
            });
        }
    };

    var _initDetailsDatePickers = function(){
        if (jQuery().datepicker) {
            $('.date-picker').datepicker({
                rtl: Metronic.isRTL(),
                orientation: "left",
                autoclose: true,
                startView: 2,
                minViewMode: 2,
                format: 'yyyy',
                clearBtn: true
            });
            //$('body').removeClass("modal-open"); // fix bug when inline picker is used in modal
        }};

    return {
        initExcel: function () {
            _initExcel()
        },

        initSpecificSelects: function () {
            _initSpecificSelects()
        },

        switchSelectByCheck: function () {
            _switchSelectByCheck()
        },

        initRequestsListEmbeddedFormsFunctionalities: function () {
            _initRequestsListEmbeddedFormsFunctionalities()
        },

        initRequestEmbeddedFormsFunctionalitites: function () {
            _initRequestEmbeddedFormsFunctionalitites()
        },

        initRequestDatePickers: function () {
            _initRequestDatePickers()
        },

        initDetailsDatePickers: function () {
            _initDetailsDatePickers()
        }
    }
}();