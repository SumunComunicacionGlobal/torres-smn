// Añade clase a body cuando se hace scroll
window.addEventListener("scroll", function() {
    if (window.scrollY > 180) {
        document.body.classList.add("scrolled");
    } else {
        document.body.classList.remove("scrolled");
    }
});

// En el menú responsive móvil: el texto navega y el icono despliega submenú.
document.addEventListener('DOMContentLoaded', function () {
  const responsiveContainer = document.querySelector('#masthead .wp-block-navigation__responsive-container');

  if (!responsiveContainer) {
    return;
  }

  const isMobileOverlayOpen = function () {
    return window.matchMedia('(max-width: 991px)').matches && responsiveContainer.classList.contains('is-menu-open');
  };

  const getUrlFromMenuItem = function (menuItem, labelNode) {
    if (!menuItem) {
      return '';
    }

    const directLink = menuItem.querySelector(':scope > a.wp-block-navigation-item__content[href]');
    if (directLink) {
      return directLink.getAttribute('href') || '';
    }

    const dataUrl = menuItem.getAttribute('data-url') || menuItem.getAttribute('data-parent-url');
    if (dataUrl) {
      return dataUrl;
    }

    const label = labelNode ? labelNode.textContent.trim() : '';
    if (!label) {
      return '';
    }

    const candidates = document.querySelectorAll(
      '.wp-block-navigation a.wp-block-navigation-item__content[href] .wp-block-navigation-item__label'
    );
    for (let i = 0; i < candidates.length; i += 1) {
      if (candidates[i].textContent.trim() === label) {
        const anchor = candidates[i].closest('a[href]');
        return anchor ? anchor.getAttribute('href') || '' : '';
      }
    }

    const submenuLinks = menuItem.querySelectorAll(':scope > .wp-block-navigation__submenu-container a[href]');
    if (submenuLinks.length > 0) {
      const paths = [];

      for (let i = 0; i < submenuLinks.length; i += 1) {
        try {
          const url = new URL(submenuLinks[i].getAttribute('href'), window.location.origin);
          const segments = url.pathname.split('/').filter(Boolean);
          if (segments.length > 0) {
            paths.push(segments);
          }
        } catch (e) {
          // Ignore malformed URLs and continue with the remaining submenu items.
        }
      }

      if (paths.length > 0) {
        const common = [];
        const first = paths[0];

        for (let i = 0; i < first.length; i += 1) {
          const currentSegment = first[i];
          const allMatch = paths.every(function (segments) {
            return segments[i] === currentSegment;
          });

          if (!allMatch) {
            break;
          }

          common.push(currentSegment);
        }

        if (common.length > 0) {
          return '/' + common.join('/') + '/';
        }
      }
    }

    return '';
  };

  responsiveContainer.addEventListener('click', function (event) {
    if (!isMobileOverlayOpen()) {
      return;
    }

    const icon = event.target.closest('.wp-block-navigation__submenu-icon');
    if (icon) {
      const menuItem = icon.closest('.wp-block-navigation-item.has-child');
      const toggle = menuItem ? menuItem.querySelector(':scope > .wp-block-navigation-submenu__toggle') : null;

      if (toggle) {
        event.preventDefault();
        event.stopPropagation();
        toggle.click();
      }
      return;
    }

    const labelNode = event.target.closest('.wp-block-navigation-item.has-child > .wp-block-navigation-submenu__toggle .wp-block-navigation-item__label');
    if (!labelNode) {
      return;
    }

    const menuItem = labelNode.closest('.wp-block-navigation-item.has-child');
    const href = getUrlFromMenuItem(menuItem, labelNode);

    if (!href || href === '#' || href.indexOf('javascript:') === 0) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      window.open(href, '_blank', 'noopener');
      return;
    }

    window.location.assign(href);
  }, true);
});

