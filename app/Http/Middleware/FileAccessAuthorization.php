<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ReportAttachment;
use App\Models\ProgressAttachment;
use App\Models\Report;
use App\Models\ProgressUpdate;
use Symfony\Component\HttpFoundation\Response;

class FileAccessAuthorization
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Only protect report-attachments and progress-attachments
        if (! str_starts_with($path, 'storage/report-attachments/') &&
            ! str_starts_with($path, 'storage/progress-attachments/') &&
            ! str_starts_with($path, 'storage/profile-photos/')) {
            return $next($request);
        }

        $user = Auth::user();

        if (! $user) {
            abort(403, 'Authentication required to access this file.');
        }

        $filename = basename($path);

        // Check report attachments
        if (str_starts_with($path, 'storage/report-attachments/')) {
            $attachment = ReportAttachment::where('file_path', 'report-attachments/'.$filename)->first();

            if (! $attachment) {
                abort(404, 'File not found.');
            }

            $report = $attachment->report;

            // Admin can access all
            if ($user->isAdmin()) {
                return $next($request);
            }

            // Citizen can access their own report attachments
            if ($user->isCitizen() && $report->citizen_id === $user->id) {
                return $next($request);
            }

            // Staff can access attachments of their assigned reports
            if ($user->isStaff() && $report->assignments()->where('staff_id', $user->id)->exists()) {
                return $next($request);
            }

            abort(403, 'You are not authorized to access this file.');
        }

        // Check progress attachments
        if (str_starts_with($path, 'storage/progress-attachments/')) {
            $attachment = ProgressAttachment::where('file_path', 'progress-attachments/'.$filename)->first();

            if (! $attachment) {
                abort(404, 'File not found.');
            }

            $progressUpdate = $attachment->progressUpdate;
            $report = $progressUpdate->report;

            // Admin can access all
            if ($user->isAdmin()) {
                return $next($request);
            }

            // Staff who created the progress update can access
            if ($user->isStaff() && $progressUpdate->staff_id === $user->id) {
                return $next($request);
            }

            // Citizen can access if progress update is public (not internal)
            if ($user->isCitizen() && $report->citizen_id === $user->id && ! $progressUpdate->is_internal) {
                return $next($request);
            }

            // Staff assigned to the report can access public progress updates
            if ($user->isStaff() && $report->assignments()->where('staff_id', $user->id)->exists() && ! $progressUpdate->is_internal) {
                return $next($request);
            }

            abort(403, 'You are not authorized to access this file.');
        }

        // Check profile photos
        if (str_starts_with($path, 'storage/profile-photos/')) {
            // Users can access their own profile photo, admins can access all
            $profileUser = \App\Models\User::where('profile_photo', 'profile-photos/'.$filename)->first();

            if (! $profileUser) {
                abort(404, 'File not found.');
            }

            if ($user->isAdmin() || $user->id === $profileUser->id) {
                return $next($request);
            }

            abort(403, 'You are not authorized to access this file.');
        }

        return $next($request);
    }
}