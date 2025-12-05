
<?php
/*
* Helper tool used during development to generate secure password hashes.
* How to use:
* - Run this script once to generate password hashes for your users.
* - Then copy the hashes into your database.
*/

function createPassword($plain_password) {
    return password_hash($plain_password, PASSWORD_DEFAULT);
}

echo "Password Hash Generator\n";
echo "======================\n\n";

// Generate sample passwords
$passwords = [
    'password123' => createPassword('password123'),
    'admin2024' => createPassword('admin2024'),
    'client123' => createPassword('client123'),
];

foreach ($passwords as $plain => $hash) {
    echo "Plain: $plain\n";
    echo "Hash: $hash\n\n";
}

echo "Use these hashes in your INSERT statements.\n";
?>
