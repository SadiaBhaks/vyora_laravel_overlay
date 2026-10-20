<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('moderate', Review::class);
        $status = in_array($request->query('status'), ['pending', 'approved', 'rejected'], true) ? $request->query('status') : 'pending';

        return view('admin.reviews.index', [
            'reviews' => Review::with(['product', 'user'])->where('status', $status)->latest()->paginate(20)->withQueryString(),
            'status' => $status,
            'counts' => Review::selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function update(Request $request, Review $review)
    {
        $this->authorize('moderate', Review::class);
        $data = $request->validate(['status' => ['required', 'in:approved,rejected,pending']]);
        $review->update($data);

        return back()->with('toast', 'Review '.$data['status'].'.');
    }

    public function destroy(Review $review)
    {
        $this->authorize('moderate', Review::class);
        $review->delete();

        return back()->with('toast', 'Review deleted.');
    }
}
