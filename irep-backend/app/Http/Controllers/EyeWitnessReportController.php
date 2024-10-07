<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\EyeWitnessReportRequest;
use App\Http\Resources\EyeWitnessReportResource;
use Database\Factories\EyeWitnessReportFactory;
use App\Http\Requests\CommentRequest;
use Illuminate\Http\Request;

class EyeWitnessReportController extends Controller
{
    protected $eyeWitnessReportFactory;

    public function __construct()
    {
        $this->eyeWitnessReportFactory = new EyeWitnessReportFactory();
    }

    public function index(Request $request)
    {
        $criteria = $request->only(['search', 'filter', 'sort_by', 'sort_order']);

        $reports = $this->eyeWitnessReportFactory->getFilteredReports($criteria);

        return response()->json(EyeWitnessReportResource::collection($reports));
    }

    public function create(EyeWitnessReportRequest $request)
    {
        $validated = $request->validated();
        $validated['creatorId'] = Auth::id();

        $reportId = $this->eyeWitnessReportFactory->createReport($validated);

        return response()->json([
            'message' => 'Report created successfully',
            'report_id' => $reportId,
        ], 201);
    }

    public function show($id)
    {
        $report = $this->eyeWitnessReportFactory->findById($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        return response()->json(new EyeWitnessReportResource($report));
    }

    public function approve($id, CommentRequest $request)
    {
        $report = $this->eyeWitnessReportFactory->findById($id);

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        if ($this->eyeWitnessReportFactory->hasUserApproved($id, Auth::id())) {
            return response()->json(['message' => 'You have already approved this report'], 400);
        }

        $comment = $request->input('comment');

        $this->eyeWitnessReportFactory->insertApproval($id, Auth::id(), $comment);

        return response()->json(['message' => 'Report approved successfully']);
    }

    public function share($id)
    {
        $report = $this->eyeWitnessReportFactory->findById($id);

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $shareableUrl = url("/api/reports/{$id}");
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
