function inicio_image() {
    $('.image_multiple').each(function() {
        inicio_image_multiple($(this));
    });
    $('.image_individual').each(function() {
        var t = $(this);
        $(t).on('change', 'input[name="..."]', function(e) {
            habilitar(false);
            if (e.target.files.length > 0) {
                post(create_url(modulo, 'upload'), {}, "Subiendo Archivo", !1, e.target.files, after_guardar_image, t);
            }
        });
    });
}
$('body').on('click', '.eliminar_archivo', function() {
    $('#eliminar_editar', t).data('id', $(this).data('id'));
});
$('body').on('click', '#formulario .fileinput-exists,input[name="..."],#cancelar', function() {
    cancelar_archivo($(this).data('id'));
});
var after_guardar_image = function(data, t) {
    $('.tmp', t).val(data.archivos[0].name);
    $('.name', t).val(data.archivos[0].name);
};

function cancelar_archivo(data_id) {
    if (xhr != null) {
        xhr.abort();
        notificacion_footer("Subida cancelada");
        xhr = null;
    }
    barra(0);
    habilitar(true);
    eliminar_archivo(data_id);
}

function eliminar_archivo(campo) {
    $('input[name="image[' + campo + '][0][url]"]').val('');
    $('input[name="image[' + campo + '][0][tmp]"]').val('');
    $('input[name="' + campo + '"]').val('');
    $('img.' + campo).remove();
    $('.eliminar_archivo[data-id=' + campo + ']').hide();
}

function inicio_image_multiple(e) {
    var url = create_url(modulo, 'upload');
    var multiple = false;
    var parallel = 3;
    var new_line = $('.new_line', e).clone();
    $('.new_line', e).remove();
    $(".multiple_image", e).addClass('dropzone');
    var fotos_temporal = [];
    var id = [];
    var id_actual = 0;
    $('.image_list .campo.fields', e).each(function() {
        if ($('.id', this).val() > id) {
            id[$('.id', this).val()] = $('.id', this).val();
        }
    });
    $('.campo.fields .active', e).each(function() {
        multiple_active($(this));
    });
    $(".multiple_image", e).dropzone({
        addRemoveLinks: true,
        paramName: 'file',
        createImageThumbnails: false,
        url: url,
        parallelUploads: parallel,
        uploadMultiple: multiple,
        acceptedFiles: "image/*",
        init: function() {
            this.on("addedfile", function(e) {
                    fotos_temporal.push(e.name), habilitar(!1), e.previewElement.addEventListener("click", function() {
                        this.removeFile(e)
                    })
                }),
                this.on("removedfile", function(e, i) {
                    var a = $.inArray(e.name, fotos_temporal); - 1 != a && fotos_temporal.splice(a, 1), 0 == fotos_temporal.length && habilitar(!0)
                }),
                this.on("success", function(file, data) {
                    var datos = $.parseJSON(data);
                    if (typeof(datos['exito']) != 'undefined' && datos['exito']) {
                        var mensaje = 'Imagen ' + (($.isArray(datos['mensaje'])) ? datos['mensaje'].join('<br/>') : datos['mensaje']) + ' añadida correctamente';
                        notificacion_footer(mensaje);
                        $(datos['archivos']).each(function(k, v) {
                            if (file.name == v.original_name) {
                                do {
                                    id_actual++;
                                } while (typeof(id[id_actual]) != 'undefined');
                                id[id_actual] = id_actual;
                                var new_l = new_line.clone();
                                $('.image', new_l).prop('src', v.url);
                                $('.tmp', new_l).val(v.name);
                                $('.id', new_l).val(id_actual);
                                multiple_active($('.active', new_l));
                                $('.image_list').append(new_l);
                                if (id_actual == 1) $('.active', new_l).click();
                                $('.tooltips,.tooltip, [data-toggle="tooltip"]').tooltip();
                            }
                        });
                    } else {
                        notificacion('Oh no!', datos['mensaje'], 'error');
                    }
                    this.removeFile(file);
                }), this.on("complete", function(e, i) {
                    var a = $.inArray(e.name, fotos_temporal); - 1 != a && fotos_temporal.splice(a, 1), 0 == fotos_temporal.length && habilitar(!0)
                })
        }
    });
    $(".image_list.sorted_multiple").sortable({
        handle: ".move",
        vertical: !1,
        itemSelector: ".campo",
        placeholder: '<div class=" placeholder campo col-sm-2"><div class="move"></div></div>',
        containerSelector: ".sorted_multiple",
        distance: 30,
        tolerance: 0,
        onDragStart: function(e, i, a) {
            e.css({
                height: e.outerHeight() / 2,
                width: e.outerWidth() / 2
            }), e.addClass(i.group.options.draggedClass)
        }
    })
}