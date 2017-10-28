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

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\State;
use Magento\Framework\AppInterface;
use Magento\Framework\App\RequestInterface;
use MSP\AdminRestriction\Api\RestrictInterface;
use MSP\SecuritySuiteCommon\Api\LockDownInterface;
use MSP\SecuritySuiteCommon\Api\AlertInterface;

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
     * @var RestrictInterface
     */
    private $restrict;

    /**
     * @var LockDownInterface
     */
    private $lockDown;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var AlertInterface
     */
    private $securitySuite;

    public function __construct(
        RequestInterface $request,
        State $state,
        RestrictInterface $restrict,
        DeploymentConfig $deploymentConfig,
        AlertInterface $securitySuite,
        LockDownInterface $lockDown
    ) {
        $this->request = $request;
        $this->state = $state;
        $this->restrict = $restrict;
        $this->lockDown = $lockDown;
        $this->deploymentConfig = $deploymentConfig;
        $this->securitySuite = $securitySuite;
    }

    /**
     * Return true if $uri is a backend URI
     * @param string $uri
     * @return bool
     */
    private function isBackendUri($uri = null)
    {
        $uri = $this->sanitizeUri($uri);

        $backendConfigData = $this->deploymentConfig->getConfigData('backend');
        $backendPath = $backendConfigData['frontName'];

        // @codingStandardsIgnoreStart
        $uri = parse_url($uri, PHP_URL_PATH);
        // @codingStandardsIgnoreEnd

        return (strpos($uri, "/$backendPath/") === 0) || preg_match("|/$backendPath$|", $uri);
    }

    /**
     * Get sanitized URI
     * @param string $uri
     * @return string
     */
    private function sanitizeUri($uri = null)
    {
        if ($uri === null) {
            $uri = $this->request->getRequestUri();
        }

        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        $uri = preg_replace('|/+|', '/', $uri);
        $uri = preg_replace('|^/.+?\.php|', '', $uri);

        return $uri;
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public function aroundLaunch(AppInterface $subject, \Closure $proceed)
    {
        if ($this->isBackendUri()) {
            if (!$this->restrict->isAllowed()) {
                $this->securitySuite->event(
                    'MSP_AdminRestriction',
                    'Unauthorized access attempt',
                    AlertInterface::LEVEL_WARNING
                );

                $this->state->setAreaCode('frontend');
                return $this->lockDown->doHttpLockdown(__('Unauthorized access attempt'));
            }
        }

        return $proceed();
    }
}
