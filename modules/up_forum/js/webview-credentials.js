/* global drupalSettings */
/* eslint no-undef: "error" */
(function (Drupal, drupalSettings) {
  function redirect(data, destination) {
    document.cookie = data.cookie;
    window.location.replace(destination);
  }

  Drupal.behaviors.getCredentials = {
    attach: function () {
      if (window.ReactNativeWebView) {
        window.addEventListener("message", function(message) {
          redirect(message.data, drupalSettings.upForum.webviewCredentials.discourseURL);
        });

        window.ReactNativeWebView.postMessage('GET_CREDENTIALS');
      }
    }
  }
})(Drupal, drupalSettings);
