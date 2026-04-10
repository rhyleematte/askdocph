<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\PostMedia;
use App\Models\User;
use App\Models\Resource;
use App\Models\Group;
use App\Models\GroupMember;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    // ── View any user's profile ───────────────────────────────────
    public function show($id)
    {
        $profileUser = User::with('doctorApplication')->findOrFail($id);
        $me = Auth::user();
        $posts = Post::where('user_id', $id)
            ->with(['user', 'likes', 'comments.user', 'comments.replies.user', 'media', 'resource', 'sharedPost.user', 'sharedPost.media', 'sharedPost.resource'])
            ->latest()
            ->get();

        // Joined / created resources
        $joinedResources = $profileUser->joinedResources()
            ->with('user')
            ->latest()
            ->get();

        $createdResources = Resource::with('user')
            ->where('user_id', $profileUser->id)
            ->latest()
            ->get();

        // Joined groups: any group where the user is a member
        $joinedGroups = Group::withCount('members')
            ->whereHas('members', function ($q) use ($profileUser) {
                $q->where('user_id', $profileUser->id);
            })
            ->latest()
            ->get();

        // Created groups: groups where the user is the creator
        $createdGroups = Group::withCount('members')
            ->where('creator_id', $profileUser->id)
            ->latest()
            ->get();

        $application = null;
        $requirements = null;
        $professional_titles = null;
        $savedPosts = collect();
        if (Auth::check() && Auth::id() === $profileUser->id) {
            $application = \App\Models\DoctorApplication::where('user_id', Auth::id())->first();
            $requirements = \App\Models\DoctorRequirement::all();
            $professional_titles = \App\Models\ProfessionalTitle::orderBy('name')->get();

            $savedPosts = $profileUser->savedPosts()
                ->with(['user', 'likes', 'comments.user', 'comments.replies.user', 'media', 'resource', 'sharedPost.user', 'sharedPost.media', 'sharedPost.resource'])
                ->latest('post_saves.created_at')
                ->get();
        }

        $followers = $profileUser->followers()->get();
        $following = $profileUser->following()->get();
        $isFollowing = $me ? $me->following()->where('following_id', $profileUser->id)->exists() : false;

        return view('profile.show', [
            'profileUser'      => $profileUser,
            'posts'            => $posts,
            'application'      => $application,
            'requirements'     => $requirements,
            'professional_titles' => $professional_titles,
            'joinedResources'  => $joinedResources,
            'createdResources' => $createdResources,
            'joinedGroups'     => $joinedGroups,
            'createdGroups'    => $createdGroups,
            'savedPosts'       => $savedPosts,
            'followers'        => $followers,
            'following'        => $following,
            'isFollowing'      => $isFollowing,
        ]);
    }

    public function showPost(Request $request, Post $post)
    {
        $me = Auth::user();

        $post->load([
            'user',
            'user.doctorApplication',
            'likes',
            'comments.user',
            'comments.replies.user',
            'media',
            'resource',
            'sharedPost.user',
            'sharedPost.media',
            'sharedPost.resource',
        ]);

        $group = null;
        $canView = true;

        if ($post->group_id) {
            $group = Group::with('creator')->find($post->group_id);
            if ($group) {
                $isMember = $group->members()->where('user_id', $me->id)->exists();
                $canView = $isMember || $group->creator_id === $me->id || $post->user_id === $me->id;
            }
        }

        return view('posts.show', [
            'post' => $post,
            'me' => $me,
            'group' => $group,
            'canView' => $canView,
        ]);
    }

    // ── Update bio / name / username ─────────────────────────────
    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'fname' => ['required', 'min:2'],
            'mname' => ['nullable', 'min:2'],
            'lname' => ['required', 'min:2'],
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users')->ignore($user->id)],
            'bio' => ['nullable', 'max:300'],
        ], [
            'fname.required' => 'First name is required.',
            'fname.min' => 'First name must be at least 2 characters.',
            'lname.required' => 'Last name is required.',
            'lname.min' => 'Last name must be at least 2 characters.',
            'username.required' => 'Username is required.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username cannot exceed 20 characters.',
            'username.unique' => 'Username already taken.',
            'bio.max' => 'Bio cannot exceed 300 characters.',
        ]);

        // Normalize name parts to Title Case before saving,
        // e.g. "EDWIN" or "edwin" → "Edwin"
        $titleCase = function (string $str): string {
            return mb_convert_case(mb_strtolower(trim($str)), MB_CASE_TITLE, 'UTF-8');
        };

        $user->update([
            'fname' => $titleCase($data['fname']),
            'mname' => (isset($data['mname']) && trim($data['mname']) !== '')
            ? $titleCase($data['mname'])
            : null,
            'lname' => $titleCase($data['lname']),
            'username' => $data['username'],
            'bio' => $data['bio'] ?? null,
        ]);

        // Re-read from DB to get the final normalized values
        $user->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Profile updated successfully!',
            'full_name' => $user->full_name,
            'username' => $user->username,
            'bio' => $user->bio,
        ]);
    }

    // ── Upload / change profile photo ────────────────────────────
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:4096'],
        ]);

        $user = Auth::user();

        if ($user->profile_photo && $user->profile_photo !== 'profiles/default.png') {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $path = $request->file('photo')->store('profiles', 'public');
        $user->update(['profile_photo' => $path]);

        return response()->json([
            'ok' => true,
            'avatar_url' => $user->avatar_url,
            'message' => 'Profile photo updated!',
        ]);
    }

    // ── Delete profile photo ─────────────────────────────────────
    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo && $user->profile_photo !== 'profiles/default.png') {
            try {
                if (Storage::disk('public')->exists($user->profile_photo)) {
                    Storage::disk('public')->delete($user->profile_photo);
                }
            }
            catch (\Exception $e) {
            // Fallback gracefully if disk error
            }
        }

        $user->update(['profile_photo' => 'profiles/default.png']);

        return response()->json([
            'ok' => true,
            'avatar_url' => asset('assets/img/default.png'),
            'message' => 'Profile photo removed successfully.',
        ]);
    }

    // ── Dashboard feed (all users, latest) ───────────────────────
    public function dashboardFeed(Request $request)
    {
        $followedIds = Auth::check()
            ? Auth::user()->following()->pluck('users.id')->toArray()
            : [];

        if (Auth::check()) {
            // Priority: Self + Followed
            $followedIds[] = Auth::id();
        }

        $posts = Post::with(['user', 'user.doctorApplication', 'likes', 'comments.user', 'comments.replies.user', 'media', 'resource', 'group', 'sharedPost.user', 'sharedPost.user.doctorApplication', 'sharedPost.media', 'sharedPost.resource', 'sharedPost.group'])
            ->when(!empty($followedIds), function ($q) use ($followedIds) {
                // Remove duplicates in case user is somehow following themselves
                $uniqueIds = array_unique($followedIds);
                $ids = implode(',', $uniqueIds);
                $q->orderByRaw("CASE WHEN user_id IN ($ids) THEN 0 ELSE 1 END");
            })
            ->latest()
            ->paginate(15);

        $formatted = $posts->getCollection()->map(function ($p) {
            return $this->formatPost($p);
        });

        return response()->json([
            'ok' => true,
            'posts' => $formatted,
            'has_more' => $posts->hasMorePages(),
        ]);
    }

    // ── Search users ─────────────────────────────────────────────
    public function searchUsers(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json(['ok' => true, 'users' => []]);
        }

        // Split query into words to match partial names
        $terms = explode(' ', trim($query));
        $usersQuery = User::query();

        foreach ($terms as $term) {
            $usersQuery->where(function ($q) use ($term) {
                $q->where('fname', 'like', "%{$term}%")
                    ->orWhere('lname', 'like', "%{$term}%")
                    ->orWhere('mname', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            });
        }

        $users = $usersQuery->take(8)->get()->map(function ($u) {
            return $this->formatUserSummary($u);
        });

        return response()->json([
            'ok' => true,
            'users' => $users
        ]);
    }

    // â”€â”€ Network: followers + following â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    public function network(User $user)
    {
        $followers = $user->followers()->get()->map(function ($u) {
            return $this->formatUserSummary($u);
        });
        $following = $user->following()->get()->map(function ($u) {
            return $this->formatUserSummary($u);
        });

        return response()->json([
            'ok' => true,
            'user_id' => $user->id,
            'followers' => $followers,
            'following' => $following,
            'followers_count' => $followers->count(),
            'following_count' => $following->count(),
        ]);
    }

    // ── Create post ───────────────────────────────────────────────
    public function storePost(Request $request)
    {
        $request->validate([
            'text_content' => ['required_without:media', 'nullable', 'max:5000'],
            'media.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,gif,mp4,mov', 'max:20480'],
            'mood' => ['nullable', 'string', 'max:64'],
            'hashtags' => ['nullable', 'string', 'max:500'],
            'group_id' => ['nullable', 'exists:groups,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $hasText = filled($request->text_content);
        $hasMedia = $request->hasFile('media');

        $postType = ($hasText && $hasMedia) ? 'mixed' : ($hasMedia ? 'media' : 'text');

        // Normalise hashtags: strip leading #, trim spaces, rejoin with comma
        $rawTags = $request->input('hashtags', '');
        $tags = array_values(array_filter(
            array_map(function ($t) {
            return ltrim(trim($t), '#');
        },
            preg_split('/[\s,]+/', $rawTags))
        ));
        $hashtagsStr = count($tags) ? implode(',', $tags) : null;

        $post = Post::create([
            'user_id' => $user->id,
            'group_id' => $request->input('group_id') ?: null,
            'post_type' => $postType,
            'text_content' => $request->text_content,
            'mood' => $request->input('mood') ?: null,
            'hashtags' => $hashtagsStr,
        ]);

        if ($hasMedia) {
            $order = 0;
            foreach ($request->file('media') as $file) {
                $mimeType = $file->getMimeType();
                $mediaType = (strpos($mimeType, 'video') === 0) ? 'video' : 'image';
                $filePath = $file->store('post_media', 'public');

                PostMedia::create([
                    'post_id' => $post->id,
                    'media_type' => $mediaType,
                    'path' => $filePath,
                    'mime_type' => $mimeType,
                    'size_bytes' => $file->getSize(),
                    'sort_order' => $order++,
                ]);
            }
        }

        $post->load(['user', 'likes', 'comments.user', 'media']);

        // Notifications: group post + mentions
        $actorName = $user->short_name ?: $user->full_name;
        if ($post->group_id) {
            $group = Group::with('creator')->find($post->group_id);
            if ($group && $group->creator_id && $group->creator_id !== $user->id) {
                NotificationService::create($group->creator, $user, 'group_post', [
                    'message' => $actorName . ' posted in your group ' . $group->name . '.',
                    'url' => $this->postUrl($post),
                    'group_id' => $group->id,
                    'post_id' => $post->id,
                ]);
            }
        }

        if ($post->text_content) {
            $mentionMsg = $post->group_id
                ? $actorName . ' mentioned you in a group post.'
                : $actorName . ' mentioned you in a post.';
            NotificationService::notifyMentions($post->text_content, $user, [
                'message' => $mentionMsg,
                'url' => $this->postUrl($post),
                'group_id' => $post->group_id,
                'post_id' => $post->id,
            ]);
        }

        return response()->json(['ok' => true, 'post' => $this->formatPost($post)]);
    }

    // ── Edit post ─────────────────────────────────────────────────
    public function updatePost(Request $request, Post $post)
    {
        abort_if($post->user_id !== Auth::id(), 403);

        $request->validate([
            'text_content' => ['nullable', 'max:5000'],
            'media.*' => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,gif,mp4,mov', 'max:20480'],
            'deleted_media' => ['nullable', 'array'],
            'deleted_media.*' => ['integer', 'exists:post_media,id'],
        ]);

        // Delete specified media
        if ($request->has('deleted_media') && is_array($request->deleted_media)) {
            $mediaToDelete = PostMedia::whereIn('id', $request->deleted_media)
                ->where('post_id', $post->id)
                ->get();
            foreach ($mediaToDelete as $m) {
                try {
                    Storage::disk('public')->delete($m->path);
                }
                catch (\Exception $e) {
                // Ignore missing files
                }
                $m->delete();
            }
        }

        // Add new media
        if ($request->hasFile('media')) {
            $order = $post->media()->max('sort_order') + 1;
            foreach ($request->file('media') as $file) {
                $mimeType = $file->getMimeType();
                $mediaType = (strpos($mimeType, 'video') === 0) ? 'video' : 'image';
                $filePath = $file->store('post_media', 'public');

                PostMedia::create([
                    'post_id' => $post->id,
                    'media_type' => $mediaType,
                    'path' => $filePath,
                    'mime_type' => $mimeType,
                    'size_bytes' => $file->getSize(),
                    'sort_order' => $order++,
                ]);
            }
        }

        $post->refresh();
        $hasText = filled($request->text_content);
        $hasMedia = $post->media()->exists();

        // Validation for empty post
        if (!$hasText && !$hasMedia) {
            return response()->json(['ok' => false, 'message' => 'Post cannot be empty.'], 400);
        }

        $postType = ($hasText && $hasMedia) ? 'mixed' : ($hasMedia ? 'media' : 'text');

        $post->update([
            'text_content' => $request->text_content,
            'post_type' => $postType,
        ]);

        $post->load(['user', 'likes', 'comments.user', 'media']);

        return response()->json([
            'ok' => true,
            'message' => 'Post updated.',
            'post' => $this->formatPost($post)
        ]);
    }

    // ── Delete post ───────────────────────────────────────────────
    public function destroyPost(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            $isGroupCreator = false;
            if ($post->group_id) {
                $isGroupCreator = Group::where('id', $post->group_id)
                    ->where('creator_id', Auth::id())
                    ->exists();
            }
            abort_if(!$isGroupCreator, 403);
        }

        foreach ($post->media as $media) {
            Storage::disk('public')->delete($media->path);
        }

        $post->delete();

        return response()->json(['ok' => true, 'message' => 'Post deleted.']);
    }

    // ── Follow / Unfollow user ─────────────────────────────────────────────
    public function toggleFollow(Request $request, User $user)
    {
        $me = Auth::user();
        if ($me->id === $user->id) {
            return response()->json(['ok' => false, 'message' => 'You cannot follow yourself.'], 400);
        }

        $action = $request->input('action'); // 'follow' | 'unfollow' | null (toggle)
        $exists = $me->following()->where('users.id', $user->id)->exists();
        $wasFollowing = $exists;
        if ($action === 'follow') {
            if (!$exists) {
                $me->following()->syncWithoutDetaching([$user->id]);
            }
        } elseif ($action === 'unfollow') {
            if ($exists) {
                $me->following()->detach($user->id);
            }
        } else {
            if ($exists) {
                $me->following()->detach($user->id);
            } else {
                $me->following()->attach($user->id);
            }
        }

        $following = $me->following()->where('users.id', $user->id)->exists();

        if (!$wasFollowing && $following) {
            $actorName = $me->short_name ?: $me->full_name;
            NotificationService::create($user, $me, 'follow', [
                'message' => $actorName . ' followed you.',
                'url' => route('profile.show', $me->id),
            ]);
        }

        return response()->json([
            'ok' => true,
            'following' => $following,
            'followers_count' => $user->followers()->count(),
            'following_count' => $user->following()->count(),
        ]);
    }

    // ── Toggle like ───────────────────────────────────────────────
    public function toggleLike(Request $request, Post $post)
    {
        $userId = Auth::id();
        $existing = PostLike::where('post_id', $post->id)->where('user_id', $userId)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        }
        else {
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => $userId,
                'reaction_type' => $request->input('reaction_type', 'heart'),
            ]);
            $liked = true;
        }

        return response()->json([
            'ok' => true,
            'liked' => $liked,
            'like_count' => $post->likes()->count(),
        ]);
    }

    // ── Toggle save ───────────────────────────────────────────────
    public function toggleSave(Post $post)
    {
        $user = Auth::user();
        $exists = $user->savedPosts()->where('post_id', $post->id)->exists();

        if ($exists) {
            $user->savedPosts()->detach($post->id);
            $saved = false;
        } else {
            $user->savedPosts()->attach($post->id);
            $saved = true;
        }

        return response()->json([
            'ok' => true,
            'saved' => $saved,
        ]);
    }

    // ── Add comment ───────────────────────────────────────────────
    public function storeComment(Request $request, Post $post)
    {
        $request->validate([
            'comment_text' => ['required', 'max:1000'],
            'parent_comment_id' => ['nullable', 'exists:post_comments,id'],
        ]);

        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_comment_id' => $request->parent_comment_id,
            'comment_text' => $request->comment_text,
        ]);

        $comment->load('user');

        // Notifications: post owner + group creator + mentions
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        $actorName = $actor->short_name ?: $actor->full_name;
        $postUrl = $this->postUrl($post, $comment->id);

        if ($post->user_id !== $actor->id) {
            NotificationService::create($post->user, $actor, 'post_comment', [
                'message' => $actorName . ' commented on your post.',
                'url' => $postUrl,
                'post_id' => $post->id,
                'comment_id' => $comment->id,
                'group_id' => $post->group_id,
            ]);
        }

        if ($post->group_id) {
            $group = Group::with('creator')->find($post->group_id);
            if ($group && $group->creator_id && $group->creator_id !== $actor->id && $group->creator_id !== $post->user_id) {
                NotificationService::create($group->creator, $actor, 'group_comment', [
                    'message' => $actorName . ' commented in your group ' . $group->name . '.',
                    'url' => $postUrl,
                    'group_id' => $group->id,
                    'post_id' => $post->id,
                    'comment_id' => $comment->id,
                ]);
            }
        }

        if ($comment->comment_text) {
            $mentionMsg = $post->group_id
                ? $actorName . ' mentioned you in a group comment.'
                : $actorName . ' mentioned you in a comment.';
            NotificationService::notifyMentions($comment->comment_text, $actor, [
                'message' => $mentionMsg,
                'url' => $postUrl,
                'group_id' => $post->group_id,
                'post_id' => $post->id,
                'comment_id' => $comment->id,
            ]);
        }

        return response()->json([
            'ok' => true,
            'comment' => $this->formatComment($comment),
        ]);
    }

    // ── Delete comment ────────────────────────────────────────────
    public function destroyComment(PostComment $comment)
    {
        abort_if($comment->user_id !== Auth::id(), 403);
        $comment->delete();

        return response()->json(['ok' => true, 'message' => 'Comment deleted.']);
    }

    public function sharePost(Request $request, Post $post)
    {
        $request->validate([
            'text_content' => 'nullable|string|max:5000',
        ]);

        // Always share the original/root post (not the reshare wrapper)
        // If the target is already a share, share its original.
        $origin = $post;
        $guard = 0;
        while ($origin->shared_post_id && $guard < 10) {
            $origin = Post::find($origin->shared_post_id) ?: $origin;
            $guard++;
            if ($origin->id === $post->id) {
                break;
            }
        }

        $shared = Post::create([
            'user_id' => Auth::id(),
            'shared_post_id' => $origin->id,
            'post_type' => 'post_share',
            'text_content' => $request->text_content,
        ]);

        // Notifications: shared post owner + mentions in share text
        /** @var \App\Models\User $actor */
        $actor = Auth::user();
        $actorName = $actor->short_name ?: $actor->full_name;
        if ($origin->user_id !== $actor->id) {
            NotificationService::create($origin->user, $actor, 'post_share', [
                'message' => $actorName . ' shared your post.',
                'url' => $this->postUrl($shared),
                'post_id' => $origin->id,
                'shared_post_id' => $shared->id,
            ]);
        }

        if ($request->text_content) {
            NotificationService::notifyMentions($request->text_content, $actor, [
                'message' => $actorName . ' mentioned you in a shared post.',
                'url' => $this->postUrl($shared),
                'post_id' => $shared->id,
            ]);
        }

        return response()->json(['ok' => true, 'message' => 'Post shared!', 'post' => $this->formatPost($shared)]);
    }

    public function updateCoverPhoto(Request $request)
    {
        $request->validate([
            'cover_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB limit
        ]);

        $user = Auth::user();

        if ($request->hasFile('cover_photo')) {
            $file = $request->file('cover_photo');
            $filename = time() . '_' . uniqid() . '.' . $file->extension();
            $path = $file->storeAs('covers', $filename, 'public');

            // Delete old cover if exists
            if ($user->cover_photo) {
                try {
                    if (Storage::disk('public')->exists($user->cover_photo)) {
                        Storage::disk('public')->delete($user->cover_photo);
                    }
                }
                catch (\Exception $e) {
                // Ignore
                }
            }

            $user->update(['cover_photo' => $path]);

            return response()->json([
                'ok' => true,
                'cover_url' => $user->cover_url,
                'message' => 'Cover photo updated successfully.',
            ]);
        }

        return response()->json(['ok' => false, 'message' => 'No image uploaded.'], 400);
    }

    public function deleteCoverPhoto()
    {
        $user = Auth::user();

        if ($user->cover_photo) {
            try {
                if (Storage::disk('public')->exists($user->cover_photo)) {
                    Storage::disk('public')->delete($user->cover_photo);
                }
            }
            catch (\Exception $e) {
            // Fallback gracefully
            }
        }

        $user->update(['cover_photo' => null]);

        return response()->json([
            'ok' => true,
            'cover_url' => $user->cover_url,
            'message' => 'Cover photo removed successfully.',
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────
    private function formatPost(Post $post): array
    {
        $mediaData = [];
        foreach ($post->media as $m) {
            $mediaData[] = [
                'id' => $m->id,
                'media_type' => $m->media_type,
                'url' => asset('storage/' . $m->path),
            ];
        }

        $commentsData = [];
        foreach ($post->comments as $c) {
            $commentsData[] = $this->formatComment($c);
        }

        return [
            'id' => $post->id,
            'text_content' => $post->text_content,
            'post_type' => $post->post_type,
            'mood' => $post->mood,
            'hashtags' => $post->hashtags_array, // array like ['anxiety','hope']
            'created_at' => $post->created_at->diffForHumans(),
            'like_count' => $post->likes->count(),
            'comment_count' => $post->allComments()->count(),
            'is_liked' => $post->isLikedBy(Auth::id()),
            'is_saved' => $post->isSavedBy(Auth::id()),
            'can_manage' => $post->user_id === Auth::id(),
            'user' => [
                'id' => $post->user->id,
                'role' => $post->user->role,
                'name' => $post->user->full_name,
                'username' => $post->user->username,
                'avatar_url' => $post->user->avatar_url,
                'profile_url' => route('profile.show', $post->user->id),
                'doctor_status' => $post->user->doctor_status,
                'professional_titles' => optional($post->user->doctorApplication)->professional_titles,
            ],
            'media' => $mediaData,
            'comments' => $commentsData,
            'resource' => $post->resource ? [
                'id' => $post->resource->id,
                'title' => $post->resource->title,
                'type' => $post->resource->type,
                'description' => $post->resource->description,
                'thumbnail_url' => $post->resource->thumbnail_url,
                'url' => route('resources.show', $post->resource->id),
            ] : null,
            'group' => $post->group ? [
                'id' => $post->group->id,
                'name' => $post->group->name,
                'description' => $post->group->description,
                'cover_url' => $post->group->cover_url,
                'url' => route('groups.show', $post->group->id),
            ] : null,
            'shared_post' => $post->sharedPost ? $this->formatPost($post->sharedPost) : null,
        ];
    }

    private function formatComment(PostComment $comment): array
    {
        $repliesData = [];
        if ($comment->replies) {
            foreach ($comment->replies as $r) {
                $repliesData[] = $this->formatComment($r);
            }
        }

        return [
            'id' => $comment->id,
            'post_id' => $comment->post_id,
            'comment_text' => $comment->comment_text,
            'created_at' => $comment->created_at->diffForHumans(),
            'can_delete' => $comment->user_id === Auth::id(),
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->full_name,
                'username' => $comment->user->username,
                'avatar_url' => $comment->user->avatar_url,
                'profile_url' => route('profile.show', $comment->user->id),
            ],
            'replies' => $repliesData,
        ];
    }

    private function formatUserSummary(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->short_name ?: $user->full_name,
            'username' => $user->username,
            'avatar_url' => $user->avatar_url,
            'profile_url' => route('profile.show', $user->id),
        ];
    }

    private function postUrl(Post $post, ?int $commentId = null): string
    {
        $base = route('posts.show', $post->id);

        $params = ['post_id' => $post->id];
        if ($commentId) {
            $params['comment_id'] = $commentId;
        }

        return $base . '?' . http_build_query($params);
    }

    public function updateAiRecommendation(Request $request)
    {
        $user = Auth::user();
        $user->update([
            'allow_ai_recommendation' => $request->has('allow_ai_recommendation')
        ]);
        
        return back()->with('success', 'AI Recommendation settings updated successfully.');
    }
}
