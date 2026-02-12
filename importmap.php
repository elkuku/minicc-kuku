<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    'chart.js' => [
        'version' => '4.5.1',
    ],
    'bootstrap' => [
        'version' => '5.3.8',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.8',
        'type' => 'css',
    ],
    'open-iconic/font/css/open-iconic-bootstrap.css' => [
        'version' => '1.1.1',
        'type' => 'css',
    ],
    'stimulus-use' => [
        'version' => '0.52.3',
    ],
    '@tiptap/core' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/state' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/view' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/keymap' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/model' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/transform' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/commands' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/schema-list' => [
        'version' => '3.19.0',
    ],
    'prosemirror-state' => [
        'version' => '1.4.4',
    ],
    'prosemirror-view' => [
        'version' => '1.41.6',
    ],
    'prosemirror-keymap' => [
        'version' => '1.2.3',
    ],
    'prosemirror-model' => [
        'version' => '1.25.4',
    ],
    'prosemirror-transform' => [
        'version' => '1.11.0',
    ],
    'prosemirror-commands' => [
        'version' => '1.7.1',
    ],
    'prosemirror-schema-list' => [
        'version' => '1.5.1',
    ],
    'w3c-keyname' => [
        'version' => '2.2.8',
    ],
    'orderedmap' => [
        'version' => '2.1.1',
    ],
    'prosemirror-view/style/prosemirror.min.css' => [
        'version' => '1.41.6',
        'type' => 'css',
    ],
    '@tiptap/starter-kit' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-blockquote' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-bold' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-bullet-list' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-code' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-code-block' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-document' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-dropcursor' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-gapcursor' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-hard-break' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-heading' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-history' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-horizontal-rule' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-italic' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-list-item' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-ordered-list' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-paragraph' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-strike' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-text' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/dropcursor' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/gapcursor' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/history' => [
        'version' => '3.19.0',
    ],
    'prosemirror-dropcursor' => [
        'version' => '1.8.2',
    ],
    'prosemirror-gapcursor' => [
        'version' => '1.4.0',
    ],
    'prosemirror-history' => [
        'version' => '1.5.0',
    ],
    'rope-sequence' => [
        'version' => '1.3.4',
    ],
    'prosemirror-gapcursor/style/gapcursor.min.css' => [
        'version' => '1.4.0',
        'type' => 'css',
    ],
    '@tiptap/extension-table' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-table-row' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-table-header' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-table-cell' => [
        'version' => '3.19.0',
    ],
    '@tiptap/pm/tables' => [
        'version' => '3.19.0',
    ],
    'prosemirror-tables' => [
        'version' => '1.8.5',
    ],
    'prosemirror-tables/style/tables.min.css' => [
        'version' => '1.8.5',
        'type' => 'css',
    ],
    '@tiptap/extension-text-align' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-mention' => [
        'version' => '3.19.0',
    ],
    '@tiptap/suggestion' => [
        'version' => '3.19.0',
    ],
    'tippy.js' => [
        'version' => '6.3.7',
    ],
    '@stimulus-components/checkbox-select-all' => [
        'version' => '6.1.0',
    ],
    '@kurkle/color' => [
        'version' => '0.4.0',
    ],
    '@tiptap/extension-link' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-list' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extension-underline' => [
        'version' => '3.19.0',
    ],
    '@tiptap/extensions' => [
        'version' => '3.19.0',
    ],
    '@tiptap/core/jsx-runtime' => [
        'version' => '3.19.0',
    ],
    'linkifyjs' => [
        'version' => '4.3.2',
    ],
];
