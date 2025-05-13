<?php

if (!function_exists('isPrime')) {
    function isPrime($number)
    {
        if($number<=1) return false;
        $i = $number - 1;
        while($i>1) {
        if($number%$i==0) return false;
        $i--;
        }
        return true;
    }
}

if (!function_exists('emailFromLoginCertificate')) {
    function emailFromLoginCertificate()
    {
        if (!isset($_SERVER['SSL_CLIENT_CERT'])) return null;

        $clientCertPEM = $_SERVER['SSL_CLIENT_CERT'];
        $certResource = openssl_x509_read($clientCertPEM);
        if(!$certResource) return null;
        $subject = openssl_x509_parse($certResource, false);
        if(!isset($subject['subject']['emailAddress'])) return null;
        return $subject['subject']['emailAddress'];
    }
}
