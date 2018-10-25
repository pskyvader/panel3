function post(url_post, info, mensaje_inicial, archivo, callback, extra) {
    if (typeof(archivo) == 'undefined') {
        archivo = null;
    }
    if (typeof(callback) == 'undefined') {
        callback = null;
    }
    var data = new FormData();
    appendArray(data, info, 'campos');
    if (archivo != null) {
        $.each(archivo, function(key, value) {
            data.append(key, value);
        });
    }
    $.ajax({
        url: url_post,
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Dont process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        beforeSend: function() {
            notificacion(mensaje_inicial, 'warning');
        },
        xhr: function() {
            xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener("progress", function(evt) { //Barra de progreso de subida y ejecución
                if (evt.lengthComputable) {
                    var percentComplete = (evt.loaded / evt.total) * 80;
                    barra(percentComplete);
                } else {
                    barra(50);
                }
            }, false);
            xhr.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = 80 + (evt.loaded / evt.total) * 20;
                    barra(percentComplete);
                } else {
                    barra(90);
                }
            }, false);
            return xhr;
        },
        success: function(datos, textStatus, jqXHR) {
            console.log(datos);
            if (typeof(datos['exito']) != 'undefined' && datos['exito']) {
                var mensaje = (($.isArray(datos['mensaje'])) ? datos['mensaje'].join('<br/>') : datos['mensaje']);
                notificacion(mensaje, 'success');
                if (callback != null) {
                    callback(datos, extra);
                }
                if (typeof(datos['refresh']) != 'undefined' && datos['refresh']) {
                    go_url(url);
                }
            } else {
                var mensaje = (($.isArray(datos['mensaje'])) ? datos['mensaje'].join('<br/>') : datos['mensaje']);
                notificacion(mensaje, 'error');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
            console.log(jqXHR);
            console.log(errorThrown);
            notificacion('Ha ocurrido un error, por favor intenta más tarde', 'error');
        },
        complete: function() {
            xhr = null;
            barra(100);
            habilitar(true);
        }
    });
}


function appendArray(e, p, y) {
    if (!p && y) e.append(y, "");
    else if ("object" == typeof p)
        for (key in p) "object" == typeof p[key] ? appendArray(e, p[key], y + "[" + key + "]") : e.append(y + "[" + key + "]", p[key]);
    else e.append(y, p);
    return e
}

$.fn.serializeObject = function() {
    var a = {};
    var e, t, n = (e = this, t = [], $.each(e.serializeArray(), function(e, a) {
        if (a.name.indexOf("[]") < 0) return t.push(a), !0;
        var i = a.name.split("[]")[0],
            r = !1;
        $.each(t, function(e, n) {
            n.name === i && (r = !0, t[e].value.push(a.value))
        }), r || t.push({
            name: i,
            value: [a.value]
        })
    }), t);
    return $.each(n, function() {
        var e = this.value,
            n = function e(n, a) {
                if (n.length < 1) return a;
                var i = n[0];
                "]" == i.slice(-1) && (i = i.slice(0, -1));
                var r = {};
                if (1 == n.length) r[i] = a;
                else {
                    n.shift();
                    var t = e(n, a);
                    r[i] = t
                }
                return r
            }(this.name.split("["), e);
        $.extend(!0, a, n)
    }), a
};