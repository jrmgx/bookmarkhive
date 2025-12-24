import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { Icon } from '../components/Icon/Icon';
import { PlaceholderImage } from '../components/PlaceholderImage/PlaceholderImage';
import { EditBookmarkTags } from '../components/EditBookmarkTags/EditBookmarkTags';
import { ErrorAlert } from '../components/ErrorAlert/ErrorAlert';
import { getBookmark, ApiError } from '../services/api';
import { shareBookmark } from '../utils/share';
import { getImageUrl } from '../utils/image';
import { formatDate } from '../utils/date';
import type { Bookmark as BookmarkType } from '../types';

export const ShowBookmark = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const [bookmark, setBookmark] = useState<BookmarkType | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [errorStatus, setErrorStatus] = useState<number | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [imageError, setImageError] = useState(false);
  const [showEditTagsModal, setShowEditTagsModal] = useState(false);
  const [archiveUrl, setArchiveUrl] = useState<string | null>(null);
  const [isLoadingArchive, setIsLoadingArchive] = useState(false);

  useEffect(() => {
    const loadData = async () => {
      if (!id) {
        setError('Bookmark ID is required');
        setIsLoading(false);
        return;
      }

      setIsLoading(true);
      setError(null);
      setErrorStatus(null);

      try {
        const bookmarkData = await getBookmark(id);

        if (bookmarkData) {
          setBookmark(bookmarkData);
        } else {
          setError('Bookmark not found');
          setErrorStatus(404);
        }
      } catch (err: unknown) {
        const message = err instanceof Error ? err.message : 'Failed to load bookmark';
        const status = err instanceof ApiError ? err.status : null;
        setError(message);
        setErrorStatus(status);
      } finally {
        setIsLoading(false);
      }
    };

    loadData();
  }, [id]);

  // Load and decompress archive file
  useEffect(() => {
    const loadArchive = async () => {
      if (!bookmark?.archive?.contentUrl) {
        setArchiveUrl(null);
        return;
      }

      setIsLoadingArchive(true);

      try {
        const archiveFileUrl = getImageUrl(bookmark.archive.contentUrl);
        if (!archiveFileUrl) {
          setArchiveUrl(null);
          return;
        }

        // Fetch the gzipped file
        const response = await fetch(archiveFileUrl);
        if (!response.ok) {
          throw new Error('Failed to fetch archive');
        }

        // Get the compressed data as ArrayBuffer
        const compressedData = await response.arrayBuffer();

        // Decompress using DecompressionStream API
        const decompressionStream = new DecompressionStream('gzip');
        const stream = new Response(compressedData).body?.pipeThrough(decompressionStream);

        if (!stream) {
          throw new Error('Failed to create decompression stream');
        }

        // Get the decompressed data
        const decompressedResponse = new Response(stream);
        const decompressedText = await decompressedResponse.text();

        // Create a blob URL from the decompressed HTML
        const blob = new Blob([decompressedText], { type: 'text/html' });
        const blobUrl = URL.createObjectURL(blob);

        setArchiveUrl(blobUrl);
      } catch (err) {
        console.error('Failed to load archive:', err);
        setArchiveUrl(null);
      } finally {
        setIsLoadingArchive(false);
      }
    };

    loadArchive();

    // Cleanup: revoke blob URL when component unmounts or bookmark changes
    return () => {
      setArchiveUrl((currentUrl) => {
        if (currentUrl) {
          URL.revokeObjectURL(currentUrl);
        }
        return null;
      });
    };
  }, [bookmark?.archive?.contentUrl]);

  if (isLoading) {
    return (
      <div className="text-center pt-5">
        <div className="spinner-border" role="status">
          <span className="visually-hidden">Loading...</span>
        </div>
      </div>
    );
  }

  if (error || !bookmark) {
    return (
      <>
        <ErrorAlert error={error} statusCode={errorStatus} />
        <div className="text-center pt-5">
          <button className="btn btn-outline-secondary" onClick={() => navigate(-1)}>
            <Icon name="arrow-left" className="me-2" />
            Go back
          </button>
        </div>
      </>
    );
  }

  const imageUrl = getImageUrl(bookmark.mainImage?.contentUrl);
  const sortedTags = [...bookmark.tags].sort((a, b) => {
    return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
  });

  const handleImageError = () => {
    setImageError(true);
  };

  const handleTagClick = (slug: string) => {
    navigate(`/?tags=${slug}`);
  };

  const handleShare = (e: React.MouseEvent) => {
    e.preventDefault();
    shareBookmark(bookmark);
  };

  const handleEditTags = (e: React.MouseEvent) => {
    e.preventDefault();
    setShowEditTagsModal(true);
  };

  const handleTagsSave = () => {
    setShowEditTagsModal(false);
    // Reload bookmark to get updated tags
    if (id) {
      getBookmark(id).then((bookmarkData) => {
        if (bookmarkData) {
          setBookmark(bookmarkData);
        }
      });
    }
  };

  const handleTagsClose = () => {
    setShowEditTagsModal(false);
  };

  // Determine background image style for normal bookmarks
  const normalBookmarkStyle = imageUrl && !imageError
    ? { backgroundImage: `url(${imageUrl})` }
    : undefined;

  return (
    <>
      <ErrorAlert error={error} statusCode={errorStatus} />

      <div className="row gx-3">
        <div className="col-12 my-2 col-sm-6 col-md-4 col-xl-3 col-xxl-2">
          <div id={`bookmark-${bookmark.id}`} className="card h-100">
            <div className="card-img-top bookmark-img flex-shrink-0" style={normalBookmarkStyle}>
              {imageUrl && !imageError && (
                <img
                  src={imageUrl}
                  alt=""
                  onError={handleImageError}
                  style={{ display: 'none' }}
                  aria-hidden="true"
                />
              )}
              {(!imageUrl || imageError) && (
                <PlaceholderImage
                  type={imageError ? 'error-image' : 'no-image'}
                  style={{ position: 'absolute', top: 0, left: 0, right: 0, bottom: 0 }}
                />
              )}
              <a target="_blank" className="d-block h-100 w-100" href={bookmark.url} rel="noopener noreferrer" style={{ position: 'relative', zIndex: 1 }}></a>
            </div>

            <div className="card-body position-relative">
              <div className="card-title">
                <a
                  target="_blank"
                  className="text-decoration-none bookmark-title"
                  href={bookmark.url}
                  rel="noopener noreferrer"
                  title={bookmark.title}
                >
                  <small className="badge me-2 rounded-pill text-bg-light border fw-light domain-pill">
                    {bookmark.domain}
                  </small>
                  {bookmark.title}
                </a>
              </div>
              <div className="pt-1">
                {sortedTags.map((tag) => (
                  <button
                    key={tag.slug}
                    type="button"
                    className="btn btn-outline-secondary btn-xs me-1 mb-1"
                    onClick={() => handleTagClick(tag.slug)}
                  >
                    {tag.icon && `${tag.icon} `}
                    {tag.name}
                  </button>
                ))}
                <button
                  className="btn btn-outline-primary btn-xs mb-1"
                  type="button"
                  onClick={handleEditTags}
                  aria-label="Edit tags"
                >
                  #
                </button>
              </div>
            </div>
            <div className="card-footer text-body-secondary d-flex align-items-center py-1 pe-0">
              <div className="fs-small flex-grow-1">{formatDate(bookmark.createdAt)}</div>
              <div>
                <button
                  className="btn btn-outline-secondary border-0"
                  onClick={handleShare}
                  aria-label="Share bookmark"
                >
                  <Icon name="share-fat" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      {bookmark.archive && (
        <div className="row mt-3">
          <div className="col-12">
            {isLoadingArchive ? (
              <div className="text-center py-5">
                <div className="spinner-border" role="status">
                  <span className="visually-hidden">Loading archive...</span>
                </div>
              </div>
            ) : archiveUrl ? (
              <iframe
                src={archiveUrl}
                style={{
                  width: '100%',
                  minHeight: '600px',
                  height: '80vh',
                  border: 'none',
                }}
                title="Archived Content"
              />
            ) : (
              <div className="alert alert-warning" role="alert">
                Failed to load archived content.
              </div>
            )}
          </div>
        </div>
      )}

      <EditBookmarkTags
        bookmark={showEditTagsModal ? bookmark : null}
        onSave={handleTagsSave}
        onClose={handleTagsClose}
      />
    </>
  );
};

