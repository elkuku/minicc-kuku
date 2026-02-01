import {Controller} from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ["withTax", "withoutTax"]
    static values = {taxrate: {type: Number, default: 0}}
    taxMultiplier = 1

    connect() {
        this.taxMultiplier = 1 + this.taxrateValue / 100
        this.calcWithTax()
    }

    calcWithoutTax() {
        const totalWithTax = this.#parseValue(this.withTaxTarget.value)
        this.withoutTaxTarget.value = (totalWithTax / this.taxMultiplier).toFixed(2)
    }

    calcWithTax() {
        const baseValue = this.#parseValue(this.withoutTaxTarget.value)
        this.withTaxTarget.value = (baseValue * this.taxMultiplier).toFixed(2)
    }

    #parseValue(value) {
        const parsed = parseFloat(value.replace(',', '.'))
        return isNaN(parsed) ? 0 : parsed
    }
}
