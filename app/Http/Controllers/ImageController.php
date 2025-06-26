<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Models\Image;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Get all images.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $images = Image::select("name")->paginate(10);
        return response()->json(["images" => $images], 200);
    }

    /**
     * Save new image.
     *
     * @param \App\Http\Requests\StoreImageRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreImageRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $name = $validated["name"];
            $raw_image = $request->file("image");
            $image_info = getimagesize($raw_image);
            $image = new Image();

            if (Image::where("name", $name)->exists())
                return response()->json(["message" => "This name is already exists!"], 422);

            DB::transaction(function () use ($name, $raw_image, $validated, $image_info, $image): void {
                $image->fill([
                    "name" => $name,
                    "user_id" => $validated["user_id"],
                    "size" => $raw_image->getSize(),
                    "width" => $image_info[0],
                    "height" => $image_info[1],
                    "format" => $raw_image->getClientOriginalExtension()
                ])->save();

                Storage::disk("s3")->put("images/$name", $raw_image);
            });

            return response()->json([
                "image" => $image,
                "message" => "Image saved successfully!"
            ], 201);
        } catch (Exception $e) {
            Log::error("Error saving image: " . $e->getMessage());
            Storage::disk("s3")->delete("images/$name");
            return response()->json([
                "message" => "An unexpected error occurred while saving the image!"
            ], 500);
        }
    }

    /**
     * Get specified image.
     *
     * @param \App\Models\Image $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Image $image): JsonResponse
    {
        return response()->json(["image" => $image], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImageRequest $request, Image $image): JsonResponse
    {
        try {
            $validated = $request->validated();
            $raw_image = ($request->hasFile("image")) ? $request->file("image") : null;
            $old_image = Storage::disk("s3")->get("images/$image->name");
            $isNameValid = Image::where("name", $validated["name"])
                ->where("id", "!=", $image->id)
                ->exists();
            $old_name = $image->name;
            $updated_data = [
                "name" => $validated["name"],
                "user_id" => $validated["user_id"],
            ];

            if ($isNameValid) return response()->json(["message" => "This name is already exists!"], 422);

            if (isset($raw_image)) {
                $image_info = getimagesize($raw_image);
                Storage::disk("s3")->delete("images/$image->name");
                Storage::disk("s3")->put("images/" . $validated["name"], $raw_image);

                $updated_data = array_merge($updated_data, [
                    "size" => $raw_image->getSize(),
                    "width" => $image_info[0],
                    "height" => $image_info[1],
                    "format" => $raw_image->getClientOriginalExtension()
                ]);
            }

            DB::transaction(fn() => $image->update($updated_data));

            return response()->json([
                "image" => $image,
                "message" => "Image updated successfully!"
            ], 200);
        } catch (Exception $e) {
            Log::error("Error updated image: " . $e->getMessage());
            Storage::disk("s3")->delete("images/" . $validated["name"]);
            Storage::disk("s3")->put("images/$old_name", file_get_contents($old_image));
            return response()->json([
                "message" => "An unexpected error occurred while updated the image!"
            ], 500);
        }
    }

    /**
     * Delete image.
     *
     * @param \App\Models\Image $image
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Image $image): JsonResponse
    {
        try {
            DB::transaction(fn() => $image->delete());
            return response()->json(["message" => "Image deleted successfully!"], 200);
        } catch (Exception $e) {
            Log::error("Error deleting image: " . $e->getMessage());
            return response()->json([
                "message" => "An unexpected error occurred while deleting the image!"
            ], 500);
        }
    }
}
