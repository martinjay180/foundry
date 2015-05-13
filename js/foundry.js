function loadFilters(){
    var status = $.cookie('foundryFilters');
    if(status == "hide"){
        $(".foundryEntityTableCollapse").hide();
    }
    $(".foundryEntityTableFilterTitle").click(function(){
        $(".foundryEntityTableFiltersCollapse, .foundryEntityTableOptionsCollapse").toggle("blind");
        setFilterCookie();
    });
}

function setFilterCookie(){
    var status = $.cookie('foundryFilters');
    if(status == "hide"){
        $.cookie('foundryFilters', "show", { path: '/'});
    } else {
        $.cookie('foundryFilters', "hide", { path : '/'});
    }
}

function getFunky(action, data, afterFunk, aux){
    NProgress.start();
    $.post("index.php?" + action + "&suppress=yes", data)
        .done(function(data) {
        NProgress.done();
            switch(afterFunk){
                case AfterFunkEnum.NOACTION:
                    break;
                case AfterFunkEnum.ALERT:
                    alert(data);
                    break;
                case AfterFunkEnum.INSERTHTML:
                    $("#" + aux).html(data);
                    break;
                 case AfterFunkEnum.NEWFUNK:
                     window[aux](data);
                     break;
                 case AfterFunkEnum.RELOAD:
                     location.reload();
                     break;
            }
        });
}

AfterFunkEnum = {
    NOACTION : 0,
    ALERT : 1,
    INSERTHTML : 2,
    NEWFUNK: 3,
    RELOAD: 4
};

function round(num, decimal_places){
	var mul = Math.pow(10, decimal_places);
	return Math.round(num*mul)/mul;
}

function itemEditSave(){
    $.post( $( "#editTemplate" ).attr("action"), $( "#editTemplate" ).serialize() );
}


