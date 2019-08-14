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
* Formats product details to be sent to increasingly
*/
class Increasingly_Analytics_Helper_ProductFormatter extends Mage_Core_Helper_Abstract
{

  public function formatProductInfo($product) 
  {
    $productData = null;

    try 
    {
      $priceFormatter = Mage::helper('increasingly_analytics/PriceFormatter');
      $dateFormatter = Mage::helper('increasingly_analytics/DateFormatter');

      $productData = array(
        'product_id'      =>  $product->getId(),     
        'categories'      =>  array(),           
        'product_url'     =>  $product->getProductUrl(),    
        'product_type'  =>  $product->getTypeId(), 
        'created_at'    =>  $dateFormatter->getFormattedDate($product->getCreatedAt()),
        'updated_at'    =>  $dateFormatter->getFormattedDate($product->getUpdatedAt()) 
        );

       if($product->getResource()->getAttribute('sku'))
       {      
          $productData['product_sku'] = $product->getSku();       
       }

       if($product->getResource()->getAttribute('name'))
       {
         $productData['product_name'] = $product->getName();  
       }  

       if($product->getResource()->getAttribute('price'))
       {
         $productData['product_price'] = $priceFormatter->format($product->getPrice());  
       }  

       if($product->getResource()->getAttribute('special_price'))
       {
         $productData['special_price'] = $priceFormatter->format($product->getSpecialPrice());  
       } 
     
       if ($product->hasData('description')) 
       {
         $productData['description'] = $product->getData('description');
       }

       if($productData['description'] == null || empty($productData['description']))
       {
	  if($product->getResource()->getAttribute('description'))
	  {
	    $productData['description'] = $product->getDescription();  
	  }
       }  
 
       if ($product->hasData('short_description')) 
       {
      	  $productData['short_description'] = $product->getData('short_description');
       }

       if($productData['short_description'] == null || empty($productData['short_description']))
       {
          if($product->getResource()->getAttribute('short_description'))
          {
            $productData['short_description'] = $product->getShortDescription();  
          }  
       }

       if($product->getResource()->getAttribute('status'))
       {
         $productData['status'] = (int)$product->getStatus();  
       }

      $productDefaultImage = '';
      if($product->getResource()->getAttribute('image'))
      {
        $productDefaultImage = $product->getData('image');
        if(!empty($productDefaultImage) && $productDefaultImage !== 'no_selection')
        {          
           $productData['image_url'] =  Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
        }
        else
        {
          $productData['image_url'] = $product->getImageUrl();
        }
       }
       
       $productData['original_image_url'] = $this->buildImageUrl($product);

       if($product->getResource()->getAttribute('manufacturer'))
       {
        $manufacturer = $product->getAttributeText('manufacturer');            
        if(strlen($manufacturer) > 0 && $manufacturer != false) 
        {
          $productData['manufacturer'] = $manufacturer;
        }
       }

       if($product->getResource()->getAttribute('color'))
       {
        $color =  $product->getAttributeText('color');
        if(strlen($color) > 0 && $color != false) 
        {
         $productData['color'] = $color;
        }
       }

       if($product->getResource()->getAttribute('weight'))
       {
          $weight = $product->getWeight();
          if(strlen($weight) > 0 && $weight != false) 
          {
           $productData['weight'] = $weight;
          }
       }

      if($product->getResource()->getAttribute('size'))
      {
	 $size = $product->getAttributeText('size');
	 if(strlen($size) > 0 && $size != false) 
	 {
	   $productData['size'] = $size;
	 }
      }

      if($product->getResource()->getAttribute('visibility'))
       {
         $visibility = $product->getAttributeText('visibility');
	 if(strlen($visibility) > 0 && $visibility != false) 
	 {
	   $productData['visibility'] = $visibility;
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
         if(!empty($productDefaultImage))
         {
           if($img->getFile() != $productDefaultImage){
           	$productData['other_image_list'][] = $img->getUrl();
           }
         }
         else{
	   $productData['other_image_list'][] = $img->getUrl();
         }
       }
      } 

      }

      $inventoryDetails = $stock->getData();
      $productData['inventory_details'] = $inventoryDetails;
     
    } 
    catch(Exception $e)
    {
      Mage::log($e, null, 'Increasingly_Analytics.log');
    }

    return $productData;
  }  

  protected function buildImageUrl($product)
  {
    $store = Mage::app()->getStore();
    $url = null;   
    $helper = Mage::helper('increasingly_analytics');
    $imageVersion = $helper->getProductImageVersion($store);
    $img = $product->getData($imageVersion);
    $img = $this->isValidImage($img) ? $img : $product->getData('image');
    if ($this->isValidImage($img)) {
        // We build the image url manually in order get the correct base url
        $baseUrl = rtrim($store->getBaseUrl('media'), '/');
        $file = str_replace(DS, '/', $img);
        $file = ltrim($file, '/');
        $url = $baseUrl.'/catalog/product/'.$file;
    }
    return $url;
  }

  protected function isValidImage($image)
  {
    return (!empty($image) && $image !== 'no_selection');
  }

}


