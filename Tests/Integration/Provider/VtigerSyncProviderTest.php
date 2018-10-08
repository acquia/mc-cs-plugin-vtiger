<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://www.mautic.com
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Tests\EventListener;

use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSyncProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;

class VtigerSyncProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VtigerSyncProvider
     */
    private $integrationSyncService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationSyncService = new VtigerSyncProvider($this->dataExchange);
    }

    public function testGetName()
    {
        $this->assertEquals(VtigerCrmIntegration::NAME, $this->integrationSyncService->getName());
    }
}
