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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Helper;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Helper\Cookie
     */
    protected $_object;

    /**
     * @var \Magento\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Core\Helper\Context
     */
    protected $_context;

    public function testIsUserNotAllowSaveCookie()
    {
        $this->_initMock()->_getCookieStub(array(1 => 1));
        $this->assertFalse($this->_object->isUserNotAllowSaveCookie());
        $request = $this->getMock('\Magento\App\Request\Http', array('getCookie'), array(), '', false, false);
        $request->expects($this->any())->method('getCookie')->will($this->returnValue(json_encode(array())));
        $context = $this->getMock('Magento\App\Helper\Context', array('getRequest'), array(), '', false, false);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $this->_object = new \Magento\Core\Helper\Cookie(
            $context,
            $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false, false),
            array(
                'current_store' => $this->_getStoreStub(),
                'website' => $this->_getWebsiteStub(),
            )
        );
        $this->assertTrue($this->_object->isUserNotAllowSaveCookie());
    }

    public function testGetAcceptedSaveCookiesWebsiteIds()
    {
        $this->_initMock()->_getCookieStub(array(1 => 1));
        $this->assertEquals(
            $this->_object->getAcceptedSaveCookiesWebsiteIds(),
            json_encode(array(1 => 1))
        );
    }

    public function testGetCookieRestrictionLifetime()
    {
        $this->_request = $this->getMock('\Magento\App\Request\Http', array('getCookie'),
            array(), '', false, false);
        $this->_context = $this->getMock('Magento\App\Helper\Context', array('getRequest'), array(), '', false, false);
        $this->_context->expects($this->once())->method('getRequest')->will($this->returnValue($this->_request));
        $storeStub = $this->_getStoreStub();
        $storeStub->expects($this->once())
            ->method('getConfig')
            ->will($this->returnCallback('Magento\\Core\\Helper\\CookieTest::getConfigMethodStub'))
            ->with($this->equalTo('web/cookie/cookie_restriction_lifetime'));
        $this->_object = new \Magento\Core\Helper\Cookie(
            $this->_context,
            $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false, false),
            array(
                'current_store' => $storeStub,
                'website' => $this->_getWebsiteStub()
            )
        );
        $this->assertEquals($this->_object->getCookieRestrictionLifetime(), 60 * 60 * 24 * 365);
    }

    protected function _initMock()
    {
        $this->_request = $this->getMock('\Magento\App\Request\Http', array('getCookie'),
            array(), '', false, false);
        $this->_context = $this->getMock('Magento\App\Helper\Context', array('getRequest'), array(), '', false, false);
        $this->_context->expects($this->once())->method('getRequest')->will($this->returnValue($this->_request));
        $this->_object = new \Magento\Core\Helper\Cookie(
            $this->_context,
            $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false, false),
            array(
                'current_store' => $this->_getStoreStub(),
                'website' => $this->_getWebsiteStub(),
            )
        );
        return $this;
    }

    /**
     * Create store stub
     * @return \Magento\Core\Model\Store
     */
    protected function _getStoreStub()
    {
        $store = $this->getMock('Magento\Core\Model\Store', array(), array(), '', false);

        $store->expects($this->any())
            ->method('getConfig')
            ->will($this->returnCallback('Magento\\Core\\Helper\\CookieTest::getConfigMethodStub'));

        return $store;
    }

    /**
     * Create cookie model stub
     * @param array $cookieString
     * @return \Magento\Stdlib\Cookie
     */
    protected function _getCookieStub($cookieString = array())
    {
        $this->_request->expects($this->any())
            ->method('getCookie')
            ->will($this->returnValue(json_encode($cookieString)));
    }

    /**
     * Create Website Stub
     * @return \Magento\Core\Model\Website
     */
    protected function _getWebsiteStub()
    {
        $websiteMock = $this->getMock('Magento\Core\Model\Website', array(), array(), '', false);

        $websiteMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $websiteMock;
    }

    /**
     * Mock get config method
     * @static
     * @param string $hashName
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getConfigMethodStub($hashName)
    {

        $defaultConfig = array(
            'web/cookie/cookie_restriction' => 1,
            'web/cookie/cookie_restriction_lifetime' => 60 * 60 * 24 * 365,
        );

        if (array_key_exists($hashName, $defaultConfig)) {
            return $defaultConfig[$hashName];
        }

        throw new \InvalidArgumentException('Unknow id = ' . $hashName);
    }
}
