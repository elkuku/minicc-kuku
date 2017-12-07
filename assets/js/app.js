//var $ = require('jquery');

// This seems legacy stuff...
window.$ = $;
//window.jQuery = $;

require('bootstrap-sass');

require('chart.js');
require('bootstrap-datepicker');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
