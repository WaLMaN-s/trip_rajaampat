<?php
require_once '../includes/config.php';
require_once '../includes/session.php';
require_once '../includes/function.php';

destroy_session();
redirect('index.php');