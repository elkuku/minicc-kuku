require('tinymce')
require('tinymce/themes/silver')
require('tinymce/icons/default')
require('tinymce/plugins/fullscreen')

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
