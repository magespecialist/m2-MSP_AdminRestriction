<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_AdminRestriction
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\AdminRestriction\Plugin;

use Magento\Framework\App\State;
use Magento\Framework\AppInterface;
use Magento\Framework\App\RequestInterface;
use MSP\AdminRestriction\Api\RestrictInterface;
use MSP\SecuritySuiteCommon\Api\LockDownInterface;
use MSP\SecuritySuiteCommon\Api\LogManagementInterface;
use Magento\Framework\Event\ManagerInterface as EventInterface;
use MSP\SecuritySuiteCommon\Api\UtilsInterface;

class AppInterfacePlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var State
     */
    private $state;

    /**
     * @var EventInterface
     */
    private $event;

    /**
     * @var RestrictInterface
     */
    private $restrict;

    /**
     * @var UtilsInterface
     */
    private $utils;

    /**
     * @var LockDownInterface
     */
    private $lockDown;

    public function __construct(
        RequestInterface $request,
        State $state,
        EventInterface $event,
        RestrictInterface $restrict,
        UtilsInterface $utils,
        LockDownInterface $lockDown
    ) {
        $this->request = $request;
        $this->state = $state;
        $this->restrict = $restrict;
        $this->event = $event;
        $this->utils = $utils;
        $this->lockDown = $lockDown;
    }

    public function aroundLaunch(AppInterface $subject, \Closure $proceed)
    {
        if ($this->utils->isBackendUri()) {
            if (!$this->restrict->isAllowed()) {
                $this->event->dispatch(LogManagementInterface::EVENT_ACTIVITY, [
                    'module' => 'MSP_AdminRestriction',
                    'message' => 'Unauthorized access attempt',
                ]);

                $this->state->setAreaCode('frontend');
                return $this->lockDown->doHttpLockdown(__('Unauthorized access attempt'));
            }
        }

        return $proceed();
    }
}
