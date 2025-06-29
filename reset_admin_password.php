<?php
// Script to reset admin user password
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// New password for admin
$newPassword = 'Admin@123';

try {
    // Find admin users
    $adminUsers = User::where('role', 'admin')->get();
    
    if ($adminUsers->isEmpty()) {
        echo "No admin users found. Checking for super_admin role...\n";
        $adminUsers = User::where('role', 'super_admin')->get();
    }
    
    if ($adminUsers->isEmpty()) {
        echo "No admin users found. Looking for any user...\n";
        $adminUsers = User::take(5)->get();
        foreach ($adminUsers as $user) {
            echo "User ID: {$user->id}, Name: {$user->name}, Email: {$user->email}, Role: {$user->role}\n";
        }
        
        echo "\nEnter user ID to reset password for: ";
        $userId = trim(fgets(STDIN));
        $user = User::find($userId);
        
        if (!$user) {
            die("User not found with ID: {$userId}\n");
        }
        
        $user->password = Hash::make($newPassword);
        $user->save();
        
        echo "Password reset for user: {$user->name} (ID: {$user->id}) with new password: {$newPassword}\n";
    } else {
        // Reset password for all admin users
        foreach ($adminUsers as $admin) {
            $admin->password = Hash::make($newPassword);
            $admin->save();
            echo "Admin password reset for {$admin->name} (ID: {$admin->id}) with new password: {$newPassword}\n";
        }
    }
    
    echo "Password reset operation completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
