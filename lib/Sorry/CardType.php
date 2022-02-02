<?php
namespace Sorry;

/**
 * Class CardType enumeration representing all the different types of cards in a Sorry! game.
 * @package Sorry
 */
abstract class CardType {
    const NONE = 0;
    const ONE = 1;
    const TWO = 2;
    const THREE = 3;
    const FOUR = 4;
    const FIVE = 5;
    const SEVEN = 6;
    const EIGHT = 7;
    const TEN = 8;
    const ELEVEN = 9;
    const TWELVE = 10;
    const SORRY = 11;

    public static function getString(int $card) {
        $string = null;
        switch($card) {
            case self::NONE:
                $string = "";
                break;
            case self::ONE:
                $string = "1";
                break;
            case self::TWO:
                $string = "2";
                break;
            case self::THREE:
                $string = "3";
                break;
            case self::FOUR:
                $string = "4";
                break;
            case self::FIVE:
                $string = "5";
                break;
            case self::SEVEN:
                $string = "7";
                break;
            case self::EIGHT:
                $string = "8";
                break;
            case self::TEN:
                $string = "10";
                break;
            case self::ELEVEN:
                $string = "11";
                break;
            case self::TWELVE:
                $string = "12";
                break;
            case self::SORRY:
                $string = "sorry";
                break;
        }

        return $string;
    }
}

