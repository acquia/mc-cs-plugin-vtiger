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

use MauticPlugin\IntegrationsBundle\Form\Type\NotBlankIfPublishedConstraintTrait;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerConfigProvider;
use MauticPlugin\MauticVtigerCrmBundle\Validator\Constraints\Connection as ConnectionConstraint;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Connection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigAuthType extends AbstractType
{
    use NotBlankIfPublishedConstraintTrait;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $accessKey = null;

        /** @var VtigerConfigProvider $configProvider */
        $configProvider = $options['integration'];
        if ($configProvider->getIntegrationConfiguration() && $configProvider->getIntegrationConfiguration()->getApiKeys()) {
            $accessKey = $configProvider->getIntegrationConfiguration()->getApiKeys()['accessKey'] ?? null;
        }

        $builder->add(
            'url',
            UrlType::class,
            [
                'label'      => 'mautic.vtiger.form.url',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [
                    $this->getNotBlankConstraint(),
                    new ConnectionConstraint($this->connection)
                ],
            ]
        );

        $builder->add(
            'username',
            TextType::class,
            [
                'label'      => 'mautic.vtiger.form.username',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [$this->getNotBlankConstraint()],
            ]
        );

        $builder->add(
            'accessKey',
            PasswordType::class,
            [
                'label'      => 'mautic.vtiger.form.password',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [$this->getNotBlankConstraint()],
                'empty_data'  => $accessKey,
            ]
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'integration' => null,
        ]);
    }
}
