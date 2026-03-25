<?php
/**
 * Main app class. extend this class if you want to use any method of this class.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App;


if (!class_exists('\\RundizDownloads\\App\\App')) {
    /**
     * Main app class.
     */
    class App
    {


        /**
         * @var \RundizDownloads\App\Libraries\Loader
         */
        public $Loader;


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
            $this->Loader = new \RundizDownloads\App\Libraries\Loader();
            $this->Loader->autoRegisterControllers();
        }// run


    }// App
}
