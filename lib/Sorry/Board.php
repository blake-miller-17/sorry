<?php
namespace Sorry;

/**
 * Class Board representing the state of pieces on a Sorry! board.
 * @package Sorry
 */
class Board {
    // Board constants
    const PIECES_PER_TEAM = 4; // Number of pieces each team has
    const MAX_PERIM_SPACE = 59; // Largest valid perimeter index part
    const MAX_SAFE_SPACE = 6; // Largest valid branch part for safe zone spaces
    const MAX_START_SPACE = 1; // Largest valid branch part for start zone spaces
    const MIN_SPACE = 0; // Minimum value to be used as a part of an index for perimeter and branches

    // Slide constants
    const SHORT_SLIDE_DIST = 3; // Distance to move on a short slide
    const LONG_SLIDE_DIST = 4; // Distance to move on a long slide
    const SLIDE_TEAM_KEY = 0; // Key to get the team the slide belongs to
    const SLIDE_DIST_KEY = 1; // Key to get the distance to travel on a slide

    const TEST_MOVE_INDEX_KEY = 0; // Key to get the index from testing a move
    const TEST_MOVE_SLIDE_KEY = 1; // Key to get the slide taken from testing a move

    const STATE_INDEX_KEY = 0; // Key to get the index from a board state element
    const STATE_TEAM_KEY = 1; // Key to get the team from a board state element

    const DIST_FORWARD_KEY = 0; // Key to get the forward distance between two indices
    const DIST_BACKWARDS_KEY = 1; // Key to get the backward distance between two indices

    // Spaces that contain a slide start. Each slide has a team owner and a distance to slide
    const SLIDE_SPACES = [
        5 => [self::SLIDE_TEAM_KEY => Team::YELLOW, self::SLIDE_DIST_KEY => self::LONG_SLIDE_DIST],
        12 => [self::SLIDE_TEAM_KEY => Team::GREEN, self::SLIDE_DIST_KEY => self::SHORT_SLIDE_DIST],
        20 => [self::SLIDE_TEAM_KEY => Team::GREEN, self::SLIDE_DIST_KEY => self::LONG_SLIDE_DIST],
        27 => [self::SLIDE_TEAM_KEY => Team::RED, self::SLIDE_DIST_KEY => self::SHORT_SLIDE_DIST],
        35 => [self::SLIDE_TEAM_KEY => Team::RED, self::SLIDE_DIST_KEY => self::LONG_SLIDE_DIST],
        42 => [self::SLIDE_TEAM_KEY => Team::BLUE, self::SLIDE_DIST_KEY => self::SHORT_SLIDE_DIST],
        50 => [self::SLIDE_TEAM_KEY => Team::BLUE, self::SLIDE_DIST_KEY => self::LONG_SLIDE_DIST],
        57 => [self::SLIDE_TEAM_KEY => Team::YELLOW, self::SLIDE_DIST_KEY => self::SHORT_SLIDE_DIST]
    ];

    // Contains the index of each team's safe zone intersection
    const START_INTERSECTIONS = [
        Team::YELLOW => 0,
        Team::GREEN => 15,
        Team::RED => 30,
        Team::BLUE => 45
    ];

    // Contains the index of each team's start spaces
    const SAFE_INTERSECTIONS = [
        Team::YELLOW => 58,
        Team::GREEN => 13,
        Team::RED => 28,
        Team::BLUE => 43
    ];

    // The status of the home zones
    private $homes = [
        Team::YELLOW => 0,
        Team::GREEN => 0,
        Team::RED => 0,
        Team::BLUE => 0
    ];

    private $starts; // The status of the start zones
    private $board = []; // The status of the active spaces on the board

    /**
     * Board constructor.
     * @param array $teams List of teams that are in the game
     */
    public function __construct(array $teams) {
        $this->starts = [
            Team::RED => in_array(Team::RED, $teams) ? Board::PIECES_PER_TEAM : 0,
            Team::BLUE => in_array(Team::BLUE, $teams) ? Board::PIECES_PER_TEAM : 0,
            Team::YELLOW => in_array(Team::YELLOW, $teams) ? Board::PIECES_PER_TEAM : 0,
            Team::GREEN => in_array(Team::GREEN, $teams) ? Board::PIECES_PER_TEAM : 0
        ];
    }

