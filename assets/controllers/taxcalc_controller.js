import {Controller} from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ["withTax", "withoutTax"]
    static values = {taxrate: String}
    taxRate = 0

    connect() {
        this.taxRate = 1 + this.taxrateValue / 100
        this.calcWithTax()
    }

    calcWithoutTax() {
        this.withoutTaxTarget.value = (this.withTaxTarget.value / this.taxRate).toFixed(2)
    }

    calcWithTax() {
        this.withTaxTarget.value = (this.withoutTaxTarget.value.replace(',', '.') * this.taxRate).toFixed(2)
    }
}
