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

namespace MSP\AdminRestriction\Command;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use MSP\AdminRestriction\Api\RestrictInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestrictIp extends Command
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Manager
     */
    private $cacheManager;

    public function __construct(
        ConfigInterface $config,
        Manager $cacheManager
    ) {
        parent::__construct();
        $this->config = $config;
        $this->cacheManager = $cacheManager;
    }

    protected function configure()
    {
        $this->setName('msp:security:admin_restriction:ip');
        $this->setDescription('Set IP Admin Restriction');

        $this->addArgument('ip', InputArgument::REQUIRED, __('Authorized comma separated IP list'));

        parent::configure();
    }

    /**
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ranges = $input->getArgument('ip');

        if ($ranges == 'disable') {
            $this->config->saveConfig(
                RestrictInterface::XML_PATH_ENABLED,
                '0',
                'default',
                0
            );
        } else {
            $this->config->saveConfig(
                RestrictInterface::XML_PATH_ENABLED,
                '1',
                'default',
                0
            );

            $this->config->saveConfig(
                RestrictInterface::XML_PATH_AUTHORIZED_RANGES,
                $ranges,
                'default',
                0
            );
        }

        $this->cacheManager->flush(['config']);
    }
}
