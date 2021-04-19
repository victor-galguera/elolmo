<?php
/*
 * For pantheon hosting platform (https://pantheon.io/) we need to get content by ajax
 * because pantheon replace UTM_ variables value to 'PANTHEON_STRIPPED' and redirect the page.
 * Thanks to this solution all UTM_ variable are sent to instapage api.platform
 */
?>

<script id="b64-replace" type="text/javascript">
    (function () {
        function hasGetParameters() {
            return document.location.search !== '';
        }

        /**
         * Execute fn not earlier than DOM is ready
         */
        function onDOMReady(fn) {
            if (document.readyState != 'loading') {
                fn();
            } else {
                document.addEventListener('DOMContentLoaded', fn);
            }
        }

        /**
         * Encode query string to pass it to backend of our plugin (we omit by this stripping utm tags)
         */
        function buildBase64EncodedQueryString() {
            var query = [];
            var searchArray = document.location.search.replace('?', '').split('&');

            for (var i = 0; i < searchArray.length; i++) {
                if (searchArray[i].indexOf("PANTHEON_STRIPPED") === -1) {
                    query.push(searchArray[i]);
                }
            }

            query.push(
                '<?php echo InstapageCmsPluginLPAjaxLoaderController::AJAX_REQUEST_FLAG_NAME ?>=true'
            );

            return window.btoa(query.join('&'));
        }

        /**
         * Fetch landing page with base64 encoding query strings to ommit caching/altering layers of query params
         */
        function fetchLandingPage() {
            var url = document.location.origin + document.location.pathname;

            if (window.XMLHttpRequest) {
                var xhReq = new XMLHttpRequest();
            } else {
                var xhReq = new ActiveXObject("Microsoft.XMLHTTP");
            }

            xhReq.open('GET', url + '?b64=' + buildBase64EncodedQueryString(), false);
            xhReq.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhReq.send();

            onDOMReady(function () {
                document.getElementsByTagName('html')[0].innerHTML = xhReq.responseText;
            })
        }

        function showBody() {
            onDOMReady(function () {
                document.getElementsByTagName('body')[0].style.display = 'block';
            })
        }

        if (hasGetParameters()) {
            fetchLandingPage();
        } else {
            showBody();
        }
    })();
</script>
