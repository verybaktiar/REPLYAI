/**
 * ReplyAI WhatsApp Service
 * Main entry point for Baileys-based WhatsApp bot
 */

import express from 'express';
import cors from 'cors';
import {
    createSession,
    getSession,
    disconnectSession,
    sendMessage,
    getAllSessionsStatus
} from './src/baileys.js';
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

// Get all sessions status
app.get('/sessions', (req, res) => {
    res.json(getAllSessionsStatus());
});

// Get specific session status
app.get('/status', (req, res) => {
    const sessionId = req.query.sessionId;
    if (!sessionId) {
        return res.status(400).json({ success: false, error: 'sessionId required' });
    }

    const session = getSession(sessionId);
    if (!session) {
        return res.json({
            status: 'disconnected',
            error: 'Session not found',
            phoneNumber: null
        });
    }

    res.json({
        status: session.status,
        phoneNumber: session.phoneNumber,
        lastConnected: session.lastConnected,
        profileName: session.profileName,
        error: session.error
    });
});

// Get current QR code
app.get('/qr', (req, res) => {
    const sessionId = req.query.sessionId;
    if (!sessionId) {
        return res.status(400).json({ success: false, error: 'sessionId required' });
    }

    const session = getSession(sessionId);
    if (session && session.qr) {
        res.json({ success: true, qr: session.qr });
    } else {
        res.json({ success: false, message: 'No QR code available' });
    }
});

// Connect to WhatsApp (Create Session)
app.post('/connect', async (req, res) => {
    const { sessionId } = req.body;
    if (!sessionId) {
        return res.status(400).json({ success: false, error: 'sessionId required' });
    }

    try {
        const result = await createSession(sessionId);
        res.json({ success: true, ...result });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

// Disconnect from WhatsApp
app.post('/disconnect', async (req, res) => {
    const { sessionId } = req.body;
    if (!sessionId) {
        return res.status(400).json({ success: false, error: 'sessionId required' });
    }

    try {
        await disconnectSession(sessionId);
        res.json({ success: true, message: 'Disconnected successfully' });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

// Send message
app.post('/send', async (req, res) => {
    const { sessionId, phone, message, mediaUrl, mediaType } = req.body;

    if (!sessionId || !phone) {
        return res.status(400).json({ success: false, error: 'sessionId and phone are required' });
    }

    try {
        const result = await sendMessage(sessionId, phone, message, mediaUrl, mediaType);
        res.json({ success: true, result });
    } catch (error) {
        res.status(500).json({ success: false, error: error.message });
    }
});

// Start server
app.listen(config.port, async () => {
    console.log(`🚀 ReplyAI WhatsApp Service running on port ${config.port}`);
    console.log(`📡 Laravel webhook URL: ${config.laravelWebhookUrl}`);

    // Auto-load existing sessions
    try {
        const { initAllSessions } = await import('./src/baileys.js');
        await initAllSessions();
    } catch (err) {
        console.error('Failed to auto-load sessions:', err);
    }
});
