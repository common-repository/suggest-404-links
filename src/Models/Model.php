<?php

declare(strict_types=1);
namespace Tekod\Suggest404Links\Models;

/**
 * Class Products.
 * This is classic data model.
 */
class Model
{

    // internal properties
    protected $parsedUrl;
    protected $searchPathSlug;
    protected $searchPathSlugLen;


    /**
     * Singleton getter.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }


    /**
     * Constructor.
     */
    public function __construct()
    {
        $url = rawurldecode(
            ( is_ssl() ? 'https://' : 'http://' )
            . sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'] ?? ''))
            . sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''))
        );
        $this->parsedUrl = wp_parse_url($url);
        $this->parsedUrl['full'] = $url;
    }


    /**
     * Perform search for orders and products and returns list od IDs.
     *
     * @param string $path
     * @return array
     */
    public function search(string $path): array
    {
        $pathSegments = array_filter(explode('/', $path));
        $this->searchPathSlug = end($pathSegments);
        $this->searchPathSlugLen = strlen($this->searchPathSlug);
        if ($this->searchPathSlugLen > 200) {
            return [];  // save CPU
        }

        $allIds = $this->getAllPublicPostIds();
        $allSlugs = $this->getAllPostSlugs();
        $slugs = array_intersect_key($allSlugs, array_flip($allIds));

        $result = [];
        foreach ($slugs as $id => $slug) {
            $score = $this->calcScore($slug);
            if ($score <= 0.3) {
                $result[] = [
                    'score' => $score,
                    'id' => $id,
                    'slug' => $slug,
                ];
            }
        }
        sort($result);
        return array_slice($result, 0, 5);
    }


    /**
     * Fetch ids of all posts.
     *
     * @return array
     */
    protected function getAllPublicPostIds(): array
    {
        // use hook "suggest_404_links_get_ids" to supply your own list of valid (public) IDs
        // this is a perfect way to apply custom validation and hide certain posts
        $ids = apply_filters('suggest_404_links_get_ids', null);
        if (is_array($ids)) {
            return $ids;
        }

        // load all ids
        $args = [
            'post_type' => self::getPostTypes(),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'cache_results'  => true,
            'fields' => 'ids',
        ];

        // use hook "suggest_404_links_get_post_args" to modify arguments
        return get_posts(apply_filters('suggest_404_links_get_post_args', $args));
    }


    /**
     * Fetch all slugs.
     *
     * @return array
     */
    protected function getAllPostSlugs(): array
    {
        global $wpdb;
        $results = $wpdb->get_results("SELECT ID, post_name FROM {$wpdb->posts}");
        return array_combine(array_column($results, 'ID'), array_column($results, 'post_name'));
    }


    /**
     * Math!
     *
     * @param string $slug
     * @return float
     */
    protected function calcScore(string $slug): float
    {
        $partialMatchPenalty = $this->searchPathSlugLen / 50;
        $slugLen = strlen($slug);
        $score = abs($slugLen - $this->searchPathSlugLen) / ($this->searchPathSlugLen + 2) > 0.2
            ? PHP_INT_MAX
            : levenshtein($slug, $this->searchPathSlug);
        $words = explode('-', str_replace('_', '-', $slug));
        if (count($words) > 1) {
            foreach ($words as $chunk) {
                $partialMatchScore = abs(strlen($chunk) - $this->searchPathSlugLen) / ($this->searchPathSlugLen + 2) > 0.2
                    ? PHP_INT_MAX
                    : levenshtein($chunk, $this->searchPathSlug) + $partialMatchPenalty;
                $score = min($score, $partialMatchScore);
            }
        }
        return $score / $this->searchPathSlugLen;
    }


    /**
     * Get config: SelectedPostTypes.
     *
     * @return array
     */
    protected function getPostTypes(): array
    {
        $settings = suggest_404_links()->config()->getSettings();
        return $settings['SelectedPostTypes'];
    }


    /**
     * Returns URL that caused the 404 error.
     *
     * @param  string $type
     * @return string
     */
    public function getUrl(string $type = 'full'): string
    {
        return $this->parsedUrl[$type] ?? '';
    }

}
