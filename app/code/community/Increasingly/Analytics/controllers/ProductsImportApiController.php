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

class Increasingly_Analytics_ProductsImportApiController extends Mage_Core_Controller_Front_Action 
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

      $limit = $this->getRequest()->getParam('limit', 200);
      $offset = $this->getRequest()->getParam('offset', 1);
      
      $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
      $productsCollection = Mage::getModel('catalog/product')->getCollection();
      $productsCollection->addAttributeToSelect('*')->getSelect()->limit($limit, $offset);
      $totalProductCount = Mage::getModel('catalog/product')->getCollection()->count();

      foreach($productsCollection as $product) 
      {
	try
        {   
           if($product !== null)
           {
              $productData = array(
              'product_id'      =>  $product->getId(),      
              'categories'      =>  array(),                   
              'product_url'     =>  $product->getProductUrl(),     
              'product_type'  =>  $product->getTypeId(), 
              'created_at'    =>  $product->getCreatedAt(),
              'updated_at'    =>  $product->getUpdatedAt() 
              );

	   foreach ($attributes as $attribute)
           {
	      try
	      {
             	$attributeCode = $attribute->getAttributecode();
		$attributeValue = null;

                if($product->getResource()->getAttribute($attribute->getAttributecode()))
       		{ 
             	  $attributeValue = $product->getData($attribute->getAttributecode());                               

                  if($attributeValue == null || empty($attributeValue))
                  {
                    $attributeText = $product->getAttributeText($attribute->getAttributecode());
                    if($attributeText != null && !empty($attributeText))
                    {
                      $attributeValue = $attributeText;
		    }
                  }
               }

             	if($attributeCode == 'image' && !empty($attributeValue) && $attributeValue !== 'no_selection')
             	{
              	  $productData[$attributeCode] =  Mage::getModel('catalog/product_media_config')->getMediaUrl($attributeValue);
	      	  $productData['image_url'] = $product->getImageUrl();
             	}
             	else
             	{                  
              	  $productData[$attribute->getAttributecode()] = $attributeValue;
             	}
	      }
              catch(Exception $e)
	      {
		Mage::log($e->getMessage(), null, 'Increasingly_Analytics.log');
              }
           }

	   if($productData['product_type'] == "configurable") 
      	   {
             $configurableProducts = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($productData['product_id']);
             $configurable_items = array();

	     foreach($configurableProducts as $key=>$configurableProductList) 
	     {
	  	foreach($configurableProductList as $itemValue) 
	  	{
	    	   $configurable_items[] = $itemValue;
	  	}
	     }

	     $productData['associated_products'] = $configurable_items;
	   }

      	  if($productData['product_type'] == "grouped") 
          {
            $groupedProducts = Mage::getModel('catalog/product_type_grouped')->getChildrenIds($productData['product_id']);
            $grouped_items = array();

	    foreach($groupedProducts as $key=>$groupedProductList) 
	    {    		 
		foreach($groupedProductList as $itemValue) 
		{
		  $grouped_items[] = $itemValue;
		}
	    }
	    $productData['associated_products'] = $grouped_items;
          }

          if($productData['product_type'] == "bundle")
          {
	     $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection(
		  $product->getTypeInstance(true)->getOptionsIds($product), $product);

             $bundled_items = array();

             foreach($selectionCollection as $option) 
             {
              $bundled_items[] = $option->getId();
             }

             $productData['associated_products'] = $bundled_items;
           } 

      	  $relatedProducts = $product->getRelatedProductIds();
      	  if(count($relatedProducts) > 0)
      	  {
      	    $productData['related_products'] = $relatedProducts;
      	  }

      	  $upSellProducts = $product->getUpSellProductIds();
      	  if(count($upSellProducts) > 0)
      	  {
            $productData['up_sell_products'] = $upSellProducts;
     	  }

      	  $crossSellProducts = $product->getCrossSellProductIds();
      	  if(count($crossSellProducts) > 0)
      	  {
       	    $productData['cross_sell_products'] = $crossSellProducts;
          } 

	  // get stock info
	  $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
	  $productData['qty'] = (int)$stock->getQty();

	  $categories = $product->getCategoryCollection()
	      ->addAttributeToSelect('id')
	      ->addAttributeToSelect('name');

	  foreach($categories as $category) 
	  {
	     $categoryInfo = array();
	     $categoryInfo['id'] = $category->getId();
	     $categoryInfo['name'] = $category->getName();
	     $productData['categories'][] = $categoryInfo;
	  }

    	  if($product->getResource()->getAttribute('media_gallery'))
   	  {
     	     $otherImages = $product->getMediaGalleryImages();

      	     if($otherImages == '' || count($otherImages) == 0)
      	     {
        	$productImage_Data = Mage::getModel('catalog/product')->load($product->getId());
        	$otherImages = $productImage_Data->getMediaGalleryImages();
      	     }            

     	     if(count($otherImages) > 1)
             {
       		foreach($otherImages as $img)
       		{         	   
           	  $productData['other_image_list'][] = $img->getUrl();
         	}
       	     }
      	  }

          $products[] = $productData;

         }

       }
       catch(Exception $e)
       {
	  Mage::log($e->getMessage(), null, 'Increasingly_Analytics.log');
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
