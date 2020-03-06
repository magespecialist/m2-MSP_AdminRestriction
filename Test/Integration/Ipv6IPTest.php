<?php declare(strict_types=1);

namespace MSP\AdminRestriction\Test\Integration;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\TestFramework\Helper\Bootstrap;
use MSP\AdminRestriction\Api\RestrictInterface;
use MSP\AdminRestriction\Model\Restrict;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class Ipv6IPTest extends TestCase
{
    /**
     * @magentoConfigFixture default/msp_securitysuite_adminrestriction/general/enabled 1
     * @magentoConfigFixture default/msp_securitysuite_adminrestriction/general/authorized_ranges 123.123.123.123, 2a02:a445:999a:0:d123:9024:136z:7f4f
     */
    public function testDifferentIpAddresses() : void
    {
        $allowedIpv6 = '2a02:a445:999a:0:d123:9024:136z:7f4f';
        $disallowedIpv6 = '2a02:a445:999a:0:d123:9024:136z:123a';
        $allowedIpv4 = '123.123.123.123';
        $disalloweddIpv4 = '123.123.123.127';

        /** @var MockObject|RemoteAddress $remoteAccessMock */
        $remoteAccessMock = $this->getMockBuilder(RemoteAddress::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRemoteAddress'])
            ->getMock();
        $remoteAccessMock->method('getRemoteAddress')->willReturnOnConsecutiveCalls(
            $allowedIpv4,
            $disalloweddIpv4,
            $allowedIpv6,
            $disallowedIpv6
        );

        /** @var MockObject|Restrict $restricktMock */
        $restricktMock = $this->getMockBuilder(Restrict::class)
            ->setConstructorArgs(
                [
                    Bootstrap::getObjectManager()->get(LoggerInterface::class),
                    $remoteAccessMock,
                    Bootstrap::getObjectManager()->get(ScopeConfigInterface::class)
                ]
            )
            ->setMethods(null)
            ->getMock();

        // Ipv4 address, allowed
        $this->assertTrue($restricktMock->isAllowed());
        // Ipv4 address, disallowed, not in list
        $this->assertFalse($restricktMock->isAllowed());
        // Ipv6 address, allowed
        $this->assertTrue($restricktMock->isAllowed());
        // Ipv6 address, disallowed, not in list
        $this->assertFalse($restricktMock->isAllowed());
    }
}
