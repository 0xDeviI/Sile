<?php

namespace Signite\Modules;

class Validity {
    
        public static function isValidEmail($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        }
    
        public static function isValidMobile($mobile) {
            return preg_match("/^09[0-9]{9}$/", $mobile);
        }
    
        public static function isValidUsername($username) {
            return preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username);
        }
    
        public static function isValidPassword($password) {
            return preg_match("/^[a-zA-Z0-9!@#$*()=]{6,}$/", $password);
        }
    
        public static function isValidName($name) {
            return preg_match("/^[a-zA-Z0-9_]{3,20}$/", $name);
        }
    
        public static function isValidAddress($address) {
            return preg_match("/^[a-zA-Z0-9_]{3,20}$/", $address);
        }
    
        public static function isValidAge($age) {
            return preg_match("/^[0-9]{1,2}$/", $age);
        }
    
        public static function isValidGender($gender) {
            $genders = ["male", "female"];
            return in_array($gender, $genders);
        }

        public static function safeMysqlInput($data, $db) {
            return $db == null ? $data : mysqli_real_escape_string($db, $data);
        }
}