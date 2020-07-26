function searchDepId(url) {
    var prefix = 'transaction_type_'
    var documentIdElement = $('#' + prefix + 'document')
    var destElement = $('#' + prefix + 'depId')
    var dateElement = $('#' + prefix + 'date')
    var methodElement = $('#' + prefix + 'method')
    var amountElement = $('#' + prefix + 'amount')

    var messageElement = $('#messageContainer')

    var documentId = documentIdElement.val()

    if (!documentId) {
        messageElement.html('<div class="alert alert-warning">Introduzca el documento</div>')
        documentIdElement.focus()
        return false
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: 'document_id=' + documentId,
        beforeSend: function () {
            messageElement.html(
                '<div class="progress"><div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar"' +
                ' style="width:100%">Procesando...</div></div>')
        },
        success: function (response) {
            if (response.error) {
                messageElement.html('<div class="alert alert-danger">' + response.error + '</div>')
                documentIdElement.focus()
            } else {
                messageElement.html('<div class="alert alert-success">Deposit found.</div>')
                console.log(response.data)
                var depo = response.data[0]
                documentIdElement.val(depo.document)
                destElement.val(depo.id)
                dateElement.val(depo.date)
                amountElement.val(depo.amount)
                methodElement.val(2)
            }
        }
    })

    return false
}

window.searchDepId = searchDepId
