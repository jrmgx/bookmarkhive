import React from 'react';
import { Tag } from '../Tag/Tag';
import type { Tag as TagType } from '../../types';

interface SearchInputProps {
  value: string;
  onChange: (query: string) => void;
  onClear: () => void;
  disabled: boolean;
  placeholder?: string;
  selectedTags?: TagType[];
  selectedTagSlugs?: string[];
  onTagToggle?: (slug: string) => void;
}

export const SearchInput = ({
  value,
  onChange,
  onClear,
  disabled,
  placeholder = 'Search bookmarks...',
  selectedTags = [],
  selectedTagSlugs = [],
  onTagToggle,
}: SearchInputProps) => {
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    onChange(e.target.value);
  };

  const handleClear = (e: React.MouseEvent) => {
    e.preventDefault();
    onClear();
  };

  return (
    <div className="d-flex align-items-center flex-wrap gap-2 mb-2 mt-3">
      {/* Display selected tags on the same line before the search input */}
      {selectedTags.length > 0 && (
        <div className="d-flex align-items-center gap-2" style={{ flexShrink: 0 }}>
          {selectedTags.map((tag) => (
            <div key={tag.slug} style={{ flexShrink: 0 }}>
              <Tag
                tag={tag}
                selectedTagSlugs={selectedTagSlugs}
                onToggle={onTagToggle}
                className="flex-grow-0"
              />
            </div>
          ))}
        </div>
      )}
      <div className="position-relative" style={{ flexGrow: 1, flexShrink: 1, flexBasis: 0, minWidth: '200px' }}>
        <input
          type="text"
          className="form-control w-100"
          placeholder={placeholder}
          value={value}
          onChange={handleChange}
          disabled={disabled}
          aria-label="Search bookmarks"
        />
        {value && !disabled && (
          <button
            type="button"
            className="btn btn-link position-absolute end-0 top-50"
            onClick={handleClear}
            style={{
              padding: '0.375rem 0.75rem',
              textDecoration: 'none',
              color: 'inherit',
              border: 'none',
              background: 'none',
              cursor: 'pointer',
              zIndex: 10,
              transform: 'translateY(calc(-50% - 2px))',
            }}
            aria-label="Clear search"
          >
            ×
          </button>
        )}
      </div>
    </div>
  );
};

