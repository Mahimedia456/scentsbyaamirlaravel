import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
  build: {
    // Three.js is lazy-loaded only on pages that render the 3D atmosphere.
    // Its optional chunk is intentionally allowed to be larger than Vite's
    // generic 500 kB warning threshold while the initial app stays split.
    chunkSizeWarningLimit: 800,
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (!id.includes('node_modules')) return;
          if (id.includes('/three/')) return 'three';
          if (id.includes('/gsap/')) return 'motion';
          if (id.includes('/alpinejs/') || id.includes('/@vue/reactivity/')) return 'alpine';
          if (id.includes('/lenis/')) return 'lenis';
          return 'vendor';
        },
      },
    },
  },
});
