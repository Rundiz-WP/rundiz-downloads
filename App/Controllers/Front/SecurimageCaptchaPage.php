<?php
/**
 * Front-end download process page.
 * 
 * @package rd-downloads
 */


namespace RdDownloads\App\Controllers\Front;

if (!class_exists('\\RdDownloads\\App\\Controllers\\Front\\SecurimageCaptchaPage')) {
    /**
     * Process the download.
     * 
     * This class was called from `App\Controllers\Front\Hooks\Query\DownloadPage` class -> `goToRdDownloadsPage()` method.
     */
    class SecurimageCaptchaPage
    {


        /**
         * @var \RdDownloads\App\Libraries\Loader The loader class. 
         */
        protected $Loader;


        public function __construct()
        {
            $this->Loader = new \RdDownloads\App\Libraries\Loader();
        }// __construct


        /**
         * Display captcha.
         * 
         * This class and method required "vendor/securimage/securimage.php" from https://www.phpcaptcha.org .
         * 
         * @param string $subpage The subpage that requested to here.
         */
        public function pageIndex($subpage)
        {
            if (session_id() == '') {
                // if no session ID.
                // start the session.
                session_start();
            }

            status_header(200);

            require_once plugin_dir_path(RDDOWNLOADS_FILE) . 'vendor/securimage/securimage.php';

            $Img = new \Securimage();
            $Img->namespace = 'rddownloads_download_page';

            if ($subpage === 'securimage_captcha') {
                // if subpage is image.
                // set characters. remove ambiguous refer from https://github.com/Rundiz/serial-number-generator/blob/master/Rundiz/SerialNumberGenerator/SerialNumberGenerator.php#L70
                // 0 ambiguous with O
                // 1 ambiguous with I J L T
                // 2 ambiguous with Z
                // 5 ambiguous with S
                // 8 ambiguous with B
                // U ambiguous with V
                // Before add or change anything, make sure that audio is supported.
                $Img->charset = '0123456789ACDEFGHKMNPQRUWXY';
                $Img->code_length = mt_rand(5, 7);
                $Img->image_width = 300;
                $Img->image_height = ($Img->image_width * 0.35);
                $Img->num_lines = 9;
                $Img->perturbation = 0.85;
                $Img->show();
            } elseif ($subpage === 'securimage_captcha_audio') {
                $Img->audio_use_noise = true;
                $Img->degrade_audio = true;
                $Img->outputAudioFile(null);
            }

            unset($Img);
        }// pageIndex


    }
}