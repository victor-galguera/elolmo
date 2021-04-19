/* globals  ActiveXObject */
var InstapageCmsPluginAjax = function InstapageCmsPluginAjax() {
  var self = this;

  self.call = function call(method, url, data, callbackFunction, async) {
    var async = (typeof async === 'undefined') ? true : async;
    var xmlhttp = null;
    var urlAppendix = (url.match(/\?/) === null ? '?' : '&') + (new Date()).getTime();

    if (window.XMLHttpRequest) {
      xmlhttp = new XMLHttpRequest();
    } else {
      xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
    }

    xmlhttp.onreadystatechange = function onreadystatechange() {
      if (xmlhttp.readyState === 4 && typeof callbackFunction === 'function') {
          callbackFunction(xmlhttp.response);
      }
    };

    xmlhttp.open(method, url + urlAppendix, async);

    if (method === 'POST') {
      var formData = new FormData();
      formData.append('data', encodeURI(JSON.stringify(data)));
      xmlhttp.send(formData);
    } else {
      xmlhttp.send();
    }
  };

  self.post = function post(url, data, callbackFunction, async) {
    self.call('POST', url, data, callbackFunction, async);
  };
};

var iAjax = new InstapageCmsPluginAjax();
window.iAjax = iAjax;
