<?php

namespace App\Http\Controllers;

class FeaturePreviewController extends Controller
{
    /**
     * Preview page of a full-version module ('locked' entry in config/permissions.php): what it does + screenshots
     */
    public function __invoke(string $module)
    {
        $item = config("permissions.$module");
        abort_unless(is_array($item) && ($item['locked'] ?? false), 404);

        // Screenshots that exist in public/images/preview/ (missing ones are left out)
        $images = collect($item['preview']['images'] ?? [])
            ->filter(fn (string $file) => is_file(public_path("images/preview/$file")))
            ->values();

        return view('preview', [
            'module' => $module,
            'item' => $item,
            'images' => $images,
            'title' => $item['title'],
        ]);
    }
}
