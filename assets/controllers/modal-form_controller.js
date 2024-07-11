import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap'

export default class extends Controller {
    static targets = [
        'modal', 'modalBody'
    ]

    static values = {
        formUrl: String,
    }

    modal = null

    connect() {
        this.modal = new Modal(this.modalTarget)
    }

    async openModal(event) {
        this.modalBodyTarget.innerHTML = 'Loading....'
        this.modal.show()
        const response = await fetch(this.formUrlValue)
        this.modalBodyTarget.innerHTML = await response.text()
    }

    async submitForm(event) {
        event.preventDefault()
        const formdata = new FormData(document.querySelector('form'));
        const params = new URLSearchParams(formdata);

        try {
            const response = await fetch(this.formUrlValue, {
                method: 'POST',
                body: params,
            })
            this.modal.hide()
            this.dispatch('success')
        } catch (e) {
            this.modalBodyTarget.innerHTML = e.responseText
        }
    }
}

