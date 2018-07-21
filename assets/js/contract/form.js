require('tinymce');
require('tinymce/themes/modern/theme');

tinymce.init({
    selector: 'textarea',
    height: 500,
    menubar: false,
    plugins: [],
    skin_url: '../build/js/skins/lightgray',
    toolbar: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent'
})
