<?php

// المسار الذي حددناه سابقاً
$opensslConfPath = 'C:/laragon/bin/apache/httpd-2.4.62-240904-win64-VS17/conf/openssl.cnf';

// طباعة معلومات للمساعدة في التشخيص
echo "<h3>SSL Diagnostic Test</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "System Type: " . PHP_OS . "<br>";
echo "OpenSSL Conf Path from script: " . $opensslConfPath . "<br>";
echo "Does conf file exist? " . (file_exists($opensslConfPath) ? '<b>Yes</b>' : '<b>No, path is incorrect!</b>') . "<br><br>";

// محاولة قراءة متغير البيئة (إذا تم تعيينه)
$envVar = getenv('OPENSSL_CONF');
echo "Value of OPENSSL_CONF environment variable: " . ($envVar ? $envVar : 'Not Set') . "<br><br>";

// محاولة إنشاء المفتاح
echo "Attempting to create a new private key...<br>";

$key = openssl_pkey_new([
    'private_key_type' => OPENSSL_KEYTYPE_EC,
    'curve_name' => 'prime256v1',
]);

if ($key === false) {
    echo "<h3 style='color:red;'>Failed to create the key.</h3>";
    echo "<b>Error details:</b><br>";
    // طباعة آخر خطأ حدث في OpenSSL
    while ($msg = openssl_error_string()) {
        echo $msg . "<br>";
    }
} else {
    echo "<h3 style='color:green;'>Success! The key was created successfully.</h3>";
    echo "This means your OpenSSL configuration is now working correctly.";
}