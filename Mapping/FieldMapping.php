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
     * @param ContactRepository $contactRepository
     */
    /*
    public function __construct(
        ContactRepository $contactRepository
    ) {
        $this->contactRepository = $contactRepository;
    }
    */

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getLeadFields()
    {
        $this->initializeHackBecauseOfCircularDependency();

        $salesFields = [];

        $sfObject = 'Lead';

        if (!isset($salesFields[$sfObject])) {
            $fields = $this->contactRepository->describe();
            dump($fields);exit;
            if (!empty($fields['fields'])) {
                foreach ($fields['fields'] as $fieldInfo) {
                    $type = 'string';
                    $salesFields[$sfObject][$fieldInfo['name'].'__'.$sfObject] = [
                        'type'        => $type,
                        'label'       => $sfObject.'-'.$fieldInfo['label'],
                        'required'    => false,
                        'group'       => $sfObject,
                        'optionLabel' => $fieldInfo['label'],
                    ];
                }
            }
        }

        asort($salesFields[$sfObject]);

        return $salesFields;
    }

    private function initializeHackBecauseOfCircularDependency()
    {
        if (isset($this->contactRepository)) {
            return;
        }

        $this->contactRepository = $this->container->get('mautic.vtiger_crm.repository.contacts');
    }
}
