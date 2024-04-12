import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        console.log(document.body)
        document.body.classList.add('bg-atacames')

    }
}
