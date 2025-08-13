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

// ----- Recommandations & Similaires (Movies) -----
public function movieRecommendations(int $id): array
{
    return $this->get("/movie/{$id}/recommendations")['results'] ?? [];
}
public function movieSimilar(int $id): array
{
    return $this->get("/movie/{$id}/similar")['results'] ?? [];
}

// ----- Watch Providers (Movies) -----
public function movieWatchProviders(int $id): array
{
    // retourne un objet avec 'results' par pays ('FR', 'US', etc.)
    return $this->get("/movie/{$id}/watch/providers");
}

// ----- Recommandations & Similaires (TV) -----
public function tvRecommendations(int $id): array
{
    return $this->get("/tv/{$id}/recommendations")['results'] ?? [];
}
public function tvSimilar(int $id): array
{
    return $this->get("/tv/{$id}/similar")['results'] ?? [];
}

// ----- Watch Providers (TV) -----
public function tvWatchProviders(int $id): array
{
    return $this->get("/tv/{$id}/watch/providers");
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

    // >>> À VENIR multi-pages (corrigé: filtre les dates passées)
public function upcomingPages(int $pages = 5): array
{
    $all = [];

    // "Aujourd'hui" en Europe/Paris
    $tz = new \DateTimeZone('Europe/Paris');
    $today = (new \DateTimeImmutable('today', $tz))->format('Y-m-d');

    for ($p = 1; $p <= max(1, $pages); $p++) {
        $data = $this->get('/movie/upcoming', ['page' => $p]);
        foreach (($data['results'] ?? []) as $r) {
            $d = $r['release_date'] ?? null;
            // Garde uniquement les films qui sortent aujourd'hui ou après
            if ($d && $d >= $today) {
                $all[] = $r;
            }
        }
    }

    // Tri chronologique ascendant
    usort($all, static function ($a, $b) {
        return strcmp($a['release_date'] ?? '9999-12-31', $b['release_date'] ?? '9999-12-31');
    });

    return $all;
}

// Variante: uniquement les sorties à venir via Discover
public function upcomingFromToday(int $page = 1): array
{
    $tz = new \DateTimeZone('Europe/Paris');
    $today = (new \DateTimeImmutable('today', $tz))->format('Y-m-d');

    $data = $this->get('/discover/movie', [
        'sort_by' => 'primary_release_date.asc',
        'primary_release_date.gte' => $today,
        'page' => $page,
    ]);
    return $data['results'] ?? [];
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

