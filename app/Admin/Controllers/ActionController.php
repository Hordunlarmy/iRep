<?php

namespace App\Admin\Controllers;

use Illuminate\Http\Request;
use App\Admin\Factories\ActionFactory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class ActionController extends Controller
{
    protected $actionFactory;

    public function __construct(ActionFactory $actionFactory)
    {
        $this->actionFactory = $actionFactory;
    }

    public function addState(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:states,name',
        ]);

        $state = $this->actionFactory->insertState($validatedData['name']);

        return response()->json([
            'message' => 'State added successfully.',
            'state_id' => $state,
        ], 201);
    }

    public function addLocalGovernment(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'state_id' => 'required|integer|exists:states,id',
        ]);

        $localGovernment = $this->actionFactory->insertLocalGovernment($validatedData['name'], $validatedData['state_id']);

        return response()->json([
            'message' => 'Local government added successfully.',
            'local_government_id' => $localGovernment,
        ], 201);
    }

    public function addConstituency(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'state_id' => 'required|integer|exists:states,id',
        ]);

        $constituency = $this->actionFactory->insertConstituency($validatedData['name'], $validatedData['state_id']);

        return response()->json([
            'message' => 'Constituency added successfully.',
            'constituency_id' => $constituency,
        ], 201);
    }

    public function addDistrict(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'state_id' => 'required|integer|exists:states,id',
        ]);

        $district = $this->actionFactory->insertDistrict($validatedData['name'], $validatedData['state_id']);

        return response()->json([
            'message' => 'District added successfully.',
            'district_id' => $district,
        ], 201);
    }

    public function addPosition(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|unique:positions,title',
        ]);

        $position = $this->actionFactory->insertPosition($validatedData['title']);

        return response()->json([
            'message' => 'Position added successfully.',
            'position_id' => $position,
        ], 201);
    }

    public function addParty(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:parties,name',
            'code' => 'required|string|unique:parties,code',
        ]);

        $party = $this->actionFactory->insertParty($validatedData['name'], $validatedData['code']);

        return response()->json([
            'message' => 'Party added successfully.',
            'party_id' => $party,
        ], 201);
    }

    public function updateState(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:states,name',
        ]);

        $state = $this->actionFactory->updateState($id, $validatedData['name']);

        if ($state) {
            return response()->json([
                'message' => 'State updated successfully.',
                'state_id' => $id,
            ], 200);
        }

        return response()->json([
            'error' => 'Failed to update state.',
        ], 400);
    }

    public function updateLocalGovernment(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'state_id' => 'required|integer|exists:states,id',
        ]);

        $localGovernment = $this->actionFactory->updateLocalGovernment(
            $id,
            $validatedData['name'],
            $validatedData['state_id']
        );

        if ($localGovernment) {
            return response()->json([
                'message' => 'Local government updated successfully.',
                'local_government_id' => $id,
            ], 200);
        }

        return response()->json([
            'error' => 'Failed to update local government.',
        ], 400);
    }

    public function updateConstituency(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'state_id' => 'required|integer|exists:states,id',
        ]);

        $constituency = $this->actionFactory->updateConstituency(
            $id,
            $validatedData['name'],
            $validatedData['state_id']
        );

        if ($constituency) {
            return response()->json([
                'message' => 'Constituency updated successfully.',
                'constituency_id' => $id,
            ], 200);
        }

        return response()->json([
            'error' => 'Failed to update constituency.',
        ], 400);
    }

    public function updateDistrict(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'state_id' => 'required|integer|exists:states,id',
        ]);

        $district = $this->actionFactory->updateDistrict(
            $id,
            $validatedData['name'],
            $validatedData['state_id']
        );

        if ($district) {
            return response()->json([
                'message' => 'District updated successfully.',
                'district_id' => $id,
            ], 200);
        }

        return response()->json([
            'error' => 'Failed to update district.',
        ], 400);
    }

    public function updatePosition(Request $request, $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|unique:positions,title',
        ]);

        $position = $this->actionFactory->updatePosition($id, $validatedData['title']);

        if ($position) {
            return response()->json([
                'message' => 'Position updated successfully.',
                'position_id' => $id,
            ], 200);
        }

        return response()->json([
            'error' => 'Failed to update position.',
        ], 400);
    }

    public function updateParty(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
        ]);

        $party = $this->actionFactory->updateParty(
            $id,
            $validatedData['name'],
            $validatedData['code']
        );

        if ($party) {
            return response()->json([
                'message' => 'Party updated successfully.',
                'party_id' => $id,
            ], 200);
        }

        return response()->json([
            'error' => 'Failed to update party.',
        ], 400);
    }

    public function deleteState($id)
    {
        $this->actionFactory->deleteState($id);

        return response()->noContent();
    }

    public function deleteLocalGovernment($id)
    {
        $this->actionFactory->deleteLocalGovernment($id);

        return response()->noContent();
    }

    public function deleteConstituency($id)
    {
        $this->actionFactory->deleteConstituency($id);

        return response()->noContent();
    }

    public function deleteDistrict($id)
    {
        $this->actionFactory->deleteDistrict($id);

        return response()->noContent();
    }

    public function deletePosition($id)
    {
        $this->actionFactory->deletePosition($id);

        return response()->noContent();
    }

    public function deleteParty($id)
    {
        $this->actionFactory->deleteParty($id);

        return response()->noContent();
    }

}
