<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ControlPanels\VirtualminService;
use App\Services\ControlPanels\cPanelService;
use App\Services\ControlPanels\PleskService;
use App\Services\ControlPanels\DirectAdminService;

class ControlPanelController extends Controller
{
    private $services;

    public function __construct(
        VirtualminService $virtualminService,
        cPanelService $cPanelService,
        PleskService $pleskService,
        DirectAdminService $directAdminService
    ) {
        $this->services = [
            'virtualmin' => $virtualminService,
            'cpanel' => $cPanelService,
            'plesk' => $pleskService,
            'directadmin' => $directAdminService,
        ];
    }

    public function createAccount(Request $request)
    {
        $request->validate([
            'control_panel' => 'required|in:virtualmin,cpanel,plesk,directadmin',
            'account_data' => 'required|array',
        ]);

        $service = $this->services[$request->input('control_panel')];
        $result = $service->createAccount($request->input('account_data'));

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function suspendAccount(Request $request)
    {
        $request->validate([
            'control_panel' => 'required|in:virtualmin,cpanel,plesk,directadmin',
            'account_id' => 'required|string',
        ]);

        $service = $this->services[$request->input('control_panel')];
        $result = $service->suspendAccount($request->input('account_id'));

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function deleteAccount(Request $request)
    {
        $request->validate([
            'control_panel' => 'required|in:virtualmin,cpanel,plesk,directadmin',
            'account_id' => 'required|string',
        ]);

        $service = $this->services[$request->input('control_panel')];
        $result = $service->deleteAccount($request->input('account_id'));

        return response()->json($result, $result['success'] ? 200 : 400);
    }
}