<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This class represents a hole in the board.
 * A hole can be filled by a piece or not.
 *
 * @author Alberto Miranda <alberto.php@gmail.com>
 */
class Hole {
    private $isFilled = false;
    
    public function  __construct($isFilled = false) {
        $this->isFilled = $isFilled;
    }

    public function isFilled(){
        return $this->isFilled;
    }

    public function isEmpty(){
        return !$this->isFilled;
    }

    public function setEmpty(){
        $this->isFilled = false;
    }

    public function setFilled(){
        $this->isFilled = true;
    }
}

