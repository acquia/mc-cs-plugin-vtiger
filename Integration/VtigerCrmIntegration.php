<?php

/*
 * @copyright   2018 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Integration;

use Mautic\IntegrationsBundle\Integration\AuthenticationIntegration;
use Mautic\IntegrationsBundle\Integration\BasicIntegration;
use Mautic\IntegrationsBundle\Integration\DispatcherIntegration;
use Mautic\IntegrationsBundle\Integration\EncryptionIntegration;
use Mautic\IntegrationsBundle\Integration\Interfaces\AuthenticationInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\BasicInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\DispatcherInterface;
use Mautic\IntegrationsBundle\Integration\Interfaces\EncryptionInterface;
use Mautic\LeadBundle\Model\FieldModel;

/**
 * Class VtigerCrmIntegration
 *
 * @package MauticPlugin\MauticVtigerCrmBundle\Integration
 */
class VtigerCrmIntegration extends BasicIntegration implements
    BasicInterface,
    AuthenticationInterface,
    DispatcherInterface,
    EncryptionInterface
{
    use AuthenticationIntegration;
    use DispatcherIntegration;
    use EncryptionIntegration;

    /**
     * @var bool
     */
    protected $coreIntegration = false;

    /**
     * @var FieldModel
     */
    protected $fieldModel;

    /**
     * SlooceIntegration constructor.
     *
     * @param FieldModel $fieldModel
     */
    public function __construct(FieldModel $fieldModel)
    {
        $this->fieldModel = $fieldModel;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string { return 'VtigerCrm'; }

    /** @inheritdoc */
    public function getIcon() { return 'plugins/MauticVtigerCrmBundle/Assets/img/vtiger_crm.png'; }

    /** @inheritdoc */
    public function getRequiredKeyFields(): array
    {
        return [
            'url'       => 'mautic.vtiger.form.url',
            'username'  => 'mautic.vtiger.form.username',
            'accessKey' => 'mautic.vtiger.form.password',
        ];
    }

    /** @inheritdoc */
    public function getClientIdKey(): string { return 'username'; }

    /** @inheritdoc */
    public function getClientSecretKey(): string { return 'accessKey'; }

    /** @inheritdoc */
    public function getAuthTokenKey(): string { return 'sessionName'; }

    /** @inheritdoc */
    public function getApiUrl(): string { return sprintf('%s/webservice.php', $this->keys['url']); }
}
