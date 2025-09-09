<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class BrandingController extends Controller
{
    public function index()
    {
        // Get active logo and favicon
        $activeLogo = BrandingAsset::getActiveLogo();
        $activeFavicon = BrandingAsset::getActiveFavicon();
        
        // Get all logo and favicon versions with pagination
        $logos = BrandingAsset::ofType('logo')
            ->with('uploader')
            ->latest('version')
            ->paginate(10, ['*'], 'logos');
            
        $favicons = BrandingAsset::ofType('favicon')
            ->with('uploader')
            ->latest('version')
            ->paginate(10, ['*'], 'favicons');
        
        return view('admin.branding.index', compact(
            'activeLogo',
            'activeFavicon', 
            'logos',
            'favicons'
        ));
    }

    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:logo,favicon',
            'file' => 'required|image|mimes:png,jpg,jpeg,svg,ico|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $type = $request->input('type');
            
            // Generate unique filename
            $filename = $type . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            
            // Store file
            $path = $file->storeAs('branding/' . $type, $filename, 'public');
            $publicUrl = 'storage/' . $path;
            
            // Get image dimensions
            $dimensions = $this->getImageDimensions($file);
            
            // Get next version number
            $version = BrandingAsset::getNextVersion($type);
            
            // Create branding asset record
            $asset = BrandingAsset::create([
                'type' => $type,
                'name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_url' => $publicUrl,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'version' => $version,
                'uploaded_by' => auth()->id(),
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'upload_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]
            ]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . ' uploaded successfully',
                'asset' => $asset->load('uploader')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activate(Request $request, BrandingAsset $asset)
    {
        try {
            $asset->activate();
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($asset->type) . ' activated successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Activation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request, BrandingAsset $asset)
    {
        try {
            // Don't allow deletion of active assets
            if ($asset->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete active ' . $asset->type . '. Please activate another version first.'
                ], 422);
            }
            
            // Delete file from storage
            if (Storage::disk('public')->exists($asset->file_path)) {
                Storage::disk('public')->delete($asset->file_path);
            }
            
            // Delete database record
            $asset->delete();
            
            return response()->json([
                'success' => true,
                'message' => ucfirst($asset->type) . ' deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function preview(BrandingAsset $asset)
    {
        return response()->json([
            'success' => true,
            'asset' => $asset
        ]);
    }

    private function getImageDimensions($file)
    {
        try {
            if (class_exists('Intervention\Image\Facades\Image')) {
                $image = Image::make($file);
                return [
                    'width' => $image->width(),
                    'height' => $image->height()
                ];
            }
            
            // Fallback using getimagesize
            $imageInfo = getimagesize($file->getPathname());
            return [
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null
            ];
            
        } catch (\Exception $e) {
            return [
                'width' => null,
                'height' => null
            ];
        }
    }
}
