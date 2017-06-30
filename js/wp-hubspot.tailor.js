(function (app, WP_Hubspot) {
  app.channel.on('element:refresh', WP_Hubspot.init);
  app.channel.on('canvas:reset', WP_Hubspot.init);
}(window.app, window.WP_Hubspot));
