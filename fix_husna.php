<?php
$filePath = 'app/Helpers/HusnaHelper.php';
$content = file($filePath);
$line72 = "            ['name' => 'Al-Qayyum', 'meaning' => 'O\'z-o\'zidan turuvchi, boshqalarni tutib turuvchi'],\n";
$content[71] = $line72;
file_put_contents($filePath, implode('', $content));
echo "Fixed line 72\n";
