
let mix = require('laravel-mix');

mix.setPublicPath('public/vendor/jlab-epas')
    .js('resources/js/app.js', 'js/')
    .sass('resources/css/app.scss', 'css/')
    .sourceMaps()
    .version()
    .vue()
