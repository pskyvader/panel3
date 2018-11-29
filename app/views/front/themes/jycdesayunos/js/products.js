$('body').on('click', '.product-filter .iconc', function() {
    var data = createObjFromURI();
    data.view = $(this).prop('id');
    var url = create_url(null, data);
    go_url(url);
});

$('body').on('change', '.product-filter select#order', function() {
    var data = createObjFromURI();
    data.order = $('option:selected', $(this)).val();
    data.page = 1;
    var url = create_url(null, data);
    go_url(url);
});

$('body').on('change', '.product-filter select#limit', function() {
    var data = createObjFromURI();
    data.limit = $('option:selected', $(this)).val();
    data.page = 1;
    var url = create_url(null, data);
    go_url(url);
});