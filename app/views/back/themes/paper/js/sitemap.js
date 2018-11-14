$('body').on('click', 'button.generar_sitemap,button.nuevo_sitemap', function() {
    generar_sitemap($(this));
});

function generar_sitemap(e) {
    var accion = $(e).data('action');
    var id = $(e).data('id');
    var mensaje = $(e).data('mensaje');
    post(create_url(modulo, accion), {
        id: id
    }, mensaje, false, null, sitemap);
}

function sitemap(data) {
    if (data.vacio) {
        $('#log_sitemap').empty();
        generar_sitemap($('button.generar_sitemap'));
    } else {
        if (data.generado) {
            notificacion('SITEMAP', 'Sitemap generado correctamente', 'success');
        } else {
            var total = data.progreso;
            $('#progreso_sitemap').val(total).trigger('change');
            if (data.ultimo) {
                $('#log_sitemap').prepend('<p>' + data.ultimo.url + '<p/>');
            }
            generar_sitemap($('button.generar_sitemap'));
        }
    }
}