// Options page script for the extension

import { getBrowserStorage } from './lib/browser';
import { authenticate as apiAuthenticate } from './api';

document.addEventListener('DOMContentLoaded', () => {
    const loginButton = document.getElementById('loginButton') as HTMLButtonElement | null;
    const saveApiHostButton = document.getElementById('saveApiHostButton') as HTMLButtonElement | null;
    const apiHostInput = document.getElementById('apiHost') as HTMLInputElement | null;
    const statusMessage = document.getElementById('statusMessage') as HTMLElement | null;

    const storage = getBrowserStorage();

    // Load saved API host on page load
    loadApiHost();

    // Handle save API host button
    if (saveApiHostButton) {
        saveApiHostButton.addEventListener('click', async () => {
            await saveApiHost();
        });
    }

    // Handle login button
    if (loginButton) {
        loginButton.addEventListener('click', async () => {
            await authenticate();
        });
    }

    // Load API host from storage
    async function loadApiHost(): Promise<void> {
        if (!apiHostInput || !storage) return;

        try {
            const result = await new Promise<{ apiHost?: string }>((resolve) => {
                storage.local.get(['apiHost'], (result: { apiHost?: string }) => {
                    if (chrome.runtime.lastError) {
                        console.error('Error loading API host:', chrome.runtime.lastError.message);
                        resolve({});
                    } else {
                        resolve(result);
                    }
                });
            });

            if (result.apiHost) {
                apiHostInput.value = result.apiHost;
            }
        } catch (error) {
            console.error('Error loading API host:', error);
        }
    }

    // Save API host to storage
    async function saveApiHost(): Promise<void> {
        if (!apiHostInput || !saveApiHostButton || !storage) {
            showStatus('API host input field not found', 'error');
            return;
        }

        const apiHost = apiHostInput.value.trim();

        if (!apiHost) {
            showStatus('Please enter an API host', 'error');
            return;
        }

        // Validate URL format
        try {
            new URL(apiHost);
        } catch (error) {
            showStatus('Please enter a valid URL (e.g., https://bookmarkhive.test)', 'error');
            return;
        }

        // Disable save button during request
        saveApiHostButton.disabled = true;
        saveApiHostButton.textContent = 'Saving...';

        try {
            await new Promise<void>((resolve, reject) => {
                storage.local.set({ apiHost: apiHost }, () => {
                    if (chrome.runtime.lastError) {
                        reject(new Error(chrome.runtime.lastError.message));
                    } else {
                        resolve();
                    }
                });
            });

            showStatus('API host saved successfully!', 'success');
        } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
            showStatus(`Failed to save API host: ${errorMessage}`, 'error');
        } finally {
            // Re-enable save button
            saveApiHostButton.disabled = false;
            saveApiHostButton.textContent = 'Save API Host';
        }
    }

    // Authenticate and get JWT token
    async function authenticate(): Promise<void> {
        const emailEl = document.getElementById('email') as HTMLInputElement | null;
        const passwordEl = document.getElementById('password') as HTMLInputElement | null;

        if (!emailEl || !passwordEl) {
            showStatus('Email and password fields not found', 'error');
            return;
        }

        const email = emailEl.value.trim();
        const password = passwordEl.value;

        if (!email || !password) {
            showStatus('Please enter both email and password', 'error');
            return;
        }

        if (!storage) {
            showStatus('Storage API not available', 'error');
            return;
        }

        // Disable login button during request
        if (loginButton) {
            loginButton.disabled = true;
            loginButton.textContent = 'Logging in...';
        }

        try {
            const authResponse = await apiAuthenticate(email, password);

            // Store JWT token securely in chrome.storage.local (more secure than sync)
            // chrome.storage.local is encrypted by the browser and stored locally
            storage.local.set({ jwtToken: authResponse.token }, () => {
                if (chrome.runtime.lastError) {
                    showStatus(`Failed to save token: ${chrome.runtime.lastError.message}`, 'error');
                } else {
                    showStatus('Authentication successful! Token saved securely.', 'success');
                    // Clear password field for security
                    passwordEl.value = '';
                }
            });
        } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'Unknown error occurred';
            showStatus(`Authentication failed: ${errorMessage}`, 'error');
        } finally {
            // Re-enable login button
            if (loginButton) {
                loginButton.disabled = false;
                loginButton.textContent = 'Login & Save Token';
            }
        }
    }

    // Show status message
    function showStatus(message: string, type: 'success' | 'error'): void {
        if (!statusMessage) return;

        statusMessage.textContent = message;
        statusMessage.className = `status-message ${type} show`;

        setTimeout(() => {
            statusMessage.classList.remove('show');
        }, 3000);
    }
});

