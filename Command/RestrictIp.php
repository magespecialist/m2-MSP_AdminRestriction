<?php
namespace MSP\AdminRestriction\Command;

use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestrictIp extends Command
{
    protected $configInterface;
    protected $cacheManager;

    public function __construct(
        ConfigInterface $configInterface,
        Manager $cacheManager
    ) {
        $this->configInterface = $configInterface;
        $this->cacheManager = $cacheManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('msp:security:admin_restriction:ip');
        $this->setDescription('Set IP Admin Restriction');

        $this->addArgument('ip', InputArgument::REQUIRED, __('Authorized comma separated IP list'));

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ranges = $input->getArgument('ip');

        if ($ranges == 'disable') {
            $this->configInterface->saveConfig(
                'msp_securitysuite/adminrestriction/enabled',
                '0',
                'default',
                0
            );
        } else {
            $this->configInterface->saveConfig(
                'msp_securitysuite/adminrestriction/enabled',
                '1',
                'default',
                0
            );

            $this->configInterface->saveConfig(
                'msp_adminrestriction/adminrestriction/authorized_ranges',
                $ranges,
                'default',
                0
            );
        }

        $this->cacheManager->flush(['config']);
    }
}
