<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    
    

public function register(Request $request)
{
    // Validation rules
    $validator = Validator::make($request->all(), [
        'firstName' => 'required|string|max:255',
        'lastName' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email',
        'password' => 'required|string',
        'phone' => 'nullable|string|max:255',
    ]);

    // Check if validation fails
    if ($validator->fails()) {
        return response()->json([
            'status' => 'Bad request',
            'message' => 'Registration unsuccessful',
            'errors' => $validator->errors(),
            'statusCode' => 400
        ], 400, [], JSON_PRETTY_PRINT);
    }

    try {
        // Generate HRM ID for organization
        $orgId = IdGenerator::generate([
            'table' => 'organizations',
            'field' => 'orgId',
            'length' => 5,
            'prefix' => '2'
        ]);

        // Generate HRM ID for user
        $userId = IdGenerator::generate([
            'table' => 'users',
            'field' => 'userId',
            'length' => 5,
            'prefix' => '1'
        ]);

        // Generate a random character
        $randomCharacter = Str::random(4);

        // Concatenate the random character to the orgId and userId
        $orgId .= $randomCharacter;
        $userId .= $randomCharacter;

        // Log the generated IDs
        Log::info('Generated IDs', ['orgId' => $orgId, 'userId' => $userId]);

        // Generate a name for the organization
        $orgName = Organization::generateNameFromUser($request->firstName);

        // Create the organization
        $organization = Organization::create([
            'orgId' => $orgId,
            'name' => $orgName,
            'description' => 'Default description',
        ]);

        // Create the user
        $user = User::create([
            'userId' => $userId,
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Log organization and user creation
        Log::info('Organization created', ['orgId' => $organization->orgId]);
        Log::info('User created', ['userId' => $user->userId]);

        // Attach the user to the organization in the pivot table
        DB::table('organization_user')->insert([
            'organization_id' => $organization->orgId,
            'user_id' => $user->userId
        ]);

        // Generate an access token 
        $accessToken = $user->createToken('authToken')->plainTextToken;

        // Return the successful registration response
        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful',
            'data' => [
                'accessToken' => $accessToken,
                'user' => [
                    'id' => $user->id,
                    'userId' => $user->userId,
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ]
            ]
        ], 201, [], JSON_PRETTY_PRINT);

    } catch (\Illuminate\Database\QueryException $e) {
        Log::error('Database Query Exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
 
        return response()->json([
            'status' => 'Bad request',
            'message' => 'Registration unsuccessful',
            'statusCode' => 400,
            'error' => $e->getMessage()
        ], 400, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        Log::error('General Exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

        return response()->json([
            'status' => 'Bad request',
            'message' => 'Registration unsuccessful.',
            'statusCode' => 500,
            'error' => $e->getMessage()
        ], 400, [], JSON_PRETTY_PRINT);
    }
}



















    

   public function login(Request $request)
{
    // Validate the request
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    try {
        // Attempt to authenticate user
        $token = JWTAuth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        // Check if authentication succeeded
        if (!empty($token)) {
            $user = Auth::user(); // Retrieve authenticated user details

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'accessToken' => $token,
                    'user' => [
                        'id' => $user->id,
                        'userId' => $user->userId,
                        'firstName' => $user->firstName,
                        'lastName' => $user->lastName,
                        'email' => $user->email,
                        'phone' => $user->phone,
                    ]
                ]
            ], 200, [], JSON_PRETTY_PRINT); // Return pretty-printed JSON with 200 OK status code
        }

        // Return unsuccessful login response
        return response()->json([
            'status' => 'Bad request',
            'message' => 'Authentication failed',
            'statusCode' => 401,
        ], 401, [], JSON_PRETTY_PRINT); // Return pretty-printed JSON with 401 Unauthorized status code

    } catch (\Exception $e) {
        // Handle any other errors
        return response()->json([
            'status' => 'Bad request',
            'message' => 'Authentication failed',
            'statusCode' => 401,
        ], 401, [], JSON_PRETTY_PRINT); // Return pretty-printed JSON with 400 Bad Request status code
    }
}







public function getUser(Request $request, $id)
{
    try {
        // Fetch the authenticated user
        $authenticatedUser = $request->user();

        if (!$authenticatedUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authenticated user not found',
            ], 403);
        }

        // Fetch the user record based on the provided $id
        $user = User::findOrFail($id);

        // Check if the authenticated user can access the requested user's record
        if ($authenticatedUser->userId !== $user->userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403);
        }

        // Return the user data in the specified format
        return response()->json([
            'status' => 'success',
            'message' => 'User data retrieved successfully',
            'data' => [
                'userId' => $user->userId,
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'phone' => $user->phone,
            ]
        ], 200, [], JSON_PRETTY_PRINT);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
        ], 403, [], JSON_PRETTY_PRINT);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized',
            'error' => $e->getMessage(),
        ], 403, [], JSON_PRETTY_PRINT);
    }
}





