<?php
class Olegnax_Osc_Model_Backend_Skin extends Mage_Core_Model_Config_Data
{

    protected $old = null;    
    
    public function checkAfterSave()
    {
        $value = $this->getValue();
        if ($this->old != $value)
        {
            $currentScope = $this->getScope();
            $currentScopeId = $this->getScopeId();
            foreach ($this->_getDefaultsFor($value) as $key => $value) { Mage::getConfig()->saveConfig('olegnax_appearance/' . $key, $value, $currentScope, $currentScopeId); }
            Mage::app()->cleanCache();
        }
        return $this;
    }    
    
    protected function _beforeSave()
    {
        $this->old = (string) Mage::getConfig()->getNode('olegnax_appearance/skin/skin', $this->getScope(), $this->getScopeId());
        Mage::getSingleton('olegnax_osc/observer')->setSkinModel($this);
        return $this;
    }
    
    protected function _getDefaults()
    {
        return array(           
            'athlete' => array(
                'general_app/page_background_color'                 => 'FFFFFF',                
                
                'general_app/page_title_background_color'           => 'FFFFFF',                
                'general_app/page_title_color'                      => '000000',
                'general_app/page_title_fontsize'                   => '46',    
                'general_app/page_title_fontweight'                 => '800',   
                'general_app/page_title_font'                       => '2',     
                'general_app/page_title_gfont'                      => 'Open Sans',

                'title_app/title_background_color'                  => 'f8f8f8',
                'title_app/title_font_color'                        => '000000',
                'title_app/title_fontsize'                          => '20',    
                'title_app/title_fontweight'                        => '800',   
                'title_app/title_font'                              => '2',                
                'title_app/title_gfont'                             => 'Open Sans',
                'title_app/title_icons'                             => 'disabled',
                'title_app/title_align'                             => 'left',  
                'title_app/title_icon_pos'                          => '0',     

                'content_app/content_blocks_background_color'       => 'f8f8f8',
                'content_app/content_blocks_border_color'           => '000000',
                'content_app/content_blocks_border_width'           => '0',     
                
                'content_app/content_inputs_border_color'           => 'dfdfdf',           
                'content_app/content_divider_color'                 => 'e7e7e7',
                
                'content_app/content_title_divider_color'           => 'ffe51e',
                'content_app/content_title_border_width'            => '6',            
                
                'content_app/content_font_color'                    => '322c29',
                'content_app/content_fonts'                         => 'default',
                'content_app/content_fontsize'                      => '12',    
                'content_app/content_price_fontsize'                => '14',    
                
                'content_app/content_link_color'                    => '000000',
                'content_app/content_hoverlink_color'               => 'ffffff',
                
                'content_app/content_button_color'                  => 'ffe51e',
                'content_app/content_hoverbutton_color'             => '000000',
                'content_app/content_button_text_color'             => '000000',  
                'content_app/content_button_hover_text_color'       => 'ffffff',
                'content_app/content_button_color_inversion'        => '0',
                'content_app/content_button_style'                  => '0',
                
                'content_app/content_qty_button_color'              => '000000',
                'content_app/content_qty_hoverbutton_color'         => 'ffe51e',
                'content_app/content_qty_button_color_inversion'    => '1',     
                
                'content_app/content_price_color'                   => '000000',
                'content_app/content_discount_price_color'          => '699a00', 
                'content_app/content_special_price_color'           => '000000',

                'place_order_app/place_order_fontsize'              => '12',    
                'place_order_app/place_order_background_color'      => 'f2f2f2',  
                
                'place_order_app/place_order_button_color'          => 'ffe51e',  
                'place_order_app/place_order_button_hover_color'    => '000000',
                'place_order_app/place_order_button_text_color'          => '000000',  
                'place_order_app/place_order_button_hover_text_color'    => 'ffffff',
                
                'place_order_app/place_order_block_border_color'    => '000',   
                'place_order_app/place_order_block_border_width'    => '0',      
                
                'place_order_app/place_order_grandtotal_fontsize'   => '18',    
                'place_order_app/place_order_grandtotal_font_color' => '000000',
            ),  
        );
    }    
    
    protected function _getDefaultsFor($skin)
    {
        $defaults = $this->_getDefaults();
        if(isset($defaults[$skin])) { return $defaults[$skin]; }
        return array();        
    }
}
