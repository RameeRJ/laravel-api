<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeneratePromptRequest;
use App\Http\Resources\ImageGenerationResource;
use App\Services\OpenAiService;
use Illuminate\Support\Str;

class ImageGenerationController extends Controller
{
    public function __construct(private OpenAiService $openAiService) {}

    public function index()
    {
        $user = request()->user();
        $imageGenerations = $user->imageGenerations()->paginate();

        return ImageGenerationResource::collection($imageGenerations);
    }

    /**
     * generate prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(GeneratePromptRequest $request)
    {
        $user = $request->user();
        $image = $request->file('image');
        $orginalName = $image->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9_.]/', '_', pathinfo($orginalName, PATHINFO_FILENAME));
        $extension = $image->getClientOriginalExtension();
        $safeFilename = $sanitizedName.'_'.Str::random(10).'.'.$extension;

        $imagePath = $image->storeAs('uploads/images', $safeFilename, 'public');

        $generatedPrompt = $this->openAiService->generatePromptFromImage($image);

        $imageGeneration = $user->imageGenerations()->create([
            'image_path' => $imagePath,
            'generated_prompt' => $generatedPrompt,
            'orginal_filename' => $orginalName,
            'file_size' => $image->getSize(),
            'mime_type' => $image->getMimeType(),
        ]);

        return new ImageGenerationResource($imageGeneration, 201);

    }
}
