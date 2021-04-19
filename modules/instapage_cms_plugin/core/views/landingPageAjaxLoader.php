<?php
/*
 * For pantheon hosting platform (https://pantheon.io/) we need to get content by ajax
 * because pantheon replace UTM_ variables value to 'PANTHEON_STRIPPED' and redirect the page.
 * Thanks to this solution all UTM_ variable are sent to instapage api.platform
 */
?>

<html>
    <script id='b64-replace' type="text/javascript"> 
        (function () {
            var query = [];
            var searchArray = document.location.search.replace('?', '').split('&');
            var url = document.location.origin + document.location.pathname + '?b64=';
            var i;

            for (i = 0; i < searchArray.length; i++) {
                if (searchArray[i].indexOf("PANTHEON_STRIPPED") === -1) {
                    query.push(searchArray[i]);
                }
            }
            if (window.XMLHttpRequest) {
                var xhReq = new XMLHttpRequest();
            } else {
                var xhReq = new ActiveXObject("Microsoft.XMLHTTP");
            }

            xhReq.open('GET', url + window.btoa(query.join('&')), false);
            xhReq.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhReq.send();

            document.open();
            document.write(xhReq.responseText);
            document.close();
            document.getElementById("b64-replace").remove();
        })();
    </script>
    <body>
    </body>
</html>
