<?php

namespace App\Contracts;

interface AuthService
{
    /**
     * @param  array{nome:string,login:string,senha:string}  $data
     * @return array{usuario:array{id:int,nome:string,login:string},token:string,token_type:string}
     */
    public function register(array $data): array;

    /**
     * @return array{usuario:array{id:int,nome:string,login:string},token:string,token_type:string}
     */
    public function login(string $login, string $senha): array;
}
