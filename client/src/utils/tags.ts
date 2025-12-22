/**
 * Select a tag: replace the selection with only this tag, or deselect if already selected
 */
export const toggleTag = (slug: string, selectedSlugs: string[]): string[] => {
  if (selectedSlugs.includes(slug)) {
    return [];
  } else {
    return [slug];
  }
};

/**
 * Update URL search params with tag selection
 */
export const updateTagParams = (selectedSlugs: string[], currentParams: URLSearchParams): URLSearchParams => {
  const newParams = new URLSearchParams(currentParams);
  if (selectedSlugs.length > 0) {
    newParams.set('tags', selectedSlugs.join(','));
  } else {
    newParams.delete('tags');
  }
  return newParams;
};
