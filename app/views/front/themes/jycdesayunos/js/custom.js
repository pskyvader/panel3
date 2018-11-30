$(window).on('load', inicio);
$(window).on('load', register_sw);
$(window).on('load scroll resize', activar_imagen);
var path = $("meta[property='path']").prop("content");
var modulo = $("meta[property='modulo']").prop("content");
var url = $("meta[property='og:url']").prop("content");
var googlemaps_key = $("meta[property='googlemaps_key']").prop("content");
var google_captcha = $("meta[property='google_captcha']").prop("content");


function inicio() {
    modulo = $("meta[property='modulo']").prop("content");
    url = $("meta[property='og:url']").prop("content");
    $('[data-toggle="tooltip"]').tooltip();

    $('.preloader i').fadeOut('slow');
    $('.preloader').delay(500).fadeOut('slow');
    $('body').delay(600).css({
        'overflow': 'visible'
    });
    if ($('.carousel').length > 0) {
        $('.carousel').carousel();
        $('.carousel').on('slide.bs.carousel', function(e) {
            load_source($('source[data-srcset]', e.relatedTarget));
            load_background($('.blur[data-background]', e.relatedTarget));
            load_image($('img[data-src]', e.relatedTarget));
        });
        $('.carousel').on('slid.bs.carousel', activar_imagen);
        /*$('.carousel').hammer().on('swipeleft', function() {
            $(this).carousel('next');
        });
        $('.carousel').hammer().on('swiperight', function() {
            $(this).carousel('prev');
        });*/
    }

    if ($('.map').length > 0) {
        inicio_map();
    }

    if ($('.g-recaptcha').length > 0) {
        inicio_captcha();
    }
    if ($('.owl').length > 0) {
        inicio_owl();
    }

    if ($('.product-gallery').length > 0) {
        inicio_gallery();
    }
}


function inicio_owl() {
    var $owl = $('.owl');
    $owl.each(function() {
        var $a = $(this);
        $a.owlCarousel({
            autoPlay: JSON.parse($a.attr('data-autoplay')),
            singleItem: JSON.parse($a.attr('data-singleItem')),
            items: $a.attr('data-items'),
            itemsDesktop: [1199, $a.attr('data-itemsDesktop')],
            itemsDesktopSmall: [992, $a.attr('data-itemsDesktopSmall')],
            itemsTablet: [797, $a.attr('data-itemsTablet')],
            itemsMobile: [479, $a.attr('data-itemsMobile')],
            navigation: JSON.parse($a.attr('data-buttons')),
            pagination: JSON.parse($a.attr('data-pag')),
            navigationText: ['', ''],
        });
    });
}

function inicio_gallery() {
    var $owl = $('.product-gallery');
    $owl.each(function() {
        var $a = $(this);
        $a.owlCarousel({
            autoplay: true,
            autoplayTimeout: 4000,
            loop: true,
            items: 1,
            center: true,
            nav: false,
            thumbs: true,
            thumbImage: false,
            thumbsPrerendered: true,
            thumbContainerClass: 'owl-thumbs',
            thumbItemClass: 'owl-thumb-item',
            navText: ['<i class="fa fa-chevron-left" aria-hidden="true"></i>','<i class="fa fa-chevron-right" aria-hidden="true"></i>'],
        });
    });
    $('[data-fancybox="galeria"]').fancybox({
        baseClass: "fancybox-custom-layout",
        infobar: false,
        touch: {
          vertical: false
        },
        buttons: ["close", "thumbs", "share"],
        animationEffect: "fade",
        transitionEffect: "fade",
        preventCaptionOverlap: false,
        idleTime: false,
        gutter: 0,
        // Customize caption area
        caption: function(instance) {
            var title=$('.product-detail .titulo').text();
            var resumen=$('.product-detail .resumen').text();
          return '<h3>'+title+'</h3><p>'+resumen+'</p>';
        }
      });

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
        console.log('no sw');
    }
}