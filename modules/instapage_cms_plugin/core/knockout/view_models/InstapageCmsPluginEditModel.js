/* globals  ko, instapageKO, iAjax, iLang, INSTAPAGE_AJAXURL, masterModel */

var InstapageCmsPluginEditModel = function InstapageCmsPluginEditModel(data) {
  var self = this;

  self.randomPrefix = 'random-url-';
  self.randomSufixSet = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  self.randomSufixLength = 10;
  self.id = data && data.page ? instapageKO.observable(data.page.id) : instapageKO.observable(0);
  self.instapageId = data && data.page ? data.page.instapage_id : null;
  self.publishThrottle = instapageKO.observable(true);

  if (data && data.subAccounts && data.subAccounts.length) {
    self.subAccounts = instapageKO.observableArray(data.subAccounts);
    self.choosenSubAccount = data.page ? instapageKO.observable(data.page.subaccount) : instapageKO.observable(data.subAccounts[0].id);
  } else {
    self.subAccounts = instapageKO.observableArray();
    self.choosenSubAccount = instapageKO.observable();
    masterModel.messagesModel.addMessage(iLang.get('LOGIN_OR_ADD_TOKEN_FIRST'));
  }

  self.choosenLandingPage = data && data.page ? instapageKO.observable(data.page.instapage_id) : instapageKO.observable();
  self.choosenLandingPageType = data && data.page ? instapageKO.observable(data.page.type) : instapageKO.observable();
  self.slug = data && data.page ? instapageKO.observable(data.page.slug) : instapageKO.observable('');
  self.isSlugValid = instapageKO.observable(true);
  self.isTypeValid = instapageKO.observable(true);
  self.landingPages = instapageKO.observableArray();
  self.pageTypes = instapageKO.observableArray([new LandingPageType('page', 'Landing Page'), new LandingPageType('home', 'Home Page'), new LandingPageType('404', '404 Page')]);

  self.choosenSubAccount.subscribe(function choosenSubAccountCallback() {self.loadLandingPages();});

  self.choosenLandingPageType.subscribe(function choosenLandingPageTypeCallback() {
    if (self.choosenLandingPageType() === '404') {
      self.slug(self.generateRandomSlug());
    } else {
      self.slug('');
    }
  });

  self.publishPage = function publishPage() {
    if (self.publishThrottle.isBusy()) {
      return;
    }

    self.publishThrottle.setBusy(true);
    masterModel.messagesModel.clear();

    if (!self.validateSlug() || !self.validateLandingPageType()) {
      return false;
    }

    var post = {action: 'publishPage', apiTokens: masterModel.apiTokens, data: {
      id: self.id(),
      landingPageId: self.choosenLandingPage(),
      type: self.choosenLandingPageType(),
      slug: self.slug()
    }};

    iAjax.post(INSTAPAGE_AJAXURL, post, function publishPageCallback(responseJson) {
      var response = masterModel.parseResponse(responseJson);
      masterModel.messagesModel.addMessage(response.message, response.status);

      if (response.status === 'OK') {
        if (typeof response.updatedId !== 'undefined') {
          self.id(response.updatedId);
        }
        masterModel.pagedGridModel.originalItems.push({type: self.choosenLandingPageType()});
        masterModel.toolbarModel.loadListPages();
      }

      masterModel.messagesModel.addMessage(response.message, response.status);
      self.publishThrottle.setBusy(false);
    });

    return true;
  };

  self.validateSlug = function validateSlug() {
    var message = '';
    var result = {};

    self.isSlugValid(true);
    // removing dots, spaces, slashes from begining and the end of slug
    self.slug(self.slug().trim().replace(/ |^(\/)+|(\/)+$|\./g, ''));
    // replacing duplicated slashes in slug
    self.slug(self.slug().replace(/\/{2,}/g, '/'));
    self.isProhibitedSlug(self.slug());
    if (self.choosenLandingPageType() === 'page') {
      if (self.slug() === '') {
        masterModel.messagesModel.addMessage(iLang.get('SLUG_CANNOT_BE_EMPTY'), 'ERROR');
        self.isSlugValid(false);
        return false;
      }

      if ((masterModel.prohibitedSlugs !== null) && (masterModel.prohibitedSlugs.some(self.isSlugProhibited, result))) {
        if (result.conflictElement) {
          message = '';

          if (result.conflictElement.editUrl) {
            var editHtml = '<a href="' + result.conflictElement.editUrl + '" target="_blank">Edit item</a>';
            message = iLang.get('SLUG_IS_USED_BY_CMS', editHtml);
          } else {
            message = iLang.get('SLUG_IS_USED_BY_PLUGIN');
          }
          masterModel.messagesModel.addMessage(message, 'ERROR');
        }

        self.isSlugValid(false);
        return false;
      }
    }

    return true;
  };

  self.validateLandingPageType = function validateLandingPageType() {
    var message = '';

    self.isTypeValid(true);

    if (self.choosenLandingPageType() === 'home') {
      if (masterModel.pagedGridModel.items().some(self.isHomepage)) {
        message = iLang.get('HOMEPAGE_ALREADY_DEFINED');
        masterModel.messagesModel.addMessage(message, 'ERROR');
        self.isTypeValid(false);
        return false;
      }
    } else if (self.choosenLandingPageType() === '404') {
      if (masterModel.pagedGridModel.items().some(self.is404)) {
        message = iLang.get('404_ALREADY_DEFINED');
        masterModel.messagesModel.addMessage(message, 'ERROR');
        self.isTypeValid(false);
        return false;
      }
    }

    return true;
  };

  self.isSlugProhibited = function isSlugProhibited(element) {
    if (element.slug === self.slug().trim() && parseInt(element.id, 10) !== parseInt(self.id(), 10)) {
      this.conflictElement = element;
      return true;
    }

    return false;
  };

  self.isHomepage = function isHomepage(element) {
    return element.type === 'home' && parseInt(element.id, 10) !== parseInt(self.id(), 10);
  };

  self.is404 = function is404(element) {
    return element.type === '404' && parseInt(element.id, 10) !== parseInt(self.id(), 10);
  };

  self.generateRandomSlug = function generateRandomSlug() {
    var randomString = '';

    for (var index = 0; index < self.randomSufixLength; index++) {
      randomString += self.randomSufixSet[ Math.floor(Math.random() * self.randomSufixSet.length) ];
    }

    return self.randomPrefix + randomString;
  };

  self.loadLandingPages = function loadLandingPages() {
    var post = {action: 'getLandingPages', apiTokens: masterModel.apiTokens, data: {subAccountToken: self.getSubAccountToken(), selfInstapageId: self.instapageId}};
    var selectedLandingPage = self.choosenLandingPage();

    iAjax.post(INSTAPAGE_AJAXURL, post, function getLandingPagesCallback(responseJson) {
      var response = masterModel.parseResponse(responseJson);

      if (response.status === 'OK') {
        self.landingPages(response.data);

        if (selectedLandingPage) {
          self.choosenLandingPage(selectedLandingPage);
        }
      }
    });
  };

  self.isProhibitedSlug = function isProhibitedSlug(slug) {
    var post = {action: 'isProhibitedSlug', data: slug};

    iAjax.post(INSTAPAGE_AJAXURL, post, function isProhibitedSlugCallback(responseJson) {
      var response = masterModel.parseResponse(responseJson);

      if (response.status === 'OK') {
        masterModel.prohibitedSlugs = (response.data === false) ? null : response.data;
      }
    }, false);
  };

  self.getSubAccountToken = function getSubAccountToken() {
    var result = {};

    self.subAccounts().some(function getSubAccountTokenById(element) {
      if (element.id === self.choosenSubAccount()) {
        result = element.accountkey;
      }
    }, result);

    return result;
  };

  self.loadLandingPages();
};


var LandingPageType = function LandingPageType(name, title) {
  var self = this;

  self.name = name;
  self.title = title;
};
