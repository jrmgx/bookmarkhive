import { Link } from 'react-router-dom';

interface ErrorAlertProps {
  error: string | null;
  statusCode?: number | null;
}

export const ErrorAlert = ({ error, statusCode }: ErrorAlertProps) => {
  if (!error) return null;

  const isAuthError = statusCode === 401;

  return (
    <div className="alert alert-danger mt-3" role="alert">
      {isAuthError ? (
        <>
          Authentication failed. Please <Link to="/login">login again</Link>.
        </>
      ) : (
        error
      )}
    </div>
  );
};

