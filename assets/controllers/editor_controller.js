import {Controller} from '@hotwired/stimulus';

import {Editor} from '@tiptap/core'
import StarterKit from '@tiptap/starter-kit'
import Table from '@tiptap/extension-table'
import TableCell from '@tiptap/extension-table-cell'
import TableHeader from '@tiptap/extension-table-header'
import TableRow from '@tiptap/extension-table-row'
import TextAlign from '@tiptap/extension-text-align'
import Mention from '@tiptap/extension-mention'

import suggestion from '../helper/suggestion.js'

import '../styles/editor.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['original', 'content', 'container',
        'button'
    ]

    static values = {
        fieldName: String,
    }

    editor = null
    buttons = {}

    connect() {
        this.originalTarget.style.display = 'none'
        this.editor = new Editor({
            element: this.contentTarget,
            extensions: [
                StarterKit,// @todo select only the stuff we need
                Table,
                TableRow,
                TableHeader,
                TableCell,
                TextAlign.configure({
                    types: ['heading', 'paragraph'],
                }),
                Mention.configure({
                    HTMLAttributes: {
                        class: 'mention',
                    },
                    renderText({options, node}) {
                        return `${options.suggestion.char}${node.attrs.label ?? node.attrs.id}]`
                    },
                    renderHTML({options, node}) {
                        return [
                            'span',
                            options.HTMLAttributes,
                            `${options.suggestion.char}${node.attrs.label ?? node.attrs.id}]`,
                        ]
                    },
                    suggestion,
                })
            ],
            content: this.originalTarget.value
        })

        this.editor.on('transaction', ({editor, transaction}) => {
            this.checkActive()
        })

        this.element.onsubmit = (e) => {
            //this.originalTarget.style.display = 'block'
            this.originalTarget.value = this.editor.getHTML()
            // return false;
        }
    }

    checkActive() {
        for (const button of this.buttonTargets) {
            const command = button.dataset.editorCommandParam

            // Check if the button should be disabled
            if (command && button.dataset.editorDisableParam) {
                button.disabled = !this.editor.can().chain().focus()[command]().run();
            }

            // Check if the button is "active"
            button.classList.remove('btn-secondary', 'btn-outline-secondary')
            let active = false

            if (button.dataset.editorArgumentParam) {
                // command with parameters
                try {
                    // param is an object
                    const param = JSON.parse(button.dataset.editorArgumentParam)
                    active = this.editor.isActive(button.dataset.name, param)
                } catch (e) {
                    // param is a string
                    active = this.editor.isActive({[button.dataset.name]: button.dataset.editorArgumentParam})
                }
            } else {
                active = this.editor.isActive(button.dataset.name)
            }

            if (active) {
                button.classList.add('btn-secondary');
            } else {
                button.classList.add('btn-outline-secondary');
            }
        }
    }

    insertTable(e) {
        e.preventDefault()
        this.editor.chain().focus().insertTable({rows: 3, cols: 3, withHeaderRow: true}).run()
    }

    toggleFullscreen(e) {
        e.preventDefault()
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            this.containerTarget.requestFullscreen();
        }
    }

    execute(e) {
        e.preventDefault()
        if (e.params.argument)
            this.editor.chain().focus()[e.params.command](e.params.argument).run()
        else {
            this.editor.chain().focus()[e.params.command]().run()
        }
    }
}