public function getUserOrganisations()
{
    $user = Auth::user();

    // Fetch organizations directly from the database
    $organisations = DB::table('organizations')
        ->join('organization_user', 'organizations.orgId', '=', 'organization_user.organization_id')
        ->where('organization_user.user_id', $user->userId)
        ->select('organizations.orgId', 'organizations.name', 'organizations.description')
        ->get();

    // Prepare the response data
    $data = $organisations->map(function ($organisation) {
        return [
            'orgId' => $organisation->orgId,
            'name' => $organisation->name,
            'description' => $organisation->description,
        ];
    });

    return response()->json([
        'status' => 'success',
        'message' => 'Organisations retrieved successfully',
        'data' => [
            'organisations' => $data
        ]
    ], 200, [], JSON_PRETTY_PRINT);
}








public function getOrganisation($id)
{
    $user = Auth::user();

    // Fetch the organization data directly from the database
    $organization = DB::table('organizations')
        ->join('organization_user', 'organizations.orgId', '=', 'organization_user.organization_id')
        ->where('organization_user.user_id', $user->userId)
        ->where('organizations.orgId', $id)
        ->select('organizations.orgId', 'organizations.name', 'organizations.description')
        ->first();

    if (!$organization) {
        return response()->json([
            'status' => 'error',
            'message' => 'Organization not found or access denied',
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Organization retrieved successfully',
        'data' => [
            'orgId' => $organization->orgId,
            'name' => $organization->name,
            'description' => $organization->description,
        ]
    ], 200, [], JSON_PRETTY_PRINT);
}













    public function createOrganisation(Request $request)
    {


        $user = Auth::user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Client error',
                'statusCode' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Generate HRM ID for organization
            $orgId = IdGenerator::generate([
                'table' => 'organizations',
                'field' => 'orgId',
                'length' => 5,
                'prefix' => '2'
            ]);


              // Generate a random character
        $randomCharacter = Str::random(3);

        // Concatenate the random character to the orgId and userId
        $orgId .= $randomCharacter;

            // Create the organization
            $organization = Organization::create([
                'orgId' => $orgId,
                'name' => $request->name,
                'description' => $request->description ?? 'Default description',
            ]);


            // Attach the user to the organization in the pivot table
        DB::table('organization_user')->insert([
            'organization_id' => $organization->orgId,
            'user_id' => $user->userId
        ]);

            // Return successful response
            return response()->json([
                'status' => 'success',
                'message' => 'Organisation created successfully',
                'data' => [
                    'orgId' => $organization->orgId,
                    'name' => $organization->name,
                    'description' => $organization->description,
                ]
            ], 201, [], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Bad Request',
                'message' => 'Client error',
                'statusCode' => 500,
                'error' => $e->getMessage()
            ], 400, [], JSON_PRETTY_PRINT);
        }
        }
    





    public function addUserToOrganisation(Request $request, $id)
{
  
    {
        // Validate request data
        $request->validate([
            'userId' => 'required|string', // Assuming userId is a string
        ]);

        try {
            // Check if the organization exists
            $organization = Organization::where('orgId', $id)->first();
            if (!$organization) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Organization not found',
                    'statusCode' => 404,
                ], 404);
            }

            // Check if the user exists
            $user = User::where('userId', $request->userId)->first();
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                    'statusCode' => 400,
                ], 400);
            }

            // Check if the user is already associated with the organization
            $existingAssociation = DB::table('organization_user')
                ->where('organization_id', $organization->orgId)
                ->where('user_id', $user->id)
                ->exists();

            if ($existingAssociation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User already belongs to this organization',
                    'statusCode' => 400,
                ], 400);
            }

            // Create association in organization_user pivot table
            DB::table('organization_user')->insert([
                'organization_id' => $organization->orgId,
                'user_id' => $user->userId,
            ]);

            // Log association creation
            Log::info('User added to organization', ['orgId' => $organization->orgId, 'userId' => $user->userId]);

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'User added to organisation successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error adding user to organization', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add user to organisation',
                'statusCode' => 500,
            ], 500);
        }
    }
}









}