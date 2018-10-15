<?php

class Olegnax_BlogMod_Model_Observer {

    public function formHook($event)
    {
        $block = $event->getBlock();
    
        if ( ! $block) {
            return $this;
        }
        if ($block->getType() == 'blog/manage_blog_edit_form') {
            $form = $block->getForm();
            $form->setData('enctype', 'multipart/form-data');
        } elseif ($block->getType() == 'blog/manage_blog_edit_tab_form') {
            $form = $block->getForm();
            try {
                $form->checkElementId('blog_images');
            } catch (Exception $e) {
                return;
            }
            $data = array();
            
            if (Mage::getSingleton('adminhtml/session')->getBlogData()) {
                $data = Mage::getSingleton('adminhtml/session')->getBlogData();
            } elseif (Mage::registry('blog_data')) {
                $data = Mage::registry('blog_data')->getData();
            }
            
            $fieldsetImages = $form->addFieldset('blog_images', array('legend' => Mage::helper('blog')->__('Post Images')));
            $out = '';
            $fieldsetImages->addField('image_main', 'checkbox', array(
                'label' => Mage::helper('blog')->__('Show Main Image on post page'),
                'required' => false,
                'name' => 'image_main',
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                'checked'  => isset($data['image_main']) && $data['image_main'] == 1 ? 'checked' : '',
            ));
            $fieldsetImages->addField('image', 'image', array(
                'label' => Mage::helper('blog')->__('Main Image'),
                'required' => false,
                'name' => 'image',
                'note' => $out,
            ));
            $fieldsetImages->addField('image_retina', 'image', array(
                'label' => Mage::helper('blog')->__('Main Image for Retina'),
                'required' => false,
                'name' => 'image_retina',
                'note' => 'Upload double size image for retina<br/>' . $out,
            ));

            $fieldsetImages->addField('thumb', 'image', array(
                'label' => Mage::helper('blog')->__('Thumb Image'),
                'required' => false,
                'name' => 'thumb',
                'note' => $out,
            ));
            $fieldsetImages->addField('thumb_retina', 'image', array(
                'label' => Mage::helper('blog')->__('Thumb Image for Retina'),
                'required' => false,
                'name' => 'thumb_retina',
                'note' => $out,
            ));
            
            $filtered = array();
            foreach ($this->getFields() as $field)
                if (isset($data[$field]))
                    $filtered[$field] = $data[$field];
                
            $form->addValues($filtered);
        }
    }
    
    public function getFields()
    {
        return array(
            'image_main',
            'image',
            'image_retina',
            'thumb',
            'thumb_retina',
        );
    }

}
