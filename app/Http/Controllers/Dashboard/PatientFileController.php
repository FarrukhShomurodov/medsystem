<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Patient;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Documents attached to a patient. Routes bind the file through the patient (scopeBindings),
 * so a file id that belongs to another patient is a 404.
 */
class PatientFileController extends Controller
{
    /** Types the browser may render in place; anything else (1.x uploads included) is downloaded. */
    private const array INLINE_MIME_TYPES = ['application/pdf', 'image/jpeg', 'image/png'];

    public function show(Patient $patient, File $file): StreamedResponse
    {
        $disk = Storage::disk(File::DISK);

        abort_unless($disk->exists($file->url), 404);

        $disposition = in_array($disk->mimeType($file->url), self::INLINE_MIME_TYPES, true) ? 'inline' : 'attachment';

        return $disk->response($file->url, $file->name, disposition: $disposition);
    }

    public function destroy(Patient $patient, File $file): Response
    {
        // The row goes first: if its delete fails, the document is still on the disk it points to.
        $file->delete();
        Storage::disk(File::DISK)->delete($file->url);

        return response()->noContent();
    }
}
