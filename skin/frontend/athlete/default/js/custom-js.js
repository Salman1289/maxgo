jQuery(document).ready(function(){
    // jQuery('.add-review').css("display","none");
    // jQuery('.submit-review-btn,.add-review-link,.no-rating a').click(function(){
    //     jQuery('.add-review').css("display","block");
    // });

    jQuery('.sale-feature-products .main-heading a:first').addClass("active");
    jQuery('.sale-feature-products .category-main:first').addClass("active");

   jQuery('.sale-feature-products .main-heading a:first').click(function(){
   	jQuery('.sale-feature-products .main-heading a:nth-child(2)').removeClass("active");
   	jQuery('.sale-feature-products .main-heading a:first').addClass("active");
   	 jQuery('.sale-feature-products .category-main:eq( 0 )').addClass("active");
   	  jQuery('.sale-feature-products .category-main:eq( 1 )').removeClass("active");
   });

   jQuery('.sale-feature-products .main-heading a:nth-child(2)').click(function(){
   	jQuery('.sale-feature-products .main-heading a:first').removeClass("active");
   	jQuery('.sale-feature-products .main-heading a:nth-child(2)').addClass("active");
   	 jQuery('.sale-feature-products .category-main:eq( 1 )').addClass("active");
   	  jQuery('.sale-feature-products .category-main:first').removeClass("active");
   });

    // Home gallery points (images and videos hide show)
      jQuery('.gallery-sec .videos').hide();
    jQuery('.gallery-sec .gallery-btn .imgs-btn').click(function(){
       jQuery('.gallery-sec .images').fadeIn(1000);
       jQuery('.gallery-sec .videos').fadeOut(1000);

    });
      jQuery('.gallery-sec .gallery-btn .vid-btn').click(function(){
         jQuery('.gallery-sec .images').fadeOut(1000);
       jQuery('.gallery-sec .videos').fadeIn(1000);
      });

      jQuery('.gallery-sec .gallery-btn a').click(function(){
         jQuery('.gallery-sec .gallery-btn a').removeClass('active');
        jQuery(this).addClass('active');
      });


    jQuery(".config-img").on("click", function() {
    jQuery(".apperal").animate({width:'toggle'}, 1050);
});

jQuery(window).scroll(function(){

    var top = jQuery(window).scrollTop();

    if(top >= 70)
    {
        jQuery('.top-links-container').addClass('top-links-hide');

// this is for the account link in the header, which should be hide wen scroll
        jQuery('.secondary-account-link').css('display','block');

        jQuery('.secondary-account-link').removeClass('top-links-hide');

        jQuery('.header-container').addClass('secondary-top-header');
    }else {
        
        if(jQuery('.header-container').hasClass('secondary-top-header')){
            
       jQuery('.header-container').removeClass('secondary-top-header'); 

       jQuery('.secondary-account-link').css('display','none');
    }

      if(jQuery('.top-links-container').hasClass('top-links-hide')){
            
       jQuery('.top-links-container').removeClass('top-links-hide'); 
    }

  }
    
   if(top >= 70)
    {
  
       jQuery('.hide').css('display','block');
    }else{
         jQuery('.hide').css('display','none');
    }
});


    jQuery(".top-links .links li.first").hover(function() {
   
});

jQuery('.top-links .links li').hover(

  function() {

    jQuery('.sub-main', this).stop().slideDown(400);

  },

  function() {

    jQuery('.sub-main', this).stop().slideUp(400);

  });

 // to remove the "move to wishlist" colum from the customer panel
jQuery( "#shopping-cart-table .link-wishlist" ).parent().css( "display", "none" );


});
jQuery(function () {
    jQuery('.sale-feature-products .main-heading a,.gallery-sec .gallery-btn a').on("click", function (e) {
        e.preventDefault();
    });
});

// add-review