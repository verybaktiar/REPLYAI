/**
 * Webhook Handler
 * Sends events to Laravel backend
 */

import { config } from './config.js';

/**
 * Send webhook to Laravel
 * @param {string} event - Event type (message, status, qr)
 * @param {object} data - Event data
 */
export async function sendWebhook(event, data) {
    const url = `${config.laravelWebhookUrl}/${event}`;

    console.log(`🔗 Sending webhook to: ${url}`);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WA-Service-Key': process.env.WA_SERVICE_KEY || 'replyai-wa-secret'
            },
            body: JSON.stringify({
                event,
                timestamp: new Date().toISOString(),
                data
            })
        });

        const responseText = await response.text();

        if (!response.ok) {
            console.error(`❌ Webhook failed: ${response.status} ${response.statusText}`);
            console.error(`❌ Response: ${responseText}`);
            return false;
        } else {
            console.log(`✅ Webhook sent: ${event}`);
            return true;
        }
    } catch (error) {
        console.error(`❌ Webhook error: ${error.message}`);
        return false;
    }
}
