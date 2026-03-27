<?php
/**
 * Search downloads via editor such as TinyMCE.
 *
 * @package rundiz-downloads
 */


namespace RundizDownloads\App\Controllers\Admin\Downloads\Xhr;


if (!class_exists('\\RundizDownloads\\App\\Controllers\\Admin\\Downloads\\Xhr\\XhrDownloadsBrowser')) {
    /**
     * XHR downloads browser class.
     */
    class XhrDownloadsBrowser extends \RundizDownloads\App\Controllers\XhrBased implements \RundizDownloads\App\Controllers\ControllerInterface
    {


        /**
         * {@inheritDoc}
         */
        public function registerHooks()
        {
            if (is_admin()) {
                add_action('wp_ajax_RdDownloadsBrowserSearch', [$this, 'searchDownloads']);
            }
        }// registerHooks


        /**
         * Search for download.
         */
        public function searchDownloads()
        {
            // this was called from classic editor, so it is required at lease "edit_posts" capability.
            $this->commonAccessCheck(['get'], ['rundiz-downloads_editor-ajax-nonce', 'security'], 'edit_posts');

            if (!current_user_can('edit_posts')) {
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('You do not have permission to access this page.', 'rundiz-downloads');
                wp_send_json($output, 403);
            }

            $output = [];
            $responseStatus = 200;

            $output['per_page'] = 10;
            $output['current_page'] = (isset($_GET['page']) && !empty(trim(wp_unslash($_GET['page']))) ? floatval(wp_unslash($_GET['page'])) : 1);// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            $RdDownloads = new \RundizDownloads\App\Models\RdDownloads();
            $options = [];
            if (isset($_GET['search']) && !empty(trim(wp_unslash($_GET['search'])))) {// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                $options['*search'] = sanitize_text_field(wp_unslash($_GET['search']));// phpcs:ignore WordPress.Security.NonceVerification.Recommended
            }
            $options['*current_page'] = $output['current_page'];
            $options['*per_page'] = $output['per_page'];
            $result = $RdDownloads->listItems($options);
            unset($options);

            $output['total'] = $result['total'];
            $output['results'] = $result['results'];
            $output['total_pages'] = ceil($output['total'] / $output['per_page']);

            wp_send_json($output, $responseStatus);
        }// searchDownloads


    }// XhrDownloadsBrowser
}
