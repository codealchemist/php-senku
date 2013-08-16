<?php
require_once('Framework.class.php');

/**
 * This class represents a player for the solitaire (aka senku) game.
 * The player knows how to play the game.
 * It recieves a board with pieces and starts playing.
 * It make changes in the board while moving pieces and rollbacks them
 * when it finds that path is wrong (no moves left and without winning).
 *
 * @author Alberto Miranda <alberto.php@gmail.com>
 */
class Player extends Framework {
    private $movement;
    private $movementHistory = array();
    private $iteration;
    private $depth;
    private $timeStart;
    private $timeEnd;
    private $maxIterations = 30000;
    private $board;
    private $piecesLeft;
    private $lastMovement;
    private $debugMinPieces = 32; //[32=view all] only show some debugging when less than this pieces left on board

    public function __construct() {
        
    }

    public function getElapsedTime(){
        $seconds = time() - $this->timeStart . ' (s)';
        if($seconds>90){
            $minuteDecimal  = $seconds/60;
            $minute         = intval($minuteDecimal);
            $seconds        = intval(($minuteDecimal-$minute)*60);
            return "$minute' $seconds\"";
        }

        return $seconds;
    }

    public function play(Board $board){
        $this->board = $board;
        $this->timeStart = time();
        $result = $this->solve();
        if($result==false) return $this->debug("> SOLUTION NOT FOUND!");
        $this->debugSolve();
        return $this->debug(print_r($result, true));
    }

    public function solve(){
        //increment counters
        $this->iteration++;
        $this->depth++;

        //check iterations limit
        if($this->iteration >= $this->maxIterations)
                return $this->getResponse('ok', 'MAX ITERATIONS REACHED!', $this->iteration, 'Player.solve');

        //debug and set iteration and depth
        $this->debugSolve();

        $positions = $this->board->getPositions();
        foreach($positions as $position){
            //$this->debug(">solve: iterate positions...\n" . print_r($position, true));
            $validMovements = $this->getValidMovements($position, $positions);
            if(empty($validMovements)) continue;

            foreach($validMovements as $movement){
                $this->move($movement); //changes board

                if($this->isSolved()) return $this->getResponse('ok', 'BOARD SOLVED!', $this->getWinningMovements(), 'Player.solve');
                $result = $this->solve();
                $this->depth--;
                if($result!=false) return $result;

                //rollback movement
                $this->rollbackMovement($movement); //unchanges board
            }
        }
        return false;
    }

    private function getValidMovements($position, $positions){
        $validMovements = array();
        $movements = array();
        $movements['up']    = $this->getMovement('up', $position, $positions);
        $movements['down']  = $this->getMovement('down', $position, $positions);
        $movements['right'] = $this->getMovement('right', $position, $positions);
        $movements['left']  = $this->getMovement('left', $position, $positions);

        foreach($movements as $direction => $movement){
            if($movement!=false) $validMovements[$direction] = $movement;
        }

        return $validMovements;
    }

    private function getMovement($direction, Position $position, $positions){
        $row    = $position->getRow();
        $column = $position->getColumn();

        switch($direction){
            case 'up':
                $endPosition    = new Position($row-2, $column);
                $jumpedPosition = new Position($row-1, $column);
                break;

            case 'down':
                $endPosition    = new Position($row+2, $column);
                $jumpedPosition = new Position($row+1, $column);
                break;

            case 'right':
                $endPosition    = new Position($row, $column+2);
                $jumpedPosition = new Position($row, $column+1);
                break;

            case 'left':
                $endPosition    = new Position($row, $column-2);
                $jumpedPosition = new Position($row, $column-1);
                break;
        }

        $this->debug("> getMovement: $direction", 'full');
        $this->debug('  startPosition : ' . print_r($position, true), 'full');
        $this->debug('  jumpedPosition: ' . print_r($jumpedPosition, true), 'full');
        $this->debug('  endPosition   : ' . print_r($endPosition, true), 'full');

        //check if start position is filled
        $boardStartPosition = $positions[$position->getPositionId()];
        if($boardStartPosition->getHole()->isEmpty()) return false;
        $this->debug('   start position filled', 'full');

        //check if end position is inside the board
        $boardEndPosition = $positions[$endPosition->getPositionId()];
        if(!isset($boardEndPosition)) return false;
        $this->debug('   end position exists', 'full');

        //check if end position is an empty hole
        if($boardEndPosition->getHole()->isFilled()) return false;
        $this->debug('   end position empty', 'full');

        //check if there's a piece to jump
        $boardJumpedPosition = $positions[$jumpedPosition->getPositionId()];
        if($boardJumpedPosition->getHole()->isEmpty()) return false;
        $this->debug('   jumped position filled', 'full');

        //we are going to move the piece at the original position
        //which means that position must be left empty
        //$position->getHole()->setEmpty(); //nice if want to see debug
        
        $movement['startPosition']  = $position;
        $movement['jumpedPosition'] = $jumpedPosition;
        $movement['endPosition']    = $endPosition;
        $this->debug('   --> VALID MOVEMENT!', 'full');
        //$this->debug('+ VALID MOVEMENT @ iteration ' . $this->iteration . "\n" . print_r($movement, true), 'normal');
        return $movement;
    }

