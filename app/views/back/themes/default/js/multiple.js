function inicio_multiple() {
    var table = $('.sorted_multiple').sortable({
        handle: '.move',
        itemSelector: '.campo',
        placeholder: '<div class="placeholder"/>',
        containerSelector: '.sorted_multiple',
        distance: 20,
        tolerance: 2,
        onDrop: function($item, container, _super) {
            var $clonedItem = $('<div/>').css({
                height: 0
            });
            $item.before($clonedItem);
            $clonedItem.animate({
                'height': $item.height()
            });
            $item.animate($clonedItem.position(), function() {
                $clonedItem.detach();
                _super($item, container);
            });
        }
    });
    $('div.form-group.multiple').each(function() {
        multiple($(this));
    });
}

function multiple(e) {
    var new_line = $('.new_line', e).clone();
    $('.new_line', e).remove();
    var count = $('input[name=count]', e).val();
    $('input[name=count]', e).remove();
    $('.campo.fields .active', e).each(function() {
        multiple_active($(this));
    });
    $(e).on('click', '.agregar_editar', function() {
        var new_l = new_line.clone();
        multiple_active($('.active', new_l));
        $(this).parent().parent().after(new_l);
        count++;
        return false;
    });
    $(e).on('click', '.quitar_editar', function() {
        $(this).parent().parent().remove();
        count--;
        if (count == 0) {
            $('.new_field', e).show();
        }
        return false;
    });
}

function multiple_active(active) {
    if (typeof(active) != 'undefined' && active.length > 0) {
        var n = parseInt(Math.random() * 100000);
        active[0].dataset.field = active.data('field') + n;
        active.data('field', active.data('field') + n);
        var input = active.siblings();
        input.prop('id', input.prop('id') + n);
    }
}