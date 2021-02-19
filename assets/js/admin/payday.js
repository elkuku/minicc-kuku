const $ = require('jquery')
let currDate
let count = 0
$('.addRow').on('click', function () {
    addRow(this)
})
$('.searchDepId').on('click', function () {
    searchDepId(this)
})

function addRow(e) {
    const el = $(e)
    const oldDate = el.closest('td').prev().prev().prev().prev().prev().prev().prev().prev().find('input').val()
    $('#paymentsTable tbody').append('<tr>' + el.parent().parent().html() + '</tr>')
    e.remove()
    $('#paymentsTable tbody tr').last().find('td').first().find('input').val(oldDate)
    $('.addRow').on('click', function () {
        addRow(this)
    })
    $('.searchDepId').on('click', function () {
        searchDepId(this)
    })
}

function searchDepId(el) {
    let url = $('#lookup-url').attr('data-lookup-url')
    let e = $(el)

    let documentIdElement = e.closest('td').prev().find('input')
    let destElement = e.closest('td').next().find('input')
    let dateElement = e.closest('td').prev().prev().prev().prev().find('input')
    let methodElement = e.closest('td').prev().prev().find('select')
    let amountElement = e.closest('td').next().next().next().find('input')

    let messageElement = $('#messageContainer')

    let documentId = documentIdElement.val()

    if (!documentId) {
        messageElement.html('<div class="alert alert-warning">Insert a document number</div>')
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
                messageElement.html('<div class="alert alert-success">Deposit found</div>')
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
