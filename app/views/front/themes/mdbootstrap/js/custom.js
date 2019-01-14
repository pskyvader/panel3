$(window).on('load', inicio);
$(window).on('load', register_sw);
$(window).on('load scroll resize', activar_imagen);
var application_name = $("meta[name='application-name']");
var path = application_name.data("path");
var modulo = application_name.data("modulo");
var url = application_name.data("url");
var googlemaps_key = application_name.data("googlemaps_key");
var google_captcha = application_name.data("google_captcha");


function inicio() {
    var application_name = $("meta[name='application-name']");
    modulo = application_name.data("modulo");
    url = application_name.data("url");
    $('body').removeClassRegex(/^module-/).addClass('module-'+modulo);
    $('[data-toggle="tooltip"]').tooltip();
    $('.mdb-select').material_select();
    Waves.attach('.btn', ['waves-light']);
    if ($('.carousel').length > 0) {
        $('.carousel').carousel();

        $('.carousel').on('slide.bs.carousel', function(e) {
            load_source($('source[data-srcset]', e.relatedTarget));
            load_background($('.blur[data-background]', e.relatedTarget));
            load_image($('img[data-src]', e.relatedTarget));
        });
        $('.carousel').on('slid.bs.carousel', activar_imagen);
        $('.carousel').hammer().on('swipeleft', function() {
            $(this).carousel('next');
        });
        $('.carousel').hammer().on('swiperight', function() {
            $(this).carousel('prev');
        });
    }

    if ($('.map').length > 0) {
        inicio_map();
    }

    if ($('.g-recaptcha').length > 0) {
        inicio_captcha();
    }
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
    } else {
        //console.log('no sw');
    }
}