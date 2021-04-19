<?php

/**
 * Class InstapageCmsPluginLPAjaxLoaderController
 *
 * This controller solves problem that on some hostings `utm_` params are stripped,
 * so we passed them endoded with base64 to backend of plugin by JS Ajax Loader
 */
class InstapageCmsPluginLPAjaxLoaderController
{
    const AJAX_REQUEST_FLAG_NAME = 'landingPageAjaxLoaderRequest';
    const PANTHEON_STRIPPED = 'PANTHEON_STRIPPED';

    /**
     * Check if it is pantheon hosting platform and check if utm_ variables are replaced
     * Also Check if it is WP Engine hosting and if it is not already request from Ajax Loader JS
     * Also Check if client manually enabled LP Ajax Loader
     *
     * @param string $url URL of landing page that plugin tries to load
     * @return bool
     */
    public function shouldBeUsed($url)
    {
        return (isset($_ENV['PANTHEON_ENVIRONMENT'])
                && strpos($url, self::PANTHEON_STRIPPED) !== false)
            || (isset($_ENV['WPENGINE_ACCOUNT'])
                && strpos($url, self::AJAX_REQUEST_FLAG_NAME) === false)
            || (InstapageCmsPluginHelper::getMetadata('lpAjaxLoader', false)
                && strpos($url, self::AJAX_REQUEST_FLAG_NAME) === false);
    }

    /**
     * Check if query should be decoded
     * Query can contains b64 param and it needs to be decode to be used
     *
     * @return bool
     */
    public function shouldDecodeQuery()
    {
        return
            (isset($_ENV['PANTHEON_ENVIRONMENT'])
              || isset($_ENV['WPENGINE_ACCOUNT'])
              || InstapageCmsPluginHelper::getMetadata('lpAjaxLoader', false))
            && isset($_GET['b64']);
    }

    /**
     * Get view containing Ajax loader
     *
     * @return false|string
     */
    public function getView()
    {
        ob_start();
        include(__DIR__ . '/view.php');
        return ob_get_clean();
    }

  /**
   * Inject loading script. This script is responsible for base64 encoding query to it is not altered/stripped
   * by custom caching layers (as Pantheon for example does)
   *
   * @param $html string Whole landing page html
   * @return string
   */
    public function injectScript($html)
    {
        $headTag = '<head>';
        $scriptTag = $this->getView();

        return str_replace(
            $headTag,
            $headTag . $scriptTag,
            $html
        );
    }

    /**
     * Set display on body in passed $html
     *
     * @param $html string Whole landing page html
     * @return string
     */
    public function addDisplayNoneOnBody($html)
    {
        $bodyTag = '<body';

        return str_replace(
            $bodyTag,
            $bodyTag . ' style="display: none"',
            $html
        );
    }
}
