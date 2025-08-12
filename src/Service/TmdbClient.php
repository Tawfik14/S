<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

final class TmdbClient
{
    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
        private string $apiKey,
    ) {}

    private function get(string $path, array $params = [], int $ttl = 300): array
    {
        $params = array_merge([
            'api_key' => $this->apiKey,
            'language' => 'fr-FR',
            'region' => 'FR',
            'include_image_language' => 'fr,null',
        ], $params);

        $cacheKey = 'tmdb_'.md5($path.'?'.http_build_query($params));

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($path, $params, $ttl) {
            $item->expiresAfter($ttl);
            $res = $this->http->request('GET', 'https://api.themoviedb.org/3'.$path, ['query' => $params]);
            return $res->toArray(false);
        });
    }

    public function img(?string $path, string $size = 'w780'): ?string
    {
        return $path ? "https://image.tmdb.org/t/p/{$size}{$path}" : null;
    }

    // ----- HOME / MOVIES -----
    public function trending(): array  { return $this->get('/trending/movie/week')['results'] ?? []; }
    public function popular(): array   { return $this->get('/movie/popular')['results'] ?? []; }
    public function topRated(): array  { return $this->get('/movie/top_rated')['results'] ?? []; }
    public function upcoming(): array  { return $this->get('/movie/upcoming')['results'] ?? []; }

    public function byGenre(int $id): array
    {
        return $this->get('/discover/movie', [
            'with_genres' => $id,
            'sort_by' => 'popularity.desc',
        ])['results'] ?? [];
    }

    // Détails film
    public function movie(int $id): array
    {
        return $this->get("/movie/{$id}", ['append_to_response' => 'release_dates']);
    }
    public function credits(int $id): array
    {
        return $this->get("/movie/{$id}/credits");
    }
    public function videos(int $id): array
    {
        return $this->get("/movie/{$id}/videos");
    }
    public function bestTrailerKey(int $id): ?string
    {
        $videos = $this->videos($id)['results'] ?? [];
        usort($videos, function($a,$b){
            $sa = (($a['site'] ?? '') === 'YouTube') + (($a['type'] ?? '') === 'Trailer');
            $sb = (($b['site'] ?? '') === 'YouTube') + (($b['type'] ?? '') === 'Trailer');
            return $sb <=> $sa;
        });
        $v = $videos[0] ?? null;
        return ($v && ($v['site'] ?? '') === 'YouTube') ? ($v['key'] ?? null) : null;
    }
    
    // ----- TV -----
public function tv(int $id): array
{
    return $this->get("/tv/{$id}");
}
public function tvCredits(int $id): array
{
    return $this->get("/tv/{$id}/credits");
}
public function tvVideos(int $id): array
{
    return $this->get("/tv/{$id}/videos");
}
public function bestTvTrailerKey(int $id): ?string
{
    $videos = $this->tvVideos($id)['results'] ?? [];
    usort($videos, function($a,$b){
        $sa = (($a['site'] ?? '') === 'YouTube') + (($a['type'] ?? '') === 'Trailer');
        $sb = (($b['site'] ?? '') === 'YouTube') + (($b['type'] ?? '') === 'Trailer');
        return $sb <=> $sa;
    });
    $v = $videos[0] ?? null;
    return ($v && ($v['site'] ?? '') === 'YouTube') ? ($v['key'] ?? null) : null;
}

// ----- TV (listes) -----
public function tvPopular(): array
{
    return $this->get('/tv/popular')['results'] ?? [];
}
public function tvTopRated(): array
{
    return $this->get('/tv/top_rated')['results'] ?? [];
}
public function tvOnTheAir(): array
{
    return $this->get('/tv/on_the_air')['results'] ?? [];
}
public function tvAiringToday(): array
{
    return $this->get('/tv/airing_today')['results'] ?? [];
}
public function tvByGenre(int $genreId): array
{
    $data = $this->get('/discover/tv', [
        'with_genres' => $genreId,
        'sort_by'     => 'popularity.desc',
    ]);
    return $data['results'] ?? [];
}

// ----- TV (détails saisons & épisodes) -----
public function tvSeason(int $tvId, int $seasonNumber): array
{
    // retourne les infos de saison + la liste des épisodes
    return $this->get("/tv/{$tvId}/season/{$seasonNumber}");
}


    // Recherche
    public function searchMulti(string $query): array
    {
        if (trim($query) === '') {
            return [];
        }
        return $this->get('/search/multi', [
            'query' => $query,
            'include_adult' => false,
        ])['results'] ?? [];
    }

    // À venir multi-pages
    public function upcomingPages(int $pages = 5): array
    {
        $all = [];
        for ($p = 1; $p <= max(1, $pages); $p++) {
            $data = $this->get('/movie/upcoming', ['page' => $p]);
            foreach (($data['results'] ?? []) as $r) {
                if (!empty($r['release_date'])) {
                    $all[] = $r;
                }
            }
        }
        usort($all, fn($a,$b) => strcmp($a['release_date'] ?? '9999-12-31', $b['release_date'] ?? '9999-12-31'));
        return $all;
    }

    // ----- PERSONNES (Acteurs / Réalisateurs) -----
    public function person(int $id): array
    {
        // append_to_response permet d’avoir les images sans second appel (optionnel)
        return $this->get("/person/{$id}", ['append_to_response' => 'images']);
    }

    public function personExternalIds(int $id): array
    {
        return $this->get("/person/{$id}/external_ids");
    }

    public function personCombinedCredits(int $id): array
    {
        // films + séries; on filtrera films dans le contrôleur si besoin
        return $this->get("/person/{$id}/combined_credits");
    }
}

