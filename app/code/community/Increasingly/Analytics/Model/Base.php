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
class Increasingly_Analytics_Model_Base extends Mage_Core_Model_Abstract
{

  /**
   * Log file for plugin related messages.
   */
  const LOG_FILE_NAME = 'increasingly_analytics.log';

  /**
   * Returns a protected/private property value by invoking it's public getter.
   *
   * The getter names are assumed to be the property name in camel case with preceding word "get".
   *
   * @param string $name the property name.
   * @return mixed the property value.
   * @throws IncreasinglyException if public getter does not exist.
   */
  public function __get($name)
  {
    $getter = 'get'.str_replace('_', '', $name);
    if (method_exists($this, $getter)) {
        return $this->{$getter}();
    }
    throw new IncreasinglyException(sprintf('Property `%s.%s` is not defined.', get_class($this), $name));
  }
}
