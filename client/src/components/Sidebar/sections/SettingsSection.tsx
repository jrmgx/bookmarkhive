import { useNavigate } from 'react-router-dom';
import { SidebarSection } from '../SidebarSection';
import { SidebarAction } from '../SidebarAction';
import { logout } from '../../../services/auth';

export const SettingsSection = () => {
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  return (
    <SidebarSection
      title="Account"
      storageKey="sidebar-section-settings-collapsed"
    >
      <SidebarAction
        label="Profile"
        onClick={() => {}}
      />
      <SidebarAction
        label="Settings"
        onClick={() => {}}
      />
      <SidebarAction
        label="Logout"
        onClick={handleLogout}
      />
    </SidebarSection>
  );
};

