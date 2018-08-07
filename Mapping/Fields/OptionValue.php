<?php

declare(strict_types=1);

/*
 * @copyright   2018 Mautic Inc. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\MagentoBundle\Mapping\Fields;

use MauticPlugin\MagentoBundle\Exception\DisabledFieldException;

class OptionValue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $value;

    /**
     * @param array $data
     *
     * @throws DisabledFieldException
     */
    public function __construct(array $data)
    {
        $label = (string) $data['label'];

        if (!isset($data['options']) && !empty($data['value'])) {
            $this->id    = (int) $data['value'];
            $this->value = $label;
        } elseif (empty($data['options'])) {
            $this->id    = 0;
            $this->value = $label;
        } elseif (isset($data['options'][0]['label'])) {
            $this->id    = (int) $data['options'][0]['label'];
            $this->value = $label;
        } else {
            throw new DisabledFieldException('Option field is not supported');
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
