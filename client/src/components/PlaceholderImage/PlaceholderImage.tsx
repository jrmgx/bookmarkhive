export type PlaceholderType = 'no-embed' | 'error-image' | 'no-image';

interface PlaceholderImageProps {
  type: PlaceholderType;
  className?: string;
  style?: React.CSSProperties;
}

const NoEmbedSvg = () => (
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style={{ width: '100%', height: '100%' }}>
    <g fill="none">
      <circle cx="17" cy="7" r="5" stroke="currentColor" strokeLinecap="round" strokeWidth="1.5"/>
      <path fill="currentColor"
            d="M22.75 13.494a.75.75 0 0 0-1.5 0zm-2.216 7.26l.486.572zM3.447 6.234l-.486-.572zm9.545 15.016a.75.75 0 0 0 0 1.5zm1 .746l-.005-.75h-.006zM1.25 10.973a.75.75 0 0 0 1.5.038zM9.26 5.75a.75.75 0 0 0-.023-1.5zm11.99 7.744c0 2.03-.002 3.463-.174 4.55c-.166 1.05-.477 1.67-1.027 2.139l.97 1.143c.916-.778 1.338-1.782 1.539-3.048c.194-1.23.192-2.804.192-4.784zm-8.258 9.256l.954-.004h.058l-.011-.75l-.012-.75h-.052l-.172.002l-.765.002zm1.006-.005c1.706-.012 3.085-.058 4.204-.244c1.131-.188 2.058-.53 2.818-1.175l-.971-1.143c-.482.41-1.119.676-2.093.839c-.988.164-2.258.211-3.969.224zM2.75 11.012c.062-2.42.34-3.49 1.183-4.206L2.96 5.662c-1.402 1.191-1.65 2.92-1.71 5.31zm6.487-6.76c-2.906.044-4.89.233-6.276 1.41l.972 1.144c.912-.776 2.325-1.009 5.327-1.055z"/>
      <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
            d="M2.982 21h.01M17 9h.009"/>
      <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" d="M17 6.5v-2"/>
      <path stroke="currentColor" strokeLinecap="round" strokeWidth="1.5"
            d="M2 17.235c2.493 0 4.77 2.265 4.77 4.765M10 22c0-4.5-4.005-8-7.955-8"/>
    </g>
  </svg>
);

const ErrorImageSvg = () => (
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" style={{ width: '100%', height: '100%' }}>
    <g fill="none">
      <path fill="currentColor" d="M44 23.994a2 2 0 0 0-4 0zm-20-16a2 2 0 1 0 0-4zm15 32H9v4h30zm-31-1v-30H4v30zm32-15v15h4v-15zm-31-16h15v-4H9zm0 32a1 1 0 0 1-1-1H4a5 5 0 0 0 5 5zm30 4a5 5 0 0 0 5-5h-4a1 1 0 0 1-1 1zm-31-35a1 1 0 0 1 1-1v-4a5 5 0 0 0-5 5z"/>
      <path stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="4" d="m6 35l10.693-9.802a2 2 0 0 1 2.653-.044L32 36m-4-5l4.773-4.773a2 2 0 0 1 2.615-.186L42 31M33 7l8 8m0-8l-8 8"/>
    </g>
  </svg>
);

const NoImageSvg = () => (
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" style={{ width: '100%', height: '100%' }}>
    <path fill="currentColor"
          d="M30 3.414L28.586 2L2 28.586L3.414 30l2-2H26a2.003 2.003 0 0 0 2-2V5.414zM26 26H7.414l7.793-7.793l2.379 2.379a2 2 0 0 0 2.828 0L22 19l4 3.997zm0-5.832l-2.586-2.586a2 2 0 0 0-2.828 0L19 19.168l-2.377-2.377L26 7.414zM6 22v-3l5-4.997l1.373 1.374l1.416-1.416l-1.375-1.375a2 2 0 0 0-2.828 0L6 16.172V6h16V4H6a2 2 0 0 0-2 2v16z"/>
  </svg>
);

export const PlaceholderImage = ({ type, className, style }: PlaceholderImageProps) => {
  const size = type === 'no-embed' ? 64 : 32;
  const iconColor = '#999999';

  const containerStyle: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '100%',
    height: '100%',
    ...style,
  };

  const iconStyle: React.CSSProperties = {
    width: `${size}px`,
    height: `${size}px`,
    color: iconColor,
  };

  const renderIcon = () => {
    switch (type) {
      case 'no-embed':
        return <div style={iconStyle}><NoEmbedSvg /></div>;
      case 'error-image':
        return <div style={iconStyle}><ErrorImageSvg /></div>;
      case 'no-image':
        return <div style={iconStyle}><NoImageSvg /></div>;
      default:
        return null;
    }
  };

  return (
    <div className={className} style={containerStyle}>
      {renderIcon()}
    </div>
  );
};

