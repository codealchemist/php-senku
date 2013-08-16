<?php
require_once('Hole.class.php');
require_once('Position.class.php');
require_once('Framework.class.php');

/**
 * This class represents the board.
 * It will contain positions, which will also be filled with holes,
 * where it corresponds.
 * Each hole will have a state, representing if it is filled with a piece or not.
 *
 * @author Alberto Miranda <alberto.php@gmail.com>
 */
class Board extends Framework {
    private $startRow                       = 1;
    private $endRow                         = 7;
    private $startColumn                    = 1;
    private $endColumn                      = 7;
    private $middlePiece;
    private $unusedPositions;
    private $positions;
    private $pieceRepresentation            = 'O';
    private $holeRepresentation             = '.';
    private $unusedRepresentation           = 'X';
    private $movementStartRepresentation    = '*';
    private $movementJumpRepresentation     = '*';
    private $movementEndRepresentation      = '@';
    private $lastMovement;

    public function  __construct() {
        //set unused positions
        $this->setUnusedPositions();

        $positions = array();
        for($row = $this->startRow; $row<=$this->endRow; $row++){
            for($column=$this->startColumn; $column<=$this->endColumn; $column++){
                $hole = new Hole(true);
                $position = new Position($row, $column, $hole);
                
                if(!$this->isUnusedPosition($position)) $positions["$row$column"] = $position;
            }
        }

        //set middle piece
        $position           = new Position(4, 4, new Hole);
        $positions[44]      = $position;
        $this->middlePiece  = $position;

        //set board positions
        $this->positions    = $positions;
    }

    public function getStartRow(){
        return $this->startRow;
    }

    public function getEndRow(){
        return $this->endRow;
    }

    public function getStartColumn(){
        return $this->startColumn;
    }

    public function getEndColumn(){
        return $this->endColumn;
    }

    public function getPositions(){
        return $this->positions;
    }

    private function setUnusedPositions(){
        $unusedPositions = array(
            "11" => new Position(1, 1),
            "12" => new Position(1, 2),
            "16" => new Position(1, 6),
            "17" => new Position(1, 7),
            "21" => new Position(2, 1),
            "22" => new Position(2, 2),
            "26" => new Position(2, 6),
            "27" => new Position(2, 7),
            "61" => new Position(6, 1),
            "62" => new Position(6, 2),
            "66" => new Position(6, 6),
            "67" => new Position(6, 7),
            "71" => new Position(7, 1),
            "72" => new Position(7, 2),
            "76" => new Position(7, 6),
            "77" => new Position(7, 7)
        );

        $this->unusedPositions = $unusedPositions;
    }

    public function isUnusedPosition(Position $position){
        $positionId     = $position->getPositionId();
        $unusedPosition = $this->unusedPositions[$positionId];
        if(isset($unusedPosition)) return true;
        return false;
    }

    public function printBoard($movement = false, $returnValue = false){
        $boardString = '';
        for($row = $this->startRow; $row<=$this->endRow; $row++){
            for($column=$this->startColumn; $column<=$this->endColumn; $column++){
                $position = new Position($row, $column);
                $positionRepresentation = $this->getPositionRepresentation($position, $movement);
                $boardString.=$positionRepresentation;
            }
            $boardString.="\n";
        }

        $lineDelimiter = "-------\n";
        $output = $lineDelimiter . $boardString . $lineDelimiter . "\n\n";
        if($returnValue) return $output;
        echo $output;
    }

    public function getStringBoard(){
        return $this->printBoard(false, true);
    }

    public function getPositionRepresentation($position, $movement){
        if($movement==false){
            if(isset($this->lastMovement)) $movement = $this->lastMovement;
        }

        if($movement!=false){
            if($this->equalPositions($position, $movement['startPosition']))
                    return $this->movementStartRepresentation;

            if($this->equalPositions($position, $movement['jumpedPosition']))
                    return $this->movementJumpRepresentation;

            if($this->equalPositions($position, $movement['endPosition']))
                    return $this->movementEndRepresentation;
        }

        $isFilled   = $this->isFilled($position);
        $isHole     = $this->isHole($position);

        if($isFilled) return $this->pieceRepresentation;
        if($isHole) return $this->holeRepresentation;
        return $this->unusedRepresentation;
    }

    private function equalPositions(Position $position1, Position $position2){
        $positionId1 = $position1->getPositionId();
        $positionId2 = $position2->getPositionId();
        if($positionId1 == $positionId2) return true;
        return false;
    }

    public function isFilled(Position $position){
        $positionId     = $position->getPositionId();
        $boardPosition  = $this->positions[$positionId];

        if(!isset($boardPosition)) return false;
        if($boardPosition->getHole()->isFilled()) return true;
        return false;
    }

    public function isHole(Position $position){
        $positionId     = $position->getPositionId();
        $boardPosition  = $this->positions[$positionId];

        if(!isset($boardPosition)) return false;
        return true;
    }

    public function getFilledPositionsCount(){
        $filledPositions = 0;
        foreach($this->positions as $position){
            if($position->getHole()->isFilled()) $filledPositions++;
        }

        return $filledPositions;
    }

    public function hasMiddlePiece(){
        $positionId = $this->middlePiece->getPositionId();
        return $this->positions[$positionId]->getHole()->isFilled();
    }

    public function setEmptyPosition(Position $position){
        $boardPosition = $this->positions[$position->getPositionId()];
        if(!isset($boardPosition)) return $this->getResponse('err', 'positionOutOfBoard', $position, 'Board.setEmptyPosition');
        $boardPosition->getHole()->setEmpty();
    }

    public function setFilledPosition(Position $position){
        $boardPosition = $this->positions[$position->getPositionId()];
        if(!isset($boardPosition)) return $this->getResponse('err', 'positionOutOfBoard', $position, 'Board.setFilledPosition');
        $boardPosition->getHole()->setFilled();
    }

    public function setLastMovement($movement){
        $this->lastMovement = $movement;
    }
}
