<?php


namespace Maryne\BasicPokeapi;


use http\Exception\RuntimeException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Pokedex
{
    private HttpClientInterface $client;

    /**
     * Pokedex constructor.
     */
    public function __construct()
    {
        $this->client = HttpClient::createForBaseUri('https://pokeapi.co/api/v2/');
    }

    public function getAllPokemon(int $offset = 0, int $limit = 50): array
    {
        $response = $this->client->request('GET', 'pokemon', [
            'query' => [
                'offset' => $offset,
                'limit' => $limit
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException('Error from Pokeapi.co');
        }

        $data = $response->toArray();

        $pokemons = [];
        foreach ($data['results'] as $pokemon) {
            if (!preg_match('/([0-9]+)\/?$/', $pokemon['url'], $matches)) {
                throw new \RuntimeException('Cannot match given url for pokemon ' . $pokemon['name']);
            }

            $id = $matches[1]; //  => 25

            $pokemons[] = [
                'id' => $id,
                'name' => $pokemon['name'],
            ];
        }

        // next page
        if ($data['next']) {
            if (!preg_match('/\?.*offset=([0-9]+)/', $data['next'], $matches)) {
                throw new \RuntimeException('Cannot match offset on next page.');
            }

            $nextOffset = $matches[1];

            $nextPokemons = $this->getAllPokemon($nextOffset, $limit);

            $pokemons = array_merge($pokemons, $nextPokemons);
        }

        return $pokemons;
    }


    public function getPikachu(): array
    {
        $response = $this->client->request('GET', 'pokemon/25');

        if (200 !== $response->getStatusCode()) {
            throw new RuntimeException('Error from Pokeapi.co');
        } else {
            $data = $response->toArray();
            $pokeInfos = array_intersect_key($data, array_flip(['id', 'name', 'weight', 'base_experience']));
            $pokeInfos['image'] = $data['sprites']['front_default'];
        }
        return $pokeInfos;

        // same as lines before.
//        return [
//            'id' => $data['id'],
//            'name' => $data['name'],
//            'weight' => $data['weight'],
//            'base_experience' => $data['base_experience'],
//            'image' => $data['sprites']['front_default'],
//        ];
    }

}