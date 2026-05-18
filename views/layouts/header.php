<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Admin Panel</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
</head>
<body class="admin-body">

<header class="topbar">
    <div class="topbar-left">
        <h1 class="brand">📋 Task Management Admin</h1>
    </div>

    <div class="topbar-right">
        <span class="user-info">
            👤 <?= e(Auth::user()['name']) ?>
            <small>(<?= e(Auth::user()['role']) ?>)</small>
        </span>
        <a href="<?= BASE_URL ?>logout" class="btn-logout">Logout</a>
    </div>
</header>

<div class="admin-wrapper">

