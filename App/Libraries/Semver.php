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
         * @param string $version The specific version number. Example: 1.2.3
         * @return string Return version number with default constraint, for example: ">=1.2.3". Return empty string if the version number is nothing.
         */
        public function getDefaultVersionConstraint($version)
        {
            if ((!is_null($version) && $version !== '') && is_scalar($version)) {
                return '>=' . $version;
            }

            return '';
        }// getDefaultVersionConstraint


        /**
         * Remove prefix from version.
         * 
         * Many GitHub repositories contain "v" prefix, for example: v1.2.3. This will remove "v" and left just 1.2.3.
         * 
         * @param string $version The version string.
         * @param string $prefix The prefix text.
         * @return string Return removed prefix.
         * @throws \InvalidArgumentException
         */
        public function removePrefix($version, $prefix = 'v')
        {
            if (!is_string($version)) {
                /* translators: %s: Argument name. */
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$version'));
            }

            if (!is_string($prefix)) {
                /* translators: %s: Argument name. */
                throw new \InvalidArgumentException(sprintf(__('The %s must be string.', 'rd-downloads'), '$prefix'));
            }

            return preg_replace('#(' . $prefix . ')?(.+)#iu', '$2', $version, 1);
        }// removePrefix


    }
}