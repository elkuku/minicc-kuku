//var $ = require('jquery');

// This seems legacy stuff...
window.$ = $;

require('bootstrap');

require('chart.js');

require('tinymce');
require('tinymce/themes/modern/theme');

window.tax = require('./taxcalc.js');

var pag = require('./pagination.js');
window.paginator = new pag();

window.PayDay = require('./payday');
