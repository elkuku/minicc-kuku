require('bootstrap');

require('chart.js');

window.tax = require('./taxcalc.js');

var pag = require('./pagination.js');
window.paginator = new pag();

window.PayDay = require('./payday');
