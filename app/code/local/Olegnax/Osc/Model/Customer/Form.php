<?php
class Olegnax_Osc_Model_Customer_Form extends Mage_Customer_Model_Form 
{    
    public function validateData(array $data)
    {
        $errors = array();
        foreach ($this->getAttributes() as $currentAttribute) 
        {
            if ($this->_isAttributeOmitted($currentAttribute)) { continue; }
            $model = $this->_getAttributeDataModel($currentAttribute);
            $model->setExtractedData($data);
            if (!isset($data[$currentAttribute->getAttributeCode()])) { $data[$currentAttribute->getAttributeCode()] = null; }
            $result = $model->validateValue($data[$currentAttribute->getAttributeCode()]);
            if ($result !== true)
            {
                $mapped = $currentAttribute->getAttributeCode();
                if ($mapped == 'country_id') { $mapped = 'country'; }
                if ( ! Mage::getStoreConfig('olegnax_osc/required_fields/' . $mapped))
                {
                    $currentLabel = Mage::helper('eav')->__($currentAttribute->getStoreLabel());                    
                    $validateRules = $currentAttribute->getValidateRules();
                    if (!empty($validateRules['min_text_length']) && $length < $validateRules['min_text_length']) 
                    {
                        $v = $validateRules['min_text_length'];
                        $lengthError = Mage::helper('eav')->__('"%s" length must be equal or greater than %s characters.', $currentLabel, $v);
                    }
                    else
                    {
                        $lengthError = null;
                    }                    
                    foreach ($result as $idx => $error)
                    {
                        if ($error == Mage::helper('eav')->__('"%s" is a required value.', $currentLabel)) { unset($result[$idx]); }
                        elseif ($error === $lengthError) { unset($result[$idx]); }
                    }
                }
                if (count($result)) { $errors = array_merge($errors, $result); }
            }
        }        
        if (count($errors) == 0) { return true; }
        return $errors;
    }
}
?>
