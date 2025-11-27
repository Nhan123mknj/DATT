<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadMediaRequest;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function upload(UploadMediaRequest $request): JsonResponse
    {
        $type = $request->input('type', 'gallery');
        $folder = $request->input('folder');

        // Determine the model to attach media to
        $model = null;
        if ($request->has('model_type') && $request->has('model_id')) {
            $modelClass = $request->input('model_type');
            if (class_exists($modelClass)) {
                $model = $modelClass::find($request->input('model_id'));
            }
        }

        // If no model specified, attach to current user
        if (!$model && Auth::check()) {
            $model = Auth::user();
        }

        if (!$model) {
            return response()->json([
                'message' => 'Không tìm thấy model để gắn media',
            ], 400);
        }

        $files = $request->file('files');
        $mediaCollection = $this->mediaService->attachMultipleMedia($model, $files, $type, $folder);

        return response()->json([
            'message' => 'Upload thành công',
            'count' => $mediaCollection->count(),
            'media' => $mediaCollection,
        ], 200);
    }

    /**
     * Delete a media file
     * 
     * DELETE /api/media/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $media = Media::find($id);

        if (!$media) {
            return response()->json([
                'message' => 'Media không tồn tại',
            ], 404);
        }

        if (Auth::check() && $media->mediable_type === get_class(Auth::user())) {
            $mediable = $media->mediable;
            if ($mediable && $mediable->id !== Auth::id()) {
                return response()->json([
                    'message' => 'Bạn không có quyền xóa media này',
                ], 403);
            }
        }

        $this->mediaService->deleteMedia($media);

        return response()->json([
            'message' => 'Xóa media thành công',
        ], 200);
    }

    /**
     * Get media list for a model
     * 
     * GET /api/media?model_type=App\Models\User&model_id=1&type=avatar
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'type' => 'sometimes|string',
        ]);

        $modelClass = $request->input('model_type');
        if (!class_exists($modelClass)) {
            return response()->json([
                'message' => 'Model type không hợp lệ',
            ], 400);
        }

        $model = $modelClass::find($request->input('model_id'));
        if (!$model) {
            return response()->json([
                'message' => 'Model không tồn tại',
            ], 404);
        }

        $type = $request->input('type');
        $media = $this->mediaService->getMedia($model, $type);

        return response()->json([
            'media' => $media,
            'count' => $media->count(),
        ], 200);
    }
}
