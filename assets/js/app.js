//var $ = require('jquery');

// This seems legacy stuff...
window.$ = $;

require('bootstrap-sass');

require('chart.js');
require('bootstrap-datepicker');

require('tinymce');
require('tinymce/themes/modern/theme');

window.tax = require('./taxcalc.js');

var pag = require('./pagination.js');
window.paginator = new pag();

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});
