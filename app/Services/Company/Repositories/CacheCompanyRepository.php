<?php

namespace App\Services\Company\Repositories;

use App\Services\Company\Dto\CompanyDto;
use App\Services\Company\Dto\CreateCompanyDto;
use App\Services\Company\Dto\UpdateCompanyDto;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Cache\Repository;
use Ramsey\Uuid\UuidInterface;

final readonly class CacheCompanyRepository implements CompanyRepository
{
    const CACHE_TTL_DAYS = CarbonInterface::DAYS_PER_WEEK;
    const CACHE_TTL_INVITE = CarbonInterface::HOURS_PER_DAY;

    public function __construct(
        private DatabaseCompanyRepository $databaseCompanyRepository,
        private Repository $cache,
        private \Illuminate\Config\Repository $config,
    ) {
    }

    /**
     * @param  UuidInterface  $companyId
     * @return CompanyDto
     */
    public function find(UuidInterface $companyId): CompanyDto
    {
        $key = $this->getKeyForCache($companyId->toString());

        return $this->cache->remember(
            $key,
            Carbon::parse(self::CACHE_TTL_DAYS.' days'),
            function () use ($companyId) {
                return $this->databaseCompanyRepository->find($companyId);
            },
        );
    }

    /**
     * @param  string[]  $companyIds
     * @return CompanyDto[]
     */
    public function findMany(array $companyIds): array
    {
        $keys = [];
        foreach ($companyIds as $companyId) {
            $keys[] = $this->getKeyForCache($companyId);
        }
        $dbCompanies = $this->cache->many($keys);

        if (!array_search(null, $dbCompanies)) {
            return array_values($dbCompanies);
        }

        $dbCompanies = $this->databaseCompanyRepository->findMany($companyIds);

        foreach ($dbCompanies as $dbCompany) {
            $this->cache->add($this->getKeyForCache($dbCompany->getUuid()->toString()), $dbCompany,
                Carbon::parse(self::CACHE_TTL_DAYS.' days'),);
        }


        return array_values($dbCompanies);
    }

    /**
     * @param  CreateCompanyDto  $createCompanyDto
     * @return CompanyDto
     */
    public function create(CreateCompanyDto $createCompanyDto): CompanyDto
    {
        return $this->databaseCompanyRepository->create($createCompanyDto);
    }

    public function update(UpdateCompanyDto $updateCompanyDto): CompanyDto
    {
        $dbCompany = $this->databaseCompanyRepository->update($updateCompanyDto);

        $this->cache->put($this->getKeyForCache($dbCompany->getUuid()->toString()), $dbCompany,
            Carbon::parse(self::CACHE_TTL_DAYS.' days'));

        return $dbCompany;
    }

    public function delete(UuidInterface $companyId): bool
    {
        $result = $this->databaseCompanyRepository->delete($companyId);

        $this->cache->forget($this->getKeyForCache($companyId->toString()));

        return $result;
    }

    public function getInviteCode(string $code): ?string
    {
        $key = sprintf($this->config->get('cache.keys.company.invite_code'), $code);

        try {
            return $this->cache->get($key);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    public function setInviteCode(string $code): void
    {
        $key = sprintf($this->config->get('cache.keys.company.invite_code'), $code);
        $this->cache->set($key, $code, Carbon::parse(self::CACHE_TTL_INVITE));
    }

    public function forgetInviteCode(string $code): void
    {
        $key = sprintf($this->config->get('cache.keys.company.invite_code'), $code);
        $this->cache->forget($key);
    }

    private function getKeyForCache(string $companyId): string
    {
        return sprintf($this->config->get('cache.keys.company.company'), $companyId);
    }

}