    /**
     * Get the indices that would be clickable.
     * @param int $teamTurn Team whose turn it is
     * @param BoardIndex $selectedIndex Index that is selected
     * @param Card $drawnCard Card that is currently drawn
     * @param int $forwardSpaces Spaces that the selected piece can move forwards
     * @param int $backwardSpaces Spaces that the selected piece can move backwards
     * @param array $movedIndices Indices that have moved with the current card
     * @return array Array of indices that are clickable
     */
    public function getClickableIndices(int $teamTurn, BoardIndex $selectedIndex = null, Card $drawnCard = null, int $forwardSpaces, int $backwardSpaces, array $movedIndices) {
        $clickableIndices = [];
        if ($drawnCard != null) {
            if ($selectedIndex != null) {
                $clickableIndices[] = $selectedIndex;
                $clickableIndices = array_merge($clickableIndices, $this->getPossibleActions($selectedIndex, $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices));
            } else {
                // Test if the start zone can move
                if (count($this->getPossibleActions(new BoardIndex(self::START_INTERSECTIONS[$teamTurn], self::MAX_START_SPACE), $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices)) > 0) {
                    $clickableIndices[] = new BoardIndex(Board::START_INTERSECTIONS[$teamTurn], Board::MAX_START_SPACE);
                }

                // Test if each of the active pieces can move
                foreach ($this->getActivePieces() as $piece) {
                    if ($piece[Board::STATE_TEAM_KEY] == $teamTurn && count($this->getPossibleActions($piece[self::STATE_INDEX_KEY], $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices)) > 0) {
                        $clickableIndices[] = $piece[Board::STATE_INDEX_KEY];
                    }
                }
            }
        }
        return $clickableIndices;
    }

    /**
     * Get all the possible board indices that can be moved to from the current selected space.
     * @param BoardIndex $selectedIndex The index that is currently selected
     * @param Card $drawnCard The card that is currently drawn
     * @param int $forwardSpaces Spaces that the selected piece can move forwards
     * @param int $backwardSpaces Spaces that the selected piece can move backwards
     * @param array $movedIndices Indices that have moved with the current card
     * @return array The possible indices that can be the result of actions from the current selected space
     */
    private function getPossibleActions(BoardIndex $selectedIndex = null, Card $drawnCard, int $forwardSpaces, int $backwardSpaces, array $movedIndices) {
        $possibleActions = [];

        // Only try the actions if the index is valid and there is a piece at that index
        if ($selectedIndex != null && $selectedIndex->isValid() && !$selectedIndex->isHome()
            && ($this->isIndexOccupied($selectedIndex) || $selectedIndex->isStart())) {

            // Determine the team of the piece selected
            $team = Team::NONE; // Team of the piece that was selected
            if ($selectedIndex->isStart()) {
                foreach(Board::START_INTERSECTIONS as $color => $perim) {
                    if ($perim == $selectedIndex->getPerim()) {
                        $team = $color;
                    }
                }
            } else {
                $team = $this->indexContent($selectedIndex);
            }

            // ----- Move options -----

            $possibleActions = array_merge($this->getPossibleMoves($selectedIndex, $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices, $team));

            // ----- Start options -----

            // Allow a start if the selected index is a start zone and that start zone has a piece in it
            $startIndex = new BoardIndex(self::START_INTERSECTIONS[$team], self::MIN_SPACE); // The space where the piece would start
            if ($selectedIndex->isStart() && $drawnCard->isStart() && $this->starts[$team] > 0 && $this->indexContent($startIndex) != $team) {
                $possibleActions[] = $startIndex;
            }

            // ----- Swap and Sorry options -----

            if (($drawnCard->getCardType() == CardType::SORRY && $selectedIndex->isStart() && $this->starts[$team] > 0)
                || ($drawnCard->isSwap() && $selectedIndex->getType() == BoardIndex::PERIM_INDEX)) {

                // Test each perimeter index and see if it is a swap or sorry candidate
                foreach($this->board as $perim => $branchArray) {
                    $target = new BoardIndex($perim, self::MIN_SPACE); // Current target space to check

                    // If the target isn't the piece already selected and has a piece of another team, it is valid
                    if (!$target->equals($selectedIndex) && $this->isRealTeam($this->indexContent($target)) && $this->indexContent($target) != $team) {
                        $possibleActions[] = $target;
                    }
                }
            }

        }
        return $possibleActions;
    }

