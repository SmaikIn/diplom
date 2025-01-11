<?php

namespace App\Services\Profile\Repositories;

use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\Phone;
use App\Domain\ValueObjects\Photo;
use App\Models\Profile;
use App\Services\Profile\Dto\CreateProfileDto;
use App\Services\Profile\Dto\ProfileDto;
use App\Services\Profile\Dto\UpdateProfileDto;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class DatabaseProfileRepository implements ProfileRepository
{
    public function __construct()
    {
    }

    /**
     * @param  UuidInterface  $companyId
     * @return ProfileDto[]
     */
    public function getProfilesByCompanyId(UuidInterface $companyId): array
    {
        $dbProfiles = Profile::where('company_uuid', $companyId->toString())->get();

        $profiles = [];
        foreach ($dbProfiles as $profile) {
            $profiles[] = $this->formatToDto($profile);
        }

        return $profiles;
    }

    public function getProfileByCompanyId(UuidInterface $companyId, UuidInterface $profileId): ProfileDto
    {
        $profile = Profile::where('company_uuid', $companyId->toString())->where('uuid',
            $profileId->toString())->firstOrFail();

        return $this->formatToDto($profile);
    }

    public function updateProfileByCompanyId(UpdateProfileDto $profileDto): ProfileDto
    {
        $profile = Profile::where('company_uuid', $profileDto->getCompanyUuid()->toString())
            ->where('uuid', $profileDto->getUuid()->toString())->firstOrFail();

        $profile->name = $profileDto->getName();
        $profile->save();

        return $this->formatToDto($profile);
    }

    public function deleteProfileByCompanyId(UuidInterface $companyId, UuidInterface $profileId): bool
    {
        $profile = Profile::where('company_uuid', $companyId->toString())->where('uuid',
            $profileId->toString())->firstOrFail();

        return $profile->delete();
    }

    public function banUser(){

    }

    public function formatToDto(Profile $profile): ProfileDto
    {
        return new ProfileDto(
            id: Uuid::fromString($profile->uuid),
            userId: Uuid::fromString($profile->user_uuid),
            companyId: Uuid::fromString($profile->company_uuid),
            roleId: Uuid::fromString($profile->role_uuid),
            departmentId: Uuid::fromString($profile->department_uuid),
            firstName: $profile->first_name,
            lastName: $profile->last_name,
            secondName: $profile->second_name,
            phone: Phone::create($profile->phone),
            photo: Photo::create(
                asset($profile->avatar),
                'Фото: '.$profile->first_name.' '.$profile->last_name,
            ),
            email: Email::create($profile->email),
            birthday: Carbon::create($profile->birthday),
            createdAt: Carbon::create($profile->created_at),
        );
    }

    public function createProfileByCompanyId(CreateProfileDto $profileDto): ProfileDto
    {
        // TODO: Implement createProfileByCompanyId() method.
    }
}