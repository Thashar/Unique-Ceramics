<?php
require_once dirname(__DIR__) . '/config.php';
redirect(is_admin_logged() ? url('admin/dashboard.php') : url('admin/login.php'));
