<?php

require '../../../autoload.php';
//require '../vendor/autoload.php';
use Omatech\Editora\Utils;

$tictac=new Omatech\Editora\Utils\TicTac(true);
$tictac->tic('inicial');

$tictac->tic('sleep1');
sleep(3);
$tictac->tac('sleep1');

$tictac->tic('sleep2');
sleep(2);
$tictac->tac('sleep2');

$tictac->tac('inicial');

echo $tictac->get_full_stats();


