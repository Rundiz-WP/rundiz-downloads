<?php
/**
 * The manual update for running new version of code.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Update\Manual;

if (!interface_exists('\\RdDownloads\\App\\Update\\Manual\\ManualUpdateInterface')) {
    interface ManualUpdateInterface
    {


        /**
         * Run the manual update code.
         */
        public function run();


    }
}