import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        lastRecipeNo: Number,
        paymentMethods: Array,
    }

    static targets = ['body', 'modal', 'template',
        'modalDate', 'modalStore', 'modalAmount', 'modalRecipe', 'modalMethod', 'modalComment', 'modalIsEdit',
    ]

    modal
    editingElement

    connect() {
        this.modal = new Modal(this.modalTarget)
    }

    addPayment() {
        this.modalIsEditTarget.value = 0

        // @todo set the date?
        // this.modalDateTarget.value = ???
        this.modalStoreTarget.value = 0
        this.modalAmountTarget.value = ''
        this.modalRecipeTarget.value = this.lastRecipeNoValue
        this.modalMethodTarget.value = 1
        this.modalCommentTarget.value = ''

        this.modal.show()
    }

    editPayment(event) {
        event.preventDefault()
        console.log('Hello editElement')
        // @todo can we do better??
        const element = event.target.parentElement.parentElement.parentElement.parentElement.parentElement;
        console.log(element)
        this.editingElement = element

        this.modalIsEditTarget.value = 1
        this.modalDateTarget.value = element.querySelector('[name="payments[date_cobro][]"]').value
        this.modalStoreTarget.value = element.querySelector('[name="payments[store][]"]').value
        this.modalAmountTarget.value = element.querySelector('[name="payments[amount][]"]').value
        this.modalRecipeTarget.value = element.querySelector('[name="payments[recipe][]"]').value
        this.modalMethodTarget.value = element.querySelector('[name="payments[method][]"]').value
        this.modalCommentTarget.value = element.querySelector('[name="payments[comment][]"]').value

        this.modal.show()
    }

    deletePayment(event) {
        event.preventDefault()
        console.log('Hello deleteElement')
        // @todo can we do better??
        const element = event.target.parentElement.parentElement.parentElement.parentElement.parentElement;
        element.remove()
    }

    saveModal() {
        const isEdit = this.modalIsEditTarget.value === '1';
        const element = isEdit
            ? this.editingElement
            : this.templateTarget.firstElementChild.cloneNode(true)
        let USDollar = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        element.querySelector('[data-field="date"]').innerText = this.modalDateTarget.value
        element.querySelector('[name="payments[date_cobro][]"]').value = this.modalDateTarget.value

        element.querySelector('[data-field="store"]').innerHTML = `<h5><span class="badge text-bg-success"> ${this.modalStoreTarget.value} </span></h5>`
        element.querySelector('[name="payments[store][]"]').value = this.modalStoreTarget.value

        element.querySelector('[data-field="amount"]').innerText = USDollar.format(this.modalAmountTarget.value)
        //element.querySelector('[data-field="amount"]').innerText = new Intl.NumberFormat().format(this.modalAmountTarget.value)
        element.querySelector('[name="payments[amount][]"]').value = this.modalAmountTarget.value

        element.querySelector('[data-field="recipe"]').innerHTML = `<span class="badge text-bg-info"> ${this.modalRecipeTarget.value} </span>`
        element.querySelector('[name="payments[recipe][]"]').value = this.modalRecipeTarget.value

        element.querySelector('[data-field="method"]').innerText = this.paymentMethodsValue.filter(method => method.id === +this.modalMethodTarget.value)[0].name
        element.querySelector('[name="payments[method][]"]').value = this.modalMethodTarget.value

        element.querySelector('[data-field="comment"]').innerText = this.modalCommentTarget.value
        element.querySelector('[name="payments[comment][]"]').value = this.modalCommentTarget.value

        if (false === isEdit) {
            this.bodyTarget.appendChild(element);
            this.lastRecipeNoValue = +this.modalRecipeTarget.value + 1
        }

        this.modal.hide()
    }

}
