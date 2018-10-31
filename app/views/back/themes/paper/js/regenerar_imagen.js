$('body').on('click', 'button.regenerar', preparar_regenerar);


function preparar_regenerar() {
    var total = parseInt($('#count_elementos').text());
    if (total == 0) {
        notificacion('Oh no!', 'No existen imagenes para regenerar', 'warning');
        barra(100);
        return false;
    }
    barra(10);
    var url = create_url(modulo, 'get_all');
    setTimeout(function() {
        notificacion('Advertencia', 'La regeneracion puede tomar un tiempo <br/> <b>por favor no cierres esta ventana<b/>', 'warning');
    }, 3000);
    post_basic(url, {}, 'Recuperando lista de imagenes', function(data) {
        var error = false;
        var data = JSON.parse(data);
        total = 0;
        $(data).each(function(k, v) {
            if (typeof(v.foto) == 'undefined') {
                error = true;
                return false;
            }
            total += v.foto.length;
        });

        if (error) {
            notificacion('Oh no!', 'No existen imagenes para regenerar', 'warning');
            barra(100);
            return false;
        } else {
            regenerar_imagenes(data, total);
        }
    });
}

function regenerar_imagenes(secciones, total) {
    var url = create_url(modulo, 'regenerar');
    var count = 0;
    var ready = false;
    $(secciones).each(function(k, v) {
        $(v.foto).each(function(key, f) {
            count++;
            if (typeof(f.ready) == 'undefined' || !f.ready) {
                barra(10 + (count / total) * 90);
                var mensaje = "Regenerando ID " + v[0] + ' Foto ' + f.url;
                post_basic(url, f, mensaje, function(data) {
                    var data = JSON.parse(data);
                    if (data.exito) {
                        f.ready = true;
                        if (count < total) {
                            regenerar_imagenes(secciones, total);
                        }
                    } else {
                        notificacion('Oh no!', 'Ha ocurrido un error al regenerar la imagen, intenta mas tarde' + '<br/>' + data.mensaje, 'error');
                        barra(100);
                    }
                });
                ready = true;
                return false;
            }
        });
        if (ready) return false;
    });
    if (count == total) {
        notificacion('Confirmacion', 'Regeneracion de imagenes completada', 'success');
        barra(100);
    }
}