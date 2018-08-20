require('bootstrap');

require('chart.js');

const g11n = require('g11n-js/dist/mloader-g11n')

$(function() {
    g11n.loadJsonData($('#g11n-setup').attr('data-g11n-setup'))
});
