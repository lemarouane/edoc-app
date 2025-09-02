const Encore = require('@symfony/webpack-encore');


// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('build')    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/app.js')
    .addEntry('langues', './assets/js/langues.js')
    .addEntry('datatable_trans', './assets/js/datatable_trans.js')
    .addEntry('tableaffichage', './assets/js/tableaffichage.js')
    .addEntry('orientation', './assets/css/orientation.css')
    .addEntry('orientationJs', './assets/js/orientation.js')
    .addEntry('bootstraprtl', './assets/css/bootstrap-rtl.css')
    .addEntry('stylertl', './assets/css/style-rtl.css')
    .addEntry('stage', './assets/js/stage.js')
    .addEntry('emploi', './assets/js/emploi.js')
    .addEntry('laureat', './assets/js/laureat.js')
    .addEntry('experience', './assets/js/experience.js')
    .addEntry('examen', './assets/js/examen.js')
    .addEntry('formation_doctorale', './assets/js/formation_doctorale.js')
    .addEntry('stages', './assets/js/stages.js')
    .addEntry('reinscription_fd', './assets/js/reinscription_fd.js')
    .addEntry('conventions', './assets/js/conventions.js')

    .addEntry('cvtheque', './assets/js/cvtheque.js')
    .addEntry('experience_cvtheque', './assets/js/experience_cvtheque.js')
    .addEntry('clubs_cvtheque', './assets/js/clubs_cvtheque.js')
    .addEntry('formations_cvtheque', './assets/js/formations_cvtheque.js')






    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })



    .copyFiles({
        from: './assets/images',

         // optional target path, relative to the output dir
         to: 'images/[path][name].[ext]',

         // if versioning is enabled, add the file hash too
         //to: 'images/[path][name].[hash:8].[ext]',

         // only copy files matching this pattern
         //pattern: /\.(png|jpg|jpeg)$/
     }) 

    .copyFiles({
            from: './assets/css',
            to: 'css/[path][name].[ext]',
    })
    .copyFiles({
        from: './assets/js',
        to: 'js/[path][name].[ext]',
    })

    .copyFiles({
        from: './assets/plugins',
        to: 'plugins/[path][name].[ext]',
    })








    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();


