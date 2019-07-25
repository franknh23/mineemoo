<?php

class Advanced_Cartreminder_Model_Customergroup
{
    public function toOptionArray()
    {
		$customergroup=Mage::getModel('customer/group')->getCollection();
		
		$array_list = array();
		$count=1;
                $array_list[0]=array('value'=>0, 'label'=>Mage::helper('cartreminder')->__('No Logged In'));
		foreach($customergroup as $group){
			if($group->getCustomerGroupId()){
				$array_list[$count]=array('value'=>$group->getCustomerGroupId(), 'label'=>$group->getCustomerGroupCode());
				$count++;
			}
		}
                $array_list[$count+1]=array('value'=>'na', 'label'=>Mage::helper('cartreminder')->__('None'));
        return $array_list;
    }
}