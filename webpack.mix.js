let mix = require('laravel-mix');
var webpack = require('webpack');
const glob = require('glob');

// Esposizione JQuery per HTML
mix.webpackConfig({
    module: {
        rules: [{
            test: require.resolve('jquery'),
            use: [{
                loader: 'expose-loader',
                options: 'jQuery'
            },{
                loader: 'expose-loader',
                options: '$'
            }]
        }]
    }
});

// Caricamento automatico delle liberie più comuni
mix.autoload({
    jquery: ['$', 'jQuery', 'window.$', 'window.jQuery'],
    moment: ['moment'],
    sweetalert2: ['swal'],
    toastr: ['toastr'],
    numeral: ['numeral'],
});

// Configurazione
var config = {
    production: 'public/assets', // Cartella di destinazione
    development: 'resources/assets', // Cartella dei file di personalizzazione
    paths: {
        js: 'js',
        css: 'css',
        scss: 'scss',
        images: 'img',
        fonts: 'webfonts'
    }
};

mix.setPublicPath(config.production);

// CSS di default
mix.sass(
    config.development + '/scss/app.scss',
    config.production + '/' + config.paths.css
).options({
    processCssUrls: false
});

// Copia dei webfont di Font Awesome
mix.copyDirectory(
    'node_modules/@fortawesome/fontawesome-free/webfonts',
    config.production + '/webfonts'
);

// CSS personalizzati
mix.styles([
    config.development + '/' + config.paths.css + '/*.css',
], config.production + '/' + config.paths.css + '/style.css');

// CSS dei temi
mix.styles([
    config.development + '/' + config.paths.css + '/themes/*.css',
], config.production + '/' + config.paths.css + '/themes.css');

// CSS di stampa
mix.styles([
    config.development + '/' + config.paths.css + '/print/*.css',
], config.production + '/' + config.paths.css + '/print.css');

// JS principali
mix.js([
    config.development + '/' + config.paths.js + '/app.js'
],
    config.production +  '/' + config.paths.js + '/app.js'
);

// Copia di PDFJS
mix.copyDirectory(
    'node_modules/pdf/web',
    config.production + '/pdfjs/web'
);
mix.copyDirectory(
    'node_modules/pdf/build',
    config.production + '/pdfjs/build'
);

// PHP DebugBar
mix.copyDirectory(
    'vendor/maximebf/debugbar/src/DebugBar/Resources',
    config.production + '/php-debugbar'
);

// CSRF
mix.copy(
    'vendor/owasp/csrf-protector-php/js/csrfprotector.js',
    config.production + '/' + config.paths.js  + '/csrf'
);

// ChartJS
mix.copy(
    'node_modules/chart.js/dist/Chart.min.js',
    config.production + '/' + config.paths.js
);

// Password Strength
mix.combine(
    'node_modules/pwstrength-bootstrap/dist/*.js',
    config.production + '/' + config.paths.js + '/password.js'
);

// Immagini
mix.copyDirectory(
    config.development + '/' + config.paths.images + '/',
    config.production + '/' + config.paths.images
);

// Gestione file personalizzati
/*
const files = pattern => glob.sync(pattern, { cwd: 'resources/assets' });

const globify = (pattern, out, mixFunctionName) => {
    files(pattern).forEach((path) => {
        mix[mixFunctionName](`resources/assets/${path}`, out);
    })
};

globify(config.development + '/scss/pages/*.scss', 'assets/pages', 'sass');
*/
