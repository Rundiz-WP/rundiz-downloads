<?php
/**
 * Rundiz Downloads - Xhr based class.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers;

if (!class_exists('\\RdDownloads\\App\\Controllers\\XhrBased')) {
    abstract class XhrBased
    {


        /**
         * Common access check for many controllers.
         *
         * Check for user capability, granted access to the app, allowed methods, CSRF protection.<br>
         * This method return nothing, if all check were passed then just continue working otherwise it will response json and end the process.
         *
         * @param array $allowedMethods Allowed methods.
         * @param array|false $nonce If this value is false then it will skip checking nonce and no CSRF protection.<br>
         *                                           The first array value is action, the second value is query argument.<br>
         *                                           For more information please see <code>check_ajax_referer()</code> function.
         * @param string $capability The capability to check. See more at https://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table
         *                                          Leave this argument blank for use default (upload_files).
         */
        protected function commonAccessCheck(array $allowedMethods = ['post'], $nonce = [], $capability = '')
        {
            if (!is_string($capability) || empty($capability)) {
                $capability = 'upload_files';
            }

            // check permission
            if (!current_user_can($capability)) {
                // if user has no required permission OR did not granted access to the app.
                // response failed message immediately.
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('You do not have permission to access this page.');
                wp_send_json($output, 403);
            }

            // check for allowed methods
            if (
                !isset($_SERVER['REQUEST_METHOD']) ||
                (
                    isset($_SERVER['REQUEST_METHOD']) &&
                    !in_array(strtolower($_SERVER['REQUEST_METHOD']), array_map('strtolower', $allowedMethods))
                )
            ) {
                // if no method or method is not in allowed list.
                $output['form_result_class'] = 'notice-error';
                $output['form_result_msg'] = __('Method not allowed.', 'rd-downloads');
                wp_send_json($output, 405);
            }

            // verify nonce (CSRF protection)
            if ($nonce !== false) {
                if (is_array($nonce) && empty($nonce)) {
                    $action = -1;
                    $query_arg = false;
                } elseif (is_array($nonce) && isset($nonce[0]) && isset($nonce[1])) {
                    $action = $nonce[0];
                    $query_arg = $nonce[1];
                } else {
                    $action = -1;
                    $query_arg = false;
                }

                if (check_ajax_referer($action, $query_arg, false) === false) {
                    $output['form_result_class'] = 'notice-error';
                    $output['form_result_msg'] = __('Please reload the page and try again.', 'rd-downloads');
                    wp_send_json($output, 403);
                }

                unset($action, $query_arg);
            }
        }// commonAccessCheck


    }
}