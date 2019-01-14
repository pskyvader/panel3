$(window).on('load', inicio);
$(window).on('load', register_sw);
$(window).on('load', get_instagram);
$(window).on('scroll resize', activar_imagen);
$(window).on('resize', imageGallery);
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
    $(".scrollup").hide();
    $('[data-toggle="tooltip"]').tooltip();
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
    }

    activar_imagen();
    imageGallery();

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

function get_instagram() {
    var pictures = $('#footer-instagram .instagram-pics');
    if ($('li', pictures).length == 0) {
        post_basic(path + 'instagram', {}, function(data) {
            data = JSON.parse(data);
            $(data).each(function(k, v) {
                var li = $('<li>');
                var a = $('<a target="_blank" rel="noopener noreferrer">').prop('href', v.url);
                var img = $('<img>').prop('src',v.images.low_resolution.url).prop('alt',v.title);
                pictures.append(li.append(a.append(img)));
            });

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