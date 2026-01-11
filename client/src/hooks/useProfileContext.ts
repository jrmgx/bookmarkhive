import { useState, useEffect } from 'react';
import { resolveProfile, getPublicProfile } from '../services/publicApi';
import { ApiError } from '@shared';
import type { UserProfile } from '@shared';

export interface ProfileContext {
  profileIdentifier: string;
  username: string;
  baseUrl: string;
  profile: UserProfile | null;
  isLoading: boolean;
  error: string | null;
  errorStatus: number | null;
}

/**
 * Hook to resolve profile context from route identifier
 * Handles webfinger resolution and profile loading
 */
export function useProfileContext(profileIdentifier: string): ProfileContext {
  const [username, setUsername] = useState<string>('');
  const [baseUrl, setBaseUrl] = useState<string>('');
  const [profile, setProfile] = useState<UserProfile | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [errorStatus, setErrorStatus] = useState<number | null>(null);

  useEffect(() => {
    let cancelled = false;

    const loadProfile = async () => {
      setIsLoading(true);
      setError(null);
      setErrorStatus(null);
      setProfile(null);

      try {
        // Resolve webfinger to get API base URL and username
        const resolved = await resolveProfile(profileIdentifier);
        if (cancelled) return;

        setUsername(resolved.username);
        setBaseUrl(resolved.baseUrl);

        // Load profile info
        const profileData = await getPublicProfile(resolved.baseUrl, resolved.username);
        if (cancelled) return;

        setProfile(profileData);
      } catch (err: unknown) {
        if (cancelled) return;

        const message = err instanceof Error ? err.message : 'Failed to load profile';
        const status = err instanceof ApiError ? err.status : null;
        setError(message);
        setErrorStatus(status);
      } finally {
        if (!cancelled) {
          setIsLoading(false);
        }
      }
    };

    if (profileIdentifier) {
      loadProfile();
    } else {
      setIsLoading(false);
    }

    return () => {
      cancelled = true;
    };
  }, [profileIdentifier]);

  return {
    profileIdentifier,
    username,
    baseUrl,
    profile,
    isLoading,
    error,
    errorStatus,
  };
}

