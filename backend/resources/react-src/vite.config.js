import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'
import { VitePWA } from 'vite-plugin-pwa'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    react(),
    
    // PWA Plugin (instalar: npm install -D vite-plugin-pwa)
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.ico', 'robots.txt', 'icons/*.png'],
      manifest: {
        name: 'Sistema Créditos Eider',
        short_name: 'Créditos Eider',
        description: 'Sistema de gestión de créditos, cobros y comisiones',
        theme_color: '#3b82f6',
        icons: [
          {
            src: 'icons/icon-192x192.png',
            sizes: '192x192',
            type: 'image/png'
          },
          {
            src: 'icons/icon-512x512.png',
            sizes: '512x512',
            type: 'image/png'
          }
        ]
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff,woff2}'],
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/unpkg\.com\/.*/i,
            handler: 'CacheFirst',
            options: {
              cacheName: 'unpkg-cache',
              expiration: {
                maxEntries: 10,
                maxAgeSeconds: 60 * 60 * 24 * 365 // 1 año
              },
              cacheableResponse: {
                statuses: [0, 200]
              }
            }
          }
        ]
      }
    })
  ],
  
  // Configuración para desarrollo
  server: {
    port: 5173,
    host: true, // Permitir acceso desde red local (útil para probar en móvil)
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },

  // Configuración para producción
  build: {
    // El output irá a ../../public/build (desde resources/react-src)
    outDir: '../../public/build',
    emptyOutDir: true,
    manifest: true,
    
    // Optimizaciones para móvil
    target: 'es2015', // Compatibilidad con navegadores móviles modernos
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true, // Eliminar console.log en producción
        drop_debugger: true,
      },
    },
    
    // Code splitting optimizado
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'index.html'),
      },
      output: {
        // Separar vendor chunks
        manualChunks: {
          'react-vendor': ['react', 'react-dom', 'react-router-dom'],
          'map-vendor': ['leaflet', 'react-leaflet'],
          'utils': ['axios'],
        },
        // Nombres de archivo con hash para cache busting
        entryFileNames: 'assets/[name]-[hash].js',
        chunkFileNames: 'assets/[name]-[hash].js',
        assetFileNames: 'assets/[name]-[hash].[ext]',
      },
    },
    
    // Tamaño máximo de chunks (advertencia)
    chunkSizeWarningLimit: 1000,
    
    // Sourcemaps solo en desarrollo
    sourcemap: false,
  },

  // Alias para imports más limpios
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
      '@components': path.resolve(__dirname, './src/components'),
      '@pages': path.resolve(__dirname, './src/pages'),
      '@services': path.resolve(__dirname, './src/services'),
      '@contexts': path.resolve(__dirname, './src/contexts'),
      '@utils': path.resolve(__dirname, './src/utils'),
      '@hooks': path.resolve(__dirname, './src/hooks'),
    },
  },

  // Optimizaciones adicionales
  optimizeDeps: {
    include: ['react', 'react-dom', 'react-router-dom', 'axios', 'leaflet'],
  },

  // CSS
  css: {
    devSourcemap: true,
  },
})