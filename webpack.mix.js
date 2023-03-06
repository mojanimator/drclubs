const mix = require('laravel-mix');
require('laravel-mix-polyfill');
mix
    .vue()
    .setResourceRoot('../')
    .setPublicPath('./')
    .js('src/js/app.js', '/assets/js/script.js')
    .js('src/js/woocommerce.js', '/assets/js/woocommerce.js')
    .js('src/js/customer-ui.js', '/assets/js/customer-ui.js')

    .sass('src/scss/app.scss', '/assets/css/style.css')

    .version()
    .browserSync('http://localhost:81/_wordpress/wordpress-6.0.1/wp-admin')
    .options({processCssUrls: false})
    .webpackConfig(webpack => {
        return {
            devtool: 'source-map'
//         plugins: [
//             new webpack.DefinePlugin({
//                 __VUE_OPTIONS_API__: false,
//                 __VUE_PROD_DEVTOOLS__: false,
//             }),
//         ],
        }
    })
    // .postCss('assets/css/style.css', '/assets/css/style.css', [
    //     require('rtlcss'),
    // ])
    .polyfill({
        enabled: true,
        // useBuiltIns: "entry",
        useBuiltIns: "usage",
        // targets: false
        // targets: "> 0.05%, not ie < 10, safari >= 8",
        targets: "since 2012",
    })
;