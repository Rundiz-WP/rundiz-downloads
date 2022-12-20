<?php
/**
 * Main app class. extend this class if you want to use any method of this class.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App;

use RdDownloads\App\Controllers as Controllers;

if (!class_exists('\\RdDownloads\\App\\App')) {
    class App
    {


        /**
         * @var \RdDownloads\App\Libraries\Loader
         */
        public $Loader;


        /**
         * Load text domain. (language files)
         */
        public function loadLanguage()
        {
            load_plugin_textdomain('rd-downloads', false, dirname(plugin_basename(RDDOWNLOADS_FILE)) . '/App/languages/');
        }// loadLanguage


        /**
         * Run the WP plugin app.
         */
        public function run()
        {
            add_action('plugins_loaded', function() {
                // @link https://codex.wordpress.org/Function_Reference/load_plugin_textdomain Reference.
                // load language of this plugin.
                $this->loadLanguage();
            });

            // Any method that must be called before auto register controllers must be manually write it down here, below this line.
            $StylesAndScripts = new \RdDownloads\App\Libraries\StylesAndScripts();
            $StylesAndScripts->manualRegisterHooks();
            unset($StylesAndScripts);

            // Initialize the loader class.
            $this->Loader = new \RdDownloads\App\Libraries\Loader();
            $this->Loader->autoRegisterControllers();

            // The rest of controllers that is not able to register via loader's auto register.
            // They must be manually write it down here, below this line.
            // For example:
            // $SomeController = new \RdDownloads\App\Controllers\SomeController();
            // $SomeController->runItHere();
            // unset($SomeController);// for clean up memory.
            // ------------------------------------------------------------------------------------
        }// run


    }
}