require('bootstrap');
require('chart.js');

const $ = require('jquery')

$('#userSwitch').on('change', function () {
    window.location.href = this.value;
})
