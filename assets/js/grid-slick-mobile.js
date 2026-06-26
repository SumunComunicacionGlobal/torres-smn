/**
 * Grid to slick (mobile/tablet)
 * Convierte .wp-block-group.is-layout-grid en carrusel slick por debajo de tablet.
 */
(function ($) {
  const mediaQuery = window.matchMedia('(max-width: 991px)');
  const gridSelector = '.wp-block-group.is-layout-grid';
  const gridChildWrapperClass = 'smn-grid-direct-child-wrapper';

  function wrapGridDirectChildren($targetGrid) {
    const $grids = $targetGrid && $targetGrid.length ? $targetGrid : $(gridSelector);

    $grids.each(function () {
      const $grid = $(this);

      $grid.children().each(function () {
        const $child = $(this);

        if ($child.is('script,style,template')) {
          return;
        }

        if ($child.hasClass(gridChildWrapperClass)) {
          return;
        }

        $child.wrap('<div class="' + gridChildWrapperClass + '"></div>');
      });
    });
  }

  function unwrapGridDirectChildren($grid) {
    $grid.children('.' + gridChildWrapperClass).each(function () {
      $(this).contents().unwrap();
    });
  }

  function initGridSlick() {
    if (!mediaQuery.matches) {
      return;
    }

    $(gridSelector).each(function () {
      const $grid = $(this);

      if ($grid.hasClass('slick-initialized')) {
        return;
      }

      wrapGridDirectChildren($grid);

      if ($grid.children('.' + gridChildWrapperClass).length < 2) {
        unwrapGridDirectChildren($grid);
        return;
      }

      $grid
        .addClass('is-grid-slick-mobile')
        .slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          centerMode: true,
          autoplay: true,
          autoplaySpeed: 2000,
          speed: 300,
          arrows: false,
          dots: true,
          infinite: false,
          adaptiveHeight: false,
          pauseOnHover: true,
        });
    });
  }

  function destroyGridSlick() {
    if (mediaQuery.matches) {
      return;
    }

    $(gridSelector + '.slick-initialized').each(function () {
      const $grid = $(this);
      $grid.slick('unslick').removeClass('is-grid-slick-mobile');
      unwrapGridDirectChildren($grid);
    });

    // Limpia wrappers remanentes para mantener el layout grid intacto en desktop.
    $(gridSelector).each(function () {
      unwrapGridDirectChildren($(this));
    });
  }

  function syncGridSlick() {
    if (mediaQuery.matches) {
      initGridSlick();
      return;
    }

    destroyGridSlick();
  }

  $(function () {
    syncGridSlick();
  });

  $(window).on('resize orientationchange', function () {
    syncGridSlick();
  });
})(jQuery);
