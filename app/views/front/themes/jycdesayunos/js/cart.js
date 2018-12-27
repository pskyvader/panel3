var template_cart=null;
function inicio_cart() {
    var tc = $('#template_cart-item');
    template_cart = $('li',tc);
    tc.remove();
    var modulo = "carro/";
    var url = create_url(modulo + "current_cart", {}, path);
    post_basic(url, "", function(data) {
        try {
            data = JSON.parse(data);
        } catch (e) {
            console.log(e,data);
            data = {};
        }
        generar_cart(data);
    });
}


function generar_cart(data){
    var elementos = [];
    var cantidad=0;
    var total="$0";
    if (Object.keys(data).length > 0) {
        total=data.total;
        if (data.productos && Object.keys(data.productos).length > 0) {
            $(data.productos).each(function(k,v){
                cantidad+=parseInt(v.cantidad);
                var e=template_cart.clone();
                e.html(
                    e.html().replace(/{url_producto}/ig, v.url)
                    .replace(/{imagen_producto}/ig, v.foto)
                    .replace(/{titulo_producto}/ig, v.titulo)
                    .replace(/{precio_producto}/ig, v.precio)
                    .replace(/{cantidad_producto}/ig, v.cantidad)
                    .replace(/{id}/ig, v.idpedidoproducto)
                    .replace('data-src','src')
                );
                elementos.push(e);
            });
        } else {
            elementos = $('<li><div class="media">Carro vacío</div></li>');
        }
    } else {
        elementos = $('<li><div class="media">Carro vacío</div></li>');
    }
    $('#carro-header .carro-cantidad').text(cantidad);
    $('#carro-header .carro-total').text(total);
    
    $('#carro-header .carro-productos .lista-productos').empty().prepend(elementos);
}

function add_wish(id) {
    console.log(id);
}

function add_cart(id, cantidad) {
    if (!cantidad) cantidad = 1;
    cantidad = parseInt(cantidad);
    if (cantidad < 1) cantidad = 1;

    var modulo = "carro/";
    var url = create_url(modulo + "add_cart", {}, path);
    post_basic(url, {id:id,cantidad:cantidad}, function(data) {
        try {
            data = JSON.parse(data);
        } catch (e) {
            console.log(e,data);
            data = {};
        }
        if(data.exito){
            notificacion(data.mensaje, 'success');
        }else{
            notificacion(data.mensaje, 'error');
        }
        generar_cart(data.carro);
        $('#carro-header .dropdown-toggle').click();
    });
}



function remove_cart(id) {
    var modulo = "carro/";
    var url = create_url(modulo + "remove_cart", {}, path);
    post_basic(url, {id:id}, function(data) {
        try {
            data = JSON.parse(data);
        } catch (e) {
            console.log(e,data);
            data = {};
        }
        if(data.exito){
            notificacion(data.mensaje, 'success');
        }else{
            notificacion(data.mensaje, 'error');
        }
        generar_cart(data.carro);
    });
}