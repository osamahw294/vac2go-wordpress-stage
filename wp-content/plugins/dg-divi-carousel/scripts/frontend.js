// This script is loaded both on the frontend page and in the Visual Builder.
jQuery(function($) {
	function parseDatasetJson(value, fallback, context) {
		if (typeof value !== 'string') {
			return fallback;
		}

		const normalizedValue = value.trim();

		if (
			'' === normalizedValue ||
			'undefined' === normalizedValue ||
			'null' === normalizedValue
		) {
			return fallback;
		}

		try {
			return JSON.parse(normalizedValue);
		} catch (error) {
			if (window.console && typeof window.console.warn === 'function') {
				window.console.warn('Divi Carousel: invalid JSON dataset payload.', {
					context,
					value: normalizedValue,
					error,
				});
			}

			return fallback;
		}
	}
	if(window.location.hash) {
		let hash = window.location.hash.substring(1);
		// alert (hash);
	}
	
    $('.dica_divi_carousel').each(function(index, element){
		let active_index = 0;
	    // Limit Content
	    textExcerptController(element);
	    window.addEventListener('resize', function(){
		    textExcerptController(element);
	    })
      let selector            =   element.querySelector('.swiper-container');
      let container           =   element.querySelector('.dica-container');
      let data                =   parseDatasetJson(
                                  container && container.dataset ? container.dataset.props : null,
                                  null,
                                  'dica-container[data-props]'
                                );
      if (!data) {
        return;
      }
      let dataSpeed           =   Number(data.speed);
      let dataDesktop         =   Number(data.desktop);
      let dataTablet          =   Number(data.tablet);
      let dataMobile          =   Number(data.mobile);
      let dataArrow           =   data.arrow;
      let dataDots            =   data.dots;
      let dataAutoPlay        =   data.autoplay;
      let dataAutoPlaySpeed   =   Number(data.autoSpeed);
      let dataloop            =   data.loop;
      let itemSpace           =   Number(data.item_spacing.replace(/[^0-9.]/g, ""));
      let centerMode          =   data.center_mode;
      let sliderEffect        =   data.slider_effec;
      let pausehover          =   data.pause_onhover;
      let multislide          =   data.multislide;
      let cfshadow            =   data.cfshadow;
      let order               =   data.order;
      let lazyload            =   data.lazyload == 'on' ? 
                                  {loadedClass : 'swiper-lazy-loaded'} : false;
      let scroller_effect     =   ('on' === dataAutoPlay) ? data.scroller_effect : 'off';
      let scrollSpeed         =   Number(data.scroller_speed);
      let autoWidth           =   data.autowidth;
      let item_spacing_tablet =  Number(data.item_spacing_tablet.replace(/[^0-9.]/g, ""));
      let item_spacing_phone  =  Number(data.item_spacing_phone.replace(/[^0-9.]/g, ""));

      dataAutoPlaySpeed = 'on' !== scroller_effect ? dataAutoPlaySpeed : 1;
      
      if (data.lazybefore == 'on') {
        lazyload.loadOnTransitionStart = true ;
      } else {
        lazyload.loadOnTransitionStart = false;
      }

      let nav_object          =   (dataArrow == "on") ? { nextEl: '.dica-next-btn-'+order, prevEl: '.dica-prev-btn-'+order } : false;
      let pagination_opject   =   (dataDots == 'on') ? {el: '.dica-paination-'+order,clickable: true} : false;
      let rotate              =   Number(parseInt(data.cover_rotate));   
      rotate = isNaN(rotate) ? 0 : rotate;

	    if (data.hashNavigation === 'on') {
		    $(element)
			    .find('.dica_divi_carouselitem')
			    .each(function () {
				    const dicaItem = this.querySelector('.dica-item');
				    if (dicaItem) {
					    this.dataset.hash = dicaItem.dataset.hash || '';
				    }
			    });
	    }

      let diviCarousel = new Swiper (selector, {
        slidesPerView: 'on' !== autoWidth ? dataDesktop : 'auto',
        slidesPerGroup : ( multislide == 'on' && 'on' !== autoWidth && 'on' !== scroller_effect ) ? Number(dataDesktop) : 1,
	    slidesPerColumn: parseInt ( data.slide_row, 10 ),
	    slidesPerColumnFill: "row",
        navigation: nav_object,
        pagination: pagination_opject,
        spaceBetween: itemSpace,
        autoplay: (dataAutoPlay == 'on')? {enabled: false, delay: dataAutoPlaySpeed, disableOnInteraction: false} : false,
        speed       : (scroller_effect !== 'on' && dataAutoPlay !== 'on') ? dataSpeed : scrollSpeed,
        slideClass :   'dica_divi_carouselitem',
        loop       :   (dataloop == 'on') ? true : false,
        centeredSlides: (centerMode == 'on') ? true : false,
	      // direction: 'horizontal',
        effect: (sliderEffect == '1')? 'slide' : sliderEffect,
        coverflowEffect: {
          rotate: rotate,
          stretch: 0,
          depth: 100,
          modifier: 1,
          slideShadows : (cfshadow !== "off")? true: false,
        },
        // setWrapperSize      : true,
        observer            : true,
        observeParents      : true,
        observeSlideChildren: true,
        preloadImages: data.lazyload == 'on' ? false : true,
        watchSlidesVisibility: true,
        preventClicks : true,
        preventClicksPropagation: true,
        slideToClickedSlide: false,
        touchMoveStopPropagation : true,
        // for preventing slide on click
        threshold: 15,
        // cache : true,
        lazy: lazyload,
        // hashNavigation 
        hashNavigation: data.hashNavigation === 'on' ? {
          watchState: true,
	      replaceState: true,

        } : false,
        // Responsive breakpoints
        breakpoints: {
            // when window width is >= 981px / desktop
            981: {
              slidesPerView: 'on' !== autoWidth ? dataDesktop : 'auto',
              slidesPerGroup : ( multislide == 'on' && 'on' !== autoWidth && 'on' !== scroller_effect ) ? Number(dataDesktop) : 1,
              spaceBetween : itemSpace,
	            slidesPerColumn: parseInt ( data.slide_row, 10 ),
	            slidesPerColumnFill: "row",
            },
            // when window width is >= 768px / tablet
            768: {
              slidesPerView: 'on' !== autoWidth ? dataTablet : 'auto',
              slidesPerGroup : ( multislide == 'on' && 'on' !== autoWidth && 'on' !== scroller_effect ) ? Number(dataTablet) : 1,
              spaceBetween : item_spacing_tablet,
	            slidesPerColumn: parseInt ( data.slide_row_tablet, 10 ),
	            slidesPerColumnFill: "row",
            },
            // when window width is >= 1px / mobile
            1: {
              slidesPerView: 'on' !== autoWidth ? dataMobile : 'auto',
              slidesPerGroup : ( multislide == 'on' && 'on' !== autoWidth && 'on' !== scroller_effect ) ? Number(dataMobile) : 1,
              spaceBetween : item_spacing_phone,
	            slidesPerColumn: parseInt ( data.slide_row_phone, 10 ),
	            slidesPerColumnFill: "row",
            },
            
        },
        // update since 2.0.22
	    keyboard: { enabled: ('on' === data.keyboard), onlyInViewport: !1 },
	    mousewheel: { enabled: ('on' === data.mousewheel), invert: true },
        simulateTouch: data.simulatetouch === 'on'? false : true,
        allowTouchMove: data.allowtouchmove === 'on'? false : true,
      });
      
      // smooth scroller effect ( fix the speed at first load )
      if('on' === scroller_effect &&  'on' === dataAutoPlay ) {
        diviCarousel.freeMode = true;
        diviCarousel.autoplay.stop();
		//
        // setTimeout(start_autoplay, 1000);
		//
        // function start_autoplay(){
        //   diviCarousel.autoplay.start()
        // }
        
      }
      // if the carousel in a tab module
      if('on' !== scroller_effect) {
        diviCarousel.on('observerUpdate', function (e) {
          if(dataAutoPlay == 'on') {
            diviCarousel.autoplay.paused = false;
            diviCarousel.translate = 0;
          }
          diviCarousel.update();
        })
      } 
      // pause on hover
      if ( pausehover === 'on' && dataAutoPlay === 'on') {
        selector.addEventListener("mouseover", function(){
          diviCarousel.autoplay.stop();
        })
        selector.addEventListener("mouseout", function(){
          diviCarousel.autoplay.start();
        })
      }    
      // lazy loading effect
      if(data.lazyload == 'on') {
        diviCarousel.on("lazyImageReady", function(slideEl, imageEl){
          slideEl.querySelector('.dica-item').classList.remove('loading');
        })
      }

	  // Autoplay with Waypoint
	  if ('on' === dataAutoPlay) {
		if (typeof Waypoint === "undefined") {
		  diviCarousel.autoplay.start();
		} else {
		  new Waypoint({
			element: element,
			handler: function () {
				setTimeout(start_autoplay, 1000);

				function start_autoplay(){
				  diviCarousel.autoplay.start()
				}
			  this.destroy();
			},
			offset: data.autoplay_viewport
		  });
		}
	  }
      
      // click functionality for the link option
      $(this).find('.dica_divi_carouselitem .et_pb_module_inner').on('click', function(e) {
        let link = $(this).find('.dica-item')[0].dataset.link;

        if ( link ) {
            if(link.indexOf("#") != -1) {
                et_pb_smooth_scroll();
            } else {
                e.stopPropagation();
                let _target = $(this).find('.dica-item')[0].dataset.target;
                if ( undefined !== link ) {
                  if ( _target === '_blank') {
                    window.open(link);
                  } else {
                    window.location = link;
                  }
                }
            }
        }
          
      })
  })

  // Image light box feature
  let lightbox = $('body').append('<div class="dg-carousel-lightbox"><div class="lightbox-header"><button class="close-btn">&#9587;</button></div><div class="image-wrapper"><span><img src="" /></span></div></div>');
	$( '.dica-image-container a[data-lightbox]' ).each( function ( index, ele ) {
		const $this = $( this );
		const lightbox_type = $this.attr( 'data-lightbox_type' );
		const lightbox_target = $this.attr( 'data-lightbox_target' );
		const $image_link = $this.attr( 'data-src' );
		const lightbox_con = $( '.dg-carousel-lightbox' );

		$this.click( function ( e ) {
			const image_wrapper = lightbox_con.find( ".image-wrapper" ).empty();
			if ( 'video' === lightbox_type ) {
				if ( 'off' === lightbox_target ) {
					e.preventDefault();
					lightbox_con.addClass( 'open' );
					image_wrapper.append(
						`<span>
						<iframe 
							frameborder="0" 
							allowfullscreen 
							src="${$image_link}?autoplay=1" 
							allow="autoplay"
						></iframe>
					</span>`
					);
				}
			} else {
				e.preventDefault();
				lightbox_con.addClass( 'open' );
				const $image_caption = $this.attr( 'data-caption' );
				image_wrapper.append( `<span><img class="" src="${$image_link}" alt=""></span>` );
				if ( $image_caption.length > 0 ) {
					image_wrapper.append( `<span class="dg_caption">${$image_caption}</span>` );
				}
			}

		} )
	} )
  $('.dg-carousel-lightbox .close-btn').click(function(){
	  $(this).parent().parent().find('.image-wrapper').empty();
	  $(this).parent().parent().removeClass('open');
  })
  $('.dg-carousel-lightbox .image-wrapper').click(function(e){
    if(e.target.tagName !== 'IMG') {
	    $(this).empty();
	    $(this).parent().removeClass('open');
    }
  })


	function textExcerptController(element) {
		const contentWrapper = element.querySelectorAll('.content');
		if (contentWrapper.length > 0) {
			contentWrapper.forEach(wrapper => {
				if (!wrapper.dataset.settings) return;
				const settings = parseDatasetJson(wrapper.dataset.settings, null, '.content[data-settings]');
				if (!settings) return;
				if ('true' === settings.status){
					wrapper.innerHTML = "";
					const readMoreText = (settings.text_more);
					const showlessText = (settings.text_less);
					let setLength = parseInt(settings.limit);
					if ( $(window).width() < 981 && 'on' === settings.responsive ) {
						setLength = parseInt(settings.limit_tablet);
					}
					if ( $(window).width() < 768 && 'on' === settings.responsive ) {
						setLength = parseInt(settings.limit_phone);
					}
					const storageContentWrapper = $(wrapper).parent().find('noscript.content_storage');
					const content = (storageContentWrapper[0].innerHTML).trim();
					const maxLength = content.length;
					let limitContent = "";
					let ReadMore = "";
					let hideContent = "";

					let tempDiv = document.createElement('div');
					tempDiv.innerHTML = content;
					if(tempDiv.textContent.trim().length <= setLength){
						limitContent = content;
					}else{
						limitContent = truncateHTML(content, setLength);
						hideContent = '<div class="dg_hide_content">' + content.substring(maxLength, setLength) + '</div>';
						ReadMore = '<span><a class="dg_expand_content" href="javascript:void(0)">' + readMoreText + '</a></span>';
					}

					wrapper.innerHTML = limitContent + hideContent + ReadMore;

					if(hideContent.length > 0){
						const readMoreBtn = wrapper.querySelector('.dg_expand_content');

						readMoreBtn.addEventListener('click', function () {
							const isActive = $(this).hasClass('active');
							$(this).toggleClass("active");
							this.innerText = isActive ? readMoreText : showlessText;

							wrapper.innerHTML = isActive ? limitContent + hideContent : content;
							!isActive && $(wrapper).hasClass('dg_enable_content_limit')
								? $(wrapper).removeClass('dg_enable_content_limit')
								: $(wrapper).addClass('dg_enable_content_limit');
							wrapper.appendChild(this);
						})
					}
				}

			});
		}
	}


	function truncateHTML(html, maxLength) {
		let tempDiv = document.createElement('div');
		tempDiv.innerHTML = html;

		// Function to truncate text nodes
		let remaining = maxLength;
		function truncateTextNodes(node) {
			if (remaining <= 0) {
				// Remove all child nodes if maxLength is zero or negative
				while (node.firstChild) {
					node.removeChild(node.firstChild);
				}
				node.remove()
				return;
			}

			if (node.nodeType === Node.TEXT_NODE) {
				// // If the node is a text node, truncate its content
				let text = node.textContent.trim();
				if (text.length > remaining) {
					node.textContent = text.slice(0, remaining);
				}else{
					node.textContent = text + " "
				}
				remaining -= text.length;
			} else {
				// If the node is an element node, traverse its children
				for (let i = 0; i < node.childNodes.length; i++) {
					truncateTextNodes(node.childNodes[i]);
					if (remaining <= 0) {
						// Remove remaining child nodes if no more length is allowed
						while (node.childNodes.length > i + 1) {
							node.removeChild(node.childNodes[i + 1]);
						}
						break;
					}
				}
			}
		}
		if(tempDiv.textContent.length > maxLength){
			truncateTextNodes(tempDiv)
		}

		return tempDiv.innerHTML;
	}

});
