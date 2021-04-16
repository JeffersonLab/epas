
let mix = require('laravel-mix');

mix.setPublicPath('public')
    .js('resources/js/app.js', 'js/')
    .sass('resources/css/app.scss', 'css/')
    .webpackConfig({
        output: {
            publicPath: '/vendor/jlab-epas/',
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
