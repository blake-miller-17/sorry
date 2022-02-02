<?php
namespace Sorry;

/**
 * Class Conversions. Handles conversions between index and space coordinate systems.
 * @package Sorry
 */
abstract class Conversions {
    // Spaces that make up each team's start zones
    const START_GRIDS = [
        Team::YELLOW => [19,20,21,35,36,37,51,52,53],
        Team::GREEN => [60,61,62,76,77,78,92,93,94],
        Team::RED => [202,203,204,218,219,220,234,235,236],
        Team::BLUE => [161,162,163,177,178,179,193,194,195]
    ];

    // Spaces that make up each team's home zones
    const HOME_GRIDS = [
        Team::YELLOW => [97,98,99,113,114,115,129,130,131],
        Team::GREEN => [23,24,25,39,40,41,55,56,57],
        Team::RED => [124,125,126,140,141,142,156,157,158],
        Team::BLUE => [198,199,200,214,215,216,230,231,232]
    ];

    // Spaces that pieces go in when they're in their start zones
    const START_PIECE_SPACES = [
        Team::YELLOW => [19, 21, 51, 53],
        Team::GREEN => [60, 62, 92, 94],
        Team::RED => [202, 204, 234, 236],
        Team::BLUE => [161, 163, 193, 195],
    ];

    // Spaces that pieces go in when they're in their home zones
    const HOME_PIECE_SPACES = [
        Team::YELLOW => [97, 99, 129, 131],
        Team::GREEN => [23, 25, 55, 57],
        Team::RED => [124, 126, 156, 158],
        Team::BLUE => [198, 200, 230, 232]
    ];

    /**
     * Convert an space number into a board index on the board.
     * @param int $space space number
     * @return BoardIndex Index on the board. Null if invalid space
     */
    public static function spaceToIndex(int $space) {
        $index = null; // Resulting index after conversion
        if ($space >= 0 && $space <= 15) {
            // Top perimeter spaces
            // Index perim portions are just 4 behind the space numbers. Handle wraparound from min to max space (0 -> 59)
            $index = new BoardIndex($space - 4 >= 0 ? $space - 4 : Board::MAX_PERIM_SPACE + 1 - (-($space - 4)), Board::MIN_SPACE);
        } else if ($space >= 240 && $space <= 255) {
            // Bottom perimeter spaces
            $index = new BoardIndex(41 - ($space - 240), Board::MIN_SPACE);
        } else if ($space % 16 == 15) {
            // Right perimeter spaces
            $index = new BoardIndex((int)($space / 16) + 11, Board::MIN_SPACE);
        } else if ($space % 16 == 0) {
            // Left perimeter spaces
            $index = new BoardIndex(56 - (int)($space / 16), Board::MIN_SPACE);
        } else if ($space >= 18 && $space <= 82 && $space % 16 == 2) {
            // In yellow safe zone
            $index = new BoardIndex(58, (int)$space / 16);
        } else if ($space >= 42 && $space <= 46) {
            // In green safe zone
            $index = new BoardIndex(13, 5 - ($space - 42));
        } else if ($space >= 173 && $space <= 237 && $space % 16 == 13) {
            // In red safe zone
            $index = new BoardIndex(28, 15 - (int)($space / 16));
        } else if ($space >= 209 && $space <= 213) {
            // In blue safe zone
            $index = new BoardIndex(43, $space % 16);
        }

        // Check for start index
        if ($index == null) {
            foreach(self::START_GRIDS as $color => $grid) {
                if (in_array($space, $grid)) {
                    $index = new BoardIndex(Board::START_INTERSECTIONS[$color], Board::MAX_START_SPACE);
                }
            }
        }

        // Check for home index
        if ($index == null) {
            foreach(self::HOME_GRIDS as $color => $grid) {
                if (in_array($space, $grid)) {
                    $index = new BoardIndex(Board::SAFE_INTERSECTIONS[$color], Board::MAX_SAFE_SPACE);
                }
            }
        }

        return $index;
    }

    /**
     * Convert a board index on the board to space numbers that the view can recognize.
     * @param BoardIndex $index Index on the board
     * @return array Space numbers converted to
     */
    public static function indexToSpaces(BoardIndex $index) {
        $space = [];
        if ($index->isValid()) {
            // Index is a start zone, output all spaces
            if ($index->isStart()) {
                foreach(Board::START_INTERSECTIONS as $color => $perim) {
                    if ($perim == $index->getPerim()) {
                        $space = self::START_GRIDS[$color];
                    }
                }
            } else if ($index->isHome()) {
                // Index is a home zone, output all spaces
                foreach(Board::SAFE_INTERSECTIONS as $color => $perim) {
                    if ($perim == $index->getPerim()) {
                        $space = self::HOME_GRIDS[$color];
                    }
                }
            } else {
                // Index is a normal index
                $perim = $index->getPerim(); // Perimeter portion of the provided index
                switch ($index->getType()) {
                    case BoardIndex::PERIM_INDEX:
                        if ($perim >= 56 || $perim <= 11) {
                            // Top perimeter index
                            $space = [($perim + 4) % 60];
                        } else if ($perim >= 11 && $perim <= 26) {
                            // Right perimeter index
                            $space = [11 + (16 * ($perim - 11)) + 4];
                        } else if ($perim >= 26 && $perim <= 41) {
                            // Bottom perimeter index
                            $space = [41 + 240 - $perim];
                        } else if ($perim >= 41 && $perim <= 56) {
                            // Left perimeter index
                            $space = [240 - (16 * ($perim - 41))];
                        }
                        break;
                    case BoardIndex::SAFE_INDEX:
                        $branch = $index->getBranch(); // Branch portion of the provided index
                        switch ($perim) {
                            case 58:
                                $space = [2 + ($branch * 16)];
                                break;
                            case 13:
                                $space = [47 - $branch];
                                break;
                            case 28:
                                $space = [253 - ($branch * 16)];
                                break;
                            case 43:
                                $space = [208 + $branch];
                                break;
                        }
                }
            }
        }
        return $space;
    }
}