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

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\IntegrationsBundle\Entity\ObjectMapping;
use Mautic\IntegrationsBundle\Sync\DAO\Mapping\UpdatedObjectMappingDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use Mautic\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use Mautic\IntegrationsBundle\Sync\Exception\ObjectDeletedException;
use Mautic\IntegrationsBundle\Sync\Helper\MappingHelper;
use Mautic\IntegrationsBundle\Sync\Logger\DebugLogger;
use Mautic\IntegrationsBundle\Sync\Notification\Handler\ContactNotificationHandler;
use Mautic\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectValueException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException;
use MauticPlugin\MauticVtigerCrmBundle\Exceptions\VtigerPluginException;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\TransformerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\ContactValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\Mapping\ModelFactory;

class ContactDataExchange extends GeneralDataExchange
{
    /**
     * @var string
     */
    public const OBJECT_NAME = 'Contacts';

    /**
     * @var string
     */
    public const OBJECT_LABEL = 'Contact';

    /**
     * @var int
     */
    private const VTIGER_API_QUERY_LIMIT = 100;

    /**
     * @var ContactRepository
     */
    private $contactRepository;

    /**
     * @var ContactValidator
     */
    private $contactValidator;

    /**
     * @var MappingHelper
     */
    private $mappingHelper;

    /**
     * @var ObjectFieldMapper
     */
    private $objectFieldMapper;

    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * @param VtigerSettingProvider $vtigerSettingProvider
     * @param ValueNormalizerInterface $valueNormalizer
     * @param ContactRepository $contactRepository
     * @param ContactValidator $contactValidator
     * @param MappingHelper $mappingHelper
     * @param ObjectFieldMapper $objectFieldMapper
     * @param ModelFactory $modelFactory
     * @param ContactNotificationHandler $notificationHandler
     */
    public function __construct(
        VtigerSettingProvider $vtigerSettingProvider,
        ValueNormalizerInterface $valueNormalizer,
        ContactRepository $contactRepository,
        ContactValidator $contactValidator,
        MappingHelper $mappingHelper,
        ObjectFieldMapper $objectFieldMapper,
        ModelFactory $modelFactory,
        ContactNotificationHandler $notificationHandler
    ) {
        parent::__construct($vtigerSettingProvider, $valueNormalizer, $notificationHandler);
        $this->contactRepository = $contactRepository;
        $this->contactValidator  = $contactValidator;
        $this->mappingHelper     = $mappingHelper;
        $this->objectFieldMapper = $objectFieldMapper;
        $this->modelFactory      = $modelFactory;
    }

