import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
    }

    static targets = ['content']

    async refreshContent(event) {
        const target = this.hasContentTarget ? this.contentTarget : this.element

        target.style.opacity = .5

        const response = await fetch(this.urlValue)

        target.innerHTML = await response.text()

        target.style.opacity = 1
    }
}
