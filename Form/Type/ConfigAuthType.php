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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigAuthType extends AbstractType
{
    use NotBlankIfPublishedConstraintTrait;

    /**
     * @param FormBuilderInterface $formBuilder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $options): void
    {
        $formBuilder->add(
            'url',
            UrlType::class,
            [
                'label'      => 'mautic.vtiger.form.url',
                'label_attr' => ['class' => 'control-label'],
                'required'   => true,
                'attr'       => [
                    'class' => 'form-control',
                ],
                'constraints' => [$this->getNotBlankConstraint()],
            ]
        );

        $formBuilder->add(
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

        $formBuilder->add(
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
            ]
        );
    }
}
