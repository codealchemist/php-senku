<?php
    //@author Alberto Miranda <alberto.php@gmail.com>
    include('classes/Board.class.php');
    include('classes/Player.class.php');

    //create board and player objects
    $board  = new Board();
    $player = new Player();

    //ok, lets play!
    $player->play($board);
