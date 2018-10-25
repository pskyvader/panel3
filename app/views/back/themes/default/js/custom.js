$(window).on('load', inicio);
$(window).on('load', register_sw);
$(window).on('load', activar_imagen);
$(window).on('scroll', activar_imagen);
$(window).on('resize', activar_imagen);
var path = $("meta[property='path']").prop("content");
var modulo = $("meta[property='modulo']").prop("content");
var url = $("meta[property='og:url']").prop("content");
var googlemaps_key = $("meta[property='googlemaps_key']").prop("content");
var is_online = true;
var habilitado_online = true;
var tiempo = 120000;
var tiempo_offline = 2500;
var timer_online = setTimeout(online, tiempo);
$.skylo('start');
$.skylo('set', 50);

function inicio() {
    $.material.init();
    $.skylo('end');
    modulo = $("meta[property='modulo']").prop("content");
    url = $("meta[property='og:url']").prop("content");
    Utility.animateContent();
    $('body').scrollSidebar();
    $('.select').dropdown(); // DropdownJS
    enquire.register("screen and (max-width: 1199px)", {
        match: function() { //smallscreen
            $('body').addClass('sidebar-collapsed');
        },
        unmatch: function() { //bigscreen
            $('body').removeClass('sidebar-collapsed');
            $('.static-content').css('width', '');
        }
    });

    $('body').sidebarAccordion();
    $(window).trigger('resize');
    $.wijets.make();
    prettyPrint();
    $('.tooltips,.tooltip, [data-toggle="tooltip"]').tooltip();
    //if (url.indexOf("detail") != -1) { } else { }
    inicio_detail();
    inicio_list();
    $(".dial").knob(); // knob
	$('textarea.autosize').autosize({append: "\n"});
}

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

function online() {
    // Sólo hacer el fetch si navigator.onLine es true
    if (navigator.onLine) {
        fetch(path + 'ping').then(function(response) {
            if (!response.ok) {
                if (is_online) {
                    tiempo = tiempo_offline;
                }
                is_online = false;
            } else {
                if (!is_online) {
                    is_online = true;
                }
            }
            habilitar_online();
        }).catch(function(error) {
            if (is_online) {
                tiempo = tiempo_offline;
            }
            is_online = false;
            habilitar_online();
        });
    } else {
        if (is_online) {
            tiempo = tiempo_offline;
        }
        is_online = false;
        habilitar_online();
    }
}

function habilitar_online() {
    if (is_online) {
        tiempo = 120000;
        if (!habilitado_online) {
            habilitar(is_online);
            habilitado_online = true;
            notificacion('Conectado', 'Conexión activa', 'success');
        }
    } else {
        tiempo = tiempo * 2;
        if (tiempo > 120000) tiempo = 120000;
        habilitado_online = false;
        habilitar(is_online);
        notificacion('Sin Conexion', 'No tienes conexion, verificando conexion en ' + (tiempo / 1000) + ' segundos', 'error', {
            button: 'Reintentar',
            function() {
                clearTimeout(timer_online);
                tiempo = tiempo_offline;
                online();
            }
        });
    }
    timer_online = setTimeout(online, tiempo);
}


function register_sw() {
    if ('serviceWorker' in navigator) {
        //console.log('sw');
        navigator.serviceWorker.register(path + 'sw.js').then(function(registration) {
            // Registration was successful
            //console.log('ServiceWorker registration successful with scope: ', registration.scope);
        }).catch(function(err) {
            // registration failed :(
            console.log('ServiceWorker registration failed: ', err);
        });
    }else{
        //console.log('no sw');
    }
}