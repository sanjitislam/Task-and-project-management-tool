<?php
/**
 * User Model
 * --------------------------------------
 * Handles all database queries for the `users` table.
 * NO HTML, NO business logic — only data access.
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class UserModel extends Model
{
    /**
     * Find a user by email (used during login).
     */
    public function findByEmail($email)
    {
        return $this->db->selectOne(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            "s",
            [$email]
        );
    }

    /**
     * Find a user by ID.
     */
    public function findById($id)
    {
        return $this->db->selectOne(
            "SELECT * FROM users WHERE id = ? LIMIT 1",
            "i",
            [$id]
        );
    }

    /**
     * Verify a user's password against the stored hash.
     * Returns the user array if correct, false if not.
     */
    public function verifyLogin($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!$user['is_active']) {
            return 'inactive';   // signal that account is disabled
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        return $user;
    }

    /**
     * Count total users in the system.
     */
    public function countAll(){

       $row = $this->db->selectOne("SELECT COUNT(*) AS total FROM users");
       return (int)($row['total'] ?? 0);
    }

    /**
     * Count users grouped by role.
     * Returns array like: ['admin' => 2, 'member' => 5, ...]
     */
    
    public function countByRole()
    {
        $rows = $this->db->select(
            "SELECT role, COUNT(*) AS total 
             FROM users 
             GROUP BY role"
        );

        // Initialize all roles to 0 (so missing roles still show)
        $counts = [
            'admin'     => 0,
            'team_lead' => 0,
            'member'    => 0,
            'client'    => 0
        ];

        foreach ($rows as $row) {
            $counts[$row['role']] = (int)$row['total'];
        }

        return $counts;
    }


}


?>