<?php
/**
 * @OA\Schema(
 * )
 */
class UserInsert {
    /**
     * @OA\Property(
     * description="Name of user",
     * default = "Name",
     * required=true
     * )
     * @var string
     */
    public $name;
     /**
     * @OA\Property(
     * description="Email of user",
     * default = "Email",
     * required=true
     * )
     * @var string
     */
    public $email;
         /**
     * @OA\Property(
     * description="Phone of user",
     * default = "Phone",
     * required=true
     * )
     * @var string
     */
    public $phone;
     /**
     * @OA\Property(
     * description="Password of user",
     * default = "Email",
     * required=true
     * )
     * @var string
     */
    public $password;

}

/**
 * @OA\Schema(
 * )
 */
class UserRegisterVerify {
    /**
     * @OA\Property(
     * description="Name of user",
     * default = "Name",
     * required=true
     * )
     * @var string
     */
    public $name;
     /**
     * @OA\Property(
     * description="Email of user",
     * default = "Email",
     * required=true
     * )
     * @var string
     */
    public $email;
         /**
     * @OA\Property(
     * description="Phone of user",
     * default = "Phone",
     * required=true
     * )
     * @var string
     */
    public $phone;
     /**
     * @OA\Property(
     * description="Password of user",
     * default = "Password",
     * required=true
     * )
     * @var string
     */
    public $password;

         /**
     * @OA\Property(
     * description="OTP code from the user",
     * default = "Code",
     * required=true
     * )
     * @var string
     */
    public $code;

}

/**
 * @OA\Schema(
 * )
 */
class UserLoginVerify {
    /**
     * @OA\Property(
     * description="Code from the user",
     * default = "Code",
     * required=true
     * )
     * @var string
     */
    public $code;

}

/**
 * @OA\Schema(
 * )
 */
class UserPasswordUpdate {
    /**
     * @OA\Property(
     * description="Old password of account",
     * default = "string",
     * required=true
     * )
     * @var string
     */
    public $old_password;
     /**
     * @OA\Property(
     * description="New password of the user",
     * default = "string",
     * required=true
     * )
     * @var string
     */
    public $new_password;
}

/**
 * @OA\Schema(
 * )
 */
class UserSendEmail {
     /**
     * @OA\Property(
     * description="Email of user",
     * default = "Email",
     * required=true
     * )
     * @var string
     */
    public $email;
}