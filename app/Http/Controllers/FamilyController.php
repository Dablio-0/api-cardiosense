<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyRelationships;
use App\Models\User;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    /**
     * Creates a family. The authenticated user will be the administrator of the family
     * 
     * [POST]
     * @Dablio-0
     * 
     * @param \Illuminate\Http\Request $request The HTTP Request contains the name of the family
     * @return \Illuminate\Http\JsonResponse class of response type
     */
    public function store(Request $request) : JsonResponse
    {
        try {
            $request->validate([
                'name_family' => 'required|string',
            ]);

            $user = Auth::user();

            $family = new Family();

            $family->name_family = $request->name_family;
            $family->user_adm_id = $user->id;
            $family->save();

            return response()->json(['message' => 'Family created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create family', 'message' => $e->getMessage()], 500);
        }
    }

    /** Submit which action that will be performed with the family members
     * 
     * [POST]
     * @Dablio-0
     * 
     * @param \Illuminate\Http\Request $request The HTTP Request contains the family members and the action that will be performed
     * @return \Illuminate\Http\JsonResponse class of response type
     */
    public function syncFamilyMembers(Request $request) : JsonResponse
    {
        $request->validate([
            'members' => 'required|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.relationship' => ['required', Rule::in(User::getArraySex())],
            'family_id' => 'required|exists:families,id',
            'action' => 'required|in:ADD,REMOVE'
        ]);

        $user = Auth::user();

        $family = Family::find($request->family_id);

        switch ($request->action) {
            case 'ADD':
                $this->addMembers($family, $request);
                break;
            case 'REMOVE':
                $this->removeMembers($family, $request);
                break;
            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }   
    }

    /**
     * Add members to family
     * 
     * This method is invoked by the authenticated user to add members to the family
     * @Dablio-0
     * 
     * @param \Illuminate\Http\Request $request The HTTP Request contains an array of members to be added and their relation with the
     * authenticated user
     * @param \App\Models\Family $family The family that the members will be added
     * @return \Illuminate\Http\JsonResponse class of response type
     */
    public function addMembers(Family $family, User $members) : JsonResponse
    {
        dd($family, $members);
        try {
        
            $user = Auth::user();

            foreach ($request->members as $member) {
                $familyRelationship = new FamilyRelationships();

                $familyRelationship->user_id = $user->id;
                $familyRelationship->user_related_id = $member['user_id'];
                $familyRelationship->relationship = $member['relationship'];
                $familyRelationship->family_id = $family->id;
                $familyRelationship->save();
            }

            return response()->json(['message' => 'Members added to family successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add members to family', 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Remove members from family
     * 
     * [DELETE]
     * @Dablio-0
     * 
     * @param \Illuminate\Http\Request $request The HTTP Request contains an array of members to be removed and their relation with the
     * authenticated user
     * @param \App\Models\Family $family The family that the members will be removed
     * @return \Illuminate\Http\JsonResponse class of response type
     */
    public function removeMembers(Request $request, Family $family) : JsonResponse
    {
        dd($request, $family);
        try {

            $user = Auth::user();

            foreach ($request->members as $member) {
                $familyRelationship = new FamilyRelationships();

                $relation = $familyRelationship->where('user_id', $user->id)
                    ->where('user_related_id', $member['user_related_id'])
                    ->where('family_id', $family->id)
                    ->where('relationship', $member['relationship'])
                    ->first();

                if ($relation) {
                    $relation->delete();
                } else {
                    return response()->json(['error' => 'Member not found in family'], 404);
                }
            }

            return response()->json(['message' => 'Members removed from family successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove members from family', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Returns to user the family that he participates and your member relationships
     * 
     * [GET]
     * @Dablio-0
     * 
     * @param \App\Models\Family $family The family that the member will be removed
     * @return \Illuminate\Http\JsonResponse class of response type
     */
    public function show(Family $family) : JsonResponse
    {
        try {
            $family = Family::with('userAdm')->with('familyRelationships')->find($family->id);
            return response()->json($family, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve family', 'message' => $e->getMessage()], 500);
        }
        
    }

    /**
     * Updates the family information
     * 
     * [PUT]
     * @Dablio-0
     * 
     * @param \Illuminate\Http\Request $request The HTTP Request contains the new family information
     * @param \App\Models\Family $family The family that will be updated
     */
    public function update(Request $request, Family $family) : JsonResponse
    {
        try {
            $request->validate([
                'name_family' => 'required|string',
            ]);

            $family = Family::find($family->id);
            $family->name_family = $request->name_family;
            $family->save();

            // Sincronize the family relationships (members of family)

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update family', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the family
     * 
     * This method will remove all members and the family itself
     * 
     * [DELETE]
     * @Dablio-0
     * 
     * @param \App\Models\Family $family The family that will be removed
     * @return \Illuminate\Http\JsonResponse class of response type
     */
    public function destroy(Family $family) : JsonResponse
    {
        try {
            $family = Family::find($family->id);

            $memberRelations = $family->familyRelationships->where('family_id', $family->id);

            foreach ($memberRelations as $relation) {
                $relation->delete();
            }

            $family->delete();
            
            return response()->json(['message' => 'Family removed successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to remove family', 'message' => $e->getMessage()], 500);
        }
    }
}
