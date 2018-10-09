$(window).on('load', inicio);
$(window).on('load', register_sw);
$(window).on('load', activar_imagen);
$(window).on('scroll', activar_imagen);
$(window).on('resize', activar_imagen);
var path = $("meta[property='path']").prop("content");
var modulo = $("meta[property='modulo']").prop("content");
var url = $("meta[property='og:url']").prop("content");
var googlemaps_key = $("meta[property='googlemaps_key']").prop("content");

function inicio() {
    modulo = $("meta[property='modulo']").prop("content");
    url = $("meta[property='og:url']").prop("content");
    $('[data-toggle="tooltip"]').tooltip();
    if ($('.carousel').length > 0) {
        $('.carousel').carousel();
        $('.carousel').hammer().on('swipeleft', function() {
            $(this).carousel('next');
        });
        $('.carousel').hammer().on('swiperight', function() {
            $(this).carousel('prev');
        });

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