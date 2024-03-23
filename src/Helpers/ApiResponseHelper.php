<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

trait ApiResponseHelper
{
    public function prepareResponse(mixed $result, mixed $error = null): JsonResponse
    {
        if (is_array($result)) {
            $resultData = [];

            foreach ($result as $data) {
                $resultData[] = $data instanceof EntityInterface ? $data->toFilteredArray() : $data;
            }
        } elseif ($result instanceof EntityInterface) {
            $resultData = $result->toFilteredArray();
        }

        return new JsonResponse([
            'result' => $resultData ?? $result,
            'error' => is_array($error) && !$error ? null : $error,
        ]);
    }
}
