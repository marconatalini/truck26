<?php

namespace App\Validator;

use App\Entity\DriverLog;
use App\Repository\DriverLogRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedValueException;
use function Symfony\Component\Translation\t;

final class OverlapValidator extends ConstraintValidator
{
    public function __construct(
        readonly DriverLogRepository $driverLogRepository,
    )
    {
    }

    /**
     * @param DriverLog $value
     * @param Constraint $constraint
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        /* @var NotOverlap $constraint */
        if (!$value instanceof DriverLog) {
            throw new UnexpectedValueException($value, DriverLog::class);
        }

        if (!$constraint instanceof NotOverlap) {
            throw new UnexpectedValueException($constraint, NotOverlap::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (null !== $this->driverLogRepository->findOverlapsLog($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ driver }}', $value->getDriver())
                ->setParameter('{{ category }}', $value->getCategory())
                ->setParameter('{{ startDate }}', $value->getStartDate()->format('d.m.Y'))
                ->atPath('driverLog.startDate')
                ->addViolation()
            ;
        }
    }
}
