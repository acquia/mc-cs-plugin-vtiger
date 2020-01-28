<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MauticPlugin\MauticVtigerCrmBundle\Vtiger\Model\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * ChoiceValidator validates that the value is one of the expected values.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Florian Eckerstorfer <florian@eckerstorfer.org>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class MultiChoiceValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MultiChoice) {
            throw new UnexpectedTypeException($constraint, MultiChoice::class);
        }

        if (!is_array($constraint->choices) && !$constraint->callback) {
            throw new ConstraintDefinitionException('Either "choices" or "callback" must be specified on constraint Choice');
        }

        if (null === $value || $value == "") {
            return;
        }

        $value = explode('|##|', $value);
        array_walk($value, function(&$element){
            $element = trim($element);
        });


        if ($constraint->multiple && !is_array($value)) {
            throw new UnexpectedTypeException($value, 'array');
        }

        if ($constraint->callback) {
            if (!is_callable($choices = array($this->context->getClassName(), $constraint->callback))
                && !is_callable($choices = $constraint->callback)
            ) {
                throw new ConstraintDefinitionException('The Choice constraint expects a valid callback');
            }
            $choices = call_user_func($choices);
        } else {
            $choices = $constraint->choices;
        }

        if ($constraint->multiple) {
            foreach ($value as $_value) {
                if (!in_array($_value, $choices, $constraint->strict)) {
                    $this->context->buildViolation($constraint->multipleMessage)
                        ->setParameter('{{ value }}', $this->formatValue($_value))
                        ->setCode(MultiChoice::NO_SUCH_CHOICE_ERROR)
                        ->setInvalidValue($_value)
                        ->addViolation();
                    return;
                }
            }

            $count = count($value);

            if (null !== $constraint->min && $count < $constraint->min) {
                $this->context->buildViolation($constraint->minMessage)
                    ->setParameter('{{ limit }}', $constraint->min)
                    ->setPlural((int) $constraint->min)
                    ->setCode(MultiChoice::TOO_FEW_ERROR)
                    ->addViolation();
                return;
            }

            if (null !== $constraint->max && $count > $constraint->max) {
                $this->context->buildViolation($constraint->maxMessage)
                    ->setParameter('{{ limit }}', $constraint->max)
                    ->setPlural((int) $constraint->max)
                    ->setCode(MultiChoice::TOO_MANY_ERROR)
                    ->addViolation();
                return;
            }
        } elseif (!in_array($value, $choices, $constraint->strict)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode(MultiChoice::NO_SUCH_CHOICE_ERROR)
                ->addViolation();
        }
    }
}
