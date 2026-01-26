/**
 * ReplyAI Browser Notifications Service
 * Handles push notifications for new messages
 */
class NotificationService {
    constructor() {
        this.permission = 'default';
        this.init();
    }

    async init() {
        if (!('Notification' in window)) {
            console.log('Browser tidak mendukung notifications');
            return;
        }

        this.permission = Notification.permission;

        if (this.permission === 'default') {
            // Don't request permission immediately, wait for user action
            console.log('Notification permission belum diminta');
        }
    }

    async requestPermission() {
        if (!('Notification' in window)) {
            return false;
        }

        try {
            const permission = await Notification.requestPermission();
            this.permission = permission;
            return permission === 'granted';
        } catch (error) {
            console.error('Error requesting notification permission:', error);
            return false;
        }
    }

    isEnabled() {
        return this.permission === 'granted';
    }

    async showNotification(title, options = {}) {
        if (!this.isEnabled()) {
            console.log('Notifications not enabled');
            return null;
        }

        const defaultOptions = {
            icon: '/favicon.ico',
            badge: '/favicon.ico',
            vibrate: [200, 100, 200],
            tag: 'replyai-notification',
            renotify: true,
            requireInteraction: false,
            silent: false,
            ...options
        };

        try {
            const notification = new Notification(title, defaultOptions);

            notification.onclick = (event) => {
                event.preventDefault();
                window.focus();
                if (options.url) {
                    window.location.href = options.url;
                }
                notification.close();
            };

            // Auto close after 5 seconds
            setTimeout(() => notification.close(), 5000);

            return notification;
        } catch (error) {
            console.error('Error showing notification:', error);
            return null;
        }
    }

    // Show notification for new message
    showNewMessageNotification(senderName, messagePreview, conversationId) {
        return this.showNotification(`💬 ${senderName}`, {
            body: messagePreview.substring(0, 100),
            tag: `message-${conversationId}`,
            url: `/inbox?conversation_id=${conversationId}`,
            data: { conversationId }
        });
    }

    // Play notification sound
    playSound() {
        try {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.5;
            audio.play().catch(() => {
                // Ignore autoplay errors
            });
        } catch (error) {
            console.error('Error playing notification sound:', error);
        }
    }
}

// Initialize global notification service
window.notificationService = new NotificationService();

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = NotificationService;
}
