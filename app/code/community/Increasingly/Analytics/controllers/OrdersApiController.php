<?php
/**
* Magento
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Magento to newer
* versions in the future. If you wish to customize Magento for your
* needs please refer to http://www.magentocommerce.com for more information.
*
* @category  Increasingly
* @package   Increasingly_Analytics
* @author    Increasingly Pvt Ltd
* @copyright Copyright (c) 2015-2016 Increasingly Ltd (http://www.increasingly.co)
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
/**
* Returns order details to increasingly on API call
*/

class Increasingly_Analytics_OrdersApiController extends Mage_Core_Controller_Front_Action 
{
 
 public function ordersAction() 
  {
    try 
    {
      $version = (string)Mage::getConfig()->getModuleConfig("Increasingly_Analytics")->version;

      if(!$this->isRequestAuthorized()) 
      {
        return $this;
      }

      $orders = array();         

      $limit = $this->getRequest()->getParam('limit', 200);
      $offset = $this->getRequest()->getParam('offset', 1);
      
      $orders =Mage::getModel('sales/order')->getCollection()->setPageSize($limit)->setCurPage($offset);      
      $totalOrdersCount = Mage::getModel('sales/order')->getCollection()->getSize();

      $orderDetailsForImport = array();

      foreach ($orders as $order) 
      {
          if ($order->getId()) 
          {            
            $priceFormatter = Mage::helper('increasingly_analytics/PriceFormatter');   
           
	    $orderDetails = array(
		'order_id'            => $order->getIncrementId(),
		'order_status'        => $order->getStatus(),
		'order_amount'        => $priceFormatter->format($order->getGrandTotal()),
		'shipping_amount'     => $priceFormatter->format($order->getShippingAmount()),
		'tax_amount'          => $priceFormatter->format($order->getTaxAmount()),
		'items'               => array(),
		'shipping_method'     => $order->getShippingDescription(),
		'currency_code'       => $order->getOrderCurrencyCode(),
		'payment_method'      => $order->getPayment()->getMethodInstance()->getTitle()	    
	    );

	    if($order->getCustomerIsGuest()){
	      $orderDetails['customer_email']        = $order->getCustomerEmail();
	      $orderDetails['customer_first_name']   = $order->getCustomerFirstname();
	      $orderDetails['customer_last_name']    = $order->getCustomerLastname();
	      $orderDetails['customer_name']         = $order->getCustomerFirstname(). ' '. $order->getCustomerLastname();	       
	    }
	    else {
	      $orderDetails['customer_email']        = $order->getCustomerEmail();
	      $orderDetails['customer_first_name']   = $order->getBillingAddress()->getFirstname();
	      $orderDetails['customer_last_name']    = $order->getBillingAddress()->getLastname();
	      $orderDetails['customer_name']         = $order->getBillingAddress()->getName();     
	    }

	    if ($order->getDiscountAmount()) {
	      $orderDetails['discount_amount'] = $priceFormatter->format($order->getDiscountAmount());
	    }
	    if ($order->getCouponCode()) {
	      $orderDetails['coupons'] = $order->getCouponCode();
	    }
	    if($order->getRemoteIp()){
	      $orderDetails['user_ip'] = $order->getRemoteIp();
	    }
	    if ($order->getCreatedAt()) {
	      $orderDetails['order_time'] = $order->getCreatedAt();
	    }

	    foreach ($order->getAllItems() as $item) 
	    {
	      $orderItem = array(
		'product_id'    => $item->getProductId(),
		'product_price' => $priceFormatter->format($item->getPrice()) ? $priceFormatter->format($item->getPrice()) : 
				                    $priceFormatter->format($item->getProduct()->getFinalPrice()),
		'product_name'  => $item->getName(),
		'product_url'   => $item->getProduct()->getProductUrl(),
		'product_sku'   => $item->getSku(),
		'qty'  	   	=> (int)$item->getQtyOrdered(),
		'product_type'	=> $item->getProductType()
	      );
	      $orderDetails['items'][] = $orderItem;      
	    }  
	    
            array_push($orderDetailsForImport, $orderDetails);
          }
       }
       
      $this->getResponse()
        ->setBody(json_encode(array('orders' => $orderDetailsForImport,'version' => $version, 'total_order_count' => $totalOrdersCount)))
        ->setHttpResponseCode(200)
        ->setHeader('Content-type', 'application/json', true);


    } catch(Exception $e) {

    Mage::log($e->getMessage(), null, 'Increasingly_Analytics.log');

    $this->getResponse()
    ->setBody(json_encode(array('status' => 'error', 'message' => $e->getMessage(), 'version' => $version)))
    ->setHttpResponseCode(500)
    ->setHeader('Content-type', 'application/json', true);
    }

    return $this;

  }

  private function isRequestAuthorized()
  {
    $helper = Mage::helper('increasingly_analytics');

    if ($helper->isEnabled())
    {
      $apiKey = $helper->getApiToken(); 
      $version = (string)Mage::getConfig()->getModuleConfig("Increasingly_Analytics")->version;

      // Check for api key
      if(!$apiKey && strlen($apiKey) === 0) {
       
      $this->getResponse()
      ->setBody(json_encode(array('status' => 'error', 'message' => 'API key is missing', 'version' => $version)))
      ->setHttpResponseCode(403)
      ->setHeader('Content-type', 'application/json', true);
      return false;
      }

      $authKey = $this->getRequest()->getHeader('authKey');

      if (!$authKey || strlen($authKey) == 0) {
        $authKey = $this->getRequest()->getParam('authKey');
      }

      if (!$authKey) {

      $this->getResponse()
        ->setBody(json_encode(array('status' => 'error', 'message' => 'Error,Authorization header not found', 'version' => $version)))
        ->setHttpResponseCode(500)
        ->setHeader('Content-type', 'application/json', true);
        return false;
      }

      if(trim($authKey) !== trim($apiKey)) {

        $this->getResponse()
        ->setBody(json_encode(array('status' => 'error', 'message' => 'Authorization failed', 'version' => $version)))
        ->setHttpResponseCode(401)
        ->setHeader('Content-type', 'application/json', true);
        return false;
      }

      return true;
    }
    else {

    $this->getResponse()
      ->setBody(json_encode(array('status' => 'error', 'message' => 'Increasingly module is disabled', 'version' => $version)))
      ->setHttpResponseCode(403)
      ->setHeader('Content-type', 'application/json', true);
      return false;
    }

  }

}
