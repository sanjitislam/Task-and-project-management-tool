<?php
/**
 * Base Controller
 * --------------------------------------
 * Parent class for all Controllers.
 * Provides common methods like loading a view or a model.
 */

if (!defined('APP_RUNNING')) {
    die('Direct access not allowed.');
}

class Controller
{
    /**
     * Load a Model by name.
     *
     * Example: $this->model('UserModel') returns a new UserModel object.
     */
    protected function model($name)
    {
        $path = __DIR__ . '/../models/' . $name . '.php';

        if (!file_exists($path)) {
            die("Model not found: $name");
        }

        require_once $path;
        return new $name();
    }

    /**
     * Render a View file and pass data to it.
     *
     * Example: $this->view('auth/login', ['error' => 'Bad password']);
     *
     * Inside the view, you can use $error directly.
     */
    protected function view($viewPath, $data = [])
    {
        $file = __DIR__ . '/../views/' . $viewPath . '.php';

        if (!file_exists($file)) {
            die("View not found: $viewPath");
        }

        // extract() turns array keys into variables.
        // e.g. ['error' => 'foo'] becomes  $error = 'foo'
        extract($data);

        require $file;
    }

    /**
     * Send a JSON response (used by AJAX endpoints).
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}


?>