/**
 * Baileys Connection Handler
 * Manages WhatsApp Web connection using Baileys library
 */

import makeWASocket, {
    DisconnectReason,
    useMultiFileAuthState,
    makeCacheableSignalKeyStore,
    fetchLatestBaileysVersion
} from '@whiskeysockets/baileys';
import { Boom } from '@hapi/boom';
import pino from 'pino';
import QRCode from 'qrcode';
import fs from 'fs';
import path from 'path';
import { config } from './config.js';
import { sendWebhook } from './webhook.js';

// Logger
const logger = pino({ level: config.logLevel });

// State variables
let sock = null;
let currentQR = null;
let connectionStatus = {
    status: 'disconnected',
    phoneNumber: null,
    lastConnected: null,
    error: null
};

/**
 * Create Baileys connection
 */
export async function createBaileysConnection() {
    // Ensure session directory exists
    const sessionDir = path.resolve(config.sessionPath);
    if (!fs.existsSync(sessionDir)) {
        fs.mkdirSync(sessionDir, { recursive: true });
    }

    // Load auth state
    const { state, saveCreds } = await useMultiFileAuthState(sessionDir);

    // Fetch latest Baileys version
    const { version, isLatest } = await fetchLatestBaileysVersion();
    console.log(`📱 Using Baileys v${version.join('.')}, isLatest: ${isLatest}`);

    // Create socket connection
    sock = makeWASocket({
        version,
        logger,
        printQRInTerminal: true,
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, logger)
        },
        generateHighQualityLinkPreview: true,
        getMessage: async (key) => {
            return { conversation: '' };
        }
    });

    // Handle connection updates
    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        // QR Code received
        if (qr) {
            console.log('📲 QR Code received, scan with WhatsApp');
            currentQR = await QRCode.toDataURL(qr);
            connectionStatus.status = 'waiting_qr';

            // Notify Laravel about QR
            sendWebhook('qr', { qr: currentQR });
        }

        // Connection closed
        if (connection === 'close') {
            currentQR = null;
            const statusCode = (lastDisconnect?.error instanceof Boom)
                ? lastDisconnect.error.output.statusCode
                : 500;

            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

            console.log(`❌ Connection closed. Status code: ${statusCode}. Reconnect: ${shouldReconnect}`);

            connectionStatus.status = 'disconnected';
            connectionStatus.error = lastDisconnect?.error?.message || 'Connection closed';

            // Notify Laravel about disconnect
            sendWebhook('status', {
                status: 'disconnected',
                reason: connectionStatus.error,
                shouldReconnect
            });

            if (shouldReconnect) {
                setTimeout(() => {
                    console.log('🔄 Attempting to reconnect...');
                    createBaileysConnection();
                }, config.reconnectInterval);
            }
        }

        // Connection opened
        if (connection === 'open') {
            currentQR = null;
            const phoneNumber = sock.user?.id?.split(':')[0] || sock.user?.id?.split('@')[0];

            console.log(`✅ Connected as ${phoneNumber}`);

            connectionStatus = {
                status: 'connected',
                phoneNumber,
                lastConnected: new Date().toISOString(),
                error: null
            };

            // Notify Laravel about connection
            sendWebhook('status', {
                status: 'connected',
                phoneNumber,
                name: sock.user?.name
            });
        }
    });

    // Handle credential updates
    sock.ev.on('creds.update', saveCreds);

    // Handle incoming messages
    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        console.log(`📨 messages.upsert event - type: ${type}, count: ${messages.length}`);

        for (const msg of messages) {
            console.log(`📋 Processing message:`, JSON.stringify(msg.key));

            // Skip status broadcasts
            if (msg.key.remoteJid === 'status@broadcast') {
                console.log('⏭️ Skipping status broadcast');
                continue;
            }

            // Skip group messages
            if (msg.key.remoteJid.endsWith('@g.us')) {
                console.log('⏭️ Skipping group message');
                continue;
            }

            // Skip newsletter/channel messages
            if (msg.key.remoteJid.endsWith('@newsletter')) {
                console.log('⏭️ Skipping newsletter message');
                continue;
            }

            // Skip own messages
            if (msg.key.fromMe) {
                console.log('⏭️ Skipping own message');
                continue;
            }

            const senderJid = msg.key.remoteJid;
            const senderNumber = senderJid.split('@')[0];
            let messageContent = extractMessageContent(msg);

            if (!messageContent) {
                messageContent = '[Unknown message type]';
            }

            console.log(`📩 Message from ${senderNumber}: ${messageContent}`);

            // Send to Laravel webhook
            const webhookResult = await sendWebhook('message', {
                messageId: msg.key.id,
                from: senderNumber,
                fromJid: senderJid,
                message: messageContent,
                messageType: getMessageType(msg),
                timestamp: msg.messageTimestamp,
                pushName: msg.pushName || 'Unknown'
            });

            console.log(`📤 Webhook result:`, webhookResult);
        }
    });

    return sock;
}

