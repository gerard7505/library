const Encore = require('@symfony/webpack-encore');
const path = require('path');

// Manually configure the runtime environment if not already configured by the "encore" command.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // Set the output directory
    .setOutputPath('public/build/')
    // Set the public path used by the web server
    .setPublicPath('/build')
    // Set the manifest key prefix (if needed for CDN or subdirectory deploy)
    //.setManifestKeyPrefix('build/')

    // Add entry point for React
    .addEntry('app', './frontend/src/assets/app.js')


    // Split entry chunks for better optimization
    .splitEntryChunks()

    // Enable single runtime chunk
    .enableSingleRuntimeChunk()

    // Enable source maps for better debugging in dev mode
    .enableSourceMaps(!Encore.isProduction())

    // Clean the output before building (ensure it's empty)
    .cleanupOutputBeforeBuild()

    // Enable versioning for production builds (to create hashed filenames)
    .enableVersioning(Encore.isProduction())

    // Enable React preset for Babel (no need to configure Babel here if it's in .babelrc or babel.config.js)
    .enableReactPreset()

    // Enable Stimulus bridge (if you're using Stimulus)
    .enableStimulusBridge('./frontend/src/assets/controllers')

    // Enable build notifications
    .enableBuildNotifications()

    // Enable Babel-loader for React JSX
    .enableBabelLoader()

    // Optionally, enable Vue if needed (you can remove this line if you are not using Vue)
    //.enableVueLoader()

;

module.exports = Encore.getWebpackConfig();
