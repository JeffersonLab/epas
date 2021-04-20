
let mix = require('laravel-mix');

mix.setPublicPath('public/vendor/jlab-epas')
    .js('resources/js/app.js', 'js/')
    .sass('resources/css/app.scss', 'css/')
    .webpackConfig({
        output: {
            chunkFilename: 'js/[name].js?id=[chunkhash]'
        },
        resolve: {
            alias: {
                'vue$': 'vue/dist/vue.runtime.esm.js',
                '@': path.resolve('resources/js'),
            },
        },
    })
    .sourceMaps()
    .version()