// Oculta el header al bajar, lo muestra al subir y controla la visibilidad del logo.
document.addEventListener('DOMContentLoaded', function () {
  const masthead = document.querySelector('#masthead');

  if (!masthead) {
    return;
  }

  const siteLogo = masthead.querySelector('.wp-block-site-logo');
  let lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
  const delta = 8;
  let hiddenOffset = 0;

  function updateHiddenOffset() {
    hiddenOffset = masthead.offsetHeight + 24;
  }

  updateHiddenOffset();

  masthead.style.transition = 'transform 320ms ease, opacity 240ms ease';
  masthead.style.willChange = 'transform, opacity';

  if (siteLogo) {
    siteLogo.style.transition = 'opacity 200ms ease, visibility 200ms ease';
  }

  function setHeaderState(isScrollingDown, currentScrollTop) {
    if (currentScrollTop <= 0) {
      masthead.style.transform = 'translateY(0)';
      masthead.style.opacity = '1';
      if (siteLogo) {
        siteLogo.style.opacity = '1';
        siteLogo.style.visibility = 'visible';
        siteLogo.style.pointerEvents = '';
      }
      return;
    }

    if (isScrollingDown) {
      masthead.style.transform = 'translateY(-' + hiddenOffset + 'px)';
      masthead.style.opacity = '0';
      if (siteLogo) {
        siteLogo.style.opacity = '1';
        siteLogo.style.visibility = 'visible';
        siteLogo.style.pointerEvents = '';
      }
      return;
    }

    masthead.style.transform = 'translateY(0)';
    masthead.style.opacity = '1';
    if (siteLogo) {
      siteLogo.style.opacity = '0';
      siteLogo.style.visibility = 'hidden';
      siteLogo.style.pointerEvents = 'none';
    }
  }

  window.addEventListener('resize', updateHiddenOffset, { passive: true });

  window.addEventListener('scroll', function () {
    const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const diff = currentScrollTop - lastScrollTop;

    if (Math.abs(diff) <= delta) {
      return;
    }

    setHeaderState(diff > 0, currentScrollTop);
    lastScrollTop = currentScrollTop;
  }, { passive: true });
});
// Añade botones de scroll a la izquierda y derecha
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".is-style-group-horizontal-scroll-btns").forEach((content) => {
        if (content.children.length > 1) {
            const rightBtn = document.createElement("button");
            rightBtn.classList.add("scrolling-button", "scrolling-button--right");
            rightBtn.innerHTML = "→";
            rightBtn.disabled = false;

            const leftBtn = document.createElement("button");
            leftBtn.classList.add("scrolling-button", "scrolling-button--left");
            leftBtn.innerHTML = "←";
            leftBtn.disabled = true;

            const buttonContainer = document.createElement("div");
            buttonContainer.classList.add("scrolling-button-container");
            buttonContainer.appendChild(leftBtn);
            buttonContainer.appendChild(rightBtn);
            //content.parentNode.insertBefore(buttonContainer, content.nextSibling);
            // Agregar el contenedor de botones antes del contenido
            content.parentNode.insertBefore(buttonContainer, content);

            // Desplazamiento fijo para móvil y desktop
            function getScrollStep() {
                return window.innerWidth < 768 ? 400 : 288;
            }

            rightBtn.addEventListener("click", () => {
                const scrollContent = content;
                const scrollStep = getScrollStep();
                scrollContent.scrollLeft += scrollStep;
                leftBtn.disabled = false;

                if (scrollContent.scrollWidth - scrollContent.scrollLeft - scrollContent.clientWidth <= 0) {
                    rightBtn.disabled = true;
                }
            });

            leftBtn.addEventListener("click", () => {
                const scrollContent = content;
                const scrollStep = getScrollStep();
                scrollContent.scrollLeft -= scrollStep;
                rightBtn.disabled = false;

                if (scrollContent.scrollLeft <= 0) {
                    leftBtn.disabled = true;
                }
            });
        }
    });
});

// Añade drag para los elementos con scroll horizontal
document.addEventListener('DOMContentLoaded', (event) => {
    const sliders = document.querySelectorAll('.is-style-group-horizontal-scroll');
    let isDown = false;
    let startX;
    let scrollLeft;
  
    // Añade el evento a cada slider
    sliders.forEach(slider => {
        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.classList.add('active');
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.classList.remove('active');
        });
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.classList.remove('active');
        });
        slider.addEventListener('mousemove', (e) => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 3; //scroll-fast
            slider.scrollLeft = scrollLeft - walk;
            console.log(walk);
        });
    });
  
  });

//Rank Math FAQ Dropdown
document.addEventListener('DOMContentLoaded', (event) => {
    const faqs = document.querySelectorAll('.rank-math-list-item');
    faqs.forEach(faq => {
        const question = faq.querySelector('.rank-math-question');
        question.addEventListener('click', () => {
            faq.classList.toggle('active');
        });
    });
});

