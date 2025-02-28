<?php
class AuthController {
    private $conn;

    public function __construct(DatabaseConnection $db) {
        $this->conn = $db->getConnection();
    }

    public function login($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Start session and store user role
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['user_role'];
            return true;
        }
        return false;
    }

    public function checkAccess($requiredRole) {
        session_start();
        if (!isset($_SESSION['user_role'])) {
            return false;
        }

        $userRoles = [
            'admin' => ['admin', 'meeting_access', 'gatekeeper'],
            'meeting_access' => ['admin', 'meeting_access'],
            'gatekeeper' => ['admin', 'gatekeeper']
        ];

        return in_array($_SESSION['user_role'], $userRoles[$requiredRole]);
    }
}