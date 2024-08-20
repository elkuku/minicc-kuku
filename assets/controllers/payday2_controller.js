import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        lastRecipeNo: Number,
    }

    static targets = ['body', 'modal', 'template',
        'modalRecipe','modalStore','modalDate',
    ]

    modal

    connect() {
        console.log('Hello ' + this.lastRecipeNoValue);
        this.modal = new Modal(this.modalTarget)
    }

    showModal() {
        console.log('Hello showModal')
        this.modalRecipeTarget.value = this.lastRecipeNoValue
        this.modal.show()
    }

    saveModal() {
        console.log('Hello saveModal')
        console.log(this.modalRecipeTarget.value)
        this.lastRecipeNoValue=+this.modalRecipeTarget.value+1
        const clone = this.templateTarget.firstElementChild.cloneNode(true)

        console.log(clone)
        console.log(clone.querySelector('[data-field="date"]'))
        console.log(clone.querySelector('[data-field="date"]').innerText)

        clone.querySelector('[data-field="date"]').innerText=this.modalDateTarget.value
        clone.querySelector('[name="payments[date_cobro][]"]').value=this.modalDateTarget.value

        clone.querySelector('[data-field="store"]').innerText=this.modalStoreTarget.value
        clone.querySelector('[name="payments[store][]"]').value=this.modalStoreTarget.value

        clone.querySelector('[data-field="recipe"]').innerText=this.modalRecipeTarget.value
        clone.querySelector('[name="payments[recipe][]"]').value=this.modalRecipeTarget.value

        const inputs = clone.getElementsByTagName('input')
        const selects = clone.getElementsByTagName('select')
        console.log(inputs, selects)

        this.bodyTarget.appendChild(clone)
        this.modal.hide()
    }

    deleteElement(event) {
        console.log('Hello deleteElement')
        // @todo can we do better??
        const element = event.target.parentElement.parentElement.parentElement.parentElement.parentElement;
        element.remove()
        event.preventDefault()
    }

    editElement(event) {
        console.log('Hello editElement')
        // @todo can we do better??
        const element = event.target.parentElement.parentElement.parentElement.parentElement.parentElement;
        console.log(element)
        event.preventDefault()
    }

    addRow(event) {
        const clone = this.rowTargets[this.rowTargets.length - 1].cloneNode(true)
        const inputs = clone.getElementsByTagName('input')
        const selects = clone.getElementsByTagName('select')
        console.log(inputs, selects)
        if (this.currentDate) {
            inputs[0].value = this.currentDate
        }

        if (inputs[2].value) {
            this.lastRecipeNoValue = parseInt(inputs[2].value) + 1
        }
        /*

                inputs[1].value = ''
                inputs[2].value = this.lastRecipeNoValue
                inputs[3].value = ''
                inputs[4].value = ''

                selects[0].value = 0
                selects[1].value = 1
        */
        this.bodyTarget.appendChild(clone)

        // selects[0].focus()
    }
}

class Payment {
    date
    store
    amount
}