/**
 * Extract message content from various message types
 */
function extractMessageContent(msg) {
    const message = msg.message;
    if (!message) return '';

    if (message.conversation) return message.conversation;
    if (message.extendedTextMessage?.text) return message.extendedTextMessage.text;
    if (message.imageMessage?.caption) return `[Image] ${message.imageMessage.caption}`;
    if (message.videoMessage?.caption) return `[Video] ${message.videoMessage.caption}`;
    if (message.documentMessage?.caption) return `[Document] ${message.documentMessage.caption}`;
    if (message.audioMessage) return '[Audio]';
    if (message.stickerMessage) return '[Sticker]';
    if (message.contactMessage) return '[Contact]';
    if (message.locationMessage) return '[Location]';

    return '[Unknown message type]';
}

/**
 * Get message type
 */
function getMessageType(msg) {
    const message = msg.message;
    if (!message) return 'unknown';

    if (message.conversation || message.extendedTextMessage) return 'text';
    if (message.imageMessage) return 'image';
    if (message.videoMessage) return 'video';
    if (message.audioMessage) return 'audio';
    if (message.documentMessage) return 'document';
    if (message.stickerMessage) return 'sticker';
    if (message.contactMessage) return 'contact';
    if (message.locationMessage) return 'location';

    return 'unknown';
}

/**
 * Get current connection status
 */
export function getConnectionStatus() {
    return connectionStatus;
}

/**
 * Get current QR code
 */
export function getCurrentQR() {
    return currentQR;
}

/**
 * Disconnect session
 */
export async function disconnectSession() {
    if (sock) {
        await sock.logout();
        sock = null;
    }

    currentQR = null;
    connectionStatus = {
        status: 'disconnected',
        phoneNumber: null,
        lastConnected: null,
        error: null
    };

    // Clear session files
    const sessionDir = path.resolve(config.sessionPath);
    if (fs.existsSync(sessionDir)) {
        fs.rmSync(sessionDir, { recursive: true, force: true });
    }
}

/**
 * Send message to a phone number or JID
 * Supports both phone number format and direct JID (including @lid format)
 */
export async function sendMessage(phone, message, mediaUrl = null, mediaType = null) {
    if (!sock || connectionStatus.status !== 'connected') {
        throw new Error('WhatsApp is not connected');
    }

    // Check if input is already a JID (contains @)
    // This handles @lid, @s.whatsapp.net, and @g.us formats
    const jid = phone.includes('@') ? phone : formatPhoneToJid(phone);

    // Simulate typing
    await sock.presenceSubscribe(jid);
    await sock.sendPresenceUpdate('composing', jid);
    await new Promise(resolve => setTimeout(resolve, config.typingDelay));
    await sock.sendPresenceUpdate('paused', jid);

    let result;

    if (mediaUrl && mediaType) {
        // Send media message
        const mediaMessage = {};
        mediaMessage[mediaType] = { url: mediaUrl };
        if (message) mediaMessage.caption = message;

        result = await sock.sendMessage(jid, mediaMessage);
    } else {
        // Send text message
        result = await sock.sendMessage(jid, { text: message });
    }

    console.log(`📤 Message sent to ${phone}`);
    return result;
}

/**
 * Format phone number to WhatsApp JID
 */
function formatPhoneToJid(phone) {
    // Remove any non-numeric characters
    let cleaned = phone.replace(/\D/g, '');

    // Handle Indonesian numbers
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substring(1);
    }

    // Add @s.whatsapp.net suffix
    return cleaned + '@s.whatsapp.net';
}
