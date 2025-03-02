import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        lastRecipeNo: Number,
        paymentMethods: Array,
    }

    static targets = ['body', 'modal', 'template',
        'modalDate', 'modalStore', 'modalAmount', 'modalRecipe', 'modalMethod', 'modalComment',
        'modalDocument', 'modalDeposit',
        'modalIsEdit',
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
        this.modalDocumentTarget.value = ''
        this.modalDepositTarget.value = ''
        this.modalCommentTarget.value = ''

        this.modal.show()
    }

    editPayment(event) {
        event.preventDefault()

        // @todo can we do better??
        this.editingElement = event.target.parentElement.parentElement.parentElement.parentElement.parentElement;

        this.modalIsEditTarget.value = 1
        this.modalDateTarget.value = this.editingElement.querySelector('[name="payments[date][]"]').value
        this.modalStoreTarget.value = this.editingElement.querySelector('[name="payments[store][]"]').value
        this.modalAmountTarget.value = this.editingElement.querySelector('[name="payments[amount][]"]').value
        this.modalRecipeTarget.value = this.editingElement.querySelector('[name="payments[recipe][]"]').value
        this.modalMethodTarget.value = this.editingElement.querySelector('[name="payments[method][]"]').value
        this.modalCommentTarget.value = this.editingElement.querySelector('[name="payments[comment][]"]').value

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

        //element.querySelector('[data-field="date"]').innerText = this.modalDateTarget.value
        //element.querySelector('[name="payments[date][]"]').value = this.modalDateTarget.value
        this._updateFields(element, 'date', this.modalDateTarget.value)

//        element.querySelector('[data-field="store"]').innerHTML = `<h5><span class="badge text-bg-success"> ${this.modalStoreTarget.value} </span></h5>`
        //      element.querySelector('[name="payments[store][]"]').value = this.modalStoreTarget.value
        this._updateFields(element, 'store', this.modalStoreTarget.value,
            `<h5><span class="badge text-bg-success"> ${this.modalStoreTarget.value} </span></h5>`
        )

        element.querySelector('[data-field="amount"]').innerText = USDollar.format(this.modalAmountTarget.value)
        //element.querySelector('[data-field="amount"]').innerText = new Intl.NumberFormat().format(this.modalAmountTarget.value)
        element.querySelector('[name="payments[amount][]"]').value = this.modalAmountTarget.value

        element.querySelector('[data-field="recipe"]').innerHTML = `<span class="badge text-bg-info"> ${this.modalRecipeTarget.value} </span>`
        element.querySelector('[name="payments[recipe][]"]').value = this.modalRecipeTarget.value

        element.querySelector('[data-field="method"]').innerText = this.paymentMethodsValue.filter(method => method.id === +this.modalMethodTarget.value)[0].name
        element.querySelector('[name="payments[method][]"]').value = this.modalMethodTarget.value

        element.querySelector('[data-field="document"]').innerText = this.modalDocumentTarget.value
        element.querySelector('[name="payments[document][]"]').value = this.modalDocumentTarget.value

        element.querySelector('[data-field="deposit"]').innerText = this.modalDepositTarget.value
        element.querySelector('[name="payments[deposit][]"]').value = this.modalDepositTarget.value

        element.querySelector('[data-field="comment"]').innerText = this.modalCommentTarget.value
        element.querySelector('[name="payments[comment][]"]').value = this.modalCommentTarget.value

        if (false === isEdit) {
            this.bodyTarget.appendChild(element);
            this.lastRecipeNoValue = +this.modalRecipeTarget.value + 1
        }

        this.modal.hide()
    }

    _updateFields(element, name, inputValue, textValue = null) {
        textValue = textValue || inputValue
        element.querySelector('[data-field="' + name + '"]').innerHTML = textValue
        element.querySelector('[name="payments[' + name + '][]"]').value = inputValue
    }

}
