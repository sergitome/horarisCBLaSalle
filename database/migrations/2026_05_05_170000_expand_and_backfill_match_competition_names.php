<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->text('competition')->nullable()->change();
        });

        DB::table('matches')
            ->select(['id', 'competition', 'import_data'])
            ->orderBy('id')
            ->chunkById(100, function ($matches): void {
                foreach ($matches as $match) {
                    $importData = json_decode($match->import_data ?? 'null', true);

                    if (! is_array($importData)) {
                        continue;
                    }

                    $raw = is_array($importData['raw'] ?? null) ? $importData['raw'] : [];

                    $category = trim((string) ($raw['nameCategorySigned'] ?? $raw['nameCategory'] ?? ''));
                    $competition = trim((string) ($raw['competition'] ?? $raw['competitionName'] ?? $raw['nameCompetition'] ?? $importData['competition'] ?? ''));
                    $group = trim((string) ($raw['nameGroup'] ?? $raw['groupName'] ?? $raw['groupCompetition'] ?? $raw['registredGroups'] ?? ''));

                    $parts = [];

                    foreach ([$category, $competition] as $part) {
                        if ($part !== '' && ! in_array($part, $parts, true)) {
                            $parts[] = $part;
                        }
                    }

                    if ($group !== '' && ! in_array($group, $parts, true) && mb_strtolower($group) !== 'encreuaments') {
                        $parts[] = $group;
                    }

                    if ($parts === []) {
                        continue;
                    }

                    $fullCompetition = implode(' - ', $parts);
                    $importData['competition'] = $fullCompetition;

                    if (is_array($importData['_imported_values'] ?? null)) {
                        $importData['_imported_values']['competition'] = $fullCompetition;
                    }

                    DB::table('matches')
                        ->where('id', $match->id)
                        ->update([
                            'competition' => $fullCompetition,
                            'import_data' => json_encode($importData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->string('competition', 255)->nullable()->change();
        });
    }
};
