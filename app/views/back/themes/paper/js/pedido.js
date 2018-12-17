function inicio_pedido() {
    $('.grupo_pedido').each(function() {
        inicio_direcciones_pedido($(this));
        inicio_usuarios_pedido($(this).parents('form'));
    });
}
var new_line_direcciones = null;

function inicio_usuarios_pedido(e) {
    var usuarios = $('select[name=idusuario]', e);
    var nombre = $('#nombre', e);
    var email = $('#email', e);
    var telefono = $('#telefono', e);
    var add_direccion = $('.add_direccion', e);
    var min = 0;
    if ($('option', usuarios).length > 30) {
        min = 1;
        if ($('option', usuarios).length > 100) {
            min = 3;
        }
    }
    usuarios.select2({
        width: '100%',
        minimumInputLength: min,
    });
    $(email).prop('disabled', true);
    if(usuarios.length>0){
        $(nombre).prop('disabled', true);
        $(telefono).prop('disabled', true);
        $(add_direccion).prop('disabled', true);
    }
    $(usuarios).on('change', function() {
        var idusuario = usuarios.val();
        if (idusuario != null) {
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

                        var direcciones = $('.direccion_entrega', new_line_direcciones);
                        $(data.direcciones).each(function(k, v) {
                            var d = $('<option value="' + v[0] + '">' + v.titulo + ' (' + v.direccion + ')</option>');
                            direcciones.append(d);
                        });
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
                $(add_direccion).prop('disabled', false);
            });
        }
    });
}

function formato_imagen(icono) {
    if (!$(icono.element).data('foto')) {
        return icono.text;
    }
    var i = $('<span><img src="' + $(icono.element).data('foto') + '"> ' + icono.text + '</span><span>');
    return i;
};

function inicio_direcciones_pedido(e) {
    var boton = $('button.add_direccion', e);
    var contenedor = $('.direcciones_pedido', e);
    var id = [];
    var id_actual = 0;
    var id_producto = [];
    var id_producto_actual = 0;

    var new_row = $('.new_row', e).clone();
    $('.new_row', e).remove();
    new_line_direcciones = $('.new_line', e).clone();
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

        var new_l = new_line_direcciones.clone();
        var direccion = $('.direccion_entrega', new_l);
        direccion.prop('required', true);
        direccion.prop('name', direccion.prop('name').replace("[]", "[" + id_actual + "]"));

        var fecha = $('.fecha_entrega', new_l);
        fecha.prop('required', true);
        fecha.prop('name', fecha.prop('name').replace("[]", "[" + id_actual + "]"));

        var iddireccion = $('.iddireccionpedido', new_l);
        iddireccion.prop('name', iddireccion.prop('name').replace("[]", "[" + id_actual + "]")).val(id_actual);

        
        var cantidad_productos = $('.cantidad_productos', new_l);
        cantidad_productos.prop('name', cantidad_productos.prop('name').replace("[]", "[" + id_actual + "]"));

        

        contenedor.append(new_l);

        $(".date").datetimepicker({
            todayHighlight: true
        });
        $('.lista_productos').select2({
            width: '100%',
            templateResult: formato_imagen,
            templateSelection: formato_imagen
        });

        count_direcciones(e);
        return false;
    });
    $(contenedor).on('click', '.add_producto', function() {
        var cantidad=$('.cantidad_producto',$(this).parents('.row')).val();
        var producto=$('.lista_productos',$(this).parents('.row')).select2('data');
        var direccion = $(this).parents('.direccion');
        for (let index = 0; index < cantidad; index++) {
            add_producto(producto, 1, new_row.clone(),id_producto,id_producto_actual,direccion);
        }
        // add_producto(producto, cantidad, new_r,id_producto,id_producto_actual);
        count_productos(direccion);
        return false;
    });

}

function add_producto(producto, cantidad, new_r,id_producto,id_producto_actual,direccion) {
    do {
        id_producto_actual++;
    } while (typeof(id_producto[id_producto_actual]) != 'undefined');
    id_producto[id_producto_actual] = id_producto_actual;
    
    var iddireccionpedido = $('.iddireccionpedido', direccion).val();

    $('.titulo', new_r).text($(producto[0].element).text());
    $('.imagen', new_r).prop('src',$(producto[0].element).data('foto'));
    $('.precio_unitario', new_r).val($(producto[0].element).data('precio'));
    $('.precio', new_r).val($(producto[0].element).data('precio')*cantidad);
    var idproducto = $('.idproducto', new_r);
    idproducto.prop('name', idproducto.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
    idproducto.prop('name', idproducto.prop('name').replace("[]", "[" + id_producto_actual + "]"));
    idproducto.val($(producto[0].element).val());

    var producto_cantidad = $('.producto_cantidad', new_r);
    producto_cantidad.prop('name', producto_cantidad.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
    producto_cantidad.prop('name', producto_cantidad.prop('name').replace("[]", "[" + id_producto_actual + "]"));
    producto_cantidad.val(cantidad);

    var producto_mensaje = $('.producto_mensaje', new_r);
    producto_mensaje.prop('name', producto_mensaje.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
    producto_mensaje.prop('name', producto_mensaje.prop('name').replace("[]", "[" + id_producto_actual + "]"));

    var producto_atributo = $('.producto_atributo', new_r);
    producto_atributo.prop('name', producto_atributo.prop('name').replace("[]", "[" + iddireccionpedido + "]"));
    producto_atributo.prop('name', producto_atributo.prop('name').replace("[]", "[" + id_producto_actual + "]"));
    producto_atributo.prop('required', true);


    $('.lista_productos_pedido',direccion).append(new_r);

    $('.producto_atributo').select2({
        width: '100%',
        templateResult: formato_imagen,
        templateSelection: formato_imagen
    });
}

$('body').on('change', '.lista_productos', function() {
    $('.add_producto', $(this).parent().parent()).removeClass('disabled');
});

$('body').on('change', '.producto_cantidad', function() {
    var cantidad=$(this).val();
    var precio=$('.precio_unitario',$(this).parents('.producto')).val();
    $('.precio',$(this).parents('.producto')).val(precio*cantidad);
});

$('body').on('click', '.quitar_producto', function() {
    var direccion = $(this).parents('.direccion');
    $(this).parents('.datos_producto').remove();
    count_productos(direccion);
});

$('body').on('click', '.quitar_direccion', function() {
    $(this).parents('.linea').remove();
    count_direcciones($('.grupo_pedido'));
});



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

function count_productos(e) {
    setTimeout(function() {
        var n = $('.lista_productos_pedido .datos_producto', e).length;
        if (n > 0) {
            $('.cantidad_productos', e).val(n);
        } else {
            $('.cantidad_productos', e).val('');
        }
    }, 100);
}