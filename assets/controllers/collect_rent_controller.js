import {Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['value', 'total']

    connect() {
        this.recalc()
    }

    recalc() {
        let total = 0
        const formatterUSD = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
        });

        this.valueTargets.forEach(target => {
            total += parseFloat(target.value);
        })

        this.totalTarget.innerHTML = formatterUSD.format(total);
    }
}
