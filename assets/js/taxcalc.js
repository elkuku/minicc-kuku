const $ = require('jquery')

const taxRate = 1 + $('#tax-rate').attr('data-tax-rate') / 100

$('.taxWithTax').on('click', function () {
    let eWithTax = $(this).attr('data-with-tax')
    let eWithoutTax = $(this).attr('data-without-tax')

    $(eWithTax).val(withTax($(eWithoutTax).val()))
})

$('.taxWithoutTax').on('click', function () {
    let eWithTax = $(this).attr('data-with-tax')
    let eWithoutTax = $(this).attr('data-without-tax')

    $(eWithoutTax).val(WithoutTax($(eWithTax).val()))
})

function WithoutTax(withTax) {
    let val = withTax / taxRate

    return val.toFixed(2)
}

function withTax(WithoutTax) {
    let val = WithoutTax * taxRate

    return val.toFixed(2)
}
