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
* Returns error log details to increasingly on API call
*/

class Increasingly_Analytics_ImportLogsController extends Mage_Core_Controller_Front_Action 
{
 
 public function getLogsAction() 
  {
    try 
    {
        $errorLines = "";
        
        /* exception log */
	    $exceptionLogFilePath = Mage::getBaseDir('log')."/exception.log";
  
       if(file_exists($exceptionLogFilePath))
	   {  
         $errorLines = $errorLines.file_get_contents($exceptionLogFilePath);
	   }
   
      /* system log */
	  $systemLogFilePath = Mage::getBaseDir('log')."/system.log";
   
       if(file_exists($systemLogFilePath))
	   {   
		 $errorLines = $errorLines.file_get_contents($systemLogFilePath);
	   } 

	 /* increasingly log */
	 $increasinglyLogFilePath = Mage::getBaseDir('log')."/Increasingly_Analytics.log";
   
	  if(file_exists($increasinglyLogFilePath))
	  { 
        $errorLines = $errorLines.file_get_contents($increasinglyLogFilePath);
      }

       $this->getResponse()
      ->setBody($errorLines)
      ->setHttpResponseCode(200)
      ->setHeader('Content-type', 'application/text', true);
      
    }
    catch(Exception $e) {

    Mage::log($e->getMessage(), null, 'Increasingly_Analytics.log');

    $this->getResponse()
    ->setBody(json_encode(array('status' => 'error', 'message' => $e->getMessage(), 'version' => $version)))
    ->setHttpResponseCode(500)
    ->setHeader('Content-type', 'application/json', true);
    }
}

}
