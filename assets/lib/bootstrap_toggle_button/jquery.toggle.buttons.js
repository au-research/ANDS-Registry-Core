!function ($) {
  "use strict";

  $.fn.toggleButtons = function (opt) {
    var $element, $labelEnabled, options, active, styleActive, styleDisabled, enable;
    this.each(function () {

      $element = $(this);

      options = $.extend({}, $.fn.toggleButtons.defaults, opt);

      if (!$.browser.msie)
      {
       
        $element.addClass('toggle-button');

        if(options.animated)
          $element.addClass('toggle-button-animated');

        $element.css('width', options.width);

        $labelEnabled = $('<label></label>').attr('for', $element.find('input').attr('id'));

        $element.append($labelEnabled);

        $element.attr("data-enabled", options.label.enabled === undefined ? "ON" : options.label.enabled);
        $element.attr("data-disabled", options.label.disabled === undefined ? "OFF " : options.label.disabled);

        active = $element.find('input').is(':checked');

        if (!active)
          $element.addClass('disabled');

        styleActive = options.style.enabled === undefined ? "" : options.style.enabled;
        styleDisabled = options.style.disabled === undefined ? "" : options.style.disabled;
        enable = options.enable;

        if (active && styleActive !== undefined)
          $element.addClass(styleActive);
        if (!active && styleDisabled !== undefined)
          $element.addClass(styleDisabled);

      }

      $element.on('click', function (e) {

        if ($(e.target).is('input'))
          return options.onChange($(e.target), $(e.target).is(':checked'), e);

        e.stopPropagation();
        $(this).find('label').click();
      });


      $element.find('label').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();

        $element = $(this).parent();

        if(enable){
          $element
            .toggleClass('disabled')
            .toggleClass(styleActive)
            .toggleClass(styleDisabled);

          active = !($element.find('input').is(':checked'));
          $element.find('input').attr('checked', active);

          options.onChange($element, active, e);
        }
      });

    });
  };

  $.fn.toggleButtons.defaults = {
    onChange: function () {
    },
    enable: true,
    width: 100,
    animated: true,
    label: {
      enabled: undefined,
      disabled: undefined
    },
    style: {
      enabled: undefined,
      disabled: undefined
    }
  };

}($);