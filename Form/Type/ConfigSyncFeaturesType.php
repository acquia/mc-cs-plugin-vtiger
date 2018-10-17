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

namespace MauticPlugin\MauticVtigerCrmBundle\Form\Type;

use MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException;
use MauticPlugin\IntegrationsBundle\Form\Type\ActivityListType;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigSyncFeaturesType extends AbstractType
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * ConfigSyncFeaturesType constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'updateOwner',
            ChoiceType::class,
            [
                'choices'     => [
                    'updateOwner' => 'mautic.plugin.vtiger.updateOwner',
                ],
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'mautic.plugin.vtiger.form.updateOwner',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
            ]
        );

        $builder->add(
            'updateDncByDate',
            ChoiceType::class,
            [
                'choices'     => [
                    'updateDncByDate' => 'mautic.integrations.update.dnc.by.date',
                ],
                'expanded'    => true,
                'multiple'    => true,
                'label'       => 'mautic.integrations.form.update.dnc.by.date.label',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
            ]
        );

        $builder->add(
            'owner',
            ChoiceType::class,
            [
                'choices'    => $this->getFormOwners(),
                'label'      => 'mautic.plugin.vtiger.form.owner',
                'label_attr' => [
                    'class' => 'control-label',
                ],
                'multiple'   => false,
                'required'   => true,
            ]
        );

        $builder->add(
            'activityEvents',
            ActivityListType::class
        );
    }

    /**
     * @return array
     */
    private function getFormOwners(): array
    {
        try {
            $owners = $this->userRepository->findBy();
        } catch (PluginNotConfiguredException $e) {
            return [];
        }
        $ownersArray = [];
        foreach ($owners as $owner) {
            $ownersArray[$owner->getId()] = (string) $owner;
        }

        return $ownersArray;
    }
}
