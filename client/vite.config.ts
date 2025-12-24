import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@shared': path.resolve(__dirname, '../shared/src'),
    },
  },
  // Environment variables are automatically loaded from .env files
  // Variables prefixed with VITE_ are exposed to client code via import.meta.env
  // Vite will replace these at build time
})
