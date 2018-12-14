function inicio_pedido() {
    $('.grupo_pedido').each(function() {
        inicio_usuarios_pedido($(this));
        inicio_direcciones_pedido($(this));
    });
}

function inicio_usuarios_pedido(e) {
    var usuarios = $('select[name=idusuario]', e);
    var nombre = $('#nombre', e);
    var email = $('#email', e);
    var telefono = $('#telefono', e);
    var direcciones = {};
    usuarios.select2({
        width: '100%',
        minimumInputLength: 1,
    });
    $(usuarios).on('change', function() {
        var idusuario = usuarios.val();
        if (idusuario != null) {
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

function inicio_direcciones_pedido(e) {
    var boton = $('button.add_direccion', e);
    var contenedor = $('.direcciones_pedido', e);
    var id = [];
    var id_actual = 0;
    var id_producto = [];
    var id_producto_actual = 0;

    var new_row = $('.new_row', e).clone();
    $('.new_row', e).remove();
    var new_line = $('.new_line', e).clone();
    $('.new_line', e).remove();

    $('.datos_producto', contenedor).each(function() {
        var idp = $('.idproducto', this).val();
        id_producto[idp] = idp;
    });
    $('.direccion', contenedor).each(function() {
        var idd = $('.iddireccionpedido', this).val();
        id[idd] = idd;

    });


    $(boton).on('click', function() {
        do {
            id_actual++;
        } while (typeof(id[id_actual]) != 'undefined');
        id[id_actual] = id_actual;

        var new_l = new_line.clone();
        var direccion = $('.direccion_entrega', new_l);
        direccion.prop('required', true);
        direccion.prop('name', direccion.prop('name').replace("[]", "[" + id_actual + "]"));

        var fecha = $('.fecha_entrega', new_l);
        fecha.prop('required', true);
        fecha.prop('name', fecha.prop('name').replace("[]", "[" + id_actual + "]"));

        var iddireccion = $('.iddireccionpedido', new_l);
        iddireccion.prop('name', iddireccion.prop('name').replace("[]", "[" + id_actual + "]")).val(id_actual);

        contenedor.append(new_l);

        $(".date").datetimepicker({
            todayHighlight: true
        });

        count_direcciones(e);
        return false;
    });
    $(contenedor).on('click', '.add_producto', function() {
        do {
            id_producto_actual++;
        } while (typeof(id_producto[id_producto_actual]) != 'undefined');
        id_producto[id_producto_actual] = id_producto_actual;
        var iddireccionpedido = $('.iddireccionpedido', $(this).closest('.direccion')).val();
        var new_r = new_row.clone();

        var idproducto = $('.idproducto', new_r);
        idproducto.prop('name', idproducto.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
        idproducto.prop('name', idproducto.prop('name').replace("[]", "[" + id_producto_actual + "]"));

        var producto_cantidad = $('.producto_cantidad', new_r);
        producto_cantidad.prop('name', producto_cantidad.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
        producto_cantidad.prop('name', producto_cantidad.prop('name').replace("[]", "[" + id_producto_actual + "]"));

        var producto_mensaje = $('.producto_mensaje', new_r);
        producto_mensaje.prop('name', producto_mensaje.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
        producto_mensaje.prop('name', producto_mensaje.prop('name').replace("[]", "[" + id_producto_actual + "]"));

        var producto_atributo = $('.producto_atributo', new_r);
        producto_atributo.prop('name', producto_atributo.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
        producto_atributo.prop('name', producto_atributo.prop('name').replace("[]", "[" + id_producto_actual + "]"));


        console.log('add producto', new_r);
        return false;
    });
}

function count_direcciones(e) {
    setTimeout(function() {
        var n = $('.direcciones_pedido .campo', e).length;
        if (n > 0) {
            $('.name', e).val(n);
        } else {
            $('.name', e).val('');
        }
    }, 100);
}