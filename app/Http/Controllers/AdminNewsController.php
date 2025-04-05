<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminNewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::with('user')->latest();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('caption', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && in_array($request->status, ['approved', 'pending'])) {
            $status = $request->status === 'approved' ? 1 : 0;
            $query->where('status', $status);
        }

        $news = $query->paginate(10)->withQueryString()->through(function ($news) {
            $news->images = json_decode($news->images);

            $news->is_modified = $news->updated_at->format('Y-m-d H:i:s') !==
                                $news->created_at->format('Y-m-d H:i:s');

            return $news;
        });

        return Inertia::render('Admin/AdminNews', [
            'news' => $news,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'caption' => 'required|string',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $imagePaths = [];
        foreach ($request->file('images') as $image) {
            $imagePaths[] = $image->store('news_images', 'public');
        }

        News::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'images' => json_encode($imagePaths),
            'status' => false,
        ]);

        return redirect()->route('AdminNews')->with('success', 'News added successfully.');
    }

    public function update(Request $request, News $news)
    {
        $user = Auth::user();

        if ($user->id !== $news->user_id && !$user->hasAnyRole(['Admin', 'Super-Admin'])) {
            abort(403, 'Access denied: This news was published by another user and cannot be modified. Only administrators and super administrators have the necessary permissions to update news content.');
        }

        if ($news->status) {
            abort(403, 'Only pending news can be updated. This news has already been approved.');
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'caption' => 'required|string',
            'images' => 'nullable|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePaths = json_decode($news->images, true) ?? [];

        if ($request->has('existing_images')) {
            $imagePaths = json_decode($request->input('existing_images'), true);
        }

        if ($request->hasFile('images')) {
            foreach ($imagePaths as $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('news_images', 'public');
            }
        }

        $news->update([
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'images' => json_encode($imagePaths),
        ]);

        return redirect()->route('AdminNews')->with('success', 'News updated successfully.');
    }

    public function destroy(News $news)
    {
        $user = Auth::user();

        if ($user->id !== $news->user_id && !$user->hasAnyRole(['Admin', 'Super-Admin'])) {
            abort(403, 'Access denied: This news was posted by another user and cannot be deleted. Only administrators and super administrators are authorized to remove news entries.');
        }

        foreach (json_decode($news->images, true) as $image) {
            Storage::disk('public')->delete($image);
        }

        $news->delete();

        return redirect()->route('AdminNews')->with('success', 'News deleted successfully.');
    }

    public function toggleStatus(News $news)
    {
        if (!Auth::user()->hasRole('Super-Admin')) {
            abort(403, 'Access denied: Only super administrators can toggle news status.');
        }

        $news->timestamps = false;
        $news->update(['status' => !$news->status]);

        return redirect()->route('AdminNews')->with('success', 'Status updated.');
    }
}
