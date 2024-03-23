<?php

declare(strict_types=1);

namespace App\Helpers;

use App\DTO\ContextInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ApiRequestHelper
{
    public function getValidationErrors(
        ?ConstraintViolationListInterface $errors,
        ?ContextInterface $context = null
    ): ?array {
        $formattedErrors = [];

        if ($errors) {
            foreach ($errors as $error) {
                $formattedErrors[$error->getPropertyPath()] = $error->getMessage();
            }
        } elseif ($context) {
            $emptyContext = true;
            $params = '';

            foreach ((new \ReflectionClass($context))->getProperties() as $property) {
                if ($context->{$property->getName()}) {
                    $emptyContext = false;

                    break;
                }

                $params .= "{$property->getName()} ,";
            }

            if ($emptyContext) {
                $formattedErrors[] = sprintf('Required at least one valid parameter: %s', trim($params, '. ,'));
            }
        }

        return $formattedErrors ?: null;
    }
}
