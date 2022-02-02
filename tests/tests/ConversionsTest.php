<?php


class ConversionsTest extends \PHPUnit\Framework\TestCase {
    public function test_spaceToIndex() {
        //
        // Test perimeter spaces
        //

        // ----- Corners -----

        // Top left corner
        $this->assertTrue(Sorry\Conversions::spaceToIndex(0)->equals(new Sorry\BoardIndex(56, 0)));

        // Top right corner
        $this->assertTrue(Sorry\Conversions::spaceToIndex(15)->equals(new Sorry\BoardIndex(11, 0)));

        // Bottom right corner
        $this->assertTrue(Sorry\Conversions::spaceToIndex(255)->equals(new Sorry\BoardIndex(26, 0)));

        // Bottom left corner
        $this->assertTrue(Sorry\Conversions::spaceToIndex(240)->equals(new Sorry\BoardIndex(41, 0)));

        // ----- Side Spaces -----

        // Top side before wraparound
        $this->assertTrue(Sorry\Conversions::spaceToIndex(3)->equals(new Sorry\BoardIndex(59, 0)));

        // Top side at wraparound
        $this->assertTrue(Sorry\Conversions::spaceToIndex(4)->equals(new Sorry\BoardIndex(0, 0)));

        // Top side after wraparound
        $this->assertTrue(Sorry\Conversions::spaceToIndex(5)->equals(new Sorry\BoardIndex(1, 0)));

        // Right side
        $this->assertTrue(Sorry\Conversions::spaceToIndex(111)->equals(new Sorry\BoardIndex(17, 0)));

        // Bottom side
        $this->assertTrue(Sorry\Conversions::spaceToIndex(249)->equals(new Sorry\BoardIndex(32, 0)));

        // Left side
        $this->assertTrue(Sorry\Conversions::spaceToIndex(144)->equals(new Sorry\BoardIndex(47, 0)));

        //
        // Test start zones
        //

        // ----- Yellow Start Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(19)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(20)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(21)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(35)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(36)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(37)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(51)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(52)->equals(new Sorry\BoardIndex(0, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(53)->equals(new Sorry\BoardIndex(0, 1)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(54));

        // ----- Green Start Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(60)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(61)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(62)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(76)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(77)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(78)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(92)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(93)->equals(new Sorry\BoardIndex(15, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(94)->equals(new Sorry\BoardIndex(15, 1)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(110));

        // ----- Red Start Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(202)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(203)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(204)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(218)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(219)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(220)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(234)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(235)->equals(new Sorry\BoardIndex(30, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(236)->equals(new Sorry\BoardIndex(30, 1)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(188));

        // ----- Blue Start Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(161)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(162)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(163)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(177)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(178)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(179)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(193)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(194)->equals(new Sorry\BoardIndex(45, 1)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(195)->equals(new Sorry\BoardIndex(45, 1)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(196));

        //
        // Test home zones
        //

        // ----- Yellow Safe Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(97)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(98)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(99)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(113)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(114)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(115)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(129)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(130)->equals(new Sorry\BoardIndex(58, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(131)->equals(new Sorry\BoardIndex(58, 6)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(132));

        // ----- Green Safe Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(23)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(24)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(25)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(39)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(40)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(41)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(55)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(56)->equals(new Sorry\BoardIndex(13, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(57)->equals(new Sorry\BoardIndex(13, 6)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(58));

        // ----- Red Safe Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(124)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(125)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(126)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(140)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(141)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(142)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(156)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(157)->equals(new Sorry\BoardIndex(28, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(158)->equals(new Sorry\BoardIndex(28, 6)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(174));

        // ----- Blue Safe Zone -----

        // Start zone spaces
        $this->assertTrue(Sorry\Conversions::spaceToIndex(198)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(199)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(200)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(214)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(215)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(216)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(230)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(231)->equals(new Sorry\BoardIndex(43, 6)));
        $this->assertTrue(Sorry\Conversions::spaceToIndex(232)->equals(new Sorry\BoardIndex(43, 6)));

        // Next to start zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(182));

        //
        // Test safe zones
        //

        // ----- Yellow Safe Zone -----

        // Fist safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(18)->equals(new Sorry\BoardIndex(58, 1)));

        // Middle safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(50)->equals(new Sorry\BoardIndex(58, 3)));

        // Last safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(82)->equals(new Sorry\BoardIndex(58, 5)));

        // Too far into safe zone
        $this->assertTrue(Sorry\Conversions::spaceToIndex(98)->equals(new Sorry\BoardIndex(58, 6)));

        // Next to safe zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(65));

        // ----- Green Safe Zone -----

        // Fist safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(46)->equals(new Sorry\BoardIndex(13, 1)));

        // Middle safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(44)->equals(new Sorry\BoardIndex(13, 3)));

        // Last safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(42)->equals(new Sorry\BoardIndex(13, 5)));

        // Too far into safe zone
        $this->assertTrue(Sorry\Conversions::spaceToIndex(41)->equals(new Sorry\BoardIndex(13, 6)));

        // Next to safe zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(27));

        // ----- Red Safe Zone -----

        // Fist safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(237)->equals(new Sorry\BoardIndex(28, 1)));

        // Middle safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(205)->equals(new Sorry\BoardIndex(28, 3)));

        // Last safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(173)->equals(new Sorry\BoardIndex(28, 5)));

        // Too far into safe zone
        $this->assertTrue(Sorry\Conversions::spaceToIndex(157)->equals(new Sorry\BoardIndex(28, 6)));

        // Next to safe zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(190));

        // ----- Blue Safe Zone -----

        // Fist safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(209)->equals(new Sorry\BoardIndex(43, 1)));

        // Middle safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(211)->equals(new Sorry\BoardIndex(43, 3)));

        // Last safe space
        $this->assertTrue(Sorry\Conversions::spaceToIndex(213)->equals(new Sorry\BoardIndex(43, 5)));

        // Too far into safe zone
        $this->assertTrue(Sorry\Conversions::spaceToIndex(214)->equals(new Sorry\BoardIndex(43, 6)));

        // Next to safe zone
        $this->assertNull(Sorry\Conversions::spaceToIndex(228));
    }

