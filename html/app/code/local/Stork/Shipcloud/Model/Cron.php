<?php
class Stork_Shipcloud_Model_Cron{
  public function updateShipcloudGls()
  {
    $headers = array(
        'Accept: application/json',
        'Content-Type: application/json',
        'Affiliate-ID: plugin.magento.4JcAix8R',
    );
    $dataToSend = Mage::helper('shipcloud')->getServiceAccessData();
    $url = 'https://api.shipcloud.io/v1/shipments?carrier=gls';
    $handle = curl_init();
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($handle, CURLOPT_USERPWD, $dataToSend['api_key']);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($handle, CURLOPT_TIMEOUT, 30);
    curl_setopt($handle, CURLOPT_HTTPGET, true);

    $responseJson = curl_exec($handle);
    curl_close($handle);
    $decodedResponse = json_decode($responseJson, true);

    foreach ($decodedResponse['shipments'] as $shipment) {
      if(!empty($shipment['carrier_tracking_no'])){
        $shipcloudItem = Mage::getModel('shipcloud/shipcloud')->load('384bb11434f3acb8d39fa4b540eb9245a8e5025f','response_id');
        if($shipcloudItem && !empty($shipcloudItem->getData())){
          $shipcloudItem->setTrackingnumber($shipment['carrier_tracking_no']);
          $shipcloudItem->setShippingCarrier($shipment['carrier']);
          $shipcloudItem->save();

          $orderShipment = Mage::getModel('sales/order_shipment')->load($shipcloudItem->getShipmentId());
          $track = Mage::getModel('sales/order_shipment_track')
                   ->setNumber($shipment['carrier_tracking_no'])
                   ->setCarrierCode('GLS')
                   ->setTitle('Custom Value');

          $orderShipment->addTrack($track);

          try {
             $orderShipment->save();
          } catch (Mage_Core_Exception $e) {
             Mage::log("Can't save shipment ".$shipcloudItem->getShipmentId(),null,'shipcloud_gls_update.log');
          }
        }
      } else {
        Mage::log('Nu tracking_no for '.$shipment['id'],null,'shipcloud_gls_update.log');
      }
    }
  }
}
