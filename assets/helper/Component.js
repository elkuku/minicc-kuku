export default class {
    selectedIndex = 0

    constructor(props) {
        this.props = props;
    }

    selectItem(index) {
        const item = this.props.items[index]

        if (item) {
            this.props.command({id: item})
        }
    }

    onKeyDown(props) {
        if (props.event.key === 'ArrowUp') {
            this.selectedIndex = (this.selectedIndex + this.props.items.length - 1) % this.props.items.length
            return true
        }

        if (props.event.key === 'ArrowDown') {
            this.selectedIndex = ((this.selectedIndex + 1) % this.props.items.length)
            return true
        }

        if (props.event.key === 'Enter') {
            this.selectItem(this.selectedIndex)
            props.event.preventDefault()
            return true
        }

        return false
    }

    setProps(props) {
        this.props = props
    }

    getContent() {
        const div = document.createElement('div')

        if (this.props.items.length) {
            this.props.items.map((item, index) => {
                const button = document.createElement('button')
                button.className = index === this.selectedIndex ? 'is-selected' : ''
                button.innerText = item
                button.onclick = (e) => {
                    this.selectItem(index)
                }
                div.appendChild(button)
            })
        } else {
            div.innerHTML = 'No result'
        }

        return div
    }
}
