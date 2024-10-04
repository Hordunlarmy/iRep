<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\PetitionRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\PetitionResource;

class PetitionController extends Controller
{
    protected $petition;

    public function __construct()
    {
        $this->petition = new Petition();
    }

    public function index()
    {
        $petitions = $this->petition->getAllPetitions();
        return response()->json(PetitionResource::collection($petitions));
    }

    public function create(PetitionRequest $request)
    {
        $validated = $request->validated();
        $validated['creatorId'] = auth()->id();
        $validated['targetRepresentativeId'] = $validated['target_representative_id'];

        $petitionId = $this->petition->createPetition($validated);

        return response()->json([
            'message' => 'Petition created successfully',
            'petition_id' => $petitionId,
        ], 201);
    }

    public function show($id)
    {
        $petition = $this->petition->findById($id);
        if (!$petition) {
            return response()->json(['message' => 'Petition not found'], 404);
        }

        return response()->json(new PetitionResource($petition));
    }

    public function sign($id, CommentRequest $request)
    {
        $petitionData = $this->petition->findById($id);

        if (!$petitionData) {
            return response()->json(['message' => 'Petition not found'], 404);
        }

        if ($this->petition->hasUserSigned($id, auth()->id())) {
            return response()->json(['message' => 'You have already signed this petition'], 400);
        }

        $comment = $request->input('comment');

        $this->petition->insertSignature($id, auth()->id(), $comment);
        $this->petition->incrementSignatureCount($id);

        return response()->json(['message' => 'Petition signed successfully']);
    }

}
