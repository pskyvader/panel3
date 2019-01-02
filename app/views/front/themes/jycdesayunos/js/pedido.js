function inicio_pedido() {
    if ($('.producto_atributo').length > 0) {
        inicio_pedido_atributos();
    }
    if ($('.mensaje_pedido').length > 0) {
        inicio_pedido_mensaje();
    }
    pedido_proceso = false;
    pedido_exito = true;
}
var pedido_proceso = false;
var pedido_exito = true;

$('#next_step').on('click', function() {
    if (pedido_proceso) {
        setTimeout(function() {
            $('#next_step').trigger("click");
        }, 200);
        return false;
    } else if (!pedido_exito) {
        return false;
    }
});

function inicio_pedido_atributos() {
    var options = {
        width: '100%',
        templateResult: formato_imagen,
        templateSelection: formato_imagen,
        placeholder: "Selecciona un Globo para tu desayuno",
    };
    if (is_mobile) {
        options.minimumResultsForSearch = Infinity;
    }
    $('.producto_atributo').select2(options).on('change', function() {
        var idproductoatributo = $(this).val();
        var idpedidoproducto = $($(this).select2('data')[0].element).parent().data('id');

        var modulo = "carro/";
        var url = create_url(modulo + "change_atributo", {}, path);
        $('#next_step').addClass('disabled');
        post_basic(url, {
            idproductoatributo: idproductoatributo,
            idpedidoproducto: idpedidoproducto
        }, function(data) {
            $('#next_step').removeClass('disabled');
            try {
                data = JSON.parse(data);
            } catch (e) {
                console.log(e, data);
                data = {};
            }
            if (!data.exito) {
                notificacion(data.mensaje, 'error');
            }
        });
    });
}

function inicio_pedido_mensaje() {
    $('.mensaje_pedido').on('change', function() {
        var mensaje = $(this).val();
        var idpedidoproducto = $(this).data('id');
        console.log(mensaje,idpedidoproducto);
        var modulo = "carro/";
        var url = create_url(modulo + "change_mensaje", {}, path);
        pedido_proceso = true;
        pedido_exito = false;
        $('#next_step').css('opacity', 0.4);
        post_basic(url, {
            mensaje: mensaje,
            idpedidoproducto: idpedidoproducto
        }, function(data) {
            pedido_proceso = false;
            $('#next_step').css('opacity', 1);
            try {
                data = JSON.parse(data);
            } catch (e) {
                console.log(e, data);
                data = {};
            }
    
            pedido_exito = data.exito;
            if (!data.exito) {
                notificacion(data.mensaje, 'error');
            }
        });
    });
}

function formato_imagen(e) {
    if (!$(e.element).data('foto')) {
        return e.text;
    }
    return $('<span><img src="' + $(e.element).data('foto') + '"> ' + e.text + '</span><span>');
};