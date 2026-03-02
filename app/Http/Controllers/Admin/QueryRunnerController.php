<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueryRunnerController extends Controller
{
    /**
     * Predefined queries for common reports
     */
    private array $predefinedQueries = [
        'users_recent' => [
            'name' => 'Recent Users (Last 30 Days)',
            'query' => "SELECT id, name, email, created_at FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) ORDER BY created_at DESC",
        ],
        'active_subscriptions' => [
            'name' => 'Active Subscriptions',
            'query' => "SELECT s.id, u.name, u.email, p.name as plan_name, s.expires_at FROM subscriptions s JOIN users u ON s.user_id = u.id JOIN plans p ON s.plan_id = p.id WHERE s.status = 'active' ORDER BY s.expires_at ASC",
        ],
        'pending_payments' => [
            'name' => 'Pending Payments',
            'query' => "SELECT p.id, u.name, u.email, p.amount, p.payment_method, p.created_at FROM payments p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending' ORDER BY p.created_at DESC",
        ],
        'top_plans' => [
            'name' => 'Top Subscribed Plans',
            'query' => "SELECT p.name, COUNT(s.id) as subscription_count, p.price FROM plans p LEFT JOIN subscriptions s ON p.id = s.plan_id AND s.status = 'active' GROUP BY p.id, p.name, p.price ORDER BY subscription_count DESC",
        ],
        'users_by_month' => [
            'name' => 'User Registrations by Month',
            'query' => "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total FROM users GROUP BY month ORDER BY month DESC LIMIT 12",
        ],
        'revenue_by_month' => [
            'name' => 'Revenue by Month',
            'query' => "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(amount) as total_revenue, COUNT(*) as transaction_count FROM payments WHERE status = 'paid' GROUP BY month ORDER BY month DESC LIMIT 12",
        ],
        'failed_jobs' => [
            'name' => 'Failed Jobs Summary',
            'query' => "SELECT uuid, connection, queue, failed_at FROM failed_jobs ORDER BY failed_at DESC LIMIT 50",
        ],
        'support_tickets' => [
            'name' => 'Open Support Tickets',
            'query' => "SELECT st.id, u.name, u.email, st.subject, st.status, st.priority, st.created_at FROM support_tickets st JOIN users u ON st.user_id = u.id WHERE st.status IN ('open', 'in_progress') ORDER BY st.created_at DESC",
        ],
        'whatsapp_devices' => [
            'name' => 'WhatsApp Devices Status',
            'query' => "SELECT id, session_id, phone_number, status, user_id, created_at FROM whatsapp_devices ORDER BY created_at DESC",
        ],
        'instagram_accounts' => [
            'name' => 'Instagram Accounts Status',
            'query' => "SELECT id, username, account_id, status, user_id, created_at FROM instagram_accounts ORDER BY created_at DESC",
        ],
    ];

    /**
     * Show query interface
     */
    public function index()
    {
        return view('admin.query-runner.index', [
            'predefinedQueries' => $this->predefinedQueries,
        ]);
    }

    /**
     * Execute SELECT query (readonly - only SELECT allowed)
     */
    public function execute(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:5000',
        ]);

        $query = trim($request->input('query'));

        // Safety check: Only allow SELECT queries
        if (!$this->isSelectQuery($query)) {
            return response()->json([
                'success' => false,
                'error' => 'Only SELECT queries are allowed. INSERT, UPDATE, DELETE, DROP, ALTER, and other modifying queries are prohibited.',
            ], 403);
        }

        // Additional safety: Check for forbidden keywords
        if ($this->containsForbiddenKeywords($query)) {
            return response()->json([
                'success' => false,
                'error' => 'Query contains forbidden keywords or operations.',
            ], 403);
        }

        try {
            $startTime = microtime(true);
            
            // Execute the query
            $results = DB::select($query);
            
            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // in milliseconds
            
            // Get column names from first result
            $columns = [];
            if (!empty($results) && is_object($results[0])) {
                $columns = array_keys((array) $results[0]);
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'columns' => $columns,
                'rowCount' => count($results),
                'executionTime' => $executionTime,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Query execution failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * List all database tables
     */
    public function getTables()
    {
        try {
            $tables = [];
            $dbName = config('database.connections.mysql.database');
            
            $results = DB::select("SHOW TABLES");
            $key = 'Tables_in_' . $dbName;
            
            foreach ($results as $table) {
                $tableName = $table->$key;
                
                // Get row count for each table
                $count = DB::table($tableName)->count();
                
                $tables[] = [
                    'name' => $tableName,
                    'rows' => $count,
                ];
            }

            return response()->json([
                'success' => true,
                'tables' => $tables,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get tables: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get table schema
     */
    public function getTableSchema(string $table)
    {
        try {
            // Validate table name (alphanumeric and underscores only)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid table name.',
                ], 400);
            }

            // Get columns info
            $columns = DB::select("SHOW COLUMNS FROM {$table}");
            
            // Get indexes
            $indexes = DB::select("SHOW INDEX FROM {$table}");
            
            // Get create statement
            $createResult = DB::select("SHOW CREATE TABLE {$table}");
            $createStatement = $createResult[0]->{'Create Table'} ?? '';

            // Get row count
            $rowCount = DB::table($table)->count();

            return response()->json([
                'success' => true,
                'table' => $table,
                'columns' => $columns,
                'indexes' => $indexes,
                'createStatement' => $createStatement,
                'rowCount' => $rowCount,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get table schema: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if query is a SELECT query
     */
    private function isSelectQuery(string $query): bool
    {
        // Remove comments and whitespace
        $cleanQuery = preg_replace('/--.*$/m', '', $query);
        $cleanQuery = preg_replace('/\/\*.*?\*\//s', '', $cleanQuery);
        $cleanQuery = trim($cleanQuery);
        
        // Check if starts with SELECT (case insensitive)
        return preg_match('/^SELECT\s/i', $cleanQuery) === 1;
    }

    /**
     * Check for forbidden keywords
     */
    private function containsForbiddenKeywords(string $query): bool
    {
        $forbidden = [
            'INSERT', 'UPDATE', 'DELETE', 'DROP', 'ALTER', 'CREATE', 'TRUNCATE',
            'REPLACE', 'MERGE', 'CALL', 'EXEC', 'EXECUTE', 'GRANT', 'REVOKE',
            'LOCK', 'UNLOCK', 'LOAD', 'INTO OUTFILE', 'INTO DUMPFILE',
        ];

        // Remove comments
        $cleanQuery = preg_replace('/--.*$/m', '', $query);
        $cleanQuery = preg_replace('/\/\*.*?\*\//s', '', $cleanQuery);
        
        // Check for forbidden keywords (not inside quotes)
        foreach ($forbidden as $keyword) {
            // Match keyword as whole word, case insensitive
            if (preg_match('/\b' . $keyword . '\b/i', $cleanQuery)) {
                return true;
            }
        }

        return false;
    }
}
