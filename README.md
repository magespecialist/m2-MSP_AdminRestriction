# MSP AdminRestriction

This module permits to deny access to Magento backend from unauthorized IPs.

> Member of **MSP Security Suite**
>
> See: https://github.com/magespecialist/m2-MSP_SecuritySuiteFull

Did you lock yourself out from Magento backend? <a href="https://github.com/magespecialist/m2-MSP_AdminRestriction/new/master?readme=1#emergency-commandline-disable">click here.</a>

## Installing on Magento2:

**1. Install using composer**

From command line: 

`composer require msp/adminrestriction`

**2. Enable and configure from your Magento backend config**

<img src="https://raw.githubusercontent.com/magespecialist/m2-MSP_AdminRestriction/master/screenshots/config.png" />

## Emergency commandline disable:

If you messed up with IP list, you can disable or change authorized ip list from command-line:

**Disable filter:**
`php bin/magento msp:security:admin_restriction:ip disable`

**Authorize your IP:**
`php bin/magento msp:security:admin_restriction:ip 127.0.0.1`
