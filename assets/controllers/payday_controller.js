import { Controller } from 'stimulus';

export default class extends Controller {
    static values = {
        lastRecipeNo: Number,
    }

    static targets = ['tbody', 'row']

    currentDate = ''

    addRow(event) {
        const clone = this.rowTargets[this.rowTargets.length-1].cloneNode(true)

        const inputs = clone.getElementsByTagName('input')
        const selects = clone.getElementsByTagName('select')

        if (this.currentDate) {
            inputs[0].value = this.currentDate
        }

        if (inputs[2].value) {
            this.lastRecipeNoValue = parseInt(inputs[2].value) + 1
        }

        inputs[1].value = ''
        inputs[2].value = this.lastRecipeNoValue
        inputs[3].value = ''
        inputs[4].value = ''

        selects[0].value = 0
        selects[1].value = 1

        this.tbodyTarget.appendChild(clone)

        selects[0].focus()
    }

    changeDate(event) {
        this.currentDate = event.currentTarget.value
    }
}
