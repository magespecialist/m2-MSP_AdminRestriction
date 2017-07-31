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

namespace MSP\AdminRestriction\Api;

interface RestrictInterface
{
    const XML_PATH_ENABLED = 'msp_securitysuite_adminrestriction/general/enabled';
    const XML_PATH_AUTHORIZED_RANGES = 'msp_securitysuite_adminrestriction/general/authorized_ranges';

    /**
     * Return true if current user is allowed to access backend
     * @return bool
     */
    public function isAllowed();

    /**
     * Return a list of denied IPs
     * @return array
     */
    public function getAllowedRanges();

    /**
     * Return true if IP restriction is enabled
     * @return bool
     */
    public function getEnabled();
}
