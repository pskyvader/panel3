var update_content = $('#update_content');

if (update_content.length > 0) {
    get_update();
}

function get_update() {
    $.get(create_url(modulo, 'get_update')).done(function(respuesta) {
        try {
            respuesta = JSON.parse(respuesta);
        } catch (e) {
            respuesta = {
                mensaje: respuesta,
                exito: false
            };
        }
        if (respuesta.version) {
            var mensaje = "Version " + respuesta.version.version + "<br/>" + respuesta.version.descripcion;
            $(update_content).html(mensaje);
            $('input[name=id_update]').val(respuesta.version.version);
            $('.panel-body').show();
        } else {
            notificacion('Oh no!', respuesta.mensaje, 'error');
        }
    });
}

function update_elemento() {
    var archivo =$('input[name=id_update]').val();
    if (archivo == "") {
        notificacion('Oh no!', 'Ha ocurrido un error, por favor actualiza la pagina e intentalo nuevamente', 'error');
    } else {
        update_content.empty();
        obtener_archivo(archivo);
    }
}

function obtener_archivo(archivo){
    update_content.prepend($('<p>'+'Descargando archivo '+archivo+'</p>'));
    post_basic(create_url(modulo, 'get_file'), {file:archivo}, 'Descargando archivo '+archivo, function(data){
        console.log(data);
    });
}