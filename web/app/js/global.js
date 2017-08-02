/**
 * Created by osmany.torres on 2/24/2016.
 */

var Global = function () {
    var _notiUserCurrentlyInLib = function (nombre, apellidos) {
        setTimeout(function () {
            var unique_id = $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'Notificaci\u00F3n de CUBiM',
                // (string | mandatory) the text inside the notification
                text: 'El usuario ' + nombre + ' ' + apellidos + ' se encuentra actualmente en la biblioteca.',
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

    var _notiBannedUser = function (nombre, apellidos) {
        setTimeout(function () {
            var unique_id = $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: 'Notificaci\u00F3n de CUBiM',
                // (string | mandatory) the text inside the notification
                text: 'Al usuario  ' + nombre + ' ' + apellidos + '  se le ha prohibido el acceso a la biblioteca.',
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

    var _initSelects = function () {
        var selects = document.getElementsByTagName('select');
        for (var i = 0; i < selects.length; i++) {
            $('#' + selects[i].id).select2({
                allowClear: true,
                class_name: 'form-control'
            })
        }
    };

    var _doThisAfterInsert = function () {
        setTimeout(function () {
            _initSelects();
        }, 0);
    };

    return {
        notiUserCurrentlyInLib: function (nombre, apellidos) {
            _notiUserCurrentlyInLib(nombre, apellidos)
        },
        notiBannedUser: function (nombre, apellidos) {
            _notiBannedUser(nombre, apellidos)
        },
        initSelects: function () {
            _initSelects()
        },
        doThisAfterInsert: function () {
            _doThisAfterInsert()
        }
    }
}();