    /**
     * Determine all the possible movement actions a piece can take.
     * @param BoardIndex $selectedIndex The index to test movement operations on
     * @param Card $drawnCard The card that is currently drawn
     * @param int $forwardSpaces Spaces that the selected piece can move forwards
     * @param int $backwardSpaces Spaces that the selected piece can move backwards
     * @param array $movedIndices Indices that have moved with the current card
     * @param int $team The team that is moving
     * @return array The possible indices that can be move actions from the current selected space
     */
    private function getPossibleMoves(BoardIndex $selectedIndex, Card $drawnCard, int $forwardSpaces, int $backwardSpaces, array $movedIndices, int $team) {
        $possibleMoves = [];
        if (($drawnCard->isSplit() && (count($movedIndices) < 2 && !BoardIndex::indexInArray($selectedIndex, $movedIndices)))
            || (!$drawnCard->isSplit() && count($movedIndices) == 0)) {
            $possibleMoves = array_merge($possibleMoves, $this->testMovementDirection($selectedIndex, $drawnCard, $forwardSpaces, false, $team, $movedIndices));
            $possibleMoves = array_merge($possibleMoves, $this->testMovementDirection($selectedIndex, $drawnCard, $backwardSpaces, true, $team, $movedIndices));
        }
        return $possibleMoves;
    }

    /**
     * Determine all the possible movement spots in one direction
     * @param BoardIndex $selectedIndex Index currently selected as a starting point
     * @param Card $drawnCard Card that is currently drawn and active
     * @param int $moveAmount The amount to move (negative if backwards)
     * @param bool $backwards True if the movement direction is backwards
     * @param int $team The team that is moving
     * @param array $movedIndices Indices that have moved with the current card
     * @return array Array of all the possible movement spaces in this direction
     */
    private function testMovementDirection(BoardIndex $selectedIndex, Card $drawnCard, int $moveAmount, bool $backwards, int $team, array $movedIndices) {
        $possibleMoves = []; // Possible places the piece can be moved in this direction

        // Try moving if it would actually be moving
        if ($moveAmount != 0) {

            // Add intermediate spaces if this is a split card
            for ($i = 1; $i <= $moveAmount; $i++) {
                if ($i == $moveAmount || ($drawnCard->isSplit() && count($movedIndices) == 0)) {
                    $endResult = $this->testMove($selectedIndex, $backwards ? -$i : $i);
                    if ($endResult != null) {
                        $possibleMove = $endResult[self::TEST_MOVE_INDEX_KEY];
                        if ($endResult[self::TEST_MOVE_SLIDE_KEY] != -1) {
                            $possibleMove = new BoardIndex($endResult[self::TEST_MOVE_SLIDE_KEY], $possibleMove->getBranch());
                        }
                        if ($this->indexContent($possibleMove) != $team) {
                            $possibleMoves = $this->addToIndexArray($possibleMoves, $possibleMove);
                        }
                    }
                }
            }
        }
        return $possibleMoves;
    }

    /**
     * Add an index to an array of board indices without duplicates.
     * @param array $array Array to add the index to
     * @param Boardindex $index Index to add to the array
     * @return array Resulting array after addition
     */
    private function addToIndexArray(array $array, Boardindex $index) {
        $newArray = $array; // New array to return after possible insertion

        // Determine if this index is already in the array
        $exists = false; // Does this index already exist in the array?
        foreach($newArray as $existingIndex) {
            if ($index->equals($existingIndex)) {
                $exists = true;
                break;
            }
        }

        // If the array doesn't already exist, add it
        if (!$exists) {
            $newArray[] = $index;
        }

        return $newArray;
    }

