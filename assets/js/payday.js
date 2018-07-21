const $ = require('jquery')

// @todo window.$
window.$ = $

module.exports = function () {
    this.addRow = function (e) {
        $('#paymentsTable tbody').append('<tr>' + $(e).parent().parent().html() + '</tr>');
        e.remove();
    };

    this.searchDepId = function (e, url) {
        var documentIdElement = e.closest('td').prev().find('input');
        var destElement = e.closest('td').next().find('input');
        var dateElement = e.closest('td').prev().prev().prev().prev().find('input');
        var methodElement = e.closest('td').prev().prev().find('select');
        var amountElement = e.closest('td').next().next().next().find('input');

        var messageElement = $('#messageContainer');

        var documentId = documentIdElement.val();

        if (!documentId) {
            messageElement.html('<div class="alert alert-warning">Introduzca el documento</div>');
            documentIdElement.focus();
            return false;
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: 'document_id=' + documentId,
            beforeSend: function () {
                messageElement.html(
                    '<div class="progress"><div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar"' +
                    ' style="width:100%">Procesando...</div></div>');
            },
            success: function (response) {
                if (response.error) {
                    messageElement.html('<div class="alert alert-danger">' + response.error + '</div>');
                    documentIdElement.focus();
                }
                else {
                    messageElement.html('<div class="alert alert-success">Depósito encontrado.</div>');
                    console.log(response.data);
                    var depo = response.data[0];
                    documentIdElement.val(depo.document);
                    destElement.val(depo.id);
                    dateElement.val(depo.date);
                    amountElement.val(depo.amount);
                    methodElement.val(2);
                }
            }
        });

        return false;
    }
};
