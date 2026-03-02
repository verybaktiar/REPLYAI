<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ApiDocumentationController extends Controller
{
    /**
     * Display API documentation.
     */
    public function index()
    {
        $endpoints = $this->getApiEndpoints();
        
        return view('documentation.api', compact('endpoints'));
    }
    
    /**
     * Get all API endpoints.
     */
    private function getApiEndpoints(): array
    {
        return [
            // AI API
            [
                'group' => 'AI Services',
                'endpoints' => [
                    [
                        'method' => 'POST',
                        'path' => '/api/ai/analyze-sentiment',
                        'description' => 'Analyze sentiment of a text',
                        'params' => ['text' => 'string (required)'],
                        'response' => ['sentiment' => 'positive|negative|neutral', 'confidence' => 'float'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/ai/detect-intent',
                        'description' => 'Detect intent from message',
                        'params' => ['message' => 'string (required)'],
                        'response' => ['intent' => 'string', 'confidence' => 'float'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/ai/suggest-replies',
                        'description' => 'Get AI suggested replies',
                        'params' => ['conversation_id' => 'string (required)'],
                        'response' => ['suggestions' => 'array'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/ai/summarize',
                        'description' => 'Summarize conversation',
                        'params' => ['conversation_id' => 'string (required)'],
                        'response' => ['summary' => 'string'],
                    ],
                ],
            ],
            
            // Chat API
            [
                'group' => 'Chat',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/chat/api/conversations',
                        'description' => 'Get all conversations',
                        'params' => ['platform' => 'string (optional)'],
                        'response' => ['conversations' => 'array'],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/chat/api/messages/{platform}/{identifier}',
                        'description' => 'Get messages for a conversation',
                        'params' => [],
                        'response' => ['messages' => 'array'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/chat/api/send',
                        'description' => 'Send a message',
                        'params' => [
                            'platform' => 'string (required)',
                            'identifier' => 'string (required)',
                            'message' => 'string (required)',
                        ],
                        'response' => ['success' => 'boolean', 'message_id' => 'string'],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/chat/api/unread-counts',
                        'description' => 'Get unread message counts',
                        'params' => [],
                        'response' => ['counts' => 'object'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/chat/{type}/{id}/assign',
                        'description' => 'Assign chat to agent',
                        'params' => ['agent_id' => 'integer (required)'],
                        'response' => ['success' => 'boolean'],
                    ],
                ],
            ],
            
            // Contacts API
            [
                'group' => 'Contacts',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/contacts/{type}/{id}/details',
                        'description' => 'Get contact details',
                        'params' => [],
                        'response' => ['contact' => 'object'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/contacts/{type}/{id}/notes',
                        'description' => 'Add note to contact',
                        'params' => ['note' => 'string (required)'],
                        'response' => ['success' => 'boolean', 'note_id' => 'integer'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/contacts/{type}/{id}/block',
                        'description' => 'Block a contact',
                        'params' => [],
                        'response' => ['success' => 'boolean'],
                    ],
                ],
            ],
            
            // Quick Replies API
            [
                'group' => 'Quick Replies',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/quick-replies',
                        'description' => 'Get all quick replies',
                        'params' => [],
                        'response' => ['quick_replies' => 'array'],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/quick-replies/search',
                        'description' => 'Search quick replies',
                        'params' => ['q' => 'string (required)'],
                        'response' => ['results' => 'array'],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/quick-replies/categories',
                        'description' => 'Get quick reply categories',
                        'params' => [],
                        'response' => ['categories' => 'array'],
                    ],
                ],
            ],
            
            // Reports API
            [
                'group' => 'Reports',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/reports/realtime/stats',
                        'description' => 'Get real-time statistics',
                        'params' => [],
                        'response' => ['stats' => 'object'],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/reports/quality/sentiment',
                        'description' => 'Get sentiment analysis',
                        'params' => ['days' => 'integer (optional)'],
                        'response' => ['sentiment_data' => 'object'],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/reports/comparative/compare',
                        'description' => 'Compare periods',
                        'params' => ['period' => 'string (required)'],
                        'response' => ['comparison' => 'object'],
                    ],
                ],
            ],
            
            // Web Widget API
            [
                'group' => 'Web Widget',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/api/web/widget/{api_key}',
                        'description' => 'Get widget configuration',
                        'params' => [],
                        'response' => ['widget' => 'object'],
                        'auth' => false,
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/api/web/chat',
                        'description' => 'Send message from widget',
                        'params' => [
                            'api_key' => 'string (required)',
                            'message' => 'string (required)',
                            'visitor_id' => 'string (required)',
                        ],
                        'response' => ['success' => 'boolean'],
                        'auth' => false,
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/api/web/conversation/{visitor_id}',
                        'description' => 'Get widget conversation',
                        'params' => [],
                        'response' => ['messages' => 'array'],
                        'auth' => false,
                    ],
                ],
            ],
            
            // WhatsApp API
            [
                'group' => 'WhatsApp',
                'endpoints' => [
                    [
                        'method' => 'GET',
                        'path' => '/whatsapp/api/conversations',
                        'description' => 'Get WhatsApp conversations',
                        'params' => [],
                        'response' => ['conversations' => 'array'],
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/whatsapp/api/messages/{phone}',
                        'description' => 'Get messages for a phone number',
                        'params' => [],
                        'response' => ['messages' => 'array'],
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/whatsapp/api/messages/rate',
                        'description' => 'Rate a message',
                        'params' => [
                            'message_id' => 'string (required)',
                            'rating' => 'integer (required)',
                        ],
                        'response' => ['success' => 'boolean'],
                    ],
                ],
            ],
        ];
    }
    
    /**
     * Get OpenAPI specification.
     */
    public function openapi()
    {
        $spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'REPLYAI API',
                'description' => 'API Documentation for REPLYAI Platform',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'Support',
                    'email' => 'support@replai.my.id',
                ],
            ],
            'servers' => [
                [
                    'url' => config('app.url') . '/api',
                    'description' => 'Production API Server',
                ],
            ],
            'paths' => $this->generateOpenApiPaths(),
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                    ],
                ],
            ],
            'security' => [
                ['bearerAuth' => []],
            ],
        ];
        
        return response()->json($spec);
    }
    
    /**
     * Generate OpenAPI paths.
     */
    private function generateOpenApiPaths(): array
    {
        $paths = [];
        $routes = Route::getRoutes();
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            
            // Only include API routes
            if (!str_starts_with($uri, 'api/')) {
                continue;
            }
            
            $methods = array_diff($route->methods(), ['HEAD']);
            
            foreach ($methods as $method) {
                $paths['/' . $uri][strtolower($method)] = [
                    'summary' => $route->getName() ?? 'Endpoint',
                    'parameters' => $this->extractParameters($uri),
                    'responses' => [
                        '200' => [
                            'description' => 'Success',
                        ],
                    ],
                ];
            }
        }
        
        return $paths;
    }
    
    /**
     * Extract parameters from URI.
     */
    private function extractParameters(string $uri): array
    {
        $parameters = [];
        
        preg_match_all('/\{([^}]+)\}/', $uri, $matches);
        
        foreach ($matches[1] as $param) {
            $parameters[] = [
                'name' => $param,
                'in' => 'path',
                'required' => true,
                'schema' => ['type' => 'string'],
            ];
        }
        
        return $parameters;
    }
}
