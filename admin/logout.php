<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

destroy_session();
redirect('login.php');