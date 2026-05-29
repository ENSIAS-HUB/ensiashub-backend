<?php

namespace App\Http\Controllers;

use App\Models\AdhesionGroup;
use App\Models\PostMedia;
use App\Models\Publication;
use App\Models\Group;
use App\Http\Resources\PublicationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PublicationController extends Controller
{
    // Ã¢â€â‚¬Ã¢â€â‚¬ Shared eager-load builder Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬
    private function feedQuery(Request $request)
    {
        return Publication::with(['user', 'groupe', 'postMedia'])
            ->withCount([
                'reactions',
                'commentaires as comments_count',
            ])
            ->when(
                auth()->check(),
                fn ($q) => $q->withExists([
                    'reactions as user_reacted' => fn ($q) => $q->whereRaw('user_id = ?::uuid', [auth()->id()]),
                    'savedItems as user_saved'  => fn ($q) => $q->whereRaw('user_id = ?::uuid', [auth()->id()]),
                ])
            );
    }

    /**
     * GET /api/feed Ã¢â‚¬â€ Global feed (no group, or visibility=global)
     */
    public function feed(Request $request)
    {
        // Always include club posts so all users see club content in the feed
        $clubGroupIds = Group::where('categorie', 'Club')
            ->orWhereIn('slug', ['eitc', 'insec'])
            ->pluck('id');

        $paginator = $this->feedQuery($request)
            ->where(function ($q) use ($clubGroupIds) {
                $q->whereNull('groupe_id')
                  ->orWhere('visibility', 'global')
                  ->orWhereIn('groupe_id', $clubGroupIds);
            })
            ->where('statutValidation', 'Valide')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(
            $paginator->through(fn ($pub) => (new PublicationResource($pub))->toArray($request))
        );
    }

    /**
     * GET /api/groups/{groupId}/feed Ã¢â‚¬â€ Group feed
     */
    public function groupFeed(Request $request, string $groupId)
    {
        $paginator = $this->feedQuery($request)
            ->where('groupe_id', $groupId)
            ->where('statutValidation', 'Valide')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(
            $paginator->through(fn ($pub) => (new PublicationResource($pub))->toArray($request))
        );
    }

    /**
     * GET /api/publications Ã¢â‚¬â€ All publications (admin / legacy usage)
     */
    public function index(Request $request)
    {
        $groupId = $request->input('group_id') ?? $request->input('groupe_id');

        $paginator = $this->feedQuery($request)
            ->when($groupId, fn ($q) => $q->where('groupe_id', $groupId))
            ->when($request->has('statut'), fn ($q) => $q->where('statutValidation', $request->statut))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json(
            $paginator->through(fn ($pub) => (new PublicationResource($pub))->toArray($request))
        );
    }

    /**
     * POST /api/publications Ã¢â‚¬â€ Create a publication (text + optional media files)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'    => 'nullable|string|max:5000',
            'contenu'    => 'nullable|string|max:5000',
            'group_id'   => 'nullable|exists:groups,id',
            'groupe_id'  => 'nullable|exists:groups,id',
            'visibility' => 'nullable|in:global,group',
            'media'      => 'nullable|array|max:10',
            'media.*'    => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mkv|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $contenu  = $request->input('content') ?? $request->input('contenu');
        $groupId  = $request->input('group_id') ?? $request->input('groupe_id');

        if (empty($contenu) && !$request->hasFile('media')) {
            return response()->json([
                'success' => false,
                'message' => 'Le post doit contenir du texte ou au moins un mÃƒÂ©dia.',
            ], 422);
        }

        $visibility = $groupId ? 'group' : ($request->input('visibility', 'global'));

        $publication = Publication::create([
            'contenu'          => $contenu,
            'user_id'          => Auth::id(),
            'groupe_id'        => $groupId,
            'visibility'       => $visibility,
            'statutValidation' => 'Valide',   // auto-approve
            'publishedAt'      => now(),
        ]);

        // Handle media uploads
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $index => $file) {
                $mime    = $file->getMimeType() ?? '';
                $type    = str_starts_with($mime, 'video/') ? 'video' : 'image';
                $path    = $file->store("posts/{$publication->id}", 'public');
                $url     = Storage::disk('public')->url($path);

                PostMedia::create([
                    'publication_id' => $publication->id,
                    'url'            => $url,
                    'type'           => $type,
                    'order'          => $index,
                ]);
            }
        }

        $publication->load(['user', 'groupe', 'postMedia']);

        return response()->json([
            'success' => true,
            'message' => 'Publication crÃƒÂ©ÃƒÂ©e avec succÃƒÂ¨s',
            'data'    => (new PublicationResource($publication))->toArray($request),
        ], 201);
    }

    /**
     * GET /api/publications/{id}
     */
    public function show(string $id)
    {
        $publication = Publication::with(['user', 'groupe', 'postMedia', 'commentaires', 'reactions'])->find($id);

        if (!$publication) {
            return response()->json(['success' => false, 'message' => 'Publication non trouvÃƒÂ©e'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => (new PublicationResource($publication))->toArray(request()),
        ]);
    }

    /**
     * PUT/PATCH /api/publications/{id}
     */
    public function update(Request $request, string $id)
    {
        $publication = Publication::find($id);

        if (!$publication) {
            return response()->json(['success' => false, 'message' => 'Publication non trouvÃƒÂ©e'], 404);
        }

        if ($publication->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Non autorisÃƒÂ©'], 403);
        }

        $publication->update($request->only(['contenu', 'typeMedia']));

        return response()->json([
            'success' => true,
            'message' => 'Publication mise ÃƒÂ  jour',
            'data'    => (new PublicationResource($publication))->toArray($request),
        ]);
    }

    /**
     * DELETE /api/publications/{id}
     */
    public function destroy(string $id)
    {
        $publication = Publication::find($id);

        if (!$publication) {
            return response()->json(['success' => false, 'message' => 'Publication non trouvÃƒÂ©e'], 404);
        }

        $user        = Auth::user();
        $isMod       = $publication->groupe && $publication->groupe->estModerateur($user);
        $isAdminRole = $user->isAdminOrAbove();

        if ($publication->user_id !== $user->id && !$isMod && !$isAdminRole) {
            return response()->json(['success' => false, 'message' => 'Non autorisÃƒÂ©'], 403);
        }

        // Delete uploaded media files
        $publication->postMedia->each(function (PostMedia $m) {
            $path = str_replace(Storage::disk('public')->url(''), '', $m->url);
            Storage::disk('public')->delete($path);
        });

        $publication->delete();

        return response()->json(['success' => true, 'message' => 'Publication supprimÃƒÂ©e']);
    }

    /**
     * POST /api/publications/{id}/publier Ã¢â‚¬â€ Publish (admin / mod action)
     */
    public function publier(string $id)
    {
        $publication = Publication::find($id);

        if (!$publication) {
            return response()->json(['success' => false, 'message' => 'Publication non trouvÃƒÂ©e'], 404);
        }

        $publication->update([
            'statutValidation' => 'Valide',
            'publishedAt'      => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Publication publiÃƒÂ©e']);
    }
}
