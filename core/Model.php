<?php
/**
 * Base Model
 * --------------------------------------
 * Parent class for all Models.
 * Provides easy access to the Database singleton.
 *
 * Every Model (UserModel, WorkspaceModel, etc.) will extend this.
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class Model
{
    /**
     * Shared Database instance — accessible as $this->db in child classes.
     */
    protected $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}

?>