// Convertir lista en desplegables
document.addEventListener('DOMContentLoaded', function () {

  const items = document.querySelectorAll('.elementor-widget.preguntas li, .is-style-preguntas li');

  items.forEach(li => {
    let question = li.querySelector('b');

    if (!question) return;

    // Marcarlo como título
    question.classList.add('faq-question');

    // Buscar el primer nodo que forme parte de la respuesta
    let firstAnswerNode = question.nextSibling;

    // Saltar nodos de texto vacíos
    while (firstAnswerNode && 
           firstAnswerNode.nodeType === Node.TEXT_NODE && 
           !firstAnswerNode.textContent.trim()) {
      firstAnswerNode = firstAnswerNode.nextSibling;
    }

    // Crear wrapper para animación
    const wrap = document.createElement('div');
    wrap.classList.add('faq-answer-wrap');

    // Mover todos los nodos de respuesta dentro del wrapper
    let n = firstAnswerNode;
    while (n) {
      const next = n.nextSibling;
      wrap.appendChild(n);
      n = next;
    }

    // Añadir wrapper dentro del <li>
    li.appendChild(wrap);

    // Asegurar estado inicial cerrado
    wrap.style.maxHeight = '0';

    // Evento de clic en el título
    question.addEventListener('click', function () {
      const isAlreadyOpen = li.classList.contains('faq-open');

      // 1) Cerrar todos los items
      document.querySelectorAll('.faq-open').forEach(openLi => {
        openLi.classList.remove('faq-open');
        const openWrap = openLi.querySelector('.faq-answer-wrap');
        if (openWrap) openWrap.style.maxHeight = '0';
      });

      // 2) Si este no estaba abierto → abrirlo
      if (!isAlreadyOpen) {
        li.classList.add('faq-open');
        wrap.style.maxHeight = wrap.scrollHeight + 'px';
      }
    });
  });

});

// Ver más preguntas frecuentes
document.addEventListener('DOMContentLoaded', function () {
    const lists = document.querySelectorAll('.elementor-widget.preguntas ol, ol.is-style-preguntas, body.single-podcast ol');

    lists.forEach(list => {
        const fullHeight = list.scrollHeight;

        if (fullHeight <= 480) return;

        // Estado inicial
        list.classList.add('is-collapsed');

        // Crear botón
        const button = document.createElement('button');
        button.className = 'preguntas-ver-mas';
        button.textContent = 'Ver más';

        // Insertar botón después del ol
        list.parentNode.insertBefore(button, list.nextSibling);

        button.addEventListener('click', () => {
            const isCollapsed = list.classList.contains('is-collapsed');

            if (isCollapsed) {
                list.classList.remove('is-collapsed');
                list.style.maxHeight = 'unset';
                button.textContent = 'Ver menos';
            } else {
                list.classList.add('is-collapsed');
                list.style.maxHeight = '480px';
                button.textContent = 'Ver más';
            }
        });
    });
});

// Convertir grupo en Conclusiones destacadas
document.addEventListener("DOMContentLoaded", () => {
  const containers = document.querySelectorAll(".conclusiones-destacadas, .is-style-conclusiones");
  if (!containers) return;
	
  containers.forEach((container) => {

	  // Tomamos solo nodos de tipo ELEMENTO
	  const nodes = Array.from(container.childNodes).filter(n => n.nodeType === 1);

	  let currentCard = null;

	  nodes.forEach(node => {
		if (node.tagName === "H2") {
		  // Crear nueva card
		  currentCard = document.createElement("div");
		  currentCard.classList.add("card");
		  container.appendChild(currentCard);
		}

		// Si hay una card activa, mover el nodo dentro
		if (currentCard) {
		  currentCard.appendChild(node);
		}
		  
	  });
  });
});