    /**
     * @param \Mautic\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject
     * @param ReportDAO                                                        $syncReport
     *
     * @return ReportDAO
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \Mautic\IntegrationsBundle\Sync\Exception\ObjectNotFoundException
     * @throws \Mautic\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws VtigerPluginException
     */
    public function getObjectSyncReport(
        \Mautic\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject,
        ReportDAO $syncReport
    ): ReportDAO {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->contactRepository->describe()->getFields();

        $mappedFields = array_merge($mappedFields, [
            'isconvertedfromlead', 'leadsource', 'reference', 'source', 'contact_id', 'emailoptout', 'donotcall',
        ]);

        $deleted = [];
        $updated = $this->getReportPayload($fromDateTime, $mappedFields, self::OBJECT_NAME);

        /** @var Contact $contact */
        foreach ($updated as $key => $contact) {
            if ($contact->isConvertedFromLead()) {
                $objectDAO = new ObjectDAO(ContactDataExchange::OBJECT_NAME, $contact->getId(), $contact->getModifiedTime());
                $objectDAO->addField(
                    new FieldDAO('email', $this->valueNormalizer->normalizeForMautic(NormalizedValueDAO::EMAIL_TYPE, $contact->getEmail()))
                );
                try {
                    // beware this method also saves it :-(
                    $foundMapping = $this->mappingHelper->findMauticObject(
                        $this->objectFieldMapper->getObjectsMappingManual(),
                        'lead',
                        $objectDAO
                    );

                    // This lead has to be marked as deleted
                    if ($foundMapping) {
                        DebugLogger::log(VtigerCrmIntegration::NAME, 'Marking Lead #' . $contact->getId() . ' as deleted');
                        $objectChangeDAO = new ObjectChangeDAO(
                            VtigerCrmIntegration::NAME,
                            LeadDataExchange::OBJECT_NAME,
                            $contact->getId(),
                            $foundMapping->getObject(),
                            $foundMapping->getObjectId()
                        );

                        $deleted[] = $objectChangeDAO;
                    }
                } catch (ObjectDeletedException $e) {
                    // We have had a Lead but it is deleted already
                }
            }
        }

        $this->mappingHelper->markAsDeleted($deleted);

        /** @var Contact $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new \DateTimeImmutable($object->getModifiedTime()->format('r')));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {
                // The 'reference' key doesn't seem to exist every time. Based on readme it's used only for owners. Skip it.
                if (!isset($objectFields[$field])) {
                    continue;
                }

                try {
                    // Normalize the value from the API to what Mautic needs
                    $normalizedValue = $this->valueNormalizer->normalizeForMauticTyped($objectFields[$field], $value);
                    $reportFieldDAO = new FieldDAO($field, $normalizedValue);

                    $objectDAO->addField($reportFieldDAO);

                    $objectDAO->addField(
                        new FieldDAO(
                            'mautic_internal_dnc_email',
                            $this->valueNormalizer->normalizeForMautic(TransformerInterface::DNC_TYPE, $object->getEmailOptout())
                        )
                    );
                    $objectDAO->addField(
                        new FieldDAO(
                            'mautic_internal_dnc_sms',
                            $this->valueNormalizer->normalizeForMautic(TransformerInterface::DNC_TYPE, $object->getEmailOptout())
                        )
                    );
                } catch (InvalidQueryArgumentException $e) {
                    DebugLogger::log(
                        VtigerCrmIntegration::NAME,
                        sprintf('%s for %s %s', $e->getMessage(), self::OBJECT_NAME, $object->getId())
                    );
                    printf("%s for %s %s\n", $e->getIncomingMessage(), self::OBJECT_NAME, $object->getId());
                } catch (InvalidObjectValueException $e) {
                    DebugLogger::log(VtigerCrmIntegration::NAME, $e->getMessage());
                    continue(2);
                }
            }

            $syncReport->addObject($objectDAO);
        }

        return $syncReport;
    }

    /**
     * @param array             $ids
     * @param ObjectChangeDAO[] $objects
     *
     * @return UpdatedObjectMappingDAO[]
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function update(array $ids, array $objects): array
    {
        return $this->updateInternal($ids, $objects, self::OBJECT_NAME);
    }

    /**
     * @param ObjectChangeDAO[] $objects
     *
     * @return array|ObjectMapping[]
     * @throws VtigerPluginException
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\AccessDeniedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\DatabaseQueryException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidObjectException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidQueryArgumentException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\InvalidRequestException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     */
    public function insert(array $objects): array
    {
        if (!$this->vtigerSettingProvider->shouldBeMauticContactPushedAsContact()) {
            return [];
        }

        return $this->insertInternal($objects, self::OBJECT_NAME);
    }


    protected function getModel(array $objectData): Contact
    {
        $objectFields = $this->contactRepository->describe()->getFields();
        $normalizedFields = [];

        /**
         * @var string   $key
         * @var FieldDAO $fieldDAO
         */
        foreach ($objectData as $key => $fieldDAO) {
            $normalizedFields[$key] = $this->valueNormalizer->normalizeForVtiger($objectFields[$fieldDAO->getName()], $fieldDAO);
        }

        return $this->modelFactory->createContact($normalizedFields);
    }

    /**
     * @return ContactValidator
     */
    protected function getValidator(): ContactValidator
    {
        return $this->contactValidator;
    }

    /**
     * @return ContactRepository
     */
    protected function getRepository(): ContactRepository
    {
        return $this->contactRepository;
    }

    /**
     * @return int
     */
    protected function getVtigerApiQueryLimit(): int
    {
        return self::VTIGER_API_QUERY_LIMIT;
    }
}
