import { useNavigate } from 'react-router-dom';
import { SidebarSection } from '../SidebarSection';
import { SidebarAction } from '../SidebarAction';

export const SocialSection = () => {
  const navigate = useNavigate();

  return (
    <SidebarSection
      title="Social"
      storageKey="sidebar-section-social-collapsed"
    >
      <SidebarAction
        label="Your Timeline (may be removed while you don't follow any)"
        onClick={() => {
          // TODO: Implement timeline navigation
        }}
      />
      <SidebarAction
        label="@jrmgx"
        onClick={() => {
          navigate('/profile/jrmgx@bookmarkhive.test');
        }}
      />
      <SidebarAction
        label="PHP"
        onClick={() => {
          // TODO: Implement tag navigation
        }}
      />
      <SidebarAction
        label="This Server (could also show last tags)"
        onClick={() => {
          // TODO: Implement server navigation
        }}
      />
      <SidebarAction
        label="Trending (same)"
        onClick={() => {
          // TODO: Implement trending navigation
        }}
      />
      <SidebarAction
        label="Other Server"
        onClick={() => {
          // TODO: Implement external server navigation
        }}
      />
    </SidebarSection>
  );
};

