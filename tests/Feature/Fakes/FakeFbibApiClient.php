<?php

namespace Tests\Feature\Fakes;

use App\Services\Imports\FbibApiClient;

class FakeFbibApiClient extends FbibApiClient
{
    public function __construct(
        private readonly array $clubSheets = [],
        private readonly array $teamMatches = [],
    ) {
    }

    public function fetchClubSheet(int $clubId): array
    {
        return $this->clubSheets[$clubId] ?? [];
    }

    public function fetchTeamSeasonMatches(int $teamId): array
    {
        return $this->teamMatches[$teamId] ?? [];
    }
}
