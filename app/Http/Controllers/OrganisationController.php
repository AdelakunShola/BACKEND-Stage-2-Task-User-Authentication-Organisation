<?php

// app/Http/Controllers/OrganisationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrganisationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $organisations = $user->organisations;

        return response()->json([
            'status' => 'success',
            'message' => 'Organisations retrieved successfully',
            'data' => [
                'organisations' => $organisations
            ]
        ], 200);
    }

    public function show($orgId)
    {
        $organisation = Organisation::find($orgId);

        if (!$organisation || !auth()->user()->organisations->contains($organisation)) {
            return response()->json(['message' => 'Organisation not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation retrieved successfully',
            'data' => $organisation
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organisation = Organisation::create([
            'org_id' => (string) Str::uuid(),
            'name' => $request->name,
            'description' => $request->description,
        ]);

        auth()->user()->organisations()->attach($organisation->org_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Organisation created successfully',
            'data' => $organisation
        ], 201);
    }

    public function addUser(Request $request, $orgId)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organisation = Organisation::find($orgId);

        if (!$organisation || !auth()->user()->organisations->contains($organisation)) {
            return response()->json(['message' => 'Organisation not found'], 404);
        }

        $user = User::find($request->user_id);
        $organisation->users()->attach($user->user_id);

        return response()->json([
            'status' => 'success',
            'message' => 'User added to organisation successfully',
        ], 200);
    }
}
