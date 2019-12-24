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

use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use MauticPlugin\MauticVtigerCrmBundle\Enum\SettingsKeyEnum;
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
     *
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            SettingsKeyEnum::OWNER_UPDATE_IS_ENABLED,
            ChoiceType::class,
            [
                'choices'     => [
                    'mautic.plugin.vtiger.updateOwner' => 'updateOwner',
                ],
                'choices_as_values' => true,
                'expanded'          => true,
                'multiple'          => true,
                'label'             => 'mautic.plugin.vtiger.form.updateOwner',
                'label_attr'        => ['class' => 'control-label'],
                'empty_value'       => false,
                'required'          => false,
            ]
        );

        $builder->add(
            SettingsKeyEnum::UPDATE_DNC_BY_DATE,
            ChoiceType::class,
            [
                'choices'     => [
                    'mautic.integrations.update.dnc.by.date'  => 'updateDncByDate',
                ],
                'choices_as_values' => true,
                'expanded'          => true,
                'multiple'          => true,
                'label'             => 'mautic.integrations.form.update.dnc.by.date.label',
                'label_attr'  => ['class' => 'control-label'],
                'empty_value' => false,
                'required'    => false,
            ]
        );

        $builder->add(
            SettingsKeyEnum::OWNER,
            ChoiceType::class,
            [
                'choices'           => $this->getFormOwners(),
                'choices_as_values' => true,
                'label'             => 'mautic.plugin.vtiger.form.owner',
                'label_attr'        => [
                    'class' => 'control-label',
                ],
                'multiple'   => false,
                'required'   => true,
            ]
        );

        $builder->add(
            SettingsKeyEnum::PUSH_MAUTIC_CONTACT_AS,
            ChoiceType::class,
            [
                'choices'    => [
                    'mautic.plugin.vtiger.form.push_mautic_contact_as_lead'    => SettingsKeyEnum::PUSH_MAUTIC_CONTACT_AS_LEAD ,
                    'mautic.plugin.vtiger.form.push_mautic_contact_as_contact' => SettingsKeyEnum::PUSH_MAUTIC_CONTACT_AS_CONTACT,
                ],
                'choices_as_values' => true,
                'label'             => 'mautic.plugin.vtiger.form.push_mautic_contact_as',
                'label_attr'        => [
                    'class' => 'control-label',
                ],
                'multiple' => false,
                'required' => true,
            ]
        );

        /*
        Uncomment feature it VtigerConfigProvider::getSupportedFeatures too
        Revert changes in VtigerSettingProvider::isActivitySyncEnabled and VtigerSettingProvider::getActivityEvents

        $builder->add(
            SettingsKeyEnum::ACTIVITY_EVENTS,
            ActivityListType::class
        );
        */
    }

    /**
     * @return array
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException
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
            $ownersArray[(string) $owner] = $owner->getId();
        }

        return $ownersArray;
    }
}
