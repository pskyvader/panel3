$(window).on('load', inicio);
$(window).on('load', register_sw);
$(window).on('load', activar_imagen);
$(window).on('scroll', activar_imagen);
$(window).on('resize', activar_imagen);
var path = $("meta[property='path']").prop("content");
var modulo = $("meta[property='modulo']").prop("content");
var url = $("meta[property='og:url']").prop("content");
var googlemaps_key = $("meta[property='googlemaps_key']").prop("content");
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
    $('textarea.autosize').autosize({
        append: "\n"
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
        //console.log('no sw');
    }
}