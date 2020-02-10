<?php

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates whether a value is a valid currency.
 *
 * @author Miha Vrhovnik <miha.vrhovnik@pagein.si>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DateValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Date) {
            throw new UnexpectedTypeException($constraint, Date::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        // check if item can be converted to string
        if (!is_scalar($value) && !(is_object($value) && method_exists($value, '__toString'))) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $date = \DateTime::createFromFormat('Y-m-d', $value);

        if (!$date || ($date->format('Y-m-d') != $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(\Symfony\Component\Validator\Constraints\Date::INVALID_DATE_ERROR)
                ->addViolation();
        }
    }
}

