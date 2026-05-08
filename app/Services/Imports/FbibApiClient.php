<?php

namespace App\Services\Imports;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FbibApiClient
{
    private const BASE_URL = 'https://esb.optimalwayconsulting.com/fbib/1/jR4rgA5K6Chhh5vyfrxo9wTScdg2NT7K';

    public function fetchClubSheet(int $clubId): array
    {
        $payload = $this->request("/FCBQWeb/fitxaClub/{$clubId}");

        return $payload['messageData'] ?? [];
    }

    public function fetchTeamSeasonMatches(int $teamId): array
    {
        $payload = $this->request("/Match/getByTeamAllSeason/{$teamId}");

        return $payload['messageData'] ?? [];
    }

    protected function request(string $path): array
    {
        $response = $this->http()->get(self::BASE_URL.$path);

        if (! $response->successful()) {
            throw new RuntimeException("FBIB ha respost amb estat {$response->status()} a {$path}");
        }

        $decodedBody = $this->decodeBody($response->body());
        $payload = json_decode($decodedBody, true);

        if (! is_array($payload)) {
            throw new RuntimeException("FBIB ha retornat una resposta invalida a {$path}");
        }

        if (($payload['result'] ?? null) !== 'OK') {
            $message = $payload['message'] ?? 'Resposta d\'error sense detall';
            throw new RuntimeException("FBIB ha retornat un error a {$path}: {$message}");
        }

        return $payload;
    }

    protected function http(): PendingRequest
    {
        return Http::acceptJson()
            ->withHeaders([
                'Origin' => 'https://www.fbib.es',
                'Referer' => 'https://www.fbib.es/',
                'User-Agent' => 'Mozilla/5.0',
            ])
            ->timeout(30)
            ->retry(2, 300);
    }

    private function decodeBody(string $body): string
    {
        $trimmed = trim($body);

        if ($trimmed === '') {
            throw new RuntimeException('FBIB ha retornat una resposta buida.');
        }

        if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
            return $trimmed;
        }

        $decoded = base64_decode($trimmed, true);

        if ($decoded === false) {
            throw new RuntimeException('No s\'ha pogut decodificar la resposta de FBIB.');
        }

        return $decoded;
    }
}
