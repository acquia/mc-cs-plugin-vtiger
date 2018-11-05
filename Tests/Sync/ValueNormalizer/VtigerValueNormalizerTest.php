<?php
declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc. Jan Kozak <galvani78@gmail.com>
 *
 * @link        http://mautic.com
 * @created     2.11.18
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Tests\Sync\ValueNormalizer;

use MauticPlugin\IntegrationsBundle\Sync\DAO\Sync\Order\FieldDAO;
use MauticPlugin\IntegrationsBundle\Sync\DAO\Value\NormalizedValueDAO;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\MauticVtigerTransformer;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\Transformers\VtigerMauticTransformer;
use MauticPlugin\MauticVtigerCrmBundle\Sync\ValueNormalizer\VtigerValueNormalizer;
use MauticPlugin\MauticVtigerCrmBundle\Tests\TestDataProvider\ModulesDescriptionProvider;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\ModuleFieldInfo;
use MauticPlugin\MauticVtigerCrmBundle\Vtiger\Type\CommonType;

class VtigerValueNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VtigerValueNormalizer
     */
    private $valueNormalizer;

    /**
     * @var array
     */
    private $normalizationsMautic = [
        'date'          => ['2018-01-02', null],
        'string'        => ["khjmkjhol.sadasd", null],
        'phone'         => ["001777786786", null],
        'email'         => ["jan.kozak@mautic.com", null],
        'picklist'      => ["A", "B"],
        'multipicklist' => ["A|B", null],
        'url'           => ['http://www.mautic.com', null],
        'currency'      => [12.56, null],
        'integer'       => [34, 66, null],
        'datetime'      => ['2001-09-09 22:22:12', null],
        'text'          => ["lala\nohlala\nnnnn"],
        'boolean'       => [0, 1, null, true, false],
        'double'        => [3.14159265434, null, 0],
        'skype'         => ['jajaja'],
        'time'          => ['12:20', '23:59'],
    ];

    /** @var CommonType[] */
    private $vtigerTypes;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->valueNormalizer = new VtigerValueNormalizer(
            new VtigerMauticTransformer(),
            new MauticVtigerTransformer()
        );

        $this->vtigerTypes = ModulesDescriptionProvider::getLeadFieldTypes();
    }


    public function testNormalizeForMautic()
    {


    }

    public function testNormalizeForVtiger()
    {
        $typeObject       = new \stdClass();
        $typeObject->name = 'string';

        $objData = [
            'label'     => 'Test',
            'name'      => 'test',
            'nullable'  => true,
            'editable'  => true,
            'type'      => $typeObject,
            'mandatory' => false,
        ];

        $fieldInfo = new ModuleFieldInfo((object)$objData);

        foreach ($this->normalizationsMautic as $type => $item) {
            $fieldInfo->setType($this->vtigerTypes[$type]);
            $item = is_array($item) ? $item : [$item];
            foreach ($item as $testValue) {
                $originalValue = new NormalizedValueDAO($type, $testValue);
                $fieldDAO      = new FieldDAO('test_field', $originalValue);
                $normalized    = $this->valueNormalizer->normalizeForVtiger($fieldInfo, $fieldDAO);
                $unnormalized  = $this->valueNormalizer->normalizeForMautic($type, $normalized);
                $this->assertEquals($normalized, $unnormalized->getNormalizedValue(),
                    sprintf("Transformation for %s type failed %s<>%s<>%s",
                        $type, $testValue, $normalized, $unnormalized->getNormalizedValue()
                    )
                );
            }
        }
    }
}
