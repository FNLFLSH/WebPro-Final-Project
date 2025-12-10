<?php
// backend/puzzellogic.php
declare(strict_types=1);

/**
 * Generate solvable N x N puzzle; 0 = empty.
 */
function generatePuzzle(int $size): array {
    $tiles = range(1, $size * $size - 1);
    $tiles[] = 0;

    do {
        shuffle($tiles);
    } while (!isSolvable($tiles, $size) || isSolved($tiles));

    return $tiles;
}

function isSolved(array $tiles): bool {
    $n = count($tiles);
    for ($i = 0; $i < $n - 1; $i++) {
        if ($tiles[$i] !== $i + 1) return false;
    }
    return $tiles[$n - 1] === 0;
}

function isSolvable(array $tiles, int $size): bool {
    $inversions = 0;
    $n = count($tiles);

    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            if ($tiles[$i] && $tiles[$j] && $tiles[$i] > $tiles[$j]) {
                $inversions++;
            }
        }
    }

    if ($size % 2 === 1) {
        return $inversions % 2 === 0;
    }

    $zeroIndex = array_search(0, $tiles, true);
    $zeroRowFromTop = intdiv($zeroIndex, $size);
    $rowFromBottom  = $size - $zeroRowFromTop;

    if ($rowFromBottom % 2 === 0) {
        return $inversions % 2 === 1;
    }
    return $inversions % 2 === 0;
}

function getValidMoves(array $tiles, int $size): array {
    $zeroIndex = array_search(0, $tiles, true);
    $row = intdiv($zeroIndex, $size);
    $col = $zeroIndex % $size;

    $moves = [];
    if ($row > 0)          $moves[] = $zeroIndex - $size;
    if ($row < $size - 1)  $moves[] = $zeroIndex + $size;
    if ($col > 0)          $moves[] = $zeroIndex - 1;
    if ($col < $size - 1)  $moves[] = $zeroIndex + 1;

    return $moves;
}

function applyMove(array $tiles, int $size, int $tileIndex): array {
    $valid = getValidMoves($tiles, $size);
    if (!in_array($tileIndex, $valid, true)) {
        return $tiles;
    }
    $zeroIndex = array_search(0, $tiles, true);
    [$tiles[$zeroIndex], $tiles[$tileIndex]] = [$tiles[$tileIndex], $tiles[$zeroIndex]];
    return $tiles;
}

function manhattanScore(array $tiles, int $size): int {
    $score = 0;
    $n = count($tiles);
    for ($i = 0; $i < $n; $i++) {
        $v = $tiles[$i];
        if ($v === 0) continue;
        $r = intdiv($i, $size);
        $c = $i % $size;
        $goalIndex = $v - 1;
        $gr = intdiv($goalIndex, $size);
        $gc = $goalIndex % $size;
        $score += abs($r - $gr) + abs($c - $gc);
    }
    return $score;
}

function getHintMove(array $tiles, int $size): ?int {
    $moves = getValidMoves($tiles, $size);
    if (empty($moves)) return null;

    $bestMove = null;
    $bestScore = PHP_INT_MAX;

    foreach ($moves as $idx) {
        $clone = applyMove($tiles, $size, $idx);
        $score = manhattanScore($clone, $size);
        if ($score < $bestScore) {
            $bestScore = $score;
            $bestMove = $idx;
        }
    }
    return $bestMove;
}
