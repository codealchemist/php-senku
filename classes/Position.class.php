<?php
/**
 * This class represents a board position.
 * Think of the board as a 7x7 square.
 * Divide that square with 7 rows and columns.
 * This way we get 49 positions.
 * Some positions will have holes, other won't be used at all.
 *
 * @author Alberto Miranda <alberto.php@gmail.com>
 */
class Position {
    private $row;
    private $column;
    private $hole;

    public function  __construct($row, $column, Hole $hole = NULL) {
        $this->setRow($row);
        $this->setColumn($column);
        if($hole) $this->setHole($hole);
    }

    public function setRow($row){
        $this->row = $row;
    }

    public function getRow(){
        return $this->row;
    }

    public function setColumn($column){
        $this->column = $column;
    }

    public function getColumn(){
        return $this->column;
    }

    public function getHole(){
        return $this->hole;
    }

    public function setHole(Hole $hole){
        $this->hole = $hole;
    }

    public function removeHole(){
        unset($this->hole);
    }

    public function getPositionId(){
        $row    = $this->getRow();
        $column = $this->getColumn();
        return "$row$column";
    }
}
