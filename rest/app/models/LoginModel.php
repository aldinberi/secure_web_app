<?php
/**
 * @OA\Schema(
 * )
 */
class LoginUser {
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
     * description="Password of user",
     * default = "Password",
     * required=true
     * )
     * @var string
     */
    public $password;
}