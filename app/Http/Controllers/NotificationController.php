<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Legislation;
use App\Models\Newsletter;
use App\Models\Notice;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if($user->role == 'member') {
            $user_id = $user->id;

            $events = Event::query()
                ->whereNotExists(function ($q) use ($user_id) {
                    $q->selectRaw(1)
                    ->from('event_views')
                    ->whereColumn('event_views.event_id', 'events.id')
                    ->where('event_views.user_id', $user_id);
                })
                ->orderByDesc('events.created_at')
                // ->limit(5)
            ->get();

            $notices = Notice::query()
                ->whereNotExists(function ($q) use ($user_id) {
                    $q->selectRaw(1)
                    ->from('notice_views')
                    ->whereColumn('notice_views.notice_id', 'notices.id')
                    ->where('notice_views.user_id', $user_id);
                })
                ->orderByDesc('notices.created_at')
                // ->limit(5)
            ->get();

            $newsletters = Newsletter::query()
                ->whereNotExists(function ($q) use ($user_id) {
                    $q->selectRaw(1)
                    ->from('newsletter_views')
                    ->whereColumn('newsletter_views.newsletter_id', 'newsletters.id')
                    ->where('newsletter_views.user_id', $user_id);
                })
                ->orderByDesc('newsletters.created_at')
                // ->limit(5)
            ->get();

            $reports = Report::query()
                ->whereNotExists(function ($q) use ($user_id) {
                    $q->selectRaw(1)
                    ->from('report_views')
                    ->whereColumn('report_views.report_id', 'reports.id')
                    ->where('report_views.user_id', $user_id);
                })
                ->orderByDesc('reports.created_at')
                // ->limit(5)
            ->get();

            $legislations = Legislation::query()
                ->whereNotExists(function ($q) use ($user_id) {
                    $q->selectRaw(1)
                    ->from('legislation_views')
                    ->whereColumn('legislation_views.legislation_id', 'legislations.id')
                    ->where('legislation_views.user_id', $user_id);
                })
                ->orderByDesc('legislations.created_at')
                // ->limit(5)
            ->get();

            $data = [
                'events'  => $events,
                'notices' => $notices,
                'newsletters' => $newsletters,
                'reports' => $reports,
                'legislations' => $legislations,
                'counts'  => [
                    'events'  => $events->count(),
                    'notices' => $notices->count(),
                    'newsletters' => $newsletters->count(),
                    'reports' => $reports->count(),
                    'legislations' => $legislations->count(),
                    'total'   => $events->count() + $notices->count() + $newsletters->count() + $reports->count() + $legislations->count()
                ],
            ];
        }
        else {
            $data = 'Admin notification implementation is inprogress. Coming soon.';
        }

        return successResponse('success', 200, $data);
    }

    public function markSeen()
    {
        $user = auth()->user();

        if($user->role == 'member') {
            $user_id = auth()->id();
            $now = now();

            DB::transaction(function () use ($user_id, $now) {

                $event_ids = Event::query()
                    ->whereNotExists(function ($q) use ($user_id) {
                        $q->selectRaw(1)
                        ->from('event_views')
                        ->whereColumn('event_views.event_id', 'events.id')
                        ->where('event_views.user_id', $user_id);
                    })
                ->pluck('events.id');

                if($event_ids->isNotEmpty()) {
                    $event_rows = $event_ids->map(fn ($id) => [
                        'event_id' => $id,
                        'user_id' => $user_id,
                        'seen_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('event_views')->insertOrIgnore($event_rows);
                }

                $notice_ids = Notice::query()
                    ->whereNotExists(function ($q) use ($user_id) {
                        $q->selectRaw(1)
                        ->from('notice_views')
                        ->whereColumn('notice_views.notice_id', 'notices.id')
                        ->where('notice_views.user_id', $user_id);
                    })
                ->pluck('notices.id');

                if($notice_ids->isNotEmpty()) {
                    $notice_rows = $notice_ids->map(fn ($id) => [
                        'notice_id' => $id,
                        'user_id' => $user_id,
                        'seen_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('notice_views')->insertOrIgnore($notice_rows);
                }

                $newsletter_ids = Newsletter::query()
                    ->whereNotExists(function ($q) use ($user_id) {
                        $q->selectRaw(1)
                        ->from('newsletter_views')
                        ->whereColumn('newsletter_views.newsletter_id', 'newsletters.id')
                        ->where('newsletter_views.user_id', $user_id);
                    })
                ->pluck('newsletters.id');

                if($newsletter_ids->isNotEmpty()) {
                    $newsletter_rows = $newsletter_ids->map(fn ($id) => [
                        'newsletter_id' => $id,
                        'user_id' => $user_id,
                        'seen_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('newsletter_views')->insertOrIgnore($newsletter_rows);
                }

                $report_ids = Report::query()
                    ->whereNotExists(function ($q) use ($user_id) {
                        $q->selectRaw(1)
                        ->from('report_views')
                        ->whereColumn('report_views.report_id', 'reports.id')
                        ->where('report_views.user_id', $user_id);
                    })
                ->pluck('reports.id');

                if($report_ids->isNotEmpty()) {
                    $report_rows = $report_ids->map(fn ($id) => [
                        'report_id' => $id,
                        'user_id' => $user_id,
                        'seen_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('report_views')->insertOrIgnore($report_rows);
                }

                $legislation_ids = Legislation::query()
                    ->whereNotExists(function ($q) use ($user_id) {
                        $q->selectRaw(1)
                        ->from('legislation_views')
                        ->whereColumn('legislation_views.legislation_id', 'legislations.id')
                        ->where('legislation_views.user_id', $user_id);
                    })
                ->pluck('legislations.id');

                if($legislation_ids->isNotEmpty()) {
                    $legislation_rows = $legislation_ids->map(fn ($id) => [
                        'legislation_id' => $id,
                        'user_id' => $user_id,
                        'seen_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();

                    DB::table('legislation_views')->insertOrIgnore($legislation_rows);
                }
            });
        }
        else {
            dd('Admin mark seen work inprogress. Coming soon.');
        }

        return successResponse('success', 200);
    }
}