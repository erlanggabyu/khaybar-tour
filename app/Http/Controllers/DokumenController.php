<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDokumenRequest;
use App\Models\Dokumen;
use App\Models\Jamaah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DokumenController extends Controller
{
    public function index(Jamaah $jamaah): View
    {
        $this->authorizeJamaah($jamaah);
        $jamaah->load(['pendaftaran.paketUmroh', 'dokumens.verifier']);

        return view('dokumen.index', compact('jamaah'));
    }

    public function create(Jamaah $jamaah): View
    {
        $this->authorizeJamaah($jamaah);
        abort_unless($jamaah->pendaftaran->canUploadDocuments(), 422, 'Upload dokumen baru dapat dilakukan setelah pembayaran awal diverifikasi Valid.');
        $jamaah->load('dokumens');

        return view('dokumen.create', compact('jamaah'));
    }

    public function store(StoreDokumenRequest $request, Jamaah $jamaah): RedirectResponse
    {
        $this->authorizeJamaah($jamaah);
        abort_unless($jamaah->pendaftaran->canUploadDocuments(), 422, 'Upload dokumen baru dapat dilakukan setelah pembayaran awal diverifikasi Valid.');
        $validated = $request->validated();
        $existing = $jamaah->dokumens()->where('jenis_dokumen', $validated['jenis_dokumen'])->first();

        if ($existing && ! $existing->canBeReuploaded()) {
            return back()->withErrors(['jenis_dokumen' => 'Dokumen ini sedang diperiksa atau sudah valid sehingga tidak dapat diupload ulang.']);
        }

        $file = $request->file('file_dokumen');
        $randomName = Str::uuid().'.'.$file->extension();
        $newPath = $file->storeAs('dokumen', $randomName, 'local');
        if (! $newPath) {
            throw new RuntimeException('File dokumen gagal disimpan.');
        }

        try {
            DB::transaction(function () use ($file, $jamaah, $newPath, $validated): void {
                $lockedDocument = Dokumen::where('jamaah_id', $jamaah->id)
                    ->where('jenis_dokumen', $validated['jenis_dokumen'])
                    ->lockForUpdate()
                    ->first();

                if ($lockedDocument && ! $lockedDocument->canBeReuploaded()) {
                    abort(422, 'Dokumen ini sedang diperiksa atau sudah valid sehingga tidak dapat diupload ulang.');
                }

                $oldPath = $lockedDocument?->path_file;
                $data = [
                    'pendaftaran_id' => $jamaah->pendaftaran_id,
                    'jenis_dokumen' => $validated['jenis_dokumen'],
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $newPath,
                    'mime_type' => $file->getMimeType(),
                    'ukuran_file' => $file->getSize(),
                    'status_dokumen' => Dokumen::STATUS_MENUNGGU,
                    'catatan_admin' => null,
                    'verified_by' => null,
                    'verified_at' => null,
                ];

                if ($lockedDocument) {
                    $lockedDocument->update($data);
                    Storage::disk('local')->delete($oldPath);
                } else {
                    $jamaah->dokumens()->create($data);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newPath);
            throw $exception;
        }

        return redirect()->route('jamaah.dokumen.index', $jamaah)->with('success', 'Dokumen berhasil diupload dan menunggu pemeriksaan admin.');
    }

    public function destroy(Dokumen $dokumen): RedirectResponse
    {
        $this->authorizeJamaah($dokumen->jamaah);
        abort_unless($dokumen->canBeDeletedByCustomer(), 422, 'Dokumen yang sudah diverifikasi tidak dapat dihapus.');

        $jamaah = $dokumen->jamaah;
        $dokumen->delete();

        return redirect()->route('jamaah.dokumen.index', $jamaah)->with('success', 'Dokumen berhasil dihapus.');
    }

    public function download(Dokumen $dokumen): StreamedResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $dokumen->jamaah->pendaftaran->user_id === $user->id, 403);
        abort_unless(Storage::disk('local')->exists($dokumen->path_file), 404);

        return Storage::disk('local')->download($dokumen->path_file, $dokumen->nama_file);
    }

    private function authorizeJamaah(Jamaah $jamaah): void
    {
        abort_unless($jamaah->pendaftaran->user_id === Auth::id(), 403);
    }
}
