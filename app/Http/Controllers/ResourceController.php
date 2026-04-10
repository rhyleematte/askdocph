<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    public function index()
    {
        $resources = Resource::with('user')->latest()->get();

        $joinedResourceIds = [];
        if (Auth::check()) {
            $joinedResourceIds = Auth::user()
                ->joinedResources()
                ->pluck('resources.id')
                ->toArray();
        }

        return view('resources.index', [
            'resources' => $resources,
            'joinedResourceIds' => $joinedResourceIds,
        ]);
    }

    public function show(Resource $resource)
    {
        $resource->loadMissing(['user', 'body']);

        $isJoined = false;
        if (Auth::check()) {
            $isJoined = Auth::user()
                ->joinedResources()
                ->where('resources.id', $resource->id)
                ->exists();
        }

        // For Article vs others, logic might differ but we'll use same show page for now
        return view('resources.show', compact('resource', 'isJoined'));
    }

    public function create()
    {
        $this->authorize('create', Resource::class);
        return view('resources.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Resource::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Article,Audio,Workbook,Media,Video',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xml,xls,xlsx,mp3,wav,ogg,mp4,webm,mov|max:51200', // 50MB max
            'thumbnail' => 'nullable|image|max:5120',
            'duration_meta' => 'nullable|string|max:50',
            'hashtags' => 'nullable|string',
        ]);

        $tileData = $request->except(['thumbnail', 'file', 'content']);
        $tileData['user_id'] = Auth::id();

        // Truncate title to 50 chars as requested
        $tileData['title'] = $this->limitGraphemes($request->title, 50);

        if ($request->hasFile('thumbnail')) {
            $tileData['thumbnail'] = $request->file('thumbnail')->store('resources/thumbnails', 'public');
        }

        $resource = Resource::create($tileData);

        $bodyData = [
            'content' => $request->input('content'),
        ];

        if ($request->hasFile('file')) {
            $bodyData['file_path'] = $this->storeResourceFile($request->file('file'));
            $bodyData['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        $resource->body()->create($bodyData);

        return redirect()->route('resources.show', $resource->id)->with('success', 'Resource created successfully.');
    }

    public function edit(Resource $resource)
    {
        $this->authorize('update', $resource);
        $resource->loadMissing('body');
        return view('resources.edit', compact('resource'));
    }

    public function update(Request $request, Resource $resource)
    {
        $this->authorize('update', $resource);

        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:Article,Audio,Workbook,Media,Video',
            'description' => 'required|string',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xml,xls,xlsx,mp3,wav,ogg,mp4,webm,mov|max:51200', // 50MB max
            'thumbnail' => 'nullable|image|max:5120',
            'duration_meta' => 'nullable|string|max:50',
            'hashtags' => 'nullable|string',
            'remove_file' => 'nullable|boolean',
        ]);

        $tileData = $request->except(['thumbnail', 'file', 'content', 'remove_file']);

        // Truncate title to 50 chars
        $tileData['title'] = $this->limitGraphemes($request->title, 50);

        if ($request->hasFile('thumbnail')) {
            if ($resource->thumbnail) {
                Storage::disk('public')->delete($resource->thumbnail);
            }
            $tileData['thumbnail'] = $request->file('thumbnail')->store('resources/thumbnails', 'public');
        }

        $resource->update($tileData);

        $bodyData = [];
        if ($request->has('content')) {
            $bodyData['content'] = $request->input('content');
        }

        // If user requested to remove existing attached file (e.g. remove audio)
        if ($request->boolean('remove_file') && $resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
            $bodyData['file_path'] = null;
            $bodyData['file_type'] = null;
        }

        if ($request->hasFile('file')) {
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            $bodyData['file_path'] = $this->storeResourceFile($request->file('file'));
            $bodyData['file_type'] = $request->file('file')->getClientOriginalExtension();
        }

        if ($bodyData) {
            $resource->body()->updateOrCreate([], $bodyData);
        }

        return redirect()->route('resources.show', $resource->id)->with('success', 'Resource updated successfully.');
    }

    public function share(Request $request, Resource $resource)
    {
        $request->validate([
            'text_content' => 'nullable|string|max:5000',
            'hashtags' => 'nullable|string|max:500',
        ]);

        // Sharing a resource creates a post
        $post = Post::create([
            'user_id' => Auth::id(),
            'resource_id' => $resource->id,
            'post_type' => 'resource_share',
            'text_content' => $request->text_content ?: ("Shared a resource: " . $resource->title),
            'hashtags' => $request->hashtags ?: $resource->hashtags,
        ]);

        return response()->json(['ok' => true, 'message' => 'Shared to feed!', 'post_id' => $post->id]);
    }

    public function join(Resource $resource)
    {
        $user = Auth::user();
        $user->joinedResources()->syncWithoutDetaching([
            $resource->id => ['status' => 'joined'],
        ]);

        return back()->with('success', 'Resource joined.');
    }

    public function unjoin(Resource $resource)
    {
        $user = Auth::user();
        $user->joinedResources()->detach($resource->id);

        return back()->with('success', 'Resource unjoined.');
    }

    public function destroy(Resource $resource)
    {
        $this->authorize('delete', $resource);

        $resource->loadMissing('body');

        if ($resource->thumbnail) {
            Storage::disk('public')->delete($resource->thumbnail);
        }

        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return redirect()->route('resources.index')->with('success', 'Resource deleted successfully.');
    }

    protected function storeResourceFile($file)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);

        // Keep original file name where possible, but sanitize invalid filesystem chars.
        $safeBase = preg_replace('/[\\\\\/\?%\*:\|"<>]/', '_', $baseName);
        $safeBase = trim($safeBase);
        if ($safeBase === '') {
            $safeBase = 'file';
        }

        $folder = 'resources/files';
        $disk = Storage::disk('public');

        // Ensure unique filename by appending a counter when collisions occur.
        $filename = $safeBase . '.' . $extension;
        $fullPath = $folder . '/' . $filename;
        $counter = 1;
        while ($disk->exists($fullPath)) {
            $filename = $safeBase . '-' . $counter++ . '.' . $extension;
            $fullPath = $folder . '/' . $filename;
        }

        $disk->putFileAs($folder, $file, $filename);
        return $fullPath;
    }

    protected function limitGraphemes(string $text, int $limit): string
    {
        if ($limit <= 0 || $text === '') {
            return '';
        }

        $matches = [];
        $ok = preg_match_all('/\\X/u', $text, $matches);
        if ($ok === false || empty($matches[0])) {
            return mb_substr($text, 0, $limit);
        }

        if (count($matches[0]) <= $limit) {
            return $text;
        }

        return implode('', array_slice($matches[0], 0, $limit));
    }

    public function serveFile($path)
    {
        $fullPath = 'resources/files/' . $path;
        $storagePath = storage_path('app/public/' . $fullPath);

        if (!file_exists($storagePath)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($storagePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml' => 'application/xml',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

        $downloadFilename = basename($storagePath);

        // Allow forcing a download with a user-provided filename (for correct download naming).
        // Example: /resource-file/.../?dl=1&fn=My%20File.docx
        if (request()->query('dl')) {
            $fn = request()->query('fn');
            if ($fn) {
                $downloadFilename = basename($fn);
            }

            return response()->download($storagePath, $downloadFilename, [
                'Content-Type' => $mimeType,
            ]);
        }

        return response()->file($storagePath, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function uploadMedia(Request $request)
    {
        // Uploads are used both when creating a new resource and when editing an existing one.
        // In edit mode we allow uploads for users who can update the resource (owner),
        // while in create mode we keep the same "create" authorization rule.
        if ($request->filled('resource_id')) {
            $resource = Resource::find($request->input('resource_id'));
            if (! $resource) {
                abort(404);
            }
            $this->authorize('update', $resource);
        } else {
            $this->authorize('create', Resource::class);
        }

        $request->validate([
            'media' => 'required|file|mimes:pdf,doc,docx,xml,xls,xlsx,mp3,wav,ogg,mp4,webm,mov|max:51200',
        ]);

        $file = $request->file('media');
        $path = $this->storeResourceFile($file);
        $filename = basename($path);
        $url = route('resource.file', $filename);

        return response()->json([
            'success' => true,
            'url' => $url,
            'filename' => $filename,
        ]);
    }
}
