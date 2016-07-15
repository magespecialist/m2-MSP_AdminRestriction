<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_AdminRestriction
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\AdminRestriction\Observer;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use MSP\AdminRestriction\Api\RestrictInterface;
use Magento\Backend\App\Action;

class ControllerActionPredispatchObserver implements ObserverInterface
{
    protected $restrictInterface;
    protected $response;
    protected $actionFlag;

    public function __construct(
        RestrictInterface $restrictInterface,
        Response $response,
        ActionFlag $actionFlag
    ) {
        $this->restrictInterface = $restrictInterface;
        $this->response = $response;
        $this->actionFlag = $actionFlag;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function execute(Observer $observer)
    {
        if (!$this->restrictInterface->isAllowed()) {
            $this->response->setHttpResponseCode(403);
            $this->response->setBody('<h1>Forbidden</h1>');
            $this->response->sendResponse();
            $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        }
    }
    // @codingStandardsIgnoreEnd
}
