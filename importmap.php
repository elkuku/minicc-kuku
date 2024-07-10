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
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    'chart.js' => [
        'version' => '3.9.1',
    ],
    'bootstrap' => [
        'version' => '5.3.3',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.3',
        'type' => 'css',
    ],
    'open-iconic/font/css/open-iconic-bootstrap.css' => [
        'version' => '1.1.1',
        'type' => 'css',
    ],
    'stimulus-use' => [
        'version' => '0.52.2',
    ],
    '@tiptap/core' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/state' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/view' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/keymap' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/model' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/transform' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/commands' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/schema-list' => [
        'version' => '2.4.0',
    ],
    'prosemirror-state' => [
        'version' => '1.4.3',
    ],
    'prosemirror-view' => [
        'version' => '1.33.6',
    ],
    'prosemirror-keymap' => [
        'version' => '1.2.2',
    ],
    'prosemirror-model' => [
        'version' => '1.21.0',
    ],
    'prosemirror-transform' => [
        'version' => '1.9.0',
    ],
    'prosemirror-commands' => [
        'version' => '1.5.2',
    ],
    'prosemirror-schema-list' => [
        'version' => '1.3.0',
    ],
    'w3c-keyname' => [
        'version' => '2.2.8',
    ],
    'orderedmap' => [
        'version' => '2.1.1',
    ],
    'prosemirror-view/style/prosemirror.min.css' => [
        'version' => '1.33.6',
        'type' => 'css',
    ],
    '@tiptap/starter-kit' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-blockquote' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-bold' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-bullet-list' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-code' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-code-block' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-document' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-dropcursor' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-gapcursor' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-hard-break' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-heading' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-history' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-horizontal-rule' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-italic' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-list-item' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-ordered-list' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-paragraph' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-strike' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-text' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/dropcursor' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/gapcursor' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/history' => [
        'version' => '2.4.0',
    ],
    'prosemirror-dropcursor' => [
        'version' => '1.8.1',
    ],
    'prosemirror-gapcursor' => [
        'version' => '1.3.2',
    ],
    'prosemirror-history' => [
        'version' => '1.4.0',
    ],
    'rope-sequence' => [
        'version' => '1.3.4',
    ],
    'prosemirror-gapcursor/style/gapcursor.min.css' => [
        'version' => '1.3.2',
        'type' => 'css',
    ],
    '@tiptap/extension-table' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-table-row' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-table-header' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-table-cell' => [
        'version' => '2.4.0',
    ],
    '@tiptap/pm/tables' => [
        'version' => '2.4.0',
    ],
    'prosemirror-tables' => [
        'version' => '1.3.7',
    ],
    'prosemirror-tables/style/tables.min.css' => [
        'version' => '1.3.7',
        'type' => 'css',
    ],
    '@tiptap/extension-text-align' => [
        'version' => '2.4.0',
    ],
    '@tiptap/extension-mention' => [
        'version' => '2.4.0',
    ],
    '@tiptap/suggestion' => [
        'version' => '2.4.0',
    ],
    'tippy.js' => [
        'version' => '6.3.7',
    ],
];
