import {Controller} from '@hotwired/stimulus'
import {useClickOutside, useDebounce} from 'stimulus-use'

export default class extends Controller {
    static values = {
        urlSearch: String,
        urlLookup: String,
    }

    static targets = ['result', 'resultItem',
        'date', 'amount', 'method', 'document', 'depid']

    static debounces = ['_search']

    currentSelection = 0

    connect() {
        console.log('eyyy')
        useClickOutside(this)
        useDebounce(this)
    }

    clickOutside(event) {
        this.resultTarget.innerHTML = ''
    }

    onSearchInput(event) {
        this._search(event.currentTarget.value)
        this.currentSelection = 0
    }

    onKeydown(event) {
        switch (event.code) {
            case 'ArrowDown':
                event.preventDefault()
                if (this.currentSelection === this.resultItemTargets.length) {
                    return
                }
                this.currentSelection++
                this._updateSearchResults()
                break
            case 'ArrowUp':
                event.preventDefault()
                if (this.currentSelection < 2) {
                    return
                }
                this.currentSelection--
                this._updateSearchResults()
                break
            case 'Enter':
                event.preventDefault()
                if (!this.currentSelection) {
                    return
                }
                this._queryDepo(this.resultItemTargets[this.currentSelection - 1].dataset.id)
                break
            default:
        }
    }

    _updateSearchResults() {
        this.resultItemTargets.forEach(function (e) {
            e.classList.remove('active')
        })
        this.resultItemTargets[this.currentSelection - 1].classList.add('active')
    }

    async _search(query) {
        if (!query) {
            this.resultTarget.innerHTML = ''
            return
        }
        const params = new URLSearchParams({
            q: query
        })

        const response = await fetch(`${this.urlSearchValue}?${params.toString()}`)
        this.resultTarget.innerHTML = await response.text()
    }

    queryDepo(event) {
        this._queryDepo(event.currentTarget.dataset.id)
    }

    async _queryDepo(id) {
        const params = new URLSearchParams({
            id: id
        })

        const response = await fetch(`${this.urlLookupValue}?${params.toString()}`)

        const resp = JSON.parse(await response.text())

        this.dateTarget.value = resp.date;
        this.amountTarget.value = resp.amount
        this.methodTarget.value = resp.entity
        this.documentTarget.value = resp.document
        this.depidTarget.value = resp.id

        this.resultTarget.innerHTML = ''
    }
}
