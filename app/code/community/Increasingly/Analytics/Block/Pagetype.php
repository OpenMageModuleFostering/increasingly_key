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
 * @author    Increasingly Solutions Ltd <magento@Increasingly.com>
 * @copyright Copyright (c) 2013-2015 Increasingly Solutions Ltd (http://www.increasingly.co)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Increasingly meta block.
 * Used to render meta tag elements to to the HTML document
 */
class Increasingly_Analytics_Block_Pagetype extends Mage_Core_Block_Template
{
  /**
   * Render meta tags if the module is enabled for the current store.
   *
   * @return string
   */
  protected function _toHtml()
  {
    if (!Mage::helper('increasingly_analytics')->isEnabled()) {
      return '';
    }
    return parent::_toHtml();
  }


  /**
   * Returns the current page type of the Increasingly extension.
   *
   * @return string the page type
   */
  public function getCurrentPageType()
  {
    $pageIdentifier = Mage::app()->getFrontController()->getAction()->getFullActionName(); 
    return $pageIdentifier;
  }

  /**
   * Returns the current API key of the Increasingly extension.
   *
   * @return string the API key for the current store.
   */
  public function getAPIKey()
  {
    return substr(
      Mage::app()->getStore()->getConfig('general/locale/code'), 0, 2);
  }

  /**
   * Returns the current Form key of the user.
   *
   * @return string the Form key for adding the product bundles to the cart.
   */
  public function getFormKey()
  {
    $request = $this->getRequest();
    $formKey = Mage::getSingleton('core/session')->getFormKey();
    return $formKey;
  }

  public function getStoreDetails(){
    $store_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
    return $store_url;
  }
}