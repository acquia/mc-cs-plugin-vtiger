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

use Mautic\LeadBundle\Model\FieldModel;
use MauticPlugin\MauticIntegrationsBundle\Integration\AuthenticationIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\BasicIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\DispatcherIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\EncryptionIntegration;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\AuthenticationInterface;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\BasicInterface;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\DispatcherInterface;
use MauticPlugin\MauticIntegrationsBundle\Integration\Interfaces\EncryptionInterface;

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
     * @var FieldModel
     */
    protected $fieldModel;

    /**
     * VtigerCrmIntegration constructor.
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
