<?php
/**
 * The manual update for running new version of code.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Update\Manual;


if (!interface_exists('\\RundizDownloads\\App\\Update\\Manual\\ManualUpdateInterface')) {
    /**
     * Manual update interface.
     */
    interface ManualUpdateInterface
    {


        /**
         * Run the manual update code.
         */
        public function run();


    }// ManualUpdateInterface
}
