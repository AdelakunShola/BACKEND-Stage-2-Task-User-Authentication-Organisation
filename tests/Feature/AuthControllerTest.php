<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function authenticateUser($user)
    {
        $token = JWTAuth::fromUser($user);
        return $token;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    /** @test */
    use RefreshDatabase;

    /**
     * Test successful user registration
     */
    public function test_register_user_successfully()
    {
        $response = $this->postJson('/auth/register', [
            'firstName' => 'harry',
            'lastName' => 'Ekundayo',
            'email' => 'harry@gmail.com',
            'password' => bcrypt('password123'), // Hash the password
            'phone' => '071049082096',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'accessToken',
                         'user' => [
                             'userId',
                             'firstName',
                             'lastName',
                             'email',
                             'phone',
                         ]
                     ]
                 ]);
    }


    /** @test */
    public function registration_fails_with_invalid_data()
    {
        $response = $this->postJson('/auth/register', [
            'firstName' => '',
            'lastName' => '',
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertStatus(400)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'errors',
                     'statusCode'
                 ]);
    }


    /** @test */
    public function a_user_can_login()
    {
        $response = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'accessToken',
                         'user' => [
                             'id', 'userId', 'firstName', 'lastName', 'email', 'phone'
                         ]
                     ]
                 ]);
    }





    

    /** @test */
    public function a_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'invalidpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'status' => 'Bad request',
                     'message' => 'Authentication failed',
                     'statusCode' => 401,
                 ]);
    }

    /** @test */

    public function successful_login_return_authToken_and_user_details()
    {
        // Create a user
        $user = User::create([
            'userId' => (string) Str::uuid(),
            'firstName' => 'Test2',
            'lastName' => 'Test2',
            'email' => 'email1@email.com',
            'password' => bcrypt('password123'), // Hash the password
            'phone' => '090876712882',
        ]);

        // Make a POST request to login with correct credentials
        $loginData = [
            'email' => 'email1@email.com',
            'password' => 'password123',
        ];

        $response = $this->json('POST', '/auth/login', $loginData);

        // Assert that the login was successful (HTTP status code 200)
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Login successful',
                     'data' => [
                         'accessToken' => true, // Ensure accessToken is present
                         'user' => [
                             'userId' => $user->userId,
                             'firstName' => $user->firstName,
                             'lastName' => $user->lastName,
                             'email' => $user->email,
                             'phone' => $user->phone,
                         ],
                     ],
                 ])
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'accessToken',
                         'user' => [
                             'userId',
                             'firstName',
                             'lastName',
                             'email',
                             'phone',
                         ],
                     ],
                 ]);
    }

    /** @test */





    public function testUserCannotAccessAnotherUsersRecord()
    {
        // Register User 1
        $user1Data = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'Test2',
            'lastName' => 'Test2',
            'email' => 'email1@email.com',
            'password' => bcrypt('password123'), // Hash the password
            'phone' => '090876712882',
        ];
    
        $response1 = $this->json('POST', '/auth/register', $user1Data);
    
        // Assert User 1 registration success
        $response1->assertStatus(201);
    
        // Extract User 1 userId
        $user1UserId = $response1['data']['user']['userId'];
    
        // Register User 2
        $user2Data = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'Test1',
            'lastName' => 'Test1',
            'email' => 'emai@email.com',
            'password' => bcrypt('password123'), // Hash the password
            'phone' => '090876712882',
        ];
    
        $response2 = $this->json('POST', '/auth/register', $user2Data);
    
        // Assert User 2 registration success
        $response2->assertStatus(201);
    
        // Extract User 2 access token
        $user2AccessToken = $response2['data']['accessToken'];
    
        // Attempt to access User 1's record with User 2's token
        $response3 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $user2AccessToken,
        ])->json('GET', '/api/user/' . $user1UserId);
    
        // Assert that User 2 receives a 403 Forbidden error
        $response3->assertStatus(403)
                  ->assertJson([
                      'status' => 'error',
                      'message' => 'Unauthorized',
                  ]);
    }
    

    


    /** @test */
    public function a_user_can_get_their_own_organisations()
    
    {
        // Create a user
        $user = User::factory()->create();
    
        // Authenticate the user
        $token = $this->authenticateUser($user);
    
        // Make a request to get user's organisations
        $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->json('GET', '/api/user/organisations');
    
        // Assert response
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     // Add more assertions based on your expected JSON response
                 ]);
    }
    

    /** @test */
    public function a_user_can_get_an_organization_they_belong_to()
    {
        $org = Organization::factory()->create();
        DB::table('organization_user')->insert([
            'organization_id' => $org->orgId,
            'user_id' => $this->user->userId,
        ]);

        $token = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ])->json('data.accessToken');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/organization/' . $org->orgId);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Organization retrieved successfully',
                     'data' => [
                         'orgId' => $org->orgId,
                         'name' => $org->name,
                         'description' => $org->description,
                     ]
                 ]);
    }

    /** @test */
 

    public function testUserCannotAccessOrganizationTheyDontBelongTo()
    {
        // Register User 1
        $user1Data = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
        ];
    
        $response1 = $this->json('POST', '/auth/register', $user1Data);
    
        // Assert User 1 registration success
        $response1->assertStatus(201);
    
        // Extract User 1 access token
        $user1AccessToken = $response1['data']['accessToken'];
    
        // Register User 2
        $user2Data = [
            'userId' => (string) Str::uuid(),
            'firstName' => 'Jane',
            'lastName' => 'Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'password456',
        ];
    
        $response2 = $this->json('POST', '/api/auth/register', $user2Data);
    
        // Assert User 2 registration success
        $response2->assertStatus(201);
    
        // Extract User 2 access token
        $user2AccessToken = $response2['data']['accessToken'];
    
        // Attempt to access User 1's organization with User 2's token
        $response3 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $user2AccessToken,
        ])->json('GET', '/api/organizations/' . $this->retrieveOrgId($user1AccessToken));
    
        // Assert that User 2 receives a 403 Forbidden error
        $response3->assertStatus(403);
    }
    
    /**
     * Helper function to retrieve orgId using User 1's access token.
     *
     * @param string $accessToken
     * @return string
     */
    protected function retrieveOrgId($accessToken)
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->json('GET', '/api/organizations');
    
        return $response['data']['organizations'][0]['orgId'];
    }

    



    /** @test */
    public function a_user_can_create_an_organisation()
    {
        $user = User::factory()->create();
    
        $this->actingAs($user, 'api');
    
        $response = $this->postJson('/api/organisations', [
            'name' => 'New Organisation',
            'description' => 'A new organisation description',
            'userId' => $user->id,
        ]);
    
        $response->assertStatus(201);
    }

    /** @test */
    public function a_user_can_add_another_user_to_an_organisation()
    {
        $org = Organization::factory()->create();
        DB::table('organization_user')->insert([
            'organization_id' => $org->orgId,
            'user_id' => $this->user->userId,
        ]);

        $anotherUser = User::factory()->create();

        $token = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password123',
        ])->json('data.accessToken');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/organization/' . $org->orgId . '/add-user', [
            'userId' => $anotherUser->userId,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'User added to organisation successfully',
                 ]);
    }
}