// Overlay clicable para embeds de video: asegura cursor pointer y primer clic reproducible.
document.addEventListener('DOMContentLoaded', function () {
  const videoEmbeds = document.querySelectorAll('figure.wp-block-embed.is-type-video iframe');
  const embedPlayers = [];

  function parseMessageData(data) {
    if (!data) {
      return null;
    }

    if (typeof data === 'string') {
      try {
        return JSON.parse(data);
      } catch (e) {
        return null;
      }
    }

    if (typeof data === 'object') {
      return data;
    }

    return null;
  }

  function ensureIframeParam(iframe, key, value) {
    try {
      const src = iframe.getAttribute('src') || '';
      const url = new URL(src, window.location.origin);

      if (url.searchParams.get(key) !== value) {
        url.searchParams.set(key, value);
        iframe.setAttribute('src', url.toString());
      }
    } catch (e) {
      // Ignore malformed iframe URLs.
    }
  }

  function getProvider(iframe) {
    try {
      const src = iframe.getAttribute('src') || '';
      const host = new URL(src, window.location.origin).hostname.toLowerCase();

      if (host.indexOf('vimeo.com') !== -1) {
        return 'vimeo';
      }

      if (host.indexOf('youtube.com') !== -1 || host.indexOf('youtu.be') !== -1) {
        return 'youtube';
      }
    } catch (e) {
      // Ignore malformed iframe URLs.
    }

    return 'unknown';
  }

  function subscribePlayerEvents(player) {
    if (!player || !player.iframe || !player.iframe.contentWindow) {
      return;
    }

    if (player.provider === 'vimeo') {
      player.iframe.contentWindow.postMessage(JSON.stringify({ method: 'addEventListener', value: 'play' }), '*');
      player.iframe.contentWindow.postMessage(JSON.stringify({ method: 'addEventListener', value: 'pause' }), '*');
      player.iframe.contentWindow.postMessage(JSON.stringify({ method: 'addEventListener', value: 'ended' }), '*');
    }
  }

  window.addEventListener('message', function (event) {
    const message = parseMessageData(event.data);

    if (!message) {
      return;
    }

    const player = embedPlayers.find(function (entry) {
      return entry.iframe && entry.iframe.contentWindow === event.source;
    });

    if (!player || !player.figure) {
      return;
    }

    if (player.provider === 'vimeo') {
      if (message.event === 'play') {
        player.figure.classList.add('is-playing');
      }

      if (message.event === 'pause' || message.event === 'ended') {
        player.figure.classList.remove('is-playing');
      }
    }

    if (player.provider === 'youtube' && message.event === 'onStateChange') {
      if (message.info === 1) {
        player.figure.classList.add('is-playing');
      }

      if (message.info === 0 || message.info === 2) {
        player.figure.classList.remove('is-playing');
      }
    }
  });

  videoEmbeds.forEach(function (iframe) {
    const wrapper = iframe.closest('.wp-block-embed__wrapper') || iframe.parentElement;
    const figure = iframe.closest('figure.wp-block-embed.is-type-video');
    const provider = getProvider(iframe);

    if (!wrapper || !figure || wrapper.querySelector('.smn-video-overlay')) {
      return;
    }

    if (provider === 'vimeo') {
      ensureIframeParam(iframe, 'api', '1');
    }

    if (provider === 'youtube') {
      ensureIframeParam(iframe, 'enablejsapi', '1');
    }

    if (window.getComputedStyle(wrapper).position === 'static') {
      wrapper.style.position = 'relative';
    }

    const player = { iframe: iframe, figure: figure, provider: provider };
    embedPlayers.push(player);

    iframe.addEventListener('load', function () {
      subscribePlayerEvents(player);
    });

    subscribePlayerEvents(player);

    const overlay = document.createElement('button');
    overlay.type = 'button';
    overlay.className = 'smn-video-overlay';
    overlay.setAttribute('aria-label', 'Reproducir video');

    overlay.addEventListener('click', function () {
      try {
        const currentSrc = iframe.getAttribute('src') || '';
        const url = new URL(currentSrc, window.location.origin);

        if (!url.searchParams.has('autoplay')) {
          url.searchParams.set('autoplay', '1');
          iframe.setAttribute('src', url.toString());
        }
      } catch (e) {
        // Ignore malformed iframe URLs.
      }

      figure.classList.add('is-playing');
    });

    wrapper.appendChild(overlay);
  });

  const nativeVideos = document.querySelectorAll('.wp-block-video video');

  nativeVideos.forEach(function (video) {
    const block = video.closest('.wp-block-video');

    if (!block) {
      return;
    }

    video.addEventListener('play', function () {
      block.classList.add('is-playing');
    });

    video.addEventListener('pause', function () {
      block.classList.remove('is-playing');
    });

    video.addEventListener('ended', function () {
      block.classList.remove('is-playing');
    });
  });
});

