<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Mapping;

use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FieldMapping
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * FieldMapping constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    public function getLeadFields()
    {
        $salesFields = [];

        $this->contactRepository = $this->container->get('mautic.vtiger_crm.repository.contacts');

        $fields = $this->contactRepository->describe()->getFields();

        /** @var ModuleFieldInfo $fieldInfo */
        foreach ($fields as $fieldInfo) {
            $type = 'string';
            $salesFields[$fieldInfo->getName()] = [
                'type' => $type,
                'label' => $fieldInfo->getLabel(),
                'required' => $fieldInfo->isMandatory(),
                'optionLabel' => $fieldInfo->getLabel(),
            ];
        }

        asort($salesFields);

        return $salesFields;
    }

    private function initializeHackBecauseOfCircularDependency()
    {
        if (isset($this->contactRepository)) {
            return;
        }

    }
}
