function inicio_cart() {
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
    var elementos = null;
    if (Object.keys(data).length > 0) {
        if (data.productos && Object.keys(data.productos).length > 0) {
            $(data.productos).each(function(k,v){
                elementos.add($('<li><div class="media">'+v.titulo+'</div></li>'));
            });
        } else {
            elementos = $('<li><div class="media">Carro vacío</div></li>');
        }
    } else {
        elementos = $('<li><div class="media">Carro vacío</div></li>');
    }
    $('#carro-header .carro-productos').prepend(elementos);
}

function add_wish(id) {
    console.log(id);
}

function add_cart(id, cantidad) {
    if (!cantidad) cantidad = 1;
    cantidad = parseInt(cantidad);
    if (cantidad < 1) cantidad = 1;
    console.log(id, cantidad);

    var modulo = "carro/";
    var url = create_url(modulo + "add_cart", {}, path);
    post_basic(url, {id:id,cantidad:cantidad}, function(data) {
        try {
            data = JSON.parse(data);
        } catch (e) {
            console.log(e,data);
            data = {};
        }
        console.log(data);
    });

}