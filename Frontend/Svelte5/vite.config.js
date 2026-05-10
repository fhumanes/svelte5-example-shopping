import { defineConfig } from 'vite'
import { svelte } from '@sveltejs/vite-plugin-svelte'
import path from 'path';

// https://vite.dev/config/
export default defineConfig({
  plugins: [svelte()],
  base: '/my-shopping/',
  build: { chunkSizeWarningLimit: 3000 },
  resolve: {
    alias: {
      $lib: path.resolve('./src/lib'),
      $svar: path.resolve('./src/svar')
    }
  }
})
