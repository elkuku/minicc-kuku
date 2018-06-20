const fs = require("fs");
const Encore = require('@symfony/webpack-encore');

const CopyWebpackPlugin = require('copy-webpack-plugin');
const ImageminPlugin = require('imagemin-webpack-plugin').default;

Encore

    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')

    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')

    // show OS notifications when builds finish/fail
    //.enableBuildNotifications()

    //-- A sub directory...
    //.setPublicPath(Encore.isProduction() ? '/build' : '/minicc-kuku-4/public/build')

    .cleanupOutputBeforeBuild()
    //.enableSourceMaps(!Encore.isProduction())

    // uncomment for legacy applications that require $/jQuery as a global variable
    .autoProvidejQuery()

    // uncomment if you use Sass/SCSS files
    //.enableSassLoader()
    .enableSassLoader(function(sassOptions) {}, {
        resolveUrlLoader: false
    })

    // uncomment to create hashed filenames (e.g. app.abc123.css)
    //.enableVersioning(Encore.isProduction())
    .enableVersioning(false)

    // uncomment to define the assets of the project
    .createSharedEntry('js/common', ['jquery'])

    .addEntry('js/app', './assets/js/app.js')
    .addEntry('js/index-charts', './assets/js/index-charts.js')
    .addEntry('js/stores/transactions', './assets/js/stores/transactions.js')
    .addEntry('js/login', './assets/js/login.js')
    .addEntry('js/pagos-por-ano', './assets/js/pagos-por-ano.js')

    .addStyleEntry('css/app', './assets/css/app.scss')

    .addPlugin(new CopyWebpackPlugin([
        {
            from: 'node_modules/tinymce/skins',
            to: 'js/skins'
        },
        {
            from: 'assets/images/',
            to: 'images/'
        }
    ]))

    .addPlugin(new ImageminPlugin({ test: /\.(jpe?g|png|gif|svg)$/i }))

;

let config = Encore.getWebpackConfig();

if(!Encore.isProduction()) {
    fs.writeFile("fakewebpack.config.js", "module.exports = "+JSON.stringify(config), function(err) {
        if(err) {
            return console.log(err);
        }
        console.log("fakewebpack.config.js written");
    });
}

module.exports = config;
