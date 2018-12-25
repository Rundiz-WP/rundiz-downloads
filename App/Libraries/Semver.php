<?php
/**
 * Semantic versioning
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Semver')) {
    /**
     * Semantic versioning class that connect between the plugin and Composer/Semver classes.
     * 
     * Call to this class and then you can use the Composer/Semver classes.
     * 
     * @link https://github.com/composer/semver See the doc for more usage.
     */
    class Semver
    {


        /**
         * Semantic versioning by Composer.
         * 
         * Call to this class and then you can use the Composer/Semver classes.
         * 
         * @see https://github.com/composer/semver For the document.
         */
        public function __construct()
        {
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/semver/src/Constraint/ConstraintInterface.php';// interface must required first
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/semver/src/Constraint/Constraint.php';
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/semver/src/Constraint/EmptyConstraint.php';
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/semver/src/Constraint/MultiConstraint.php';
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/semver/src/Comparator.php';
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/semver/src/Semver.php';
            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/semver/src/VersionParser.php';
        }// __construct


        /**
         * Get default version constraint from specific version number.
         * 
         * @param string $version The specific version number.
         * @return string Return version number with default constraint, for example: ">=1.2.3". Return empty string if the version number is nothing.
         */
        public function getDefaultVersionConstraint($version)
        {
            if ((!is_null($version) && $version !== '')) {
                return '>=' . $version;
            }
            return '';
        }// getDefaultVersionConstraint


    }
}