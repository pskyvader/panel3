function inicio_pedido() {
    mover('.order', 400, 500);
    if ($('.producto_atributo').length > 0) {
        inicio_pedido_atributos();
    }
    if ($('.mensaje_pedido').length > 0) {
        inicio_pedido_mensaje();
    }
    if ($('.direccion_entrega').length > 0) {
        inicio_direccion_entrega();
    }
    pedido_proceso = false;
    pedido_exito = true;

    $('#next_step').on('click', function() {
        if (pedido_proceso) {
            setTimeout(function() {
                $('#next_step').trigger("click");
            }, 200);
            return false;
        } else if (!pedido_exito) {
            return false;
        }
        var error = false;
        $('.producto_atributo').each(function() {
            if ($(this).val() == null) {
                notificacion("Debes elegir un globo", 'error');
                mover($(this).parents('.producto')[0]);
                error = true;
                return false;
            }
        });
        if (error) {
            return false;
        }
    });
}
var pedido_proceso = false;
var pedido_exito = true;


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
        console.log(mensaje, idpedidoproducto);
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


function inicio_direccion_entrega() {

    $('.direccion_entrega').on('change', function() {
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

    $('.fecha_entrega').datepicker({
        format: "yyyy-mm-dd",
        startDate: "2019-01-04",
        endDate: "2019-04-24",
        maxViewMode: 1,
        language: "es",
        autoclose: true,
        beforeShowDay: function(date) {
            var day = date.getDate();
            var monthIndex = date.getMonth() + 1;
            var year = date.getFullYear();
            if (date.getMonth() == (new Date()).getMonth())
                switch (date.getDate()) {
                    case 4:
                        return {
                            tooltip: 'Example tooltip',
                            classes: 'active'
                        };
                    case 8:
                        return false;
                    case 12:
                        return "green";
                }
        },
        datesDisabled: ['01/06/2019', '01/21/2019']
    });


    $(".grupo_pedido .lista_productos_pedido").sortable({
        connectWith: ".lista_productos_pedido",
        cursor: "producto",
        tolerance: "pointer",
        axis:"y",
        handle: '.handle',
        revert: true,
        scrollSensitivity: 120,
        scrollSpeed: 15,
        stop: function(event, ui) {
            mover(ui.item, 200, 0);
        },
        receive: function(event, ui) {
            var idfinal = $(ui.item).parents('.direccion').data('id');
            cambiar_id_productopedido(ui.item, idfinal);
        }
    });
}

function cambiar_id_productopedido(e, idfinal) {
    var idpedidoproducto = $(e).data('id');
    var modulo = "pedido/";
    var url = create_url(modulo + "change_productodireccion", {}, path);
    $('#next_step').addClass('disabled');
    post_basic(url, {
        idpedidoproducto: idpedidoproducto,
        idfinal: idfinal,
    }, function(data) {
        pedido_proceso = false;
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
}


function formato_imagen(e) {
    if (!$(e.element).data('foto')) {
        return e.text;
    }
    return $('<span><img src="' + $(e.element).data('foto') + '"> ' + e.text + '</span><span>');
};