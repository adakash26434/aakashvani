<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
flash('Facebook साइन-इन हाल उपलब्ध छैन। कृपया इमेल/पासवर्डबाट लगइन गर्नुहोस्।', 'error');
header('Location: /login.php'); exit;
