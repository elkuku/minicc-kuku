import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        lastRecipeNo: Number,
        paymentMethods: Array,
    }

    static targets = ['body', 'modal', 'template',
        'modalDate', 'modalStore', 'modalAmount', 'modalRecipe', 'modalMethod','modalComment',
    ]

    modal

    connect() {
        this.modal = new Modal(this.modalTarget)
    }

    showModal() {
        this.modalRecipeTarget.value = this.lastRecipeNoValue

        this.modal.show()
    }

    saveModal() {
        this.lastRecipeNoValue = +this.modalRecipeTarget.value + 1
        const clone = this.templateTarget.firstElementChild.cloneNode(true)

        clone.querySelector('[data-field="date"]').innerText = this.modalDateTarget.value
        clone.querySelector('[name="payments[date_cobro][]"]').value = this.modalDateTarget.value

        clone.querySelector('[data-field="store"]').innerHTML = `<h5><span class="badge text-bg-success"> ${this.modalStoreTarget.value} </span></h5>`
        clone.querySelector('[name="payments[store][]"]').value = this.modalStoreTarget.value

        clone.querySelector('[data-field="amount"]').innerText = this.modalAmountTarget.value
        clone.querySelector('[name="payments[amount][]"]').value = this.modalAmountTarget.value

        clone.querySelector('[data-field="recipe"]').innerHTML = `<span class="badge text-bg-info"> ${this.modalRecipeTarget.value} </span>`
        clone.querySelector('[name="payments[recipe][]"]').value = this.modalRecipeTarget.value

        clone.querySelector('[data-field="method"]').innerText = this.paymentMethodsValue.filter(method => method.id === +this.modalMethodTarget.value)[0].name
        clone.querySelector('[name="payments[method][]"]').value = this.modalMethodTarget.value

        clone.querySelector('[data-field="comment"]').innerText = this.modalCommentTarget.value
        clone.querySelector('[name="payments[comment][]"]').value = this.modalCommentTarget.value

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
}
