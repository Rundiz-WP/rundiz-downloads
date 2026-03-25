<?php
/**
 * Cache class between this plugin and Rundiz\SimpleCache.
 * 
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Libraries;

if (!class_exists('\\RundizDownloads\\App\\Libraries\\Cache')) {
    class Cache
    {


        /**
         * @var \RundizDownloads\Rundiz\SimpleCache\Drivers\FileSystem;
         */
        protected $SimpleCache;


        /**
         * Cache class that connect between this plugin and Rundiz\SimpleCache.
         * 
         */
        public function __construct()
        {
            require_once plugin_dir_path(RUNDIZDOWNLOADS_FILE) . 'vendor/simple-cache/Rundiz/SimpleCache/SimpleCacheInterface.php';
            require_once plugin_dir_path(RUNDIZDOWNLOADS_FILE) . 'vendor/simple-cache/Rundiz/SimpleCache/Drivers/FileSystem.php';

            $this->SimpleCache = new \RundizDownloads\Rundiz\SimpleCache\Drivers\FileSystem(plugin_dir_path(RUNDIZDOWNLOADS_FILE) . '_cache');
        }// __construct


        /**
         * Get Simple Cache instance.
         * 
         * @return \RundizDownloads\Rundiz\SimpleCache\Drivers\FileSystem
         */
        public function getInstance()
        {
            return $this->SimpleCache;
        }// getInstance


    }
}