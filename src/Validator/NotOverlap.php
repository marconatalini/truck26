<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;


#[\Attribute]
final class NotOverlap extends Constraint
{
//    public string $message = '{{ driver }} has already a {{ category }} log in the date {{ startDate }}';
    public string $message = 'log.overlap.error';

    // You can use #[HasNamedArguments] to make some constraint options required.
    // All configurable options must be passed to the constructor.
    public function __construct(
        public string $mode = 'strict',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return OverlapValidator::class;
    }


}
