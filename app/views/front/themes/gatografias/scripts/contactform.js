(function($) {
    "use strict";

    var options2 = { success: showResponseContact, beforeSubmit: showRequestContact };
    $('#contact-form').submit(function() {
        $(this).ajaxSubmit(options2);
        return false;
    });

})(jQuery);

function showResponseContact(responseText, statusText) {
    if (statusText == 'success') {
        jQuery('#contact-form-holder').html('<h5>Mensaje enviado</h5>');
        jQuery('#output-contact').html('<p>Gracias por contactarse con nosotros! Revisaremos tu mensaje lo antes posible.</p>');
    } else {
        alert('status: ' + statusText + '\n\nAlgo sali&oacute; mal');
    }
}

function showRequestContact(formData, jqForm, options2) {
    var form = jqForm[0];
    var validRegExp = /^[^@]+@[^@]+.[a-z]{2,}$/i;

    if (!form.name.value) {
        jQuery('#output-contact').html('<div class="output2">Favor de llenar el campo "Nombre"!</div>');
        return false;
    } else if (!form.email.value) {
        jQuery('#output-contact').html('<div class="output2">Favor de llenar el campo "Email"!</div>');
        return false;
    } else if (form.email.value.search(validRegExp) == -1) {
        jQuery('#output-contact').html('<div class="output2">Favor introducir Email v&aacute;lido!</div>');
        return false;
    } else if (!form.subject.value) {
        jQuery('#output-contact').html('<div class="output2">Favor de llenar el campo "Asunto"!</div>');
        return false;
    } else if (!form.message.value) {
        jQuery('#output-contact').html('<div class="output2">Favor de llenar el campo "Mensaje"!</div>');
        return false;
    } else {
        jQuery('#output-contact').html('Enviando mensaje...!');
        return true;
    }
}