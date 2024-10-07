<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PetitionRequest;
use App\Http\Requests\CommentRequest;
use App\Http\Resources\PetitionResource;
use Database\Factories\PetitionFactory;
use Illuminate\Http\Request;

class PetitionController extends Controller
{
    protected $petitionFactory;

    public function __construct()
    {
        $this->petitionFactory = new PetitionFactory();
    }

    public function index(Request $request)
    {
        $criteria = $request->only(['search', 'filter', 'sort_by', 'sort_order']);

        $petitions = $this->petitionFactory->getFilteredPetitions($criteria);

        return response()->json(PetitionResource::collection($petitions));
    }

    public function create(PetitionRequest $request)
    {
        $validated = $request->validated();
        $validated['creatorId'] = Auth::id();
        $validated['targetRepresentativeId'] = $validated['target_representative_id'];

        $petitionId = $this->petitionFactory->createPetition($validated);

        return response()->json([
            'message' => 'Petition created successfully',
            'petition_id' => $petitionId,
        ], 201);
    }

    public function show($id)
    {
        $petition = $this->petitionFactory->findById($id);
        if (!$petition) {
            return response()->json(['message' => 'Petition not found'], 404);
        }

        return response()->json(new PetitionResource($petition));
    }

    public function sign($id, CommentRequest $request)
    {
        $petitionData = $this->petitionFactory->findById($id);

        if (!$petitionData) {
            return response()->json(['message' => 'Petition not found'], 404);
        }

        if ($this->petitionFactory->hasUserSigned($id, Auth::id())) {
            return response()->json(['message' => 'You have already signed this petition'], 400);
        }

        $comment = $request->input('comment');

        $this->petitionFactory->insertSignature($id, Auth::id(), $comment);

        return response()->json(['message' => 'Petition signed successfully']);
    }

    public function share($id)
    {
        $petition = $this->petitionFactory->findById($id);

        if (!$petition) {
            return response()->json(['message' => 'Petition not found'], 404);
        }

        $shareableUrl = url("/api/petitions/{$id}");
        $twitterShareUrl = "https://twitter.com/intent/tweet?url={$shareableUrl}";
        $facebookShareUrl = "https://www.facebook.com/sharer/sharer.php?u={$shareableUrl}";
        $whatsappShareUrl = "whatsapp://send?text={$shareableUrl}";

        return response()->json([
            'shareable_url' => $shareableUrl,
            'twitter_share_url' => $twitterShareUrl,
            'facebook_share_url' => $facebookShareUrl,
            'whatsapp_share_url' => $whatsappShareUrl,
        ]);
    }

}
