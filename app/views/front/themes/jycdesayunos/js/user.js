function inicio_login(){
    var modulo=path+"cuenta";
    var url=create_url(modulo+"/verificar");
    post_basic(url, "", function(data){
        try {
            data = JSON.parse(data);
        } catch (e) {
            data = {
                mensaje: data,
                exito: false
            };
        }
        if(data.exito){
            var a=$('<a href="'+modulo+'/datos">Bienvenido '+data.mensaje+' / </a>');
            var b=$('<button id="logout">Salir</button>');
        }else{
            var a=$('<a href="'+modulo+'/login">Login / </a>');
            var b=$('<a href="'+modulo+'/registro">Registro</a>');
        }
        $('#cuenta').empty().append(a).append(b);
    });
}