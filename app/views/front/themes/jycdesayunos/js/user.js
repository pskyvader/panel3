function inicio_login() {
    var modulo = "cuenta/";
    var url = create_url(modulo + "verificar", null, path);
    post_basic(url, "", function(data) {
        try {
            data = JSON.parse(data);
        } catch (e) {
            data = {
                mensaje: data,
                exito: false
            };
        }
        if (data.exito) {
            var a = $('<a href="' + path + modulo + 'datos">Bienvenido ' + data.mensaje + ' / </a>');
            var b = $('<button id="logout">Salir</button>');
        } else {
            var a = $('<a href="' + path + modulo + 'login">Login / </a>');
            var b = $('<a href="' + path + modulo + 'registro">Registro</a>');
        }
        $('#cuenta').empty().append(a).append(b);
    });
}


$(document).on('submit', 'form.update-datos', function() {
    var modulo = "cuenta/";
    var url = create_url(modulo + "datos_process", null, path);
    var data = $(this).serializeObject();
    post(url, data, "Modificando datos", null, function(datos) {
        if (datos.exito) {
            if (datos.redirect) {
                var url = create_url(modulo + "logout", null, path);
                post(url, {}, "", null, function() {
                    inicio_login();
                    var url = create_url(modulo + "login", null, path);
                    go_url(url);
                });
            }
        }
    });
    return false;
});

$(document).on('submit', 'form.registro', function() {
    var modulo = "cuenta/";
    var url = create_url(modulo + "registro_process", null, path);
    var data = $(this).serializeObject();
    post(url, data, "Enviando datos de registro", null, function(datos) {
        if (datos.exito) {
            var url = create_url(modulo + "datos", null, path);
            inicio_login();
            go_url(url);
        }
    });
    return false;
});
$(document).on('submit', 'form.login', function() {
    var modulo = "cuenta/";
    var url = create_url(modulo + "login_process", null, path);
    var data = $(this).serializeObject();
    post(url, data, "Enviando datos de login", null, function(datos) {
        if (datos.exito) {
            var url = create_url(modulo + "datos", null, path);
            inicio_login();
            go_url(url);
        }
    });
    return false;
});

$(document).on('click', '#cuenta #logout', function() {
    var modulo = "cuenta/";
    var url = create_url(modulo + "logout", null, path);
    post(url, {}, "", null, function() {
        inicio_login();
    });
});