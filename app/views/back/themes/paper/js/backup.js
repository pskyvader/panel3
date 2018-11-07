$('body').on('click', 'button.new_respaldo', function() {
    tiempo_promedio = parseInt($('input[name=tiempo_rapido]').val());
    tipo_backup = 'rapido';
    post_basic(create_url(modulo, 'vaciar_log'), {}, 'Iniciando', function(data) {
        generar_backup_rapido($(this));
    });
});

$('body').on('click', 'button.new_respaldo_lento', function() {
    tiempo_promedio = parseInt($('input[name=tiempo_lento]').val());
    tipo_backup = 'lento';
    post_basic(create_url(modulo, 'vaciar_log'), {}, 'Iniciando', function(data) {
        generar_backup($(this));
    });
});

var tipo_backup = 'lento';
var respaldo_finalizado = false;
var total_respaldo = 0;
var tiempo_promedio = 0;
var tiempo_guardar = 0;
var tiempo_promedio_guardar = 0;

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
    //console.log(seconds + " segundos");
    return seconds;
}


function generar_backup_rapido(e) {
    habilitar(false);
    start();
    respaldo_finalizado = false;
    var accion = 'generar_backup';
    barra(5);
    post_basic(create_url(modulo, accion), {}, 'Recuperando lista de archivos', fin_backup);
    leer_log();
    setTimeout(function() {
        if (!respaldo_finalizado) {
            notificacion('Advertencia', 'El respaldo puede tomar un tiempo <br/> <b>por favor no cierres esta ventana<b/>', 'warning');
        }
    }, 3000);
}

function generar_backup(e) {
    habilitar(false);
    start();
    respaldo_finalizado = false;
    var accion = 'generar';
    barra(5);
    post_basic(create_url(modulo, accion), {}, 'Recuperando lista de archivos', lista_backup);
    leer_log();
    setTimeout(function() {
        if (!respaldo_finalizado) {
            notificacion('Advertencia', 'El respaldo puede tomar un tiempo <br/> <b>por favor no cierres esta ventana<b/>', 'warning');
        }
    }, 3000);
}


function leer_log() {
    if (!respaldo_finalizado) {
        $.ajax({
            url: path + 'log.json',
            success: function(data) {
                //console.log('leer', data);
                //end();
                if (typeof(data) == 'object') {
                    if (data.mensaje) {
                        notificacion_footer(data.mensaje);
                    }
                    if (data.porcentaje) {
                        barra(data.porcentaje + tiempo_promedio_guardar);
                        if (data.porcentaje == 100) {
                            fin_backup('{"exito":"true"}');
                        }
                    }
                    if (data.notificacion) {
                        if (tiempo_promedio > 0) {
                            var restante = tiempo_promedio - end() - 5;
                            if (tiempo_guardar == 0) tiempo_guardar = restante;
                            if (restante < 1) restante = 1;
                            notificacion(data.notificacion, 'Tiempo restante aproximado: ' + restante + ' segundos', 'warning');
                            tiempo_promedio_guardar = 40 - (restante / tiempo_guardar) * 40;
                            if (tiempo_promedio_guardar > 40) tiempo_promedio_guardar = 40;
                        } else {
                            notificacion(data.notificacion, '', 'warning');
                        }
                        setTimeout(leer_log, 5000);
                    } else {
                        setTimeout(leer_log, 500);
                    }
                } else {
                    setTimeout(leer_log, 500);
                }
            },
            error: function() {
                setTimeout(leer_log, 1000);
            },
            timeout: 500 //in milliseconds
        });
    }
}


function lista_backup(data) {
    var data = JSON.parse(data);
    total_respaldo = data.lista.length;
    if (data.exito) {
        //end();
        barra(10);
        post_basic(create_url(modulo, 'continuar'), {
            archivo_backup: data.archivo_backup,
            lista: JSON.stringify(data.lista),
            total: total_respaldo
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
        //end();
        if (data.lista.length > 0) {
            //var porcentaje = 10 + ((total_respaldo - data.lista.length) / total_respaldo) * 80;
            //barra(porcentaje);
            post_basic(create_url(modulo, 'continuar'), {
                archivo_backup: data.archivo_backup,
                lista: JSON.stringify(data.lista),
                total: total_respaldo
            }, data.archivo_actual + ' (' + (total_respaldo - data.lista.length) + '/' + total_respaldo + ')', continuar_backup);
        } else {
            post_basic(create_url(modulo, 'bdd'), {
                archivo_backup: data.archivo_backup
            }, 'Respaldando Base de datos', fin_backup);
        }
    } else {
        var mensaje = (($.isArray(data['mensaje'])) ? data['mensaje'].join('<br/>') : data['mensaje']);
        notificacion('Oh no!', mensaje, 'error');
        barra(0);
    }
}

function fin_backup(data) {
    if (!respaldo_finalizado) {
        habilitar(true);
        respaldo_finalizado = true;
        var data = JSON.parse(data);
        if (data.exito) {
            var tiempo = end();
            notificacion('Confirmacion', 'Respaldo completado', 'success');
            notificacion_footer("Tiempo total del respaldo: " + tiempo + " segundos");
            barra(100);
            post_basic(create_url(modulo, 'eliminar_error'));
            post_basic(create_url(modulo, 'actualizar_tiempo'), {
                tiempo: tiempo,
                tipo_backup: tipo_backup
            });
            go_url(url);
        } else {
            var mensaje = (($.isArray(data['mensaje'])) ? data['mensaje'].join('<br/>') : data['mensaje']);
            notificacion('Oh no!', mensaje, 'error');
            barra(0);
        }
    }
}