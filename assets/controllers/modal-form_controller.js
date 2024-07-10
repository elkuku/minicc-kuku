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
        //const $form = $(this.modalBodyTarget).find('form')
        //const $form = document.getElementsByTagName('form')
        //console.log($form)
        //console.log($form.serialize())
        console.log(this.formUrlValue)
        const formdata = new FormData(document.querySelector('form'));
        const params = new URLSearchParams(formdata);
        console.log('Serialized data:', params.toString())

        try {
            const response = await fetch(this.formUrlValue, {
                method: 'POST',
                body: params,
            })
            /*
            await $.ajax({
                url: this.formUrlValue,
                method: $form.prop('method'),
                data: $form.serialize()
            });
            */
            this.modal.hide()
            this.dispatch('success')
        } catch (e) {
            this.modalBodyTarget.innerHTML = e.responseText
        }
    }
}

