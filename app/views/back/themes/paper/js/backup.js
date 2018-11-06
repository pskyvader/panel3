$('body').on('click', 'button.new_respaldo', function() {
    generar_backup($(this));
});

var respaldo_finalizado = false;
var total_respaldo = 0;


var startTime, endTime;

function start() {
    startTime = new Date();
};

function end() {
    endTime = new Date();
    var timeDiff = endTime - startTime; //in ms
    // strip the ms
    timeDiff /= 1000;

    // get seconds 
    var seconds = Math.round(timeDiff);
    console.log(seconds + " seconds");
}


function generar_backup(e) {
    start();
    respaldo_finalizado = false;
    var accion = 'generar';
    barra(5);
    post_basic(create_url(modulo, accion), {}, 'Recuperando lista de archivos', lista_backup);
    setTimeout(function() {
        if (!respaldo_finalizado) {
            notificacion('Advertencia', 'El respaldo puede tomar un tiempo <br/> <b>por favor no cierres esta ventana<b/>', 'warning');
        }
    }, 3000);
}


function lista_backup(data) {
    var data = JSON.parse(data);
    total_respaldo = data.lista.length;
    if (data.exito) {
        end();
        barra(10);
        post_basic(create_url(modulo, 'continuar'), {
            archivo_backup: data.archivo_backup,
            lista: JSON.stringify(data.lista)
        }, 'Generando archivo', continuar_backup);
    } else {
        var mensaje = (($.isArray(data['mensaje'])) ? data['mensaje'].join('<br/>') : data['mensaje']);
        notificacion('Oh no!', mensaje, 'error');
        barra(0);
    }
}

function continuar_backup(data) {
    var data = JSON.parse(data);
    if (data.exito) {
        end();
        if (data.lista.length > 0) {
            var porcentaje = 10 + ((total_respaldo - data.lista.length) / total_respaldo) * 80;
            barra(porcentaje);
            post_basic(create_url(modulo, 'continuar'), {
                archivo_backup: data.archivo_backup,
                lista: JSON.stringify(data.lista)
            }, data.archivo_actual + ' (' + (total_respaldo - data.lista.length) + '/' + total_respaldo + ')', continuar_backup);
        } else {
            post_basic(create_url(modulo, 'bdd'), {
                archivo_backup: data.archivo_backup
            }, 'Respaldando Base de datos', bdd_backup);

        }
    } else {
        var mensaje = (($.isArray(data['mensaje'])) ? data['mensaje'].join('<br/>') : data['mensaje']);
        notificacion('Oh no!', mensaje, 'error');
        barra(0);
    }
}

function bdd_backup(data) {
    var data = JSON.parse(data);
    if (data.exito) {
        end();
        notificacion('Confirmacion', 'Respaldo completado', 'success');
        notificacion_footer(false);
        barra(100);
        go_url(url);
    } else {
        var mensaje = (($.isArray(data['mensaje'])) ? data['mensaje'].join('<br/>') : data['mensaje']);
        notificacion('Oh no!', mensaje, 'error');
        barra(0);
    }
}