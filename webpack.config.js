var Encore = require('@symfony/webpack-encore');
const path = require('path');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')
    .enableSingleRuntimeChunk()
    .enableSassLoader(options => {
         options.sassOptions = {
             ...options.sassOptions,
             quietDeps: true
         };
    })
    .enablePostCssLoader()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

const config = Encore.getWebpackConfig();
config.resolve.alias = config.resolve.alias || {};
config.resolve.alias['@symfony/stimulus-bridge/controllers.json'] = path.resolve(__dirname, 'assets/controllers.json');

module.exports = config;
