<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     7.9.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Sync;

use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\IntegrationsBundle\Entity\ObjectMapping;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\ObjectChangeDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ObjectDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Report\ReportDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\IntegrationsBundle\Sync\Helper\MappingHelper;
use MauticPlugin\IntegrationsBundle\Sync\Logger\DebugLogger;
use MauticPlugin\IntegrationsBundle\Sync\ValueNormalizer\ValueNormalizerInterface;
use MauticPlugin\MauticVtigerCrmBundle\Integration\VtigerCrmIntegration;
use MauticPlugin\MauticVtigerCrmBundle\Integration\Provider\VtigerSettingProvider;
use MauticPlugin\MauticVtigerCrmBundle\Mapping\ObjectFieldMapper;
use MauticPlugin\MauticVtigerCrmBundle\Sync\Helpers\DataExchangeOperationsTrait;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Contact;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\ContactValidator;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Repository\ContactRepository;

/**
 * Class ContactDataExchange
 * @package MauticPlugin\MauticVtigerCrmBundle\Sync
 */
final class ContactDataExchange implements ObjectSyncDataExchangeInterface
{
    use DataExchangeOperationsTrait {
        insert as private internalInsert;
    }

    const OBJECT_NAME = 'Contacts';

    /** @var ContactRepository */
    private $objectRepository;

    /** @var ValueNormalizerInterface */
    private $valueNormalizer;

    /** @var LeadModel */
    private $mauticModel;

    /** @var VtigerSettingProvider */
    private $settings;

    /** @var ContactValidator */
    private $objectValidator;

    /** @var MappingHelper */
    private $mappingHelper;

    /** @var ObjectFieldMapper */
    private $objectFieldMapper;

    /** @var int */
    const VTIGER_API_QUERY_LIMIT = 100;

    /**
     * ContactDataExchange constructor.
     *
     * @param ContactRepository        $contactRepository
     * @param VtigerSettingProvider    $settingProvider
     * @param LeadModel                $leadModel
     * @param ValueNormalizerInterface $valueNormalizer
     * @param ContactValidator         $objectValidator
     * @param MappingHelper            $mappingHelper
     * @param ObjectFieldMapper        $objectFieldMapper
     */
    public function __construct(
        ContactRepository $contactRepository,
        VtigerSettingProvider $settingProvider,
        LeadModel $leadModel,
        ValueNormalizerInterface $valueNormalizer,
        ContactValidator $objectValidator,
        MappingHelper $mappingHelper,
        ObjectFieldMapper $objectFieldMapper
    )
    {
        $this->objectRepository  = $contactRepository;
        $this->objectValidator   = $objectValidator;
        $this->valueNormalizer   = $valueNormalizer;
        $this->mauticModel       = $leadModel;
        $this->settings          = $settingProvider;
        $this->mappingHelper     = $mappingHelper;
        $this->objectFieldMapper = $objectFieldMapper;
    }

    /**
     * @param \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject
     * @param ReportDAO                                                        $syncReport
     *
     * @return ReportDAO|mixed
     * @throws \MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotFoundException
     * @throws \MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectNotSupportedException
     * @throws \MauticPlugin\MauticVtigerCrmBundle\Exceptions\SessionException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getObjectSyncReport(
        \MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Request\ObjectDAO $requestedObject,
        ReportDAO &$syncReport
    ): ReportDAO
    {
        $fromDateTime = $requestedObject->getFromDateTime();
        $mappedFields = $requestedObject->getFields();
        $objectFields = $this->objectRepository->describe()->getFields();

        $mappedFields = array_merge($mappedFields, [
            'isconvertedfromlead', 'leadsource', 'reference', 'source', 'contact_id',
        ]);

        $deleted = [];
        $updated = $this->getReportPayload($fromDateTime, $mappedFields);

        /** @var Contact $contact */
        foreach ($updated as $key=>$contact) {
            if ($contact->isConvertedFromLead()) {
                $objectDAO = new ObjectDAO(LeadDataExchange::OBJECT_NAME, $contact->getId(), $contact->getModifiedTime());
                $objectDAO->addField(
                    new FieldDAO('email', $this->valueNormalizer->normalizeForMautic(NormalizedValueDAO::EMAIL_TYPE, $contact->getEmail()))
                );
                // beware this method also saves it :-(
                $foundMapping = $this->mappingHelper->findMauticObject(
                    $this->objectFieldMapper->getObjectsMappingManual(),
                    'lead',
                    $objectDAO
                );
                // This lead has to be marked as deleted
                if ($foundMapping) {
                    DebugLogger::log(VtigerCrmIntegration::NAME, "Marking Lead #" . $contact->getId() . " as deleted");
                    $objectChangeDAO = new ObjectChangeDAO(
                        VtigerCrmIntegration::NAME,
                        LeadDataExchange::OBJECT_NAME,
                        $contact->getId(),
                        $foundMapping->getObject(),
                        $foundMapping->getObjectId()
                    );

                    $mapping = (new ObjectMapping())
                        ->setIntegration(VtigerCrmIntegration::NAME)
                        ->setIntegrationObjectName(self::OBJECT_NAME)
                        ->setIntegrationObjectId($contact->getId())
                        ->setInternalObjectName($foundMapping->getObject())
                        ->setInternalObjectId($foundMapping->getObjectId())
                        ->setLastSyncDate($foundMapping->getChangeDateTime());

                    $this->mappingHelper->saveObjectMappings([
                        $mapping,
                    ]);

                    $deleted[] = $objectChangeDAO;
                    unset($updated[$key]);
                }
            }
        }

        $this->mappingHelper->markAsDeleted($deleted);

        /** @var Contact $object */
        foreach ($updated as $object) {
            $objectDAO = new ObjectDAO(self::OBJECT_NAME, $object->getId(), new \DateTimeImmutable($object->getModifiedTime()->format('r')));

            foreach ($object->dehydrate($mappedFields) as $field => $value) {
                // Normalize the value from the API to what Mautic needs
                $normalizedValue = $this->valueNormalizer->normalizeForMautic($objectFields[$field]->getType(), $value);
                $reportFieldDAO = new FieldDAO($field, $normalizedValue);

                $objectDAO->addField($reportFieldDAO);
            }

            $syncReport->addObject($objectDAO);
        }

        return $syncReport;
    }

    /**
     * @param array $objects
     *
     * @return ObjectMapping[]
     * @throws \MauticPlugin\IntegrationsBundle\Sync\Exception\ObjectDeletedException
     */
    public function insert(array $objects): array
    {
        $insertable = [];

        /** @var ObjectChangeDAO $object */
        foreach ($objects as $object) {
            $objectDAO = new ObjectDAO($object->getMappedObject(), $object->getMappedObjectId());

            $result = $this->mappingHelper->findIntegrationObject(
                VtigerCrmIntegration::NAME,
                'Leads',
                $objectDAO
            );

            /** If we have a lead record we won't insert it */
            if (null === $result->getObjectId()) {
                $insertable[] = $object;
            } else {
                DebugLogger::log(VtigerCrmIntegration::NAME, "Lead is remotely Lead, it won't be inserted to Contacts");
            }
        }

        return $this->internalInsert($insertable);
    }

    /**
     * @param array $objects
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function delete(array $objects)
    {
        // TODO: Implement delete() method.
        throw new \Exception('Not implemented');
    }
}