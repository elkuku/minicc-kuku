import {Controller} from '@hotwired/stimulus'

export default class extends Controller {
    static targets = [ "withTax", "withoutTax" ]
    static values = { taxrate: String }
    taxRate = 0

    outputElement = null;

    initialize() {
        this.outputElement = document.createElement('input')
        this.outputElement.className = 'form-control'
        this.outputElement.setAttribute('data-taxcalc2-target', 'withTax')
        this.outputElement.setAttribute('data-action', 'taxcalc2#calcWithoutTax')

        const label = document.createElement('label')
        label.textContent = 'incl. TAX: '

        const container = document.createElement('div')
        container.className = 'form-widget'

        container.append(this.outputElement)

        this.element.append(label)
        this.element.append(container)
    }

    connect () {
        this.taxRate = 1 + this.taxrateValue / 100
        this.calcWithTax()
    }

    calcWithoutTax() {
        console.log('calc without')
        this.withoutTaxTarget.value = (this.withTaxTarget.value / this.taxRate).toFixed(2)
    }

    calcWithTax() {
        this.withTaxTarget.value = (this.withoutTaxTarget.value * this.taxRate).toFixed(2)
    }

}
