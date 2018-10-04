<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     25.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Tests\EventListener;

use MauticPlugin\MauticVtigerCrmBundle\EventListener\IntegrationSyncService;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use PHPUnit_Framework_TestCase;

class IntegrationSyncServiceTest extends PHPUnit_Framework_TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationSyncService = new IntegrationSyncService($this->dataExchange, $this->integrationHelper);
    }

    public function testGetName(): void
    {
        $this->assertSame(VtigerCrmIntegration::NAME, $this->integrationSyncService->getName());
    }
}
