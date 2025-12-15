<?php

namespace App\Service;

use App\Model\Tag;
use Twig\Attribute\AsTwigFilter;

class TagService
{
    /**
     * @param array<Tag>    $tags
     * @param array<string> $selectedTagSlugs
     *
     * @return array<Tag>
     */
    #[AsTwigFilter('sort_tags')]
    public function sort(array $tags, array $selectedTagSlugs = []): array
    {
        $selectedSlugs = array_flip($selectedTagSlugs);

        usort($tags, function (Tag $a, Tag $b) use ($selectedSlugs) {
            $aSelected = isset($selectedSlugs[$a->slug]);
            $bSelected = isset($selectedSlugs[$b->slug]);

            // If one is selected and the other isn't, selected comes first
            if ($aSelected && !$bSelected) {
                return -1;
            }
            if (!$aSelected && $bSelected) {
                return 1;
            }

            // Both are selected or both are not selected, sort by name
            return strnatcasecmp($a->name, $b->name);
        });

        return $tags;
    }

    /**
     * Given the current query parameters and a tag slug, return the new query params with that tag toggled on or off.
     *
     * @param array<mixed> $requestQueryAll
     *
     * @return array<mixed>
     */
    #[AsTwigFilter('query_toggle_tag')]
    public function queryToggleTag(array $requestQueryAll, string $slug): array
    {
        $selectedTagSlugs = explode(',', $requestQueryAll['tags'] ?? '');

        if (null !== $index = array_find_key($selectedTagSlugs, fn (string $selectedSlug) => $slug === $selectedSlug)) {
            unset($selectedTagSlugs[$index]);
        } else {
            $selectedTagSlugs[] = $slug;
        }

        $requestQueryAll['tags'] = implode(',', array_filter($selectedTagSlugs));

        return $requestQueryAll;
    }
}
