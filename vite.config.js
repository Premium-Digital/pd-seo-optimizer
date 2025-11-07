import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
  root: __dirname,
  build: {
    outDir: 'dist',
    minify: 'terser',
    rollupOptions: {
      input: {
        front: path.resolve(__dirname, 'src/js/front.js'),
        admin: path.resolve(__dirname, 'src/js/admin.js'),
      },
      output: {
        entryFileNames: 'pd-seo-optimizer-[name].js',
        assetFileNames: 'pd-seo-optimizer-[name].css',
      },
    },
  },
  css: {
    preprocessorOptions: {
      scss: {
         additionalData: `@use "../scss/_variables.scss" as *;` //
      },
    },
  },
});