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

class Restrict implements RestrictInterface
{
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        RemoteAddress $remoteAddress,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->scopeConfig = $scopeConfig;
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

        return (bool) (($ipDecimal & $netmaskDecimal ) == ($rangeDecimal & $netmaskDecimal));
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
            if ($this->isIpInRange($ipAddress, $range)) {
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
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->scopeConfig->getValue(RestrictInterface::XML_PATH_ENABLED);
    }

    /**
     * Return true if current user is allowed to access backend
     * @return bool
     */
    public function isAllowed()
    {
        if (!$this->isEnabled()) {
            return true;
        }

        $ipAddress = $this->remoteAddress->getRemoteAddress();

        $allowedRanges = $this->getAllowedRanges();
        
        if (!empty($allowedRanges)) {
            return $this->isMatchingIp($ipAddress, $allowedRanges);
        }

        return true;
    }
}
