var ancho = 0;
var alto = 0;
var altomas = 0;
function js_mostrar(estado){
    var obj = $("#divSalir");
    if(estado == 1)
        obj.css("display", "");
    else
        obj.css("display", "none");
}
function manteneractivo(){
    $.ajax({
        url: '/usuario/manteneractivo/',
        success: function(data) { }
    });
    setTimeout("manteneractivo()", 5000);
}
$(document).ready(function() {
    if(!$.placeholder.input || !$.placeholder.textarea) {
        $('#hint').hide();
    }
    $("#cambiarestado").click(function(){
        $.ajax({
            url: '/usuario/cambiarestado/',
            success: function(data) {
                if($("#estadousu").attr("src") == "/images/usucon.png")
                    $("#estadousu").attr("src", "/images/usudes.png");
                else
                    $("#estadousu").attr("src", "/images/usucon.png");
            }
        });
    });
    $.ajax({
        url: '/usuario/comprobarestado/',
        success: function(data) {
            if(data == 0)
                $("#estadousu").attr("src", "/images/usudes.png");
            else
                $("#estadousu").attr("src", "/images/usucon.png");
        }
    });
    
    manteneractivo();
    var cadena = location.href;    
    if(cadena.indexOf("/usuario") != -1)
        $("#usuario").addClass("toolbar-activo");
    if(cadena.indexOf("/empleado") != -1)
        $("#empleado").addClass("toolbar-activo");
    if(cadena.indexOf("/cronograma") != -1)
        $("#cronograma").addClass("toolbar-activo");
    if(cadena.indexOf("/evaluacion") != -1)
        $("#evaluacion").addClass("toolbar-activo");
    if(cadena.indexOf("/reporte") != -1)
        $("#reporte").addClass("toolbar-activo");
    if(cadena.indexOf("/maestro") != -1)
        $("#maestro").addClass("toolbar-activo");
    
    $().UItoTop({ easingType: 'easeOutQuart' });
    
    $("ul.submenu").parent().append("<span></span>"); 	
    $("ul.menu li span, #maestro").hover(function() {
        $(this).parent().find("ul.submenu").slideDown(100).show();
        $(this).parent().hover(function() {}, function(){	
            $(this).parent().find("ul.submenu").slideUp(100);
        });
    }).hover(function() { 
        $(this).addClass("subhover");
    }, function(){
        $(this).removeClass("subhover"); 
    });
    js_altopagina();
});
window.onbeforeunload = Call;
function Call() {
    $("html").css("overflow","hidden");
    $(".oculto").css("display", "inherit");
    
    var userAgent = navigator.userAgent.toLowerCase();    
    if(userAgent.indexOf("msie") != -1){
        $("#imgLoading").attr("src", "http://saludocupacional.buenaventura.com.pe/images/loading.gif");
    }
    setTimeout(function() {
    	$(".oculto").css("display","none");
	$("html").css("overflow","auto");
    }, 5000 );
}
function js_altopagina(){
    var userAgent = navigator.userAgent.toLowerCase();    
     
    if(userAgent.indexOf("msie") != -1){
        var version;
        if(userAgent.indexOf("7.0") != -1) version = 7;
        if(userAgent.indexOf("8.0") != -1) version = 8;
        if(userAgent.indexOf("9.0") != -1) version = 9;
        if (version < 9){
            ancho = -17;
            alto = -30;
            altomas = -50;
        }
        if (version == 8)
            ancho = -17;
    }

    $("html").css("height","100%");
    var altoHTML = $("html").css("height");
    $("html").css("height","auto");
    var obtenerAlto = altoHTML.split("px")
    var nuevoAltoTablaCuerpo = parseInt(obtenerAlto[0]) - 55;
    $(".tblCuerpo").css("height", nuevoAltoTablaCuerpo + "px");
}
function js_resetForm(contenedor){   
    $(contenedor + ' form').each (function(){
      this.reset();
    })
}

function isJson(data){
    try{
        var objs = jQuery.parseJSON(data);
        if(objs.status != -1)
            return false;
    }catch(err){
        return false;
    }
    return true;
}
function js_processDataReturn(dialog, data)
{   
    var obj = jQuery.parseJSON(data);
    if ( obj.status >=0 )
    {
        if ( $( "#jqgResult" ).length > 0 ) 
    
        if ( dialog!="" )
            $( dialog ).dialog( "close" );
        return true;
    }

    if (obj.message != "")
    {
        $( dialog + " #divMessage" ).html( obj.message );
        $( dialog + " #divMessage" ).show();
    }

    if (obj.alert != "")
        alert( obj.alert );

    if (obj.object != "")
        $( dialog + ' label[for="'+ obj.object +'"]' ).addClass("error-text");
    return true;
}