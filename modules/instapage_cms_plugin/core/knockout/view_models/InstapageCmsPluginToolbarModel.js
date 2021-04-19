/* globals  ko, iLang, instapageKO, iAjax, masterModel, InstapageCmsPluginPagedGridModel, InstapageCmsPluginSettingsModel, InstapageCmsPluginEditModel, INSTAPAGE_AJAXURL */
var InstapageCmsPluginToolbarModel = function InstapageCmsPluginToolbarModel() {
  var self = this;

  self.loadListPages = function loadListPages() {
    var element = document.getElementById('instapage-container');

    masterModel.messagesModel.clear();
    element.innerHTML = self.getLoader();

    loadPageList();
    self.setActiveTab('listing-view');
  };

  self.loadEditPage = function loadEditPage(item) {
    var post = null;
    var element = document.getElementById('instapage-container');

    masterModel.messagesModel.clear();
    element.innerHTML = self.getLoader();

    if (typeof item.id !== 'undefined') {
      post = {action: 'loadEditPage', apiTokens: masterModel.apiTokens, data: item};
    } else {
      post = {action: 'loadEditPage', apiTokens: masterModel.apiTokens};
    }

    iAjax.post(INSTAPAGE_AJAXURL, post, function loadEditPageCallback(responseJson) {
      var response = masterModel.parseResponse(responseJson);

      if (response.status === 'OK') {
        instapageKO.cleanNode(element);
        element.innerHTML = response.html;
        masterModel.editModel = new InstapageCmsPluginEditModel(response.data);
        instapageKO.applyBindings(masterModel.editModel, element);
        self.setActiveTab('edit-view');
      } else {
        element.innerHTML = ''; // remove loader on fail
        masterModel.messagesModel.addMessage(response.message, response.status);
      }
    });
  };

  self.loadSettings = function loadSettings() {
    var element = document.getElementById('instapage-container');

    masterModel.messagesModel.clear();
    element.innerHTML = self.getLoader();

    iAjax.post(INSTAPAGE_AJAXURL, {action: 'loadSettings', apiTokens: masterModel.apiTokens}, function clearLogCallback(responseJson) {
      var response = masterModel.parseResponse(responseJson);

      if (response.status === 'OK') {
        instapageKO.cleanNode(element);
        element.innerHTML = response.html;
        masterModel.settingsModel = new InstapageCmsPluginSettingsModel(response.initialData);
        instapageKO.applyBindings(masterModel.settingsModel, element);
        self.setActiveTab('settings-view');
        masterModel.settingsModel.validateAllTokens();
      } else {
        masterModel.messagesModel.addMessage(response.message, response.status);
      }
    });
  };

  self.getLoader = function getLoader() {
    var html = '<div class="l-group__item page-loader">' +
      '<span class="c-loader c-loader--x-large"></span>' +
      '</div>';

    return html;
  };

  self.setActiveTab = function setActiveTab(tabClassName) {
    var tabs = document.getElementById('instapage-toolbar').getElementsByClassName('c-tab');
    var tabToChange = document.getElementById('instapage-toolbar').getElementsByClassName('c-tab ' + tabClassName);

    if (tabToChange.length) {
      tabToChange = tabToChange[0];
    } else {
      return;
    }

    for (var i = 0; i < tabs.length; ++i) {
      tabs[i].classList.remove('is-active');
    }

    tabToChange.classList.add('is-active');
    document.whitekitTabsInit();
    masterModel.getOptions(masterModel.addDiagnosticsWarning);
  };
};
