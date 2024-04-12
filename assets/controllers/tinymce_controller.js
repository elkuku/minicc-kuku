import { Controller } from '@hotwired/stimulus';

import tinymce from 'tinymce';
import 'tinymce/themes/silver'
import 'tinymce/icons/default'
import 'tinymce/plugins/fullscreen'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        console.log('tiny')
        tinymce.init({
            selector: 'textarea',
            height: 500,
            menubar: false,
            plugins: 'fullscreen',
            theme: 'silver',
            skin_url: '../build/js/skins/ui/oxide',
            content_css: '../build/js/skins/content/default/content.css',
            toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | fullscreen'
        })
    }
}
