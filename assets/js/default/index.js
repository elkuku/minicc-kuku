const $ = require('jquery')

$(function() {
    console.log('buh');
    $('html').addClass('bg-atacames')
    $('#main-container').remove()
    $('#app-layout').css({'padding': '0'})
});
