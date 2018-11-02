$('body').on('click', 'div.navbar-collapse li.nav-item', function() {
    $('div.navbar-collapse li.nav-item').removeClass('active');
    $(this).addClass('active');
});
$('footer h3.btn-desplegable').on('click', function() {
    var ul = $(this).siblings('.desplegable');
    var icono = $('.icono', this);
    icono.toggleClass('open');
    ul.slideToggle();
});


$('.dropdown-menu .dropdown-toggle').on('click', function(e) {
    if (!$(this).next().hasClass('show')) {
        $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
    }
    var $subMenu = $(this).next(".dropdown-menu");
    $subMenu.toggleClass('show');


    $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
        $('.dropdown-submenu .show').removeClass("show");
    });
    return false;
});







$('body').on('click', '.disabled', function() {
    return false;
});
$('body').on('click', 'a', function(e) {
    if ($(this).prop('target') != '_blank') {
        var href = $(this).prop('href');
        if (check_link(href)) {
            cargar_ajax(href);
            e.preventDefault();
        }
    }
});
$(window).on('popstate', function(e) {
    var href = e.currentTarget.location.href;
    if (check_link(href)) {
        cargar_ajax(href, false);
    }
});


function go_url(url) {
    if (check_link(url)) {
        cargar_ajax(url);
    } else {
        $(location).prop('href', url);
    }
}


function check_link(href) {
    if (href.indexOf(path) < 0) return false;
    else if (href == '') return false;
    else if (href.indexOf('#') >= 0) return false;
    else if (href.indexOf('jpg') >= 0) return false;
    else if (href.indexOf('png') >= 0) return false;
    else if (href.indexOf('jpeg') >= 0) return false;
    else if (href.indexOf('pdf') >= 0) return false;
    else if (href.indexOf('xlsx') >= 0) return false;
    else if (href.indexOf('lsx') >= 0) return false;
    else return true;
}

function cargar_ajax(href, push, data_form) {
    if (typeof(push) == 'undefined') {
        push = true;
    }
    if (typeof(data_form) == 'undefined') {
        data_form = "";
    }
    var actualizado = false;
    var actualizado_head = false;
    var valido = true;
    setTimeout(function() {
        if (!actualizado) {
            var e = $('#contenido-principal');
            $(e).css('opacity', 0.5);
            $(e).prepend($('#cargando').html());
        }
    }, 200);
    setTimeout(function() {
        if (!actualizado) {
            $(location).prop('href', href);
            valido = false;
        }
    }, 1500);
    $.post(href, data_form + '&ajax_header=true', function(data) {
        if (data.current_url != href) {
            console.log(data.current_url, href);
            $(location).prop('href', href);
            valido = false;
        } else {
            $("body").prop("id", "module-" + data.class);
            document.title = data.title;
            $("meta[property='og\\:site_name']").prop("content", data.title);
            $("meta[property='og\\:title']").prop("content", data.title);
            $("meta[property='og\\:url']").prop("content", data.current_url);
            $("meta[property='og\\:url']").prop("content", data.current_url);
            $("meta[property='modulo']").prop("content", data.modulo);
            if (data.image) {
                $("meta[property='og\\:image']").prop("content", data.image_url);
            } else {
                $("meta[property='og\\:image']").prop("content", data.logo);
            }
            if (data.description) {
                $("meta[name='description']").prop("content", data.description_text);
                $("meta[name='og\\:description']").prop("content", data.description_text);
            } else {
                $("meta[name='description']").prop("content", data.description_text);
                $("meta[name='og\\:description']").prop("content", data.description_text);
            }
            if (data.keywords) {
                $("meta[name='keywords']").prop("content", data.keywords_text);
            }
            if (push) history.pushState(data.current_url, data.title, data.current_url);
            actualizado_head = true;
            iniciar(actualizado, actualizado_head, data_form);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.responseText);
        $(location).prop('href', href);
        valido = false;
    });
    $.post(href, data_form + '&ajax=true', function(data) {
        if (valido) {
            actualizado = true;
            $('#contenido-principal').html(data);
            iniciar(actualizado, actualizado_head, data_form);
        }
    }).fail(function(jqXHR) {
        console.log(jqXHR.responseText);
        $(location).prop('href', href);
        valido = false;
    });
}

function iniciar(body, head, data_form) {
    if (body && head) {
        $('#contenido-principal').css('opacity', 1).removeClass();
        if (typeof inicio === "function") {
            inicio();
        }
        if (data_form == '') {
            mover('body', 0);
        }
        $('#navbarCollapse').removeClass('show');
    }
}


function activar_imagen() {
    $('source[data-srcset]').each(function() {
        if (is_visible($(this))) {
            load_source($(this));
        }
    });

    $('[data-background]').each(function() {
        if (is_visible($(this))) {
            load_background($(this));
        }
    });
    $('img[data-src]').each(function() {
        if (is_visible($(this))) {
            load_image($(this));
        }
    });
}

function load_image(image) {
    if (typeof($(image).data('src')) != 'undefined' && $(image).data('src') != '') {
        var src = $(image).data('src');
        $(image)[0].removeAttribute('data-src');
        $(image).prop('src', src).on('load', function(e) {
            $(e.target).fadeIn();
        });
    }
}

function load_source(source) {
    if (typeof($(source).data('srcset')) != 'undefined' && $(source).data('srcset') != '') {
        var srcset = $(source).data('srcset');
        $(source)[0].removeAttribute('data-srcset');
        $(source).prop('srcset', srcset);
    }
}


function load_background(background) {
    if (typeof($(background).data('background')) != 'undefined' && $(background).data('background') != '') {
        var back = $(background).data('background');
        $(background).css('background-image', 'url(' + back + ')');
        $(background)[0].removeAttribute('data-background');
    }
}



function is_visible(images) {
    var $w = $(window),
        th = 0;

    var $e = $(images);
    if ($e.is(":hidden")) {
        return false;
    } else {
        var wt = $w.scrollTop(),
            wb = wt + $w.height(),
            et = $e.offset().top,
            eb = et + $e.height();

        return eb >= wt - th && et <= wb + th;

    }
}

function mover(elemento, tiempo, delay) {
    var alto = 0;
    if (delay != 0) {
        setTimeout(function() {
            $('html, body').animate({
                scrollTop: ($(elemento).first().offset().top - alto)
            }, tiempo);
        }, delay);
    } else {
        $('html, body').animate({
            scrollTop: ($(elemento).first().offset().top - alto)
        }, tiempo);
    }
}

var notify = null;

function notificacion(mensaje, tipo) {
    if (tipo == 'error') tipo = 'danger';
    if (notify != null) {
        notify.close();
    }
    notify = $.notify({
        message: mensaje
    }, {
        type: tipo
    });
}

function barra(porcentaje) {
    /*if (porcentaje >= 0 && porcentaje < 100) {
        $.skylo('show', function() {
            $.skylo('set', porcentaje);
        });
    } else {
        setTimeout(function() {
            $.skylo('end');
        }, 500);
    }*/
}