<?php
/**
 * Based controller that have common use method(s).
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front;

if (!class_exists('\\RdDownloads\\App\\Controllers\Front\\ControllerBased')) {
    abstract class ControllerBased
    {


        /**
         * Set page title instead of letting it displaying "Page not found."
         * 
         * @link https://developer.wordpress.org/reference/hooks/document_title_parts/ Reference.
         * @param string $customTitle The page title.
         */
        public function setTitle($customTitle)
        {
            if (!is_scalar($customTitle)) {
                return null;
            }

            add_filter('document_title_parts', function($title) use ($customTitle) {
                $title['title'] = $customTitle;
                return $title;
            });

            // in case this site has Yoast SEO plugin.
            // this plugin make `document_title_parts` filter dead.
            // @link https://github.com/Yoast/wordpress-seo/issues/3579 See issue.
            add_filter('wpseo_title', function($title) use ($customTitle) {
                $sep = '|';
                if (!is_admin()) {
                    $name = get_bloginfo('name');
                    return "{$customTitle} {$sep} {$name}";
                }
                return $title;
            });
        }// setTitle


    }
}