import tippy from 'tippy.js'
import Component from './Component.js'

export default {
    char: '[',
    items: async ({query}) => {
        const response = await fetch('/contracts/get-template-strings')
        const items = await response.json()
        return items.filter(item => item.toLowerCase().startsWith(query.toLowerCase()))
            .slice(0, 5)
    },

    render: () => {
        let component
        let popup

        return {
            onStart: props => {
                if (!props.clientRect) {
                    return
                }

                component = new Component(props)

                popup = tippy('body', {
                    getReferenceClientRect: props.clientRect,
                    appendTo: () => document.body,
                    content: component.getContent(),
                    showOnCreate: true,
                    interactive: true,
                    trigger: 'manual',
                    placement: 'bottom-start',
                })
            },

            onUpdate(props) {
                if (!props.clientRect) {
                    return
                }

                component.setProps(props)
                popup[0].setContent(component.getContent())

                popup[0].setProps({
                    getReferenceClientRect: props.clientRect,
                })
            },

            onKeyDown(props) {
                if (props.event.key === 'Escape') {
                    popup[0].hide()

                    return true
                }

                const ret = component.onKeyDown(props)
                popup[0].setContent(component.getContent())
                return ret
            },

            onExit() {
                popup[0].destroy()
            }
        }
    }
}