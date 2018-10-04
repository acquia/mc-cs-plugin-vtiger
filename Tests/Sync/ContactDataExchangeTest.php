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

namespace MauticPlugin\MauticVtigerCrmBundle\Tests\Sync;

use MauticPlugin\MauticVtigerCrmBundle\Sync\ContactDataExchange;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use PHPUnit_Framework_TestCase;

class ContactDataExchangeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ContactRepository
     */
    private $contactRepository;

    private $settingsProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->contactRepository
            ->method('describe')
            ->willReturn($this->getDescribe());

        $this->objectDataExchane = new ContactDataExchange(
            $this->contactRepository,
            $this->settingsProvider,
            $this->leadModel,
            $this->valueNormalizer,
            $this->contactValidator
        );
    }

    public function testGetObjectSyncReport(): void
    {
    }

    public function testInsert(): void
    {
    }

    public function testDelete(): void
    {
    }

    private function getDescribe()
    {
        return [];
    }
}
