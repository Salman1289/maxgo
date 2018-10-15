jQuery( document ).ready(function() {

			jQuery("#content-slider").lightSlider({
                loop:true,
                keyPress:true
            });
          jQuery('#image-gallery').lightSlider({
                gallery:true,
                item:1,
                thumbItem:9,
                slideMargin: 0,
                speed:700,
                auto:true,
                loop:true,
                onSliderLoad: function() {
                  jQuery('#image-gallery').removeClass('cS-hidden');
                }  
            });
		

});