jQuery('.beforeAfter, .is-style-before-after').beforeAfter({

  // is draggable/swipeable
  movable: true,

  // click image to move the slider
  clickMove: false,

  // intial position of the slider
  position: 50,

  // opacity between 0 and 1
  opacity: 0.4,
  activeOpacity: 1,
  hoverOpacity: 0.8,

  // slider colors
  separatorColor: '#ffffff',
  bulletColor: '#ffffff',
  arrowColor: '#333333',

  // Callback functions
  onMoveStart: function() {},
  onMoving: function() {},
  onMoveEnd: function() {}
  
});

  // Rotacion ciclica del titulo con efecto drop-in.
  jQuery(function ($) {
    var $dynamicTitle = $('#dynamic-title > span');

    if (!$dynamicTitle.length) {
      return;
    }

    var words = $('#dynamic-title-words li')
      .map(function () {
        return $(this).text().trim();
      })
      .get()
      .filter(function (word) {
        return word.length > 0;
      });

    if (!words.length) {
      words = [
        'importantes',
        'sonrientes',
        'tímidas',
        'chillonas',
        'discretas',
        'maduras',
        'infantiles',
        'libres'
      ];
    }

    var index = 0;
    var intervalMs = 2200;
    var outDuration = 220;
    var inDuration = 360;

    function showNextWord() {
      index = (index + 1) % words.length;

      $dynamicTitle
        .stop(true, true)
        .animate(
          {
            opacity: 0
          },
          outDuration,
          function () {
            $dynamicTitle
              .text(words[index])
              .css({
                opacity: 0,
                transform: 'translateY(-16px)'
              })
              .animate(
                {
                  opacity: 1
                },
                {
                  duration: inDuration,
                  step: function (now) {
                    var offset = (1 - now) * -16;
                    $(this).css('transform', 'translateY(' + offset + 'px)');
                  },
                  complete: function () {
                    $(this).css('transform', 'translateY(0)');
                  }
                }
              );
          }
        );
    }

    $dynamicTitle.text(words[index]).css({
      display: 'inline-block',
      position: 'relative',
      transform: 'translateY(0)'
    });

    setInterval(showNextWord, intervalMs);
  });

    // Scrollspy para navegaciones sticky de anclas.
    document.addEventListener('DOMContentLoaded', function () {
      const stickyNavs = document.querySelectorAll('.wp-block-group.is-position-sticky .wp-block-navigation');

      stickyNavs.forEach(function (nav) {
        const links = Array.from(nav.querySelectorAll('.wp-block-navigation-item__content[href^="#"]'));

        if (!links.length) {
          return;
        }

        const items = links
          .map(function (link) {
            const hash = link.getAttribute('href') || '';
            const id = hash.replace(/^#/, '');
            const section = id ? document.getElementById(id) : null;

            if (!section) {
              return null;
            }

            return {
              link: link,
              section: section,
            };
          })
          .filter(Boolean);

        if (!items.length) {
          return;
        }

        const scrollContainer = nav.querySelector('.wp-block-navigation__container') || nav;
        let activeLink = null;

        function centerActiveLink(link) {
          if (!link || !scrollContainer || scrollContainer.scrollWidth <= scrollContainer.clientWidth) {
            return;
          }

          const containerRect = scrollContainer.getBoundingClientRect();
          const linkRect = link.getBoundingClientRect();
          const currentScrollLeft = scrollContainer.scrollLeft;
          const visibilityPadding = 24;
          let targetLeft = currentScrollLeft;

          if (linkRect.left < containerRect.left + visibilityPadding) {
            targetLeft -= (containerRect.left + visibilityPadding) - linkRect.left;
          } else if (linkRect.right > containerRect.right - visibilityPadding) {
            targetLeft += linkRect.right - (containerRect.right - visibilityPadding);
          }

          targetLeft = Math.max(0, Math.min(targetLeft, scrollContainer.scrollWidth - scrollContainer.clientWidth));

          scrollContainer.scrollTo({
            left: targetLeft,
            behavior: 'smooth',
          });
        }

        function setActive(link) {
          if (!link || activeLink === link) {
            return;
          }

          links.forEach(function (itemLink) {
            itemLink.classList.remove('is-active');
            itemLink.removeAttribute('aria-current');
          });

          link.classList.add('is-active');
          link.setAttribute('aria-current', 'location');
          activeLink = link;
          centerActiveLink(link);
        }

        function updateActiveFromScroll() {
          const activationOffset = 140;
          let current = items[0];

          items.forEach(function (item) {
            const top = item.section.getBoundingClientRect().top;

            if (top - activationOffset <= 0) {
              current = item;
            }
          });

          if (current && current.link) {
            setActive(current.link);
          }
        }

        links.forEach(function (link) {
          link.addEventListener('click', function () {
            setActive(link);
          });
        });

        window.addEventListener('scroll', updateActiveFromScroll, { passive: true });
        window.addEventListener('resize', updateActiveFromScroll, { passive: true });
        updateActiveFromScroll();
      });
    });

// Navegación suave para enlaces internos con offset 80px
jQuery(function ($) {
  $('a[href^="#"]').on('click', function (e) {
    var target = this.hash;
    var $target = $(target);

    if ($target.length) {
      e.preventDefault();
      $('html, body').animate(
        {
          scrollTop: $target.offset().top - 96
        },
        600,
        'swing'
      );
    }
  });
});