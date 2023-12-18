import {defineConfig} from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [react()],
    server: {
        host: "192.168.0.112",
        port: 3000,
        strictPort: true,
        proxy: {
            "/api/": {
                target: "https://onpremise.dev.apitest.team/",
                changeOrigin: true,
                cookieDomainRewrite: "192.168.0.112",
                secure: false,
            }
        }
    }
})