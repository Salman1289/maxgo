//HACK FOR OLEGNAX_OSC REDIRECT TO CHECKOUT
jQuery(function($) 
{
	$('.btn-cart').off('click');
	$('.btn-cart').on('click', function () 
	{		
		var onclick = $(this).attr('data-click');
		if (onclick == '') { return true; }
		if ($(this).closest("form").length || onclick.indexOf("submit") != -1) 
		{
			$(this).attr('onclick', $(this).attr('data-click'));
			$(this).attr('data-click', '');
			$(this).trigger('click');
			return false;
		}
		var url = onclick.replace("setLocation('",'').replace("')",'');
		if ( url.indexOf("checkout/cart") != -1) 
		{
			document.location.href = url;
			return false;
		} 
		else 
		{
			if (Olegnax.Ajaxcart.options.quick_view) 
			{               
				$(this).closest('li.item').find('button.quick-view').trigger('click');
				return false;
			} 
			else 
			{
				$(this).attr('onclick', $(this).attr('data-click'));
				$(this).attr('data-click', '');
				$(this).trigger('click');
				return false;
			}
		}
	});	
});