    private function move($movement){
        //make movement
        $this->board->setEmptyPosition($movement['startPosition']);
        $this->board->setEmptyPosition($movement['jumpedPosition']);
        $this->board->setFilledPosition($movement['endPosition']);

        //save last movement
        $this->lastMovement = $movement;

        //update board last movement
        $this->board->setLastMovement($movement);

        //update pieces left
        $this->piecesLeft = $this->board->getFilledPositionsCount();

        //record movement into history
        $this->movementHistory[] = $movement;
    }

    private function rollbackMovement($movement){
        $this->board->setFilledPosition($movement['startPosition']);
        $this->board->setFilledPosition($movement['jumpedPosition']);
        $this->board->setEmptyPosition($movement['endPosition']);

        //update pieces left
        $this->piecesLeft = $this->board->getFilledPositionsCount();

        //remove last movement from history
        array_pop($this->movementHistory);
    }

    private function isSolved(){
        //$this->debug("> isSolved: piecesLeft    : {$this->piecesLeft}");
        //$this->debug("            hasMiddlePiece: {$this->board->hasMiddlePiece()}");
        if($this->piecesLeft==1 && $this->board->hasMiddlePiece()) return true;
        return false;
    }

    private function getWinningMovements(){
        $movementsString    = '';
        $counter            = 0;
        foreach($this->movementHistory as $movement){
            $counter++;
            $startPositionId    = $movement['startPosition']->getPositionId();
            $jumpedPositionId   = $movement['jumpedPosition']->getPositionId();
            $endPositionId      = $movement['endPosition']->getPositionId();
            $movementsString.="$counter- $startPositionId --> $endPositionId\n";
        }
        return "\n$movementsString";
    }

    private function debugSolve(){
        system('clear');

        //debug output
        $this->debug('iteration     : ' . $this->iteration);
        $this->debug('depth         : ' . $this->depth);
        $this->debug('elapsed time  : ' . $this->getElapsedTime());
        $this->debug('memory usage  : ' . $this->getMemoryUsage());
        if($this->piecesLeft <= $this->debugMinPieces){
            if(isset ($this->lastMovement)){
                $moveString = "> move: " . $this->lastMovement['startPosition']->getPositionId() . " --> " .
                                           $this->lastMovement['jumpedPosition']->getPositionId() . " --" .
                                           $this->lastMovement['endPosition']->getPositionId();
                $this->debug($moveString, 'normal');
            }
            $this->debug('  pieces left : ' .  $this->piecesLeft, 'normal');

            $this->board->printBoard();
            $fileOutput =   "iteration  : {$this->iteration}\n" .
                            "pieces left: {$this->piecesLeft}\n" .
                            $this->board->getStringBoard();
            //$this->writeFile('php-senku.debug', $fileOutput);

            if(isset ($this->lastMovement)){
                $moveString = $this->lastMovement['startPosition']->getPositionId() . " --> " .
                              $this->lastMovement['endPosition']->getPositionId() . "\n";
                $this->writeFile('php-senku.debug', $moveString);
            }
        }
    }
}
