/**
 * Global type declarations
 */

declare global {
  interface Window {
    bootstrap?: {
      Modal: {
        getInstance: (element: HTMLElement | string) => { show: () => void; hide: () => void } | null;
        getOrCreateInstance: (element: HTMLElement | string) => { show: () => void; hide: () => void };
      };
      Offcanvas: {
        getInstance: (element: HTMLElement | string) => { hide: () => void } | null;
      };
    };
  }
}

export {};

