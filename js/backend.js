/*global $, dotclear, jsToolBar */
'use strict';

$(() => {
  if (typeof jsToolBar === 'function') {
    $('#construction_message').each(function () {
      const tbWidgetTextDisclaimer = new jsToolBar(this);
      tbWidgetTextDisclaimer.context = 'construction_message';
      tbWidgetTextDisclaimer.draw('xhtml');
    });
  }
});