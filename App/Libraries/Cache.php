<?php
/**
 * Cache class between this plugin and Rundiz\SimpleCache.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Cache')) {
    class Cache
    {


        /**
         * @var \RdDownloads\Rundiz\SimpleCache\Drivers\FileSystem;
         */
        protected $SimpleCache;


        /**
         * Cache class that connect between this plugin and Rundiz\SimpleCache.
         * 
         */
        public function __construct()
        {
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/simple-cache/Rundiz/SimpleCache/SimpleCacheInterface.php';
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/simple-cache/Rundiz/SimpleCache/Drivers/FileSystem.php';

            $this->SimpleCache = new \RdDownloads\Rundiz\SimpleCache\Drivers\FileSystem(plugin_dir_path(RDDOWNLOADS_FILE) . '_cache');
        }// __construct


        /**
         * Get Simple Cache instance.
         * 
         * @return \RdDownloads\Rundiz\SimpleCache\Drivers\FileSystem
         */
        public function getInstance()
        {
            return $this->SimpleCache;
        }// getInstance


    }
}