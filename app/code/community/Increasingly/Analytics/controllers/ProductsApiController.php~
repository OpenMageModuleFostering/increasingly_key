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
* Returns product details to increasingly on API call
*/

class Increasingly_Analytics_ProductsApiController extends Mage_Core_Controller_Front_Action 
{
 
 public function productsAction() 
  {
    try 
    {
      $version = (string)Mage::getConfig()->getModuleConfig("Increasingly_Analytics")->version;

      if(!$this->isRequestAuthorized()) 
      {
        return $this;
      }

      $products = array();
      $productFormatHelper = Mage::helper('increasingly_analytics/ProductFormatter');

      // $attributes = array(
      //   'name',
      //   'sku',
      //   'image',
      //   'manufacturer',
      //   'price',
      //   'final_price',
      //   'special_price',
      //   'description',
      //   'short_description',
      //   'color',
      //   'weight',
      //   'size'          
      //   );

      $limit = $this->getRequest()->getParam('limit', 200);
      $offset = $this->getRequest()->getParam('offset', 1);
      $tempAttributes = array();
      $productsCollection = Mage::getModel('catalog/product')->getCollection();
      $tempProduct = $productsCollection->getFirstItem(); 
      $attributes = $tempProduct->getAttributes(); 
      foreach ($attributes as $attribute) { 
        if($attribute->getAttributeCode() == 'name')      
        {
            array_push($tempAttributes,'name');     
        }
        if($attribute->getAttributeCode() == 'sku')      
        {
            array_push($tempAttributes,'sku');
        }
        if($attribute->getAttributeCode() == 'image')      
        {
            array_push($tempAttributes,'image');     
        }
        if($attribute->getAttributeCode() == 'manufacturer')      
        {
            array_push($tempAttributes,'manufacturer');
        }   
        if($attribute->getAttributeCode() == 'price')      
        {
            array_push($tempAttributes,'price');     
        }       
        if($attribute->getAttributeCode() == 'special_price')      
        {
            array_push($tempAttributes,'special_price');
        }  
        if($attribute->getAttributeCode() == 'description')      
        {
            array_push($tempAttributes,'description');
        }  
        if($attribute->getAttributeCode() == 'short_description')      
        {
            array_push($tempAttributes,'short_description');
        } 
        if($attribute->getAttributeCode() == 'color')      
        {
            array_push($tempAttributes,'color');
        }  
        if($attribute->getAttributeCode() == 'weight')      
        {
            array_push($tempAttributes,'weight');
        }  
        if($attribute->getAttributeCode() == 'size')      
        {
            array_push($tempAttributes,'size');
        }      
      }      
      $attributes = $tempAttributes;
      $productsCollection = Mage::getModel('catalog/product')->getCollection();

      $productsCollection->addAttributeToSelect($attributes)->getSelect()->limit($limit, $offset);

      $totalProductCount = Mage::getModel('catalog/product')->getCollection()->count();

      foreach($productsCollection as $product) 
      { 
        $product = $productFormatHelper->formatProductInfo($product);  

        if($product !== null)
        {
          $products[] = $product;
        }
      }

      $this->getResponse()
        ->setBody(json_encode(array('products' => $products,'version' => $version, 'total_product_count' => $totalProductCount)))
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
