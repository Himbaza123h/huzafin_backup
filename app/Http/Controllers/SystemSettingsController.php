<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSystemSettingsRequest;
use App\Http\Requests\UpdateSystemSettingsRequest;
use App\Models\SystemSettings;
use Illuminate\Http\Client\Request;

class SystemSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = SystemSettings::first();
        return \response()->success($settings, "");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSystemSettingsRequest $request)
    {
        $settings = SystemSettings::first();
        $data = $request->validated();
        if ($settings) {
            $settings->update($data);
        } else {
            $settings = SystemSettings::create($data);
        }
        return
            response()->success($data, 'System settings Updated successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(SystemSettings $systemSettings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSystemSettingsRequest $request, SystemSettings $systemSettings)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SystemSettings $systemSettings)
    {
        //
    }
}
