<?php

class BoardIndexTest extends \PHPUnit\Framework\TestCase {
    public function test_construct() {
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $this->assertInstanceOf('Sorry\BoardIndex', $boardIndex);
    }

    public function test_equals() {
        // Equal indices
        $boardIndex1 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $boardIndex2 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $this->assertTrue($boardIndex1->equals($boardIndex2));

        // Perimeter is unequal only
        $boardIndex1 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $boardIndex2 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE + 1, Sorry\Board::MIN_SPACE);
        $this->assertFalse($boardIndex1->equals($boardIndex2));

        // Branch is unequal only
        $boardIndex1 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $boardIndex2 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE + 1);
        $this->assertFalse($boardIndex1->equals($boardIndex2));

        // Both dimensions unequal
        $boardIndex1 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $boardIndex2 = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE + 1, Sorry\Board::MIN_SPACE + 1);
        $this->assertFalse($boardIndex1->equals($boardIndex2));
    }

    public function test_getPerim() {
        //
        // Test valid index
        //

        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE);
        $this->assertEquals(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], $boardIndex->getPerim());

        //
        // Test invalid index
        //

        // Perimeter invalid only
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE - 5);
        $this->assertEquals(-1, $boardIndex->getPerim());

        // Branch invalid only
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 5, Sorry\Board::MIN_SPACE);
        $this->assertEquals(-1, $boardIndex->getPerim());

        // Perimeter and branch invalid
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 5, Sorry\Board::MIN_SPACE - 5);
        $this->assertEquals(-1, $boardIndex->getPerim());
    }

    public function test_getBranch() {
        //
        // Test valid index
        //
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE);
        $this->assertEquals(Sorry\Board::MAX_SAFE_SPACE, $boardIndex->getBranch());

        //
        // Test invalid index
        //

        // Perimeter invalid only
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE - 5);
        $this->assertEquals(-1, $boardIndex->getBranch());

        // Branch invalid only
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 5, Sorry\Board::MIN_SPACE);
        $this->assertEquals(-1, $boardIndex->getBranch());

        // Perimeter and branch invalid
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 5, Sorry\Board::MIN_SPACE - 5);
        $this->assertEquals(-1, $boardIndex->getBranch());
    }

    public function test_isValid() {
        //
        // Test valid index
        //

        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE);
        $this->assertTrue($boardIndex->isValid());

        //
        // Test invalid index
        //

        // Perimeter invalid only
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE - 5);
        $this->assertFalse($boardIndex->isValid());

        // Branch invalid only
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 5, Sorry\Board::MIN_SPACE);
        $this->assertFalse($boardIndex->isValid());

        // Perimeter and branch invalid
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 5, Sorry\Board::MIN_SPACE - 5);
        $this->assertFalse($boardIndex->isValid());
    }

    public function test_getType() {
        //
        // Test perimeter dimension
        //

        $boardIndex = new Sorry\BoardIndex(-1, 0);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(0, 0);
        $this->assertEquals(Sorry\BoardIndex::PERIM_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(60, 0);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(59, 0);
        $this->assertEquals(Sorry\BoardIndex::PERIM_INDEX, $boardIndex->getType());

        //
        // Test branch dimension
        //

        // On a safe intersection
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE - 1);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE);
        $this->assertEquals(Sorry\BoardIndex::PERIM_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE + 1);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE);
        $this->assertEquals(Sorry\BoardIndex::SAFE_INDEX, $boardIndex->getType());

        // On a start intersection
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE - 1);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE);
        $this->assertEquals(Sorry\BoardIndex::PERIM_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_START_SPACE + 1);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_START_SPACE);
        $this->assertEquals(Sorry\BoardIndex::START_INDEX, $boardIndex->getType());

        // Not on an intersection
        $boardIndex = new Sorry\BoardIndex(1, Sorry\Board::MIN_SPACE - 1);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(1, Sorry\Board::MIN_SPACE);
        $this->assertEquals(Sorry\BoardIndex::PERIM_INDEX, $boardIndex->getType());
        $boardIndex = new Sorry\BoardIndex(1, Sorry\Board::MIN_SPACE + 1);
        $this->assertEquals(Sorry\BoardIndex::INVALID_INDEX, $boardIndex->getType());
    }

    public function test_isActiveIndex() {
        // Invalid
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 1, Sorry\Board::MIN_SPACE - 1);
        $this->assertFalse($boardIndex->isActiveIndex());

        // Perimeter space
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $this->assertTrue($boardIndex->isActiveIndex());

        // Safe zone
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE + 1);
        $this->assertTrue($boardIndex->isActiveIndex());

        // Home
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE);
        $this->assertFalse($boardIndex->isActiveIndex());

        // Start
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_START_SPACE);
        $this->assertFalse($boardIndex->isActiveIndex());
    }

    public function test_isStart() {
        // Invalid
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 1, Sorry\Board::MIN_SPACE - 1);
        $this->assertFalse($boardIndex->isStart());

        // Perimeter space
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $this->assertFalse($boardIndex->isStart());

        // Safe zone
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE + 1);
        $this->assertFalse($boardIndex->isStart());

        // Home
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE);
        $this->assertFalse($boardIndex->isStart());

        // Start
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_START_SPACE);
        $this->assertTrue($boardIndex->isStart());
    }

    public function test_isHome() {
        // Invalid
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE - 1, Sorry\Board::MIN_SPACE - 1);
        $this->assertFalse($boardIndex->isHome());

        // Perimeter space
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::MIN_SPACE, Sorry\Board::MIN_SPACE);
        $this->assertFalse($boardIndex->isHome());

        // Safe zone
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MIN_SPACE + 1);
        $this->assertFalse($boardIndex->isHome());

        // Home
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::SAFE_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_SAFE_SPACE);
        $this->assertTrue($boardIndex->isHome());

        // Start
        $boardIndex = new Sorry\BoardIndex(Sorry\Board::START_INTERSECTIONS[Sorry\Team::BLUE], Sorry\Board::MAX_START_SPACE);
        $this->assertFalse($boardIndex->isHome());
    }

    public function test_indexInArray() {
        $indexArray = [
            new Sorry\BoardIndex(0, 0),
            new Sorry\BoardIndex(-1, -1),
            new Sorry\BoardIndex(50, 0),
        ];

        $this->assertTrue(Sorry\BoardIndex::indexInArray(new Sorry\BoardIndex(0, 0), $indexArray));
        $this->assertTrue(Sorry\BoardIndex::indexInArray(new Sorry\BoardIndex(-1, -1), $indexArray));
        $this->assertTrue(Sorry\BoardIndex::indexInArray(new Sorry\BoardIndex(50, 0), $indexArray));
        $this->assertTrue(Sorry\BoardIndex::indexInArray(new Sorry\BoardIndex(50, 1), $indexArray));
        $this->assertFalse(Sorry\BoardIndex::indexInArray(new Sorry\BoardIndex(30, 0), $indexArray));
    }

    public function test_indexArrayRemove() {
        $indexArray = [
            new Sorry\BoardIndex(0, 0),
            new Sorry\BoardIndex(-1, -1),
            new Sorry\BoardIndex(50, 0),
        ];

        // Index is not in the array, so it is not removed
        $resultArray = Sorry\BoardIndex::indexArrayRemove(new Sorry\BoardIndex(40, 0), $indexArray);
        $this->assertCount(3, $resultArray);

        // Index is in the array, so it is removed
        $resultArray = Sorry\BoardIndex::indexArrayRemove(new Sorry\BoardIndex(50, 0), $indexArray);
        $this->assertCount(2, $resultArray);
        $resultArray = Sorry\BoardIndex::indexArrayRemove(new Sorry\BoardIndex(50, 1), $indexArray);
        $this->assertCount(2, $resultArray);
    }
}