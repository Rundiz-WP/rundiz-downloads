<?php
/**
 * Cookies class.
 *
 * @package rd-downloads
 */


namespace RdDownloads\App\Libraries;

if (!class_exists('\\RdDownloads\\App\\Libraries\\Cookies')) {
    class Cookies
    {


        /**
         * Delete a cookie
         *
         * @param string $name
         * @return bool
         */
        public static function deleteCookie($name)
        {
            return static::setCookie($name, '', (time() - 3600));
        }// deleteCookie


        /**
         * Set cookie.
         *
         * @link http://php.net/manual/en/function.setcookie.php PHP `setcookie()` document.
         * @see http://php.net/manual/en/function.setcookie.php
         * @param string $name The name of the cookie.
         * @param string $value The value of the cookie. This value is stored on the clients computer; do not store sensitive information. Assuming the name is 'cookiename', this value is retrieved through $_COOKIE['cookiename']
         * @param int|false $expire The time the cookie expires. This is a Unix timestamp so is in number of seconds since the epoch. In other words, you'll most likely set this with the time() function plus the number of seconds before you want it to expire. Or you might use mktime(). time()+60*60*24*30 will set the cookie to expire in 30 days. If set to 0, or omitted, the cookie will expire at the end of the session (when the browser closes). Set to false to use default.
         * @param string|false $path The path on the server in which the cookie will be available on. If set to '/', the cookie will be available within the entire domain. If set to '/foo/', the cookie will only be available within the /foo/ directory and all sub-directories such as /foo/bar/ of domain. The default value is the current directory that the cookie is being set in. Set to false to use WP constant.
         * @param string|false $domain The (sub)domain that the cookie is available to. Setting this to a subdomain (such as 'www.example.com') will make the cookie available to that subdomain and all other sub-domains of it (i.e. w2.www.example.com). To make the cookie available to the whole domain (including all subdomains of it), simply set the value to the domain name ('example.com', in this case). Set to false to use WP constant.
         * @param bool|empty $secure Indicates that the cookie should only be transmitted over a secure HTTPS connection from the client. When set to TRUE, the cookie will only be set if a secure connection exists. On the server-side, it's on the programmer to send this kind of cookie only on secure connection (e.g. with respect to $_SERVER["HTTPS"]). Leave blank for auto detect.
         * @param bool $httponly When TRUE the cookie will be made accessible only through the HTTP protocol. This means that the cookie won't be accessible by scripting languages, such as JavaScript. It has been suggested that this setting can effectively help to reduce identity theft through XSS attacks (although it is not supported by all browsers), but that claim is often disputed.
         * @return bool If output exists prior to calling this function, setcookie() will fail and return FALSE. If setcookie() successfully runs, it will return TRUE. This does not indicate whether the user accepted the cookie.
         */
        public static function setCookie($name, $value = '', $expire = false, $path = false, $domain = false, $secure = '', $httponly = true)
        {
            if ($expire === false) {
                $expire = apply_filters('rddownloads_cookie_expires', (time() + (14 * DAY_IN_SECONDS)));
            }
            if ($path === false) {
                $path = COOKIEPATH;
            }
            if ($domain === false) {
                $domain = COOKIE_DOMAIN;
            }
            if ($secure === '') {
                $secure = is_ssl();
            }
            if (!is_bool($httponly)) {
                $httponly = true;
            }
            return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }// setCookie


    }
}