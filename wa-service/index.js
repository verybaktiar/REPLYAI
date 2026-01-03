/**
 * ReplyAI WhatsApp Service
 * Main entry point for Baileys-based WhatsApp bot
 */

import express from 'express';
import cors from 'cors';
import { createBaileysConnection, getConnectionStatus, disconnectSession, sendMessage, getCurrentQR } from './src/baileys.js';
import { config } from './src/config.js';

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ 
        status: 'ok', 
        service: 'replyai-wa-service',
        timestamp: new Date().toISOString()
    });
});

// Get connection status
app.get('/status', (req, res) => {
    const status = getConnectionStatus();
    res.json(status);
});

// Get current QR code
app.get('/qr', (req, res) => {
    const qr = getCurrentQR();
    if (qr) {
        res.json({ success: true, qr });
    } else {
        res.json({ success: false, message: 'No QR code available. Already connected or not initialized.' });
    }
});

// Connect to WhatsApp
app.post('/connect', async (req, res) => {
    try {
        await createBaileysConnection();
        res.json({ success: true, message: 'Connection initiated. Check /qr for QR code or /status for connection status.' });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

// Disconnect from WhatsApp
app.post('/disconnect', async (req, res) => {
    try {
        await disconnectSession();
        res.json({ success: true, message: 'Disconnected successfully' });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

// Send message
app.post('/send', async (req, res) => {
    const { phone, message, mediaUrl, mediaType } = req.body;
    
    if (!phone || !message) {
        return res.status(400).json({ success: false, error: 'Phone and message are required' });
    }

    try {
        const result = await sendMessage(phone, message, mediaUrl, mediaType);
        res.json({ success: true, result });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

// Start server
app.listen(config.port, () => {
    console.log(`🚀 ReplyAI WhatsApp Service running on port ${config.port}`);
    console.log(`📡 Laravel webhook URL: ${config.laravelWebhookUrl}`);
    
    // Auto-connect on startup if session exists
    createBaileysConnection().catch(err => {
        console.log('⏳ Waiting for connection command...');
    });
});
