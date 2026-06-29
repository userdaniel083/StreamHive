<?php
// StreamHive - User Model
// Klasse voor gebruiker database operaties

class UserModel {
    
    // Maak nieuw gebruiker account aan
    public function createUser($name, $email, $password, $role = 'user') {
        // Hash het wachtwoord veilig
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        return query(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)",
            [$name, $email, $hashedPassword, $role]
        );
    }
    
    // Haal gebruiker op via ID
    public function getUserById($id) {
        $result = query("SELECT id, name, email, role FROM users WHERE id = ?", [$id]);
        return $result ? $result[0] : null;
    }
    
    // Haal gebruiker op via email adres (voor inloggen)
    public function getUserByEmail($email) {
        $result = query("SELECT * FROM users WHERE email = ?", [$email]);
        return $result ? $result[0] : null;
    }
    
    // Verifieer gebruiker inloggegevens
    public function authenticateUser($email, $password) {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    // Update gebruiker informatie
    public function updateUser($id, $name, $email) {
        return query(
            "UPDATE users SET name = ?, email = ? WHERE id = ?",
            [$name, $email, $id]
        );
    }
    
    // Haal alle gebruikers op
    public function getAllUsers() {
        return query("SELECT id, name, email, role, created_date FROM users");
    }
    
    // Verwijder gebruiker
    public function deleteUser($id) {
        return query("DELETE FROM users WHERE id = ?", [$id]);
    }
    
    // Controleer of gebruiker admin is
    public function isAdmin($userId) {
        $user = $this->getUserById($userId);
        return $user && $user['role'] === 'admin';
    }
    
    // Controleer of gebruiker al bestaat
    public function userExists($email) {
        $user = $this->getUserByEmail($email);
        return $user !== null;
    }
    
    // Update gebruiker rol (alleen admin)
    public function updateUserRole($userId, $newRole) {
        return query(
            "UPDATE users SET role = ? WHERE id = ?",
            [$newRole, $userId]
        );
    }
}

?>
