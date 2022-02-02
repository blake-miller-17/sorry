<?php
namespace Sorry;

/**
 * Class BoardIndex representing an index on a Sorry! board.
 * @package Sorry
 */
class BoardIndex {

    // Types of indices
    const PERIM_INDEX = 0; // Indices that are on the perimeter of the board
    const SAFE_INDEX = 1; // Indices that are inside a safe zone or home
    const START_INDEX = 2; // Indices that are inside a safe zone
    const INVALID_INDEX = 3; // Indices that are invalid

    private $perim = -1; // Perimeter portion of the index
    private $branch = -1; // Branch portion of the index
    private $type = self::INVALID_INDEX; // The type of this index

    /**
     * BoardIndex constructor.
     * @param int $perim Perimeter portion of the index
     * @param int $branch Branch portion of the index
     */
    public function __construct(int $perim, int $branch) {
        // Set the parts if the index is valid
        $this->type = $this->determineIndexType($perim, $branch);
        if ($this->isValid()) {
            $this->perim = $perim;
            $this->branch = $branch;
        }
    }

    /**
     * Determine if an index is valid.
     * @param int $perim Proposed perimeter portion of the index
     * @param int $branch Proposed branch portion of the index
     * @return int Type of this index
     */
    private function determineIndexType(int $perim, int $branch) {
        if ($this->determineValidity($perim, $branch)) {
            if ($branch == Board::MIN_SPACE) {
                return self::PERIM_INDEX;
            } else {
                if (in_array($perim, Board::START_INTERSECTIONS)) {
                    return self::START_INDEX;
                } else if (in_array($perim, Board::SAFE_INTERSECTIONS)) {
                    return self::SAFE_INDEX;
                }
            }
        }
        return self::INVALID_INDEX;
    }

    /**
     * Determine the validity of this index.
     * @param int $perim Proposed perimeter portion of the index
     * @param int $branch Proposed branch portion of the index
     * @return bool True if the proposed index is valid
     */
    private static function determineValidity(int $perim, int $branch) {
        return  (
            // Perimeter part must be in a valid range
            $perim >= Board::MIN_SPACE && $perim <= Board::MAX_PERIM_SPACE

            // Branch part must be in a valid branch area and in the correct range for that area
            && (
                ($branch == Board::MIN_SPACE)
                || (in_array($perim, Board::START_INTERSECTIONS) && $branch >= Board::MIN_SPACE && $branch <= Board::MAX_START_SPACE)
                || (in_array($perim, Board::SAFE_INTERSECTIONS) && $branch >= Board::MIN_SPACE && $branch <= Board::MAX_SAFE_SPACE)
            )
        );
    }

    /**
     * Determine if another index is equal to this index.
     * @param BoardIndex $other The other index to test against
     * @return bool True if the other index is equal to this one
     */
    public function equals(BoardIndex $other) {
        return (
            $other != null && $other instanceof BoardIndex
            && $other->getPerim() == $this->perim && $other->getBranch() == $this->branch
        );
    }

    /**
     * Getter for that perimeter portion of this index.
     * @return int The perimeter portion of this index
     */
    public function getPerim() {
        return $this->perim;
    }

    /**
     * Getter for the branch portion of this index.
     * @return int The branch portion of this index
     */
    public function getBranch() {
        return $this->branch;
    }

    /**
     * Getter for the valid status of this index.
     * @return bool True if the index is valid
     */
    public function isValid() {
        return $this->type != self::INVALID_INDEX;
    }

    /**
     * Getter for the type of this index.
     * @return int The type of this index
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Determine if this index is an active index and can be the start of a move.
     * @return bool True if this index is an active index
     */
    public function isActiveIndex() {
        return (
            $this->isValid()
            && (
                $this->type == self::PERIM_INDEX
                || ($this->type == self::SAFE_INDEX && $this->branch < Board::MAX_SAFE_SPACE)
            )
        );
    }

    /**
     * Determine if this index is in a start zone.
     * @return bool True if this index is in a start zone
     */
    public function isStart() {
        return $this->type == self::START_INDEX && $this->getBranch() == Board::MAX_START_SPACE;
    }

    /**
     * Determine if this index is in a home zone.
     * @return bool True if this index is in a home zone
     */
    public function isHome() {
        return $this->type == self::SAFE_INDEX && $this->getBranch() == Board::MAX_SAFE_SPACE;
    }

    /**
     * Determine if an array in in an array of indexes
     * @param BoardIndex $testIndex
     * @param array $indexArray
     * @return false
     */
    public static function indexInArray(BoardIndex $testIndex, array $indexArray) {
        foreach ($indexArray as $index) {
            if ($index->equals($testIndex)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Remove an index from an array of indices.
     * @param BoardIndex $testIndex Index to remove
     * @param array $indexArray Array of indices
     * @return array Resulting array of indices
     */
    public static function indexArrayRemove(BoardIndex $testIndex, array $indexArray) {
        foreach ($indexArray as $key => $index) {
            if ($index->equals($testIndex)) {
                unset($indexArray[$key]);
                return array_values($indexArray);
            }
        }
        return $indexArray;
    }
}