    /**
     * Determine if is team can do a normal move operation.
     * @param int $team Team whose turn it is
     * @param Card $drawnCard Card that was drawn
     * @param int $forwardSpaces Spaces that the selected piece can move forwards
     * @param int $backwardSpaces Spaces that the selected piece can move backwards
     * @param array $movedIndices Indices that have moved with the current card
     * @return bool True if this team can do a normal move operation
     */
    public function canTeamNormalMove(int $team, Card $drawnCard = null, int $forwardSpaces, int $backwardSpaces, array $movedIndices) {
        if ($this->isRealTeam($team) && $drawnCard != null) {
            foreach ($this->getActivePieces() as $piece) {
                if ($piece[self::STATE_TEAM_KEY] == $team && count($this->getPossibleMoves($piece[self::STATE_INDEX_KEY], $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices, $team)) > 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Slide a piece along a slide if it is on a slide.
     * @param BoardIndex $index Index to slide from
     * @return bool
     */
    private function slide(BoardIndex $index) {
        $slidePerim = $index->getPerim();
        if (isset(self::SLIDE_SPACES[$slidePerim])) {
            $slideDist = self::SLIDE_SPACES[$slidePerim][self::SLIDE_DIST_KEY];
            $team = $this->indexContent($index);

            // Return pieces passed by a slide if possible
            if ($this->isRealTeam($team) && self::SLIDE_SPACES[$slidePerim][self::SLIDE_TEAM_KEY] != $team) {

                // Pick up the piece
                $this->clearIndex(new BoardIndex($slidePerim, self::MIN_SPACE));

                // Return pieces in the slide's path
                for ($i = 1; $i <= $slideDist; $i++) {
                    $this->returnPiece(new BoardIndex(($slidePerim + $i) % (self::MAX_PERIM_SPACE + 1), self::MIN_SPACE));
                }

                // Place piece at the end of the slide
                $this->setIndex(new BoardIndex(($slidePerim + $slideDist) % (self::MAX_PERIM_SPACE + 1), self::MIN_SPACE), $team);

                return true;
            }
        }
        return false;
    }

    /**
     * Determine if a team can move.
     * @param int $team Team to test on
     * @param Card $drawnCard The card that's drawn
     * @param int $forwardSpaces Spaces that the team can move forwards
     * @param int $backwardSpaces Spaces that the team can move backwards
     * @param array $movedIndices Indices of pieces that have moved during this card
     * @return bool True if the team can move
     */
    public function canMove(int $team, Card $drawnCard = null, int $forwardSpaces, int $backwardSpaces, array $movedIndices) {
        if ($drawnCard != null) {
            // Test if team can start
            if ($drawnCard->isStart() && $this->starts[$team] > 0) {
                return true;
            }

            // Testing split movement different than for normal moves
            if ($drawnCard->isSplit()) {

                // ----- Find two pieces for split ------

                // Generate move sets for all active pieces of the designated team
                $moveSets = [];
                foreach ($this->getActivePieces() as $index) {
                    if ($this->indexContent($index[self::STATE_INDEX_KEY]) == $team) {
                        $moveSets[] = [$index[self::STATE_INDEX_KEY], $this->getPossibleMoves($index[self::STATE_INDEX_KEY], $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices, $team)];
                    }
                }

                $potentialMovePieces = $movedIndices; // Potential indices for the move

                for ($i = count($potentialMovePieces); $i < 2; $i++) {
                    $largestAvailable = null; // Move set with the most available moves that hasn't already moved
                    foreach ($moveSets as $moveSet) {
                        if (!BoardIndex::indexInArray($moveSet[0], $potentialMovePieces) && ($largestAvailable == null || count($moveSet[1]) > count($largestAvailable[1]))) {
                            $largestAvailable = $moveSet;
                        }
                    }
                    if ($largestAvailable != null) {
                        $potentialMovePieces[] = $largestAvailable[0];
                    }
                }

                // ----- Determine if allowed to move -----
                $totalMoves = 0;
                foreach ($potentialMovePieces as $piece) {
                    if ($piece->isValid()) {
                        $totalMoves += count($this->getPossibleMoves($piece, $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices, $team));
                    }
                }

                return $totalMoves > 0 &&
                    ($drawnCard->getForwardSpaces() > 0 && $totalMoves >= $drawnCard->getForwardSpaces())
                    || ($drawnCard->getBackwardSpaces() > 0 &&$totalMoves >= $drawnCard->getBackwardSpaces());
            } else {
                foreach ($this->getActivePieces() as $piece) {
                    $pieceIndex = $piece[self::STATE_INDEX_KEY];
                    $pieceTeam = $piece[self::STATE_TEAM_KEY];
                    if (($pieceTeam == $team && count($this->getPossibleActions($pieceIndex, $drawnCard, $forwardSpaces, $backwardSpaces, $movedIndices)) > 0)
                        || ($pieceTeam != $team && $drawnCard->getCardType() == CardType::SORRY && $this->starts[$team] > 0 && $pieceIndex->getType() == BoardIndex::PERIM_INDEX)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Start a piece from the start zone.
     * @param int $team Team that is starting a piece
     * @return bool True if the piece was started
     */
    public function start(int $team) {
        if ($this->isRealTeam($team) && $this->starts[$team] > 0) {
            // Spot the starting will move the piece to
            $startSpot = new BoardIndex(self::START_INTERSECTIONS[$team], self::MIN_SPACE);

            // If there is a piece at the starting spot, move it back to start
            $this->returnPiece($startSpot);

            // Move the piece from the start zone to the starting spot
            $this->starts[$team]--;
            $this->setIndex($startSpot, $team);

            return true;
        }
        return false;
    }

    /**
     * Swap the pieces at 2 indices.
     * @param BoardIndex $initIndex The index of the piece initiating the swap
     * @param BoardIndex $targetSwap The index of the piece that is the target of the swap
     * @return bool True if the swap was successful
     */
    public function swap(BoardIndex $initIndex, BoardIndex $targetSwap) {
        // Swap must be between two pieces on the perimeter on different teams
        if ($initIndex->getType() == BoardIndex::PERIM_INDEX && $targetSwap->getType() == BoardIndex::PERIM_INDEX
            && $this->isRealTeam($this->indexContent($initIndex)) && $this->isRealTeam($this->indexContent($targetSwap))
            && $this->indexContent($initIndex) != $this->indexContent($targetSwap)) {

            $temp = $this->indexContent($targetSwap); // Save value in the target Index
            $this->setIndex($targetSwap, $this->indexContent($initIndex)); // Copy initiator content to target index
            $this->setIndex($initIndex, $temp); // Set initiator to the saved target content

            // Attempt to slide both swapped pieces
            $this->slide($initIndex);
            $this->slide($targetSwap);

            return true;
        }
        return false;
    }

    /**
     * Perform a sorry action from a sorry card.
     * @param BoardIndex $targetIndex The index of the sorry victim
     * @param int $team Team performing the sorry action
     * @return bool True if the sorry was successful
     */
    public function sorry(BoardIndex $targetIndex, int $team) {
        // Can only perform a sorry if the attacking team has a piece in their start zone and the space
        // they're moving to has a piece of the opposite team
        if ($this->isRealTeam($team) && $this->starts[$team] > 0 && $targetIndex->getType() == BoardIndex::PERIM_INDEX
            && $this->isIndexOccupied($targetIndex) && $this->indexContent($targetIndex) != $team) {

            // Perform the sorry
            $this->returnPiece($targetIndex);
            $this->setIndex($targetIndex, $team);
            $this->starts[$team]--;

            // Attempt to slide the piece after sorry
            $this->slide($targetIndex);

            return true;
        }
        return false;
    }

    /**
     * Move a piece from one index to another and handle corresponding events.
     * @param BoardIndex $startIndex Index to move from
     * @param int $moveAmount Amount of spaces to move (negative values go backwards)
     * @return BoardIndex|null The index that was moved to
     */
    public function move(BoardIndex $startIndex, int $moveAmount) {
        $result = $this->testMove($startIndex, $moveAmount); // Result of the move
        if ($result != null && $result[self::TEST_MOVE_INDEX_KEY]->isValid()) {
            $resultSlide = $result[self::TEST_MOVE_SLIDE_KEY]; // Perimeter part of the slide that was used

            // Index the move would end at without sliding
            $resultIndex = $resultSlide == -1
                ? $result[self::TEST_MOVE_INDEX_KEY]
                : new BoardIndex($result[self::TEST_MOVE_SLIDE_KEY], self::MIN_SPACE);

            // Pick up the piece and place it at the new location. Return any piece that was there
            $startTeam = $this->indexContent($startIndex);
            $this->clearIndex($startIndex);
            $this->returnPiece($resultIndex);
            $this->setIndex($resultIndex, $startTeam);

            // Attempt to slide the piece
            $this->slide($resultIndex);

            // If the move resulted in moving to a home, remove it from the board and add 1 to that team's home count
            if ($resultIndex->isHome()) {
                $this->homes[$this->indexContent($resultIndex)]++;
                $this->clearIndex($resultIndex);
            }
            return $result[self::TEST_MOVE_INDEX_KEY];
        }
        return null;
    }

    /**
     * Determine the index that a move will go to and if a slide happened.
     * @param BoardIndex $startIndex Index to start at
     * @param int $moveAmount Number of spaces to move
     * @return array Index that the move will go to and if the move caused a slide. Returns null if move is impossible
     */
    private function testMove(BoardIndex $startIndex, int $moveAmount) {
        $resultIndex = null; // Index the move will result at
        $slide = -1; // Slide taken during the move

        // Moves can only happen from active spaces on the board
        if ($startIndex->isActiveIndex()) {
            if ($startIndex->getType() == BoardIndex::PERIM_INDEX) {
                $team = $this->indexContent($startIndex); // Team performing the move

                // Amount the move would move the piece past their intersection if they didn't turn
                $passIntersectionAmount = $this->passIntersectionAmount($startIndex, $moveAmount, $team);

                // Tentative new perimeter portion the move will result in
                $newPerim = ($startIndex->getPerim() + $moveAmount - $passIntersectionAmount) % (self::MAX_PERIM_SPACE + 1);
                if ($newPerim < 0) {
                    $newPerim = self::MAX_PERIM_SPACE + 1 - (-$newPerim);
                }

                // New index after moving
                $newIndex = new BoardIndex(
                    $newPerim < 0 ? self::MAX_PERIM_SPACE + 1 + $newPerim + $startIndex->getPerim() : $newPerim,
                    $startIndex->getBranch() + $passIntersectionAmount
                );

                // Adjust new index if ended on a slide
                if ($newIndex->getType() == BoardIndex::PERIM_INDEX
                    && array_key_exists($newIndex->getPerim(), self::SLIDE_SPACES)
                    && self::SLIDE_SPACES[$newIndex->getPerim()][self::SLIDE_TEAM_KEY] != $team) {

                    $slide = $newIndex->getPerim();
                    $newIndex = new BoardIndex(
                        ($slide + self::SLIDE_SPACES[$slide][self::SLIDE_DIST_KEY]) % (self::MAX_PERIM_SPACE + 1),
                        $newIndex->getBranch()
                    );
                }
                $resultIndex = $newIndex->isValid() ? $newIndex : null;
            } else if ($startIndex->getType() == BoardIndex::SAFE_INDEX && !$startIndex->isHome()) {
                if ($moveAmount >= 0) {
                    $endBranch = $startIndex->getBranch() + $moveAmount; // Branch index portion after moving forward
                    if ($endBranch <= self::MAX_SAFE_SPACE) {
                        $resultIndex =  new BoardIndex($startIndex->getPerim(), $endBranch);
                    }
                } else {
                    $endBranch = $startIndex->getBranch() + $moveAmount;
                    if ($endBranch >= self::MIN_SPACE) {
                        $resultIndex =  new BoardIndex($startIndex->getPerim(), $startIndex->getBranch() + $moveAmount);
                    } else {
                        $endPerim = $startIndex->getPerim() + $endBranch;
                        if ($endPerim < 0) {
                            $endPerim = self::MAX_PERIM_SPACE + 1 + $endBranch;
                        }
                        $resultIndex =  new BoardIndex($endPerim, self::MIN_SPACE);
                    }
                }
            }
        }

        // If there was a valid move, return the results
        if ($resultIndex != null) {
            return [
                self::TEST_MOVE_INDEX_KEY => $resultIndex,
                self::TEST_MOVE_SLIDE_KEY => $slide
            ];
        }
        return null;
    }

    /**
     * Determines the number of spaces a piece will move past its safe zone intersection if it didn't turn.
     * @param BoardIndex $startIndex Index of the start of the move
     * @param int $moveAmount Number of spaces to move
     * @param int $team making the move
     * @return int Number of spaces moved passed the safe zone intersection. Returns 0 if it's not passed.
     */
    private static function passIntersectionAmount(BoardIndex $startIndex, int $moveAmount, int $team) {
        if ($moveAmount > 0 && $startIndex->getType() == BoardIndex::PERIM_INDEX && self::isRealTeam($team)) {
            $intersection = self::SAFE_INTERSECTIONS[$team]; // Perim of the intersection for this team's safe zone
            $startPerim = $startIndex->getPerim(); // Perim of the start index
            $endPerim = ($startPerim + $moveAmount) % (self::MAX_PERIM_SPACE + 1); // End perim without turn

            // Determine if the intersection was passed if the move happened without turning
            $passedIntersection = (
                // There was no wrap
                ($intersection >= $startPerim && $intersection < $endPerim)

                // There was a wrap, check fom beginning to max and from min to end
                || ($startPerim > $endPerim
                    && (
                        $intersection >= $startPerim && $startPerim <= self::MAX_PERIM_SPACE
                        || $intersection >= self::MIN_SPACE && $intersection < $endPerim)
                    )
            );

            // If the intersection was passed, determine by how much
            if ($passedIntersection) {
                $amountPassed = $endPerim - self::SAFE_INTERSECTIONS[$team];
                if ($amountPassed < 0) {
                    $amountPassed = self::MAX_PERIM_SPACE + 1 - (-$amountPassed);
                }
                return $amountPassed;
            }
        }
        return 0;
    }

    /**
     * Determine the possible distances between two indexes.
     * @param BoardIndex $start start index
     * @param BoardIndex $end end index
     * @param int $team The team that is performing the move
     * @return array possible distances between two indexes
     */
    public static function indexDistance(BoardIndex $start, BoardIndex $end, int $team) {
        $startType = $start->getType(); // The type of the start index
        $endType = $end->getType(); // The type of the end index
        $startPerim = $start->getPerim(); // Perimeter portion of the start index
        $startBranch = $start->getBranch(); // Branch portion of the start index
        $endPerim = $end->getPerim(); // Perimeter portion of the end index
        $endBranch = $end->getBranch(); // Branch portion of the end index
        $possibleDistances = []; // Possible moves to get to this location

        // The team who owns the end index
        $endTeam = Team::NONE;
        $startTeam = Team::NONE;
        foreach (self::SAFE_INTERSECTIONS as $key => $value) {
            if ($value == $startPerim && $startType == BoardIndex::SAFE_INDEX) {
                $startTeam = $key;
            }
            if ($value == $endPerim && $endType == BoardIndex::SAFE_INDEX) {
                $endTeam = $key;
            }
        }

        // The team performing the move must be allowed on the beginning and end spaces
        if (($startTeam == Team::NONE || $startTeam == $team) && ($endTeam == Team::NONE || $endTeam == $team) && !$start->isHome() && !$start->equals($end)) {
            if ($startType == BoardIndex::PERIM_INDEX && $endType == BoardIndex::PERIM_INDEX) {
                // Possible get to a index forwards AND/OR backwards
                $possibleDistances = self::perimIndexDistance($start, $end, $team);
            } else if ($startType == BoardIndex::SAFE_INDEX && $endType == BoardIndex::SAFE_INDEX) {
                // Can get to index forward OR backwards
                $distance = $endBranch - $startBranch;
                $possibleDistances[$distance > 0 ? self::DIST_FORWARD_KEY : self::DIST_BACKWARDS_KEY] = $distance;
            } else if ($startType == BoardIndex::PERIM_INDEX && $endType == BoardIndex::SAFE_INDEX && $team == $endTeam) {
                // Can only get to index forward
                $perimDistances = self::perimIndexDistance($start, new BoardIndex(self::SAFE_INTERSECTIONS[$team], self::MIN_SPACE), $team);
                if (isset($perimDistances[self::DIST_FORWARD_KEY])) {
                    $possibleDistances[self::DIST_FORWARD_KEY] = $perimDistances[self::DIST_FORWARD_KEY] + $endBranch;
                } else if ($start->equals(new BoardIndex(self::SAFE_INTERSECTIONS[$team], self::MIN_SPACE))) {
                    $possibleDistances[self::DIST_FORWARD_KEY] = $endBranch;
                }
            } else if ($startType == BoardIndex::SAFE_INDEX && $endType == BoardIndex::PERIM_INDEX) {
                // Can only get to index backward
                $perimDistances = self::perimIndexDistance(new BoardIndex(self::SAFE_INTERSECTIONS[$team], self::MIN_SPACE), $end, $team);
                $possibleDistances[self::DIST_BACKWARDS_KEY] = -$startBranch;
                if (isset($perimDistances[self::DIST_BACKWARDS_KEY])) {
                    $possibleDistances[self::DIST_BACKWARDS_KEY] += $perimDistances[self::DIST_BACKWARDS_KEY] ;
                }
            }
        }

        return $possibleDistances;
    }

    /**
     * Determine the possible distances between two perimeter indices.
     * @param Boardindex $start Start perimeter index
     * @param BoardIndex $end End perimeter index
     * @param int $team Team that is performing the move
     * @return array possible distances between the two perimeter indices
     */
    private static function perimIndexDistance(Boardindex $start, BoardIndex $end, int $team) {
        $startPerim = $start->getPerim(); // Perimeter portion of the start index
        $endPerim = $end->getPerim(); // Perimeter portion of the end index
        $possibleDistances = []; // Possible distances between the perimeter indices

        if ($start->getType() == BoardIndex::PERIM_INDEX && $end->getType() == BoardIndex::PERIM_INDEX && !$start->equals($end)) {
            // Forward
            $forwardDist = $endPerim > $startPerim
                ? $endPerim - $startPerim : (self::MAX_PERIM_SPACE + 1 - $startPerim) + $endPerim;
            if (self::passIntersectionAmount($start, $forwardDist, $team) == 0) {
                $possibleDistances[self::DIST_FORWARD_KEY] = $forwardDist;
            }

            // Backward
            $possibleDistances[self::DIST_BACKWARDS_KEY] = $endPerim < $startPerim
                ? $endPerim - $startPerim : -(self::MAX_PERIM_SPACE + 1 - ($endPerim - $startPerim));
        }

        return $possibleDistances;
    }

    /**
     * Remove a team from the game.
     * @param int $team Team to remove from the game
     */
    public function removeTeam(int $team) {
        $this->homes[$team] = 0;
        $this->starts[$team] = 0;
        foreach($this->getActivePieces() as $piece) {
            if ($piece[self::STATE_TEAM_KEY] == $team) {
                $this->clearIndex($piece[self::STATE_INDEX_KEY]);
            }
        }
    }

    /**
     * Determine the team that has won the game.
     * @return int The team that has won the game
     */
    public function hasWon() {
        foreach ($this->homes as $team => $amount) {
            if ($amount >= self::PIECES_PER_TEAM) {
                return $team;
            }
        }
        return Team::NONE;
    }

    /**
     * Determine of there is a piece at the provided index.
     * @param BoardIndex $index Index to test
     * @return bool True if there is a piece at the provided index
     */
    private function isIndexOccupied(BoardIndex $index) {
        return $index->isValid() && $this->indexContent($index) != Team::NONE;
    }

    /**
     * Get the content on the board at the provided index
     * @param BoardIndex $index The index to get the content from
     * @return int The content on the board at the provided index
     */
    public function indexContent(BoardIndex $index) {
        return $index->isValid() && isset($this->board[$index->getPerim()][$index->getBranch()])
            ? $this->board[$index->getPerim()][$index->getBranch()] : Team::NONE;
    }

    /**
     * Set the contents of a position on the board.
     * @param BoardIndex $index Index to set
     * @param int $team Team value to set the index to
     * @return bool True of the index was set
     */
    private function setIndex(BoardIndex $index, int $team) {
        if ($index->isValid() && $this->isRealTeam($team)) {
            $this->board[$index->getPerim()][$index->getBranch()] = $team;
            return true;
        }
        return false;
    }

    /**
     * Clear an index on the board
     * @param BoardIndex $index Index to clear
     * @return bool True if index was cleared
     */
    private function clearIndex(BoardIndex $index) {
        // Only clear if the index is valid
        if ($index->isValid()) {
            // Unset the branch part
            unset($this->board[$index->getPerim()][$index->getBranch()]);

            // If there are no other pieces branch off of this one, unset the perimeter part too
            if (empty($this->board[$index->getPerim()])) {
                unset($this->board[$index->getPerim()]);
            }
            return true;
        }
        return false;
    }

    /**
     * Return a piece on the perimeter to its home.
     * @param BoardIndex $index Index of the piece to return
     * @return bool True if the piece was returned
     */
    private function returnPiece(BoardIndex $index) {
        if ($index->isValid() && !$index->isStart() && !$index->isHome() && $this->isRealTeam($this->indexContent($index))) {
            $this->starts[$this->indexContent($index)]++;
            $this->clearIndex($index);
            return true;
        }
        return false;
    }

    /**
     * Determine if a team value corresponds to a real team in the game.
     * @param int $team Team value to test
     * @return bool True if the team value corresponds to a real team in the game
     */
    public static function isRealTeam(int $team) {
        return $team == Team::YELLOW
            || $team == Team::RED
            || $team == Team::BLUE
            || $team == Team::GREEN;
    }

    /**
     * Get the status of all the active pieces on the board.
     * @return array The status of all the active pieces on the board
     */
    public function getActivePieces() {
        $boardStatus = [];
        foreach ($this->board as $perim => $branchDim) {
            foreach ($branchDim as $branch => $team) {
                $index = new BoardIndex($perim, $branch);
                $boardStatus[] = [
                    self::STATE_INDEX_KEY => $index,
                    self::STATE_TEAM_KEY => $team
                ];
            }
        }
        return $boardStatus;
    }

    /**
     * Get the status of all the start zones.
     * @return array The state of all the start zones
     */
    public function getStarts() {
        return $this->starts;
    }

    /**
     * Get the status of all the home zones.
     * @return array The status of all the home zones
     */
    public function getHomes() {
        return $this->homes;
    }
}