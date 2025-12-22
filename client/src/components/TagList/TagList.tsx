import { Tag } from '../Tag/Tag';
import type { Tag as TagType } from '../../types';

interface TagListProps {
  tags: TagType[];
  selectedTagSlugs: string[];
  pinnedTags?: TagType[];
  children?: React.ReactNode;
  onTagToggle?: (slug: string) => void;
}

const sortTags = (tags: TagType[]): TagType[] => {
  return [...tags].sort((a, b) => {
    return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
  });
};

export const TagList = ({
  tags,
  selectedTagSlugs,
  pinnedTags,
  children,
  onTagToggle
}: TagListProps) => {

  const computedPinnedTags = pinnedTags ?? tags.filter((tag) => tag.pinned);
  const sortedPinnedTags = sortTags(computedPinnedTags);

  return (
    <>
      {sortedPinnedTags.length > 0 && (
        <div>
          <div className="mb-2 fw-bold">Favorite</div>
          {sortedPinnedTags.map((tag) => (
            <Tag
              key={tag.slug}
              tag={tag}
              selectedTagSlugs={selectedTagSlugs}
              onToggle={onTagToggle}
              className='mb-2'
            />
          ))}
        </div>
      )}
      {children}
    </>
  );
};

