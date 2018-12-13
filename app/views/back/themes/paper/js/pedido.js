function inicio_pedido() {
    var usuarios = $('select[name=idusuario]');
    if (usuarios.length > 0) {
        var direcciones = {};
        usuarios.select2({
            width: '100%',
            minimumInputLength: 1,
        });
        $(usuarios).on('change', function() {
            var idusuario = usuarios.val();
            if (idusuario != null) {
                var nombre = $('#nombre');
                var email = $('#email');
                var telefono = $('#telefono');
                $(nombre).prop('disabled', true);
                $(email).prop('disabled', true);
                $(telefono).prop('disabled', true);
                post_basic(create_url(modulo, 'get_usuario'), {
                    idusuario: idusuario
                }, "Recuperando informacion del usuario", function(data) {
                    try {
                        data = JSON.parse(data);
                    } catch (e) {
                        data = {
                            mensaje: data,
                            exito: false
                        };
                    }
                    if (data.exito) {
                        if (data.direcciones.length > 0) {
                            $(nombre).val(data.usuario.nombre);
                            $(email).val(data.usuario.email);
                            $(telefono).val(data.usuario.telefono);
                            notificacion_footer(false);
                        } else {
                            notificacion('Oh no!', 'El usuario no tiene direcciones asignadas', 'error', {
                                button: "Crear direccion?",
                                function() {
                                    var url = create_url('usuariodireccion', 'detail', {
                                        idusuario: idusuario,
                                        tipo: 1
                                    });
                                    go_url(url);
                                }
                            });
                        }
                    } else {
                        notificacion('Oh no!', data.mensaje, 'error');;
                    }
                    $(nombre).prop('disabled', false);
                    $(email).prop('disabled', false);
                    $(telefono).prop('disabled', false);
                });
            }
        });
    }
}