    public function test_indexToSpace() {
        //
        // Test Invalid spaces
        //

        // Invalid index
        $this->assertTrue($this->equalArrays([], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(60, 0))));

        //
        // Test Start Zones
        //

        // Yellow start zone
        $this->assertTrue($this->equalArrays([19,20,21,35,36,37,51,52,53], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(0, 1))));

        // Green start zone
        $this->assertTrue($this->equalArrays([60,61,62,76,77,78,92,93,94], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(15, 1))));

        // Red start zone
        $this->assertTrue($this->equalArrays([202,203,204,218,219,220,234,235,236], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(30, 1))));

        // Blue start zone
        $this->assertTrue($this->equalArrays([161,162,163,177,178,179,193,194,195], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(45, 1))));

        //
        // Test Home Zones
        //

        // Yellow safe zone
        $this->assertTrue($this->equalArrays([97,98,99,113,114,115,129,130,131], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(58, 6))));

        // Green safe zone
        $this->assertTrue($this->equalArrays([23,24,25,39,40,41,55,56,57], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(13, 6))));

        // Red safe zone
        $this->assertTrue($this->equalArrays([124,125,126,140,141,142,156,157,158], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(28, 6))));

        // Blue safe zone
        $this->assertTrue($this->equalArrays([198,199,200,214,215,216,230,231,232], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(43, 6))));

        //
        // Test perimeter indices
        //

        // ----- Corners -----

        // Top left corner
        $this->assertTrue($this->equalArrays([0], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(56, 0))));

        // Top right corner
        $this->assertTrue($this->equalArrays([15], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(11, 0))));

        // Bottom right corner
        $this->assertTrue($this->equalArrays([255], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(26, 0))));

        // Bottom left corner
        $this->assertTrue($this->equalArrays([240], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(41, 0))));


        // ----- Side spaces -----

        // Side before wraparound
        $this->assertTrue($this->equalArrays([3], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(59, 0))));

        // Side at wraparound
        $this->assertTrue($this->equalArrays([4], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(0, 0))));

        // Side after wraparound
        $this->assertTrue($this->equalArrays([5], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(1, 0))));

        // Right side
        $this->assertTrue($this->equalArrays([127], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(18, 0))));

        // Bottom side
        $this->assertTrue($this->equalArrays([248], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(33, 0))));

        // Left side
        $this->assertTrue($this->equalArrays([112], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(49, 0))));

        //
        // Test Safe spaces
        //

        // ----- Yellow safe spaces -----

        // Fist safe space
        $this->assertTrue($this->equalArrays([18], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(58, 1))));

        // Middle safe space
        $this->assertTrue($this->equalArrays([50], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(58, 3))));

        // Last safe space
        $this->assertTrue($this->equalArrays([82], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(58, 5))));

        // ----- Green safe spaces -----

        // Fist safe space
        $this->assertTrue($this->equalArrays([46], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(13, 1))));

        // Middle safe space
        $this->assertTrue($this->equalArrays([44], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(13, 3))));

        // Last safe space
        $this->assertTrue($this->equalArrays([42], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(13, 5))));

        // ----- Red safe spaces -----

        // Fist safe space
        $this->assertTrue($this->equalArrays([237], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(28, 1))));

        // Middle safe space
        $this->assertTrue($this->equalArrays([205], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(28, 3))));

        // Last safe space
        $this->assertTrue($this->equalArrays([173], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(28, 5))));

        // ----- Blue safe spaces -----

        // Fist safe space
        $this->assertTrue($this->equalArrays([209], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(43, 1))));

        // Middle safe space
        $this->assertTrue($this->equalArrays([211], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(43, 3))));

        // Last safe space
        $this->assertTrue($this->equalArrays([213], Sorry\Conversions::indexToSpaces(new Sorry\BoardIndex(43, 5))));
    }

    /**
     * Determine if two arrays have equal contents/
     * @param array $array1 The first array to compare
     * @param array $array2 The second array to compare
     * @return bool True of the arrays have the same contents
     */
    private function equalArrays(array $array1, array $array2) {
        return count($array1) == count($array2) && !array_diff($array1, $array2);
    }
}