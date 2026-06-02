<?php
// models/Admin.php
// Admin Database Model for Wiloty Foundation

require_once __DIR__ . '/../config/db.php';

class Admin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Authenticate Admin login credentials
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            if ($admin['must_reset_password']) {
                $_SESSION['pending_reset_id'] = $admin['id'];
                $_SESSION['pending_reset_username'] = $admin['username'];
                return 'must_reset';
            }

            // Establish safe admin session details
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_permissions'] = json_decode($admin['permissions'] ?? '[]', true) ?: [];
            return 'success';
        }
        return 'invalid';
    }

    // Check if current logged-in user has permission
    public static function hasPermission($section) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $role = $_SESSION['admin_role'] ?? 'admin';
        if ($role === 'superadmin') {
            return true; // Superadmin has access to everything
        }
        
        $permissions = $_SESSION['admin_permissions'] ?? [];
        return in_array($section, $permissions);
    }

    // Terminate Admin Session
    public static function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    // Enforce Dashboard Protection
    public static function protect() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: login.php");
            exit();
        }
    }

    // Create a new Administrator Account
    public function create($username, $email, $full_name, $phone, $role = 'admin', $permissions = []) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$';
        $random_password = '';
        for ($i = 0; $i < 10; $i++) {
            $random_password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $password_hash = password_hash($random_password, PASSWORD_BCRYPT);
        
        $permissions_json = json_encode($permissions);
        $stmt = $this->db->prepare("INSERT INTO admins (username, email, full_name, phone, password_hash, role, permissions, must_reset_password) VALUES (:username, :email, :full_name, :phone, :password_hash, :role, :permissions, 1)");
        
        if ($stmt->execute([
            'username' => $username,
            'email' => $email,
            'full_name' => $full_name,
            'phone' => $phone,
            'password_hash' => $password_hash,
            'role' => $role,
            'permissions' => $permissions_json
        ])) {
            return $random_password;
        }
        return false;
    }

    // Update an existing Administrator Account
    public function update($id, $username, $email, $full_name, $phone, $role = 'admin', $permissions = []) {
        $permissions_json = json_encode($permissions);
        
        $stmt = $this->db->prepare("UPDATE admins SET username = :username, email = :email, full_name = :full_name, phone = :phone, role = :role, permissions = :permissions WHERE id = :id");
        return $stmt->execute([
            'username' => $username,
            'email' => $email,
            'full_name' => $full_name,
            'phone' => $phone,
            'role' => $role,
            'permissions' => $permissions_json,
            'id' => $id
        ]);
    }

    // Generate random password and force reset
    public function forceResetPassword($id) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$';
        $random_password = '';
        for ($i = 0; $i < 10; $i++) {
            $random_password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $password_hash = password_hash($random_password, PASSWORD_BCRYPT);
        
        $stmt = $this->db->prepare("UPDATE admins SET password_hash = :password_hash, must_reset_password = 1 WHERE id = :id");
        $stmt->execute(['password_hash' => $password_hash, 'id' => $id]);
        
        return $random_password;
    }

    // Update password from user end and remove reset flag
    public function updatePassword($id, $new_password) {
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE admins SET password_hash = :password_hash, must_reset_password = 0 WHERE id = :id");
        return $stmt->execute(['password_hash' => $password_hash, 'id' => $id]);
    }

    // Fetch all Admin users
    public function getAll($limit = 100, $offset = 0) {
        $stmt = $this->db->prepare("SELECT id, username, email, full_name, phone, role, permissions, must_reset_password, created_at FROM admins ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM admins");
        return $stmt->fetch()['total'] ?? 0;
    }

    // Fetch single Admin by ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, full_name, phone, role, permissions, must_reset_password, created_at FROM admins WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete an Admin
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM admins WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
