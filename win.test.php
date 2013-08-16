<?php
    include_once('classes/Board.class.php');
    include_once('classes/Player.class.php');
    include_once('classes/Position.class.php');

    //create board and player objects
    $board  = new Board();
    $player = new Player();

    //unfill all positions
    $positions = $board->getPositions();
    foreach($positions as $position){
        $position->getHole()->setEmpty();
    }

    //set two position that when a move is made
    //it solves the board
    $position1 = new Position(4, 2);
    $position2 = new Position(4, 3);
    $board->setFilledPosition($position1);
    $board->setFilledPosition($position2);
    
    //test winning board
    $player->play($board);
