<?php
// Stub — configure Google OAuth client_id / secret to enable.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
flash('Google साइन-इन हाल उपलब्ध छैन। कृपया इमेल/पासवर्डबाट लगइन गर्नुहोस्।', 'error');
header('Location: /login.php'); exit;
