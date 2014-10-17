$(document).ready(function() {
    //Combobox
    $( ".combobox" ).combobox(); //nejde předávat parametry
    
    //bootstrap tooltip
    if ($("[rel=tooltip]").length) {
        $("[rel=tooltip]").tooltip();
    }
    
    // odeslání na formulářích
    $("form.ajax").submit(function () {
        $(this).ajaxSubmit();
        return false;
    });

    // odeslání pomocí tlačítek
    $("form.ajax :submit").click(function () {
        $(this).ajaxSubmit();
        return false;
    });
    
});