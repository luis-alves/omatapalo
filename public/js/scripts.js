/* Script para salientar o botão escolhido no menubar */

$(function() {
    var path = window.location.pathname;
    // console.log(path);
    path = path.replace(/\/$/, "");
    // console.log(path);
    path = decodeURIComponent(path);
    // console.log(path);
    // console.log(path.substring((path.lastIndexOf('/')+1),path.lenght));

    $(".nav li a").each(function() {
        var href = $(this).attr('href');
        // console.log(href);
        if (path === href) {
            $(this).closest('li').addClass('active');
        } else {
            $(this).closest('li').removeClass();
        }
    });
});


/* script para efeito de dropbox multi escolha de clientes e obras */

$(document).ready(function() {
    $(".chzn-select").chosen({
        no_results_text: "Oops, não encontrou nenhum resultado!"
    });
});


/* script para efeito de calendário em escolha de data */
$(function() {
    $('#datainicio').datetimepicker({
        format: 'YYYY/MM/DD'
    });

    $('#datafim').datetimepicker({
        useCurrent: false, //Important! See issue #1075
        format: 'YYYY/MM/DD'
    });

    $("#datainicio").on("dp.change", function(e) {
        $('#datafim').data("DateTimePicker").minDate(e.date);
    });

    $("#datafim").on("dp.change", function(e) {
        $('#datainicio').data("DateTimePicker").maxDate(e.date);
    });
});
