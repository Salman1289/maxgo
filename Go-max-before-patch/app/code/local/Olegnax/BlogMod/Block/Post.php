<?php 

class Olegnax_BlogMod_Block_Post extends AW_Blog_Block_Post {

    protected function _getReadMoreLink($item)
	{
		return '<button class="button aw-blog-read-more" onclick="setLocation(\'' . $item->getAddress() . '\');"><span><span>' . $this->__('Read More') . '</span></span></button>';
	}

}
