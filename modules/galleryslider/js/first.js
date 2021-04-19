$(window).load(function () {
  'use strict';
  var a = $('.slider').attr('data-settings');
  var b = JSON.parse(a);
  $('.slider').gridSlide(b);
});
