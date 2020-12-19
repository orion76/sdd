(function ($, Drupal) {

  function moveTop(ul, value, duration) {
    ul.animate({top: '+=' + value}, {duration: duration});
  };

  function moveBottom(ul, value, duration) {
    ul.animate({top: '-=' + value}, {duration: duration});
  };


  function onMouseWheel(ul, move_value, duration) {

    let placeStart = null;

    return function (event) {

      const windowHeight = $(window).height();

      const bounding = ul.get(0).getBoundingClientRect();

      if (placeStart === null) {
        placeStart = bounding.top;
      }

      if (event.originalEvent.wheelDelta > 0) {
        if (bounding.top <= (placeStart - move_value)) {
          moveTop(ul, move_value, duration);
        }

      } else {
        const hiddenBottom = windowHeight - bounding.bottom;
        if (hiddenBottom <= 0 - move_value) {
          moveBottom(ul, move_value, duration);
        }
      }
      event.stopPropagation();
      event.preventDefault();
    }
  }

  function onMouseOut(ul) {
    return function (event) {

      const bounding = ul.get(0).getBoundingClientRect();
      if (event.offsetX < 0) {
        ul.css('top', 'auto');
      }
    }
  }


  Drupal.behaviors.adminToolbarExt = {
    attach: function (context, settings) {

      var duration = 0;


      var move_value = 20;

      $('.menu-item > ul.toolbar-menu', context).each(function (index) {

        const ul = $(this);

        ul.on({
          wheel: onMouseWheel(ul, move_value, duration),
          mouseout: onMouseOut(ul)
        });
      });


      $('ul:not(.toolbar-menu)', context).on({
        // mousemove: function () {
        //   $('li.menu-item--expanded').removeClass('hover-intent');
        // },
        // hover: function () {
        //   $('li.menu-item--expanded').removeClass('hover-intent');
        // }
      });

    }
  };
})(jQuery, Drupal);

