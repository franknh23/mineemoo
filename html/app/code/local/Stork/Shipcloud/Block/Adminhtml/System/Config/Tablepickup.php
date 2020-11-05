<?php
class Stork_Shipcloud_Block_Adminhtml_System_Config_Tablepickup extends Mage_Adminhtml_Block_System_Config_Form_Field
{
  protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
  {
    $arrCarriers = array();
    $arrCarriers['dhl'] = 'DHL';
    $arrCarriers['dpd'] = 'DPD';
    $arrCarriers['fedex'] = 'FEDEX';
    $arrCarriers['hermes'] = 'HERMES';
    $arrCarriers['ups'] = 'UPS';

    if(date('w') == 0){
      $day_border = date('Y/m/d', strtotime(date("Y/m/d")) - 3 * 60 * 60 * 24);
      $day_border = date('Y/m/d', strtotime(date("Y/m/d")) - 3 * 60 * 60 * 24);
    }else if(date('w') == 6){
      $day_border = date('Y/m/d', strtotime(date("Y/m/d")) - 2 * 60 * 60 * 24);
    }else{
      $day_border = date('Y/m/d', strtotime(date("Y/m/d")) - 60 * 60 * 24);
    }

    $carrier = Mage::getStoreConfig('shipcloud/sp_pickup_manualy/pickup_manualy_carrier');

    $today = strtotime(date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s"))));
    $pichups = Mage::getModel('shipcloud/pickup')->getCollection();
    $pichups->addFieldToFilter('pickup_date',array('gteq' => $day_border))
            ->addFieldToFilter('carrier',array('eq' => $carrier))
            ->setOrder('pickup_date');
    if(!empty($pichups->getSize())){
      $elementHtml = '<table border=1 >
            <tr>
              <td>Carrier</td>
              <td>Date</td>
              <td>User</td>
              <td>Done</td>
            </tr>
      ';

      foreach($pichups as $pickup){

        if(isset($arrCarriers[$pickup->getCarrier()])){
          $pickupCarier = $arrCarriers[$pickup->getCarrier()];
        }else{
          $pickupCarier = $pickup->getCarrier();
        }
        $role_data = Mage::getModel('admin/user')->load($pickup->getUserId())->getUsername();

        $pickup_date_strtotime = strtotime($pickup->getPickupDate());

        if($pickup_date_strtotime > $today){
          $status = '<input type="checkbox" disabled/>';
        }else{
          $status = '<input type="checkbox" checked disabled />';
        }

        $elementHtml .= '<tr>
              <td>'.$pickupCarier.'</td>
              <td>'.date("Y/m/d", $pickup_date_strtotime).'</td>
              <td>'.$role_data.'</td>
              <td>'.$status.'</td>
            </tr>';
      }
      $elementHtml .= '</table>';
    }else{
      $elementHtml = 'No Pickup Requests!';
    }
    return $elementHtml;
  }
}
