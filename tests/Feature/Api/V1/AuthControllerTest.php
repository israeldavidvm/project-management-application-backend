<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use Database\Seeders\UserSeeder;

use PHPUnit\Framework\Attributes\UsesClass;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\CoversClass;

use PHPUnit\Framework\Attributes\DataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{


    use RefreshDatabase;

    protected function setUp():void{
        
        parent::setUp();

        $this->seed();
        
    }


    public static function loginProvider(): array
    {
        return [
            'login' => 
            [
                [
                    'email' => 'israeldavidvm@gmail.com',
                    'password' => 'Password1234.'
                ],
                200

            ],
            'invalid login request format' => 
            [
                [
                    'email' => 'invalid@noexist.com',
                    'password' => 'dsadsad'
                ],
                422

            ],
             'invalid credentials' => 
            [
                [
                    'email' => 'israeldavidvm@gmail.com',
                    'password' => 'Password1234.23223'
                ],
                401

            ]

        ];
    }

    #[DataProvider('loginProvider')]
    public function test_login($request,$httpStatusCode): void
    {
        
        $response = $this->postJson('/api/v1/auth/login',
            $request
        );

        $response->assertStatus($httpStatusCode);

        if($httpStatusCode==200){
            $response->assertJsonStructure([
                'message',
                'access_token',
                'type_token' ,
                'userData' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at'
                ]
            ]);
        }

    }


    public static function meProvider(): array
    {
        return [
            
            'with authenticate' => 
            [
                [
                    'email' => 'israeldavidvm@gmail.com',
                ],
                200

            ],
            'without autenticate' => 
            [
                null,
                401

            ],

        ];
    }
    
    #[DataProvider('meProvider')]
    public function test_me($request,$httpStatusCode): void
    {

        if($request!=null){

            $user=User::firstWhere('email',$request['email']);

            Sanctum::actingAs(
                $user,
                ['*']
            );

        }

        $response = $this->postJson('/api/v1/auth/me');

        $response->assertStatus($httpStatusCode);

        if($httpStatusCode==200){
            $response->assertJsonStructure([
                'message',
                'userData' => [
                    'id',
                    'name',
                    'email',
                    'email_verified_at',
                    'created_at',
                    'updated_at'
                ]
            ]);
        }

                // $accessToken=
        //     ($request!=null) ? 
        //         User::firstWhere('email',$request['email'])->createToken('API Token')->plainTextToken
        //         : null;

        // $response = $this->withHeaders([
        //     'Authorization'=> 'Bearer '.$accessToken
        // ])->postJson('/api/v1/auth/me');
    }

    public static function logoutProvider(): array
    {
        return [
            
            'with authenticate' => 
            [
                [
                    'email' => 'israeldavidvm@gmail.com',
                ],
                200

            ],
            'without autenticate' => 
            [
                null,
                401

            ],

        ];
    }
    
    #[DataProvider('logoutProvider')]
    public function test_logout($request,$httpStatusCode): void
    {

        if($request!=null){

            $user=User::firstWhere('email',$request['email']);

            Sanctum::actingAs(
                $user,
                ['*']
            );

        }

        $response = $this->postJson('/api/v1/auth/logout');
        
        // dd($response->getContent());

        $response->assertStatus($httpStatusCode);

        // $accessToken=
        //     ($request!=null) ? 
        //         User::firstWhere('email',$request['email'])->createToken('API Token')->plainTextToken
        //         : null;

        // $response = $this->withHeaders([
        //     'Authorization'=> 'Bearer '.$accessToken
        // ])->postJson('/api/v1/auth/logout');

    }

    public static function registerProvider(): array
    {
        return [
            
            'with authenticate' => 
            [
                [
                    'email' => 'israeldavidvm@gmail.com',
                ],
                201

            ],
            'without autenticate' => 
            [
                null,
                401

            ],

        ];
    }
    
    #[DataProvider('registerProvider')]
    public function test_register($request,$httpStatusCode): void
    {

        if($request!=null){

            $user=User::firstWhere('email',$request['email']);

            Sanctum::actingAs(
                $user,
                ['*']
            );

        }

        $response=$this->postJson('/api/v1/auth/register',
        [
            "name"=>'German Garcia',
            "email"=>'germangarcia@gmail.com',
            "password"=>'German1234.'
        ]);

        $response->assertStatus($httpStatusCode);
    
        if($request!=null){

            $this->assertDatabaseHas('users', [
                "name"=>'German Garcia',
                "email"=>'germangarcia@gmail.com',
            ]);
        }
    
                // $accessToken=
        //     ($request!=null) ? 
        //         User::firstWhere('email',$request['email'])->createToken('API Token')->plainTextToken
        //         : null;

        // $response = $this->withHeaders([
        //     'Authorization'=> 'Bearer '.$accessToken
        // ])->postJson('/api/v1/auth/register',[
        //     "name"=>'German Garcia',
        //     "email"=>'germangarcia@gmail.com',
        //     "password"=>'German1234.'
        // ]);
    }

}
