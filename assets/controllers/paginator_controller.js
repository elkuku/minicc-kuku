import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = [
        'page', 'order', 'orderDir'
    ]

    goToPage({params: {page}}) {
        this.pageTarget.value = page
    }

    setOrdering({params: {order, orderDir}}) {
        this.orderTarget.value=order
        this.orderDirTarget.value=orderDir
    }

    changeAndSubmit() {
        this.resetAndSubmit()
        this.element.submit()
    }

    cleanAndSubmit({params: {element}}) {
        document.getElementsByName(element)[0].value = ''
    }

    resetAndSubmit() {
        this.pageTarget.value = ''
        this.orderTarget.value = ''
        this.orderDirTarget.value = ''
    }
}
