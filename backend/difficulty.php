<?php
// backend/difficulty.php
declare(strict_types=1);

/**
 * $stats = [
 *   'avg_time'     => float|null,
 *   'avg_moves'    => float|null,
 *   'completed'    => int,
 *   'attempted'    => int
 * ]
 */
function computeDifficultyLevel(array $stats): string {
    $attempted = (int)($stats['attempted'] ?? 0);
    $completed = (int)($stats['completed'] ?? 0);
    $avgTime   = (float)($stats['avg_time'] ?? 0);
    $avgMoves  = (float)($stats['avg_moves'] ?? 0);

    if ($attempted < 3 || $completed < 2) {
        return 'easy';
    }

    if ($avgTime > 0 && $avgTime < 90 && $avgMoves < 60) {
        return 'hard';
    }

    if ($avgTime > 0 && $avgTime < 150 && $avgMoves < 80) {
        return 'medium';
    }

    return 'easy';
}

/**
 * Map difficulty to hint + power-up counts.
 */
function getPowerUpsForDifficulty(string $difficulty): array {
    switch ($difficulty) {
        case 'hard':
            return ['hints' => 1, 'freezeTime' => 0, 'swapTiles' => 0];
        case 'medium':
            return ['hints' => 2, 'freezeTime' => 1, 'swapTiles' => 0];
        default:
            return ['hints' => 3, 'freezeTime' => 1, 'swapTiles' => 1];
    }
}
