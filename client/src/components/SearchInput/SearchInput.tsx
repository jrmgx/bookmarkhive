import React from 'react';

interface SearchInputProps {
  value: string;
  onChange: (query: string) => void;
  onClear: () => void;
  disabled: boolean;
  placeholder?: string;
}

export const SearchInput = ({
  value,
  onChange,
  onClear,
  disabled,
  placeholder = 'Search bookmarks...',
}: SearchInputProps) => {
  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    onChange(e.target.value);
  };

  const handleClear = (e: React.MouseEvent) => {
    e.preventDefault();
    onClear();
  };

  return (
    <div className="position-relative mb-2 mt-3">
      <input
        type="text"
        className="form-control"
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
  );
};

