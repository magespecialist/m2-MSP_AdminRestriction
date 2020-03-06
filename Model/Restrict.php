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

namespace MSP\AdminRestriction\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use MSP\AdminRestriction\Api\RestrictInterface;
use Psr\Log\LoggerInterface;

class Restrict implements RestrictInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        LoggerInterface $logger,
        RemoteAddress $remoteAddress,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * Return true if IP is in range
     * @param string $ipAddress
     * @param string $range
     * @return bool
     */
    public function isIpInRange($ipAddress, $range)
    {
        if (strpos($range, '/') === false) {
            $range .= '/32';
        }

        list($range, $netmask) = explode('/', $range, 2);
        $rangeDecimal = ip2long($range);
        $ipDecimal = ip2long($ipAddress);
        $wildcardDecimal = pow(2, (32 - $netmask)) - 1;
        $netmaskDecimal = ~$wildcardDecimal;

        return (bool)(($ipDecimal & $netmaskDecimal) == ($rangeDecimal & $netmaskDecimal));
    }

    /**
     * Return true if IP is matched in a range list
     * @param string $ipAddress
     * @param array $ranges
     * @return bool
     */
    private function isMatchingIp($ipAddress, array $ranges)
    {
        foreach ($ranges as $range) {
            if ($this->isIpv6($ipAddress) && in_array($ipAddress, $this->getAllowedRanges(), true)) {
                return true;
            }
            if ($this->isIpv4($ipAddress) && $this->isIpInRange($ipAddress, $range)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return a list of allowed IPs
     * @return array
     */
    public function getAllowedRanges()
    {
        $ranges = $this->scopeConfig->getValue(RestrictInterface::XML_PATH_AUTHORIZED_RANGES);
        return preg_split('/\s*[,;]+\s*/', $ranges);
    }

    /**
     * Return true if IP restriction is enabled
     */
    public function isEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(RestrictInterface::XML_PATH_ENABLED);
    }

    /**
     * Return true if IP loggign is enabled.
     */
    public function isLoggingEnabled(): bool
    {
        return (bool)$this->scopeConfig->getValue(RestrictInterface::XML_PATH_ENABLE_LOG);
    }

    /**
     * Return true if current user is allowed to access backend
     */
    public function isAllowed(): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        $ipAddress = $this->remoteAddress->getRemoteAddress();

        if ($this->isLoggingEnabled()) {
            $this->logger->debug(
                sprintf('MSP/AdminRestriction: IP address %s is trying to access the Magento admin', $ipAddress)
            );
        }

        $allowedRanges = $this->getAllowedRanges();

        if (!empty($allowedRanges)) {
            return $this->isMatchingIp($ipAddress, $allowedRanges);
        }

        return true;
    }

    private function isIpv6(string $ipAddress): bool
    {
        return preg_match('/([a-f0-9:]+:+)+[a-f0-9]+/', $ipAddress);
    }

    private function isIpv4(string $ipAddress): bool
    {
        return preg_match('/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/', $ipAddress);
    }
}
