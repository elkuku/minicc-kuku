const $ = require('jquery')

$(function () {
    $('table').delegate('td', 'mouseover mouseleave', function (e) {
        if (e.type == 'mouseover') {
            $(this).parent().addClass('tableHover');
            $('colgroup').eq($(this).index()).addClass('tableHover');
        } else {
            $(this).parent().removeClass('tableHover');
            $('colgroup').eq($(this).index()).removeClass('tableHover');
        }
    });
});
