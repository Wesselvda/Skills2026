<?php

namespace App\Http\Controllers;

use App\Models\Bicycle;
use App\Models\Booking;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $this->validateFilters($request);

        $bookings = $this->historyQuery($request)
            ->paginate(10)
            ->withQueryString();

        return view('history.index', compact('bookings'));
    }

    public function export(Request $request)
    {
        $this->validateFilters($request);

        $bookings = $this->historyQuery($request)->get();
        $filename = 'exports_history_' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($bookings) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Renter name',
                'Renter phone',
                'Started at',
                'Ended at',
                'Wear',
                'Photos',
                'Final price',
                'Rating',
            ]);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->user->name ?? '',
                    $booking->user->phone ?? '',
                    $this->dateText($booking->startedAt),
                    $this->dateText($booking->endedAt),
                    $booking->percentageOfWear,
                    implode(', ', $booking->photos ?? []),
                    $booking->price,
                    $booking->rating,
                ]);
            }

            fclose($file);
        }, $filename);
    }

    private function historyQuery(Request $request)
    {
        $categoryKeys = Category::where('user_ref', Auth::user()->user_key)
            ->pluck('category_key');

        $bicycleKeys = Bicycle::whereIn('category_ref', $categoryKeys)
            ->pluck('bicycle_key');

        $query = Booking::with(['user', 'bicycle'])
            ->whereIn('bicycle_ref', $bicycleKeys)
            ->orderByDesc('startedAt');

        if ($request->from) {
            $query->whereDate('startedAt', '>=', $request->from);
        }

        if ($request->to) {
            $query->whereDate('startedAt', '<=', $request->to);
        }

        return $query;
    }

    private function validateFilters(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    private function dateText($date)
    {
        if (! $date) {
            return '';
        }

        return $date->format('Y-m-d H:i');
    }
}
