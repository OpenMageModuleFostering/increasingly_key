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
class Increasingly_Analytics_Model_Mysql4_Bundle extends Mage_Core_Model_Mysql4_Abstract
{
  /**
  increasingly_analytics_bundle table initialization
  *
  *
  */
  public function _construct()
  {       
    $this->_init('increasingly_analytics/bundle','id');
  }
}

?>
