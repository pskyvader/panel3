function add_wish(id) {
    console.log(id);
}

function add_cart(id, cantidad) {
    if (!cantidad) cantidad = 1;
    cantidad=parseInt(cantidad);
    if(cantidad<1) cantidad=1;
    console.log(id, cantidad);
}