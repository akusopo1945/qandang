const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');

const app = express();
const PORT = 4501;

// Logging middleware
app.use((req, res, next) => {
    console.log(`[${new Date().toISOString()}] ${req.method} ${req.url}`);
    next();
});

// Proxy for AI Service
app.use('/ai', createProxyMiddleware({
    target: 'http://localhost:8501',
    changeOrigin: true,
    pathRewrite: {
        '^/ai': '',
    },
}));

// Proxy for Backend (API, Admin, and Web)
app.use('/', createProxyMiddleware({
    target: 'http://localhost:8001',
    changeOrigin: true,
    filter: (pathname) => {
        // Exclude /ai as it's handled above
        return !pathname.startsWith('/ai');
    }
}));

app.listen(PORT, '127.0.0.1', () => {
    console.log(`Qandang Gateway running on port ${PORT}`);
});
