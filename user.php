<?php

use Illuminate\Support\Facades\Hash;

$db = new SQLite3('database/database.sqlite');

$results = $db->query('SELECT * FROM users');
while ($row = $results->fetchArray()) {
    print_r($row);
}


echo password_hash('LB_outiltheque',PASSWORD_DEFAULT);
echo "\n";

?>