<?php

namespace App\Services\Department\Dto;

use Ramsey\Uuid\UuidInterface;

final readonly class UpdateDepartmentDto
{
    public function __construct(
        private UuidInterface $uuid,
        private UuidInterface $companyUuid,
        private string $name,
    ) {
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCompanyUuid(): UuidInterface
    {
        return $this->companyUuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

}