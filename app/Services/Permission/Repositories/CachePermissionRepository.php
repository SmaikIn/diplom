<?php

namespace App\Services\Permission\Repositories;

use App\Services\Permission\Dto\PermissionDto;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Cache\Repository;

final readonly class CachePermissionRepository implements PermissionRepository
{

    const CACHE_TTL_DAYS = CarbonInterface::DAYS_PER_WEEK;

    public function __construct(
        private DatabasePermissionRepository $databasePermissionRepository,
        private Repository $cache,
        private \Illuminate\Config\Repository $config,
    ) {
    }

    /**
     * @param  string[]  $arrayIds
     * @return array
     */
    public function findMany(array $arrayIds): array
    {
        $keys = [];
        foreach ($arrayIds as $permissionId) {
            $keys[] = $this->getKeyForCache($permissionId);
        }
        $dbPermissions = $this->cache->many($keys);

        if (!array_search(null, $dbPermissions)) {
            return array_values($dbPermissions);
        }

        $dbPermissions = $this->databasePermissionRepository->findMany($arrayIds);

        foreach ($dbPermissions as $dbPermission) {
            $this->cache->add($this->getKeyForCache($dbPermission->getUuid()->toString()), $dbPermission,
                Carbon::parse(self::CACHE_TTL_DAYS.' days'),);
        }


        return array_values($dbPermissions);
    }

    /**
     * @return PermissionDto[]
     */
    public function all(): array
    {
        $key = $this->config->get('cache.keys.permission.all');

        return $this->cache->rememberForever($key, function () use ($key) {
            $this->databasePermissionRepository->all();
        });
    }


    private function getKeyForCache(string $permissionId): string
    {
        return sprintf($this->config->get('cache.keys.permission.permission'), $permissionId);
    }

}
