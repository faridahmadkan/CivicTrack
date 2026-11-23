<?php

namespace App\Http\Controllers\Public;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Services\RegistrationControlService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(RegistrationControlService $registrationControls): View
    {
        return view('public.home', [
            'citizenRegistrationOpen' => $registrationControls->isOpen(RoleName::Citizen),
            'staffApplicationsOpen' => $registrationControls->isOpen(RoleName::Staff),
            'workerApplicationsOpen' => $registrationControls->isOpen(RoleName::Worker),
        ]);
    }
}
