$(window).on('load', inicio);
$(window).on('load', register_sw);
$(window).on('scroll resize', activar_imagen);
var path = $("meta[property='path']").prop("content");
var modulo = $("meta[property='modulo']").prop("content");
var url = $("meta[property='og:url']").prop("content");
var googlemaps_key = $("meta[property='googlemaps_key']").prop("content");
var google_captcha = $("meta[property='google_captcha']").prop("content");


function inicio() {
    modulo = $("meta[property='modulo']").prop("content");
    url = $("meta[property='og:url']").prop("content");

    $(".scrollup").hide();
    $('[data-toggle="tooltip"]').tooltip();
    imageGallery();
    if ($('.home-slider').length > 0) {
        // home slider
        var owl = $('.home-slider').owlCarousel({
            items: 1,
            loop: true,
            autoplay: true,
            autoplayTimeout: 4000,
            animateOut: 'fadeOut'
        });
        owl.trigger('refresh.owl.carousel');

        owl.on('changed.owl.carousel', function(e) {
            activar_imagen();
        });
    } else {
        activar_imagen();
    }


    //fluid width videos

    $(".single-post-content, .custom-page-template, .post-video").fitVids({
        customSelector: "iframe[src^='https://w.soundcloud.com']"
    });

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