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

    //set positions
    $positions = array();
    $positions[] = new Position(2, 4);
    $positions[] = new Position(3, 2);
    $positions[] = new Position(3, 3);
    $positions[] = new Position(3, 5);
    $positions[] = new Position(4, 4);
    $positions[] = new Position(4, 6);
    $positions[] = new Position(5, 5);

    //fill these positions in the board
    foreach($positions as $position){
        $board->setFilledPosition($position);
    }

    //test winning board
    $player->play($board);
