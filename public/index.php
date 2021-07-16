<?php

require_once "../vendor/autoload.php";

$pokedex = new \Maryne\BasicPokeapi\Pokedex();

header('Content-type: application/json');

echo json_encode($pokedex->getAllPokemon(0, 100));