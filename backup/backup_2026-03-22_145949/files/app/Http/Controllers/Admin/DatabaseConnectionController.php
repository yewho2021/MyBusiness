<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseConnectionController extends Controller
{
    /**
     * List all saved database connections.
     */
    public function index()
    {
        $connections = DatabaseConnection::orderBy('name')->get();
        return view('admin.pages.database.connections.index', compact('connections'));
    }

    /**
     * Store a new database connection.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'dbhost'     => 'required|string|max:255',
            'dbport'     => 'nullable|string|max:10',
            'dbname'     => 'required|string|max:255',
            'dbusername' => 'required|string|max:255',
            'dbpassword' => 'required|string|max:255',
            'description'=> 'nullable|string|max:1000',
        ]);

        try {
            DatabaseConnection::create([
                'name'        => $request->name,
                'dbhost'      => $request->dbhost,
                'dbport'      => $request->dbport ?: '3306',
                'dbname'      => $request->dbname,
                'dbusername'  => $request->dbusername,
                'dbpassword'  => $request->dbpassword,
                'description' => $request->description,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Connection saved successfully.']);
            }

            return redirect()->route('admin.database.connections.index')
                ->with('success', 'Database connection saved successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Save failed: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Save failed: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing database connection.
     */
    public function update(Request $request, $id)
    {
        $conn = DatabaseConnection::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:100',
            'dbhost'     => 'required|string|max:255',
            'dbport'     => 'nullable|string|max:10',
            'dbname'     => 'required|string|max:255',
            'dbusername' => 'required|string|max:255',
            'dbpassword' => 'nullable|string|max:255',
            'description'=> 'nullable|string|max:1000',
        ]);

        try {
            $conn->name       = $request->name;
            $conn->dbhost     = $request->dbhost;
            $conn->dbport     = $request->dbport ?: '3306';
            $conn->dbname     = $request->dbname;
            $conn->dbusername = $request->dbusername;
            $conn->description = $request->description;

            // Only update password if provided (not blank)
            if ($request->filled('dbpassword')) {
                $conn->dbpassword = $request->dbpassword;
            }

            $conn->save();

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Connection updated successfully.']);
            }

            return redirect()->route('admin.database.connections.index')
                ->with('success', 'Database connection updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Update failed: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a database connection.
     */
    public function destroy($id)
    {
        $conn = DatabaseConnection::findOrFail($id);
        $conn->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Connection deleted.']);
        }

        return redirect()->route('admin.database.connections.index')
            ->with('success', 'Database connection deleted.');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $conn = DatabaseConnection::findOrFail($id);
        $conn->is_active = !$conn->is_active;
        $conn->save();

        return response()->json(['success' => true, 'is_active' => $conn->is_active]);
    }

    /**
     * Test a database connection (AJAX).
     */
    public function test(Request $request)
    {
        $request->validate([
            'dbhost'     => 'required|string',
            'dbport'     => 'nullable|string',
            'dbname'     => 'required|string',
            'dbusername' => 'required|string',
            'dbpassword' => 'required|string',
        ]);

        try {
            config([
                'database.connections.mysql_test' => [
                    'driver'    => 'mysql',
                    'host'      => $request->dbhost,
                    'port'      => $request->dbport ?: '3306',
                    'database'  => $request->dbname,
                    'username'  => $request->dbusername,
                    'password'  => $request->dbpassword,
                    'charset'   => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix'    => '',
                    'strict'    => true,
                ],
            ]);

            DB::purge('mysql_test');
            $pdo = DB::connection('mysql_test')->getPdo();
            $tables = DB::connection('mysql_test')->select('SHOW TABLES');
            DB::purge('mysql_test');

            return response()->json([
                'success' => true,
                'message' => 'Connection successful! Found ' . count($tables) . ' tables.',
            ]);
        } catch (\Exception $e) {
            DB::purge('mysql_test');
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Browse: redirect to database query view with the connection_id in session.
     */
    public function browse($id)
    {
        $conn = DatabaseConnection::findOrFail($id);

        // Update last connected timestamp
        $conn->update(['last_connected_at' => now()]);

        // Store connection ID in session so DatabaseController picks it up
        session(['db_connection_id' => $conn->id]);

        return redirect()->route('admin.database.query');
    }

    /**
     * Clear the external connection (go back to default .env database).
     */
    public function clearConnection()
    {
        session()->forget('db_connection_id');

        return redirect()->route('admin.database.connections.index');
    }
}
