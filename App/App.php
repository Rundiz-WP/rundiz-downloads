<?php
/**
 * Main app class. Extend this class if you want to use any method of this class.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App;


if (!class_exists('\\RundizDownloads\\App\\App')) {
    /**
     * Plugin application main entry class.
     */
    class App
    {


        /**
         * Run the WP plugin app.
         */
        public function run()
        {
            // Any method that must be called before auto register controllers must be manually write it down here, below this line.
            $StylesAndScripts = new \RundizDownloads\App\Libraries\StylesAndScripts();
            $StylesAndScripts->manualRegisterHooks();
            unset($StylesAndScripts);

            // Initialize the loader class.
            $Loader = new \RundizDownloads\App\Libraries\Loader();
            $Loader->autoRegisterControllers();
            unset($Loader);
        }// run


    }